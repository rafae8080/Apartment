<?php
// lease archive.php

$serverName = "LAPTOP-0QN98R6Q";
$connectionOptions = [
    "Database" => "LeaseManagementDB",
    "Uid" => "",
    "PWD" => ""
];
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

$filterApartmentName = $_GET['filter_apartment'] ?? '';

$apartments = [];
$sql = "SELECT id, name, is_active FROM Apartments ORDER BY is_active DESC, name ASC";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die("Error fetching apartments: " . print_r(sqlsrv_errors(), true));
}
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $apartments[] = $row;
}

$unitsByApartment = [];
$unitsArchivedByApartment = [];

$unitSql = "SELECT u.id, u.name, a.name as apartmentName, a.is_active as apartment_active, u.is_active as unit_active FROM units u JOIN apartments a ON u.apartment_id = a.id ORDER BY a.is_active DESC, a.name, u.name";
$unitStmt = sqlsrv_query($conn, $unitSql);
if ($unitStmt !== false) {
    while ($unitRow = sqlsrv_fetch_array($unitStmt, SQLSRV_FETCH_ASSOC)) {
        $aptName = $unitRow['apartmentName'];
        $apartmentActive = $unitRow['apartment_active'];
        $unitActive = $unitRow['unit_active'];
        if ($apartmentActive == 1 && $unitActive == 1) {
            if (!isset($unitsByApartment[$aptName])) {
                $unitsByApartment[$aptName] = [];
            }
            $unitsByApartment[$aptName][] = $unitRow['name'];
        } else {
            if (!isset($unitsArchivedByApartment[$aptName])) {
                $unitsArchivedByApartment[$aptName] = [];
            }
            $unitsArchivedByApartment[$aptName][] = $unitRow['name'];
        }
    }
}

$whereClauses = [];
if ($filterApartmentName === '__archived__') {
    $whereClauses[] = "(a.is_active = 0 OR u.is_active = 0)";
} elseif ($filterApartmentName === '' || $filterApartmentName === null) {
    $whereClauses[] = "a.is_active = 1 AND u.is_active = 1";
} else {
    $safeName = str_replace("'", "''", $filterApartmentName);
    $whereClauses[] = "a.name = '$safeName' AND u.is_active = 1";
}

$whereSql = '';
if (count($whereClauses) > 0) {
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
}

$leaseSql = "
    SELECT 
        a.name AS apartmentName,
        u.name AS unitName,
        l.tenantName,
        l.contactNumber,
        l.moveIn,
        l.moveOut
    FROM leases l
    JOIN apartments a ON l.apartment_id = a.id
    JOIN units u ON l.unit_id = u.id
    $whereSql
    ORDER BY a.is_active DESC, a.name, u.name, l.tenantName
";

$leaseStmt = sqlsrv_query($conn, $leaseSql);
if ($leaseStmt === false) {
    die("Error loading leases: " . print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Lease Overview</title>
<link rel="stylesheet" href="css/lease.css" />
<link rel="stylesheet" href="css/navbar.css" />
<link rel="stylesheet" href="css/leases.css" />
</head>
<body>

<nav class="navbar">
    <div class="logo"><a href="index.php">RM</a></div>
    <div class="nav-links">
        <a href="index.php">Apartments</a>
        <a href="lease.php" class="active">Lease</a>
        <a href="transaction.php">Transactions</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<h1>Lease Overview</h1>

<form method="GET" action="lease.php" class="filters">
    <select id="filter-apartment" name="filter_apartment" onchange="this.form.submit()">
        <option value="" <?= $filterApartmentName === '' ? 'selected' : '' ?>>All Active Apartments</option>
        <option value="__archived__" <?= $filterApartmentName === '__archived__' ? 'selected' : '' ?>>Archives</option>
<?php foreach ($apartments as $apartment): ?>
    <?php 
        $name = htmlspecialchars($apartment['name']);
        $selected = ($filterApartmentName === $apartment['name']) ? "selected" : "";
        // Only include active apartment names in dropdown
        if ($apartment['is_active'] == 1) {
            echo "<option value=\"$name\" $selected>$name</option>";
        }
    ?>
<?php endforeach; ?>

    </select>
    <select id="filter-unit" name="filter_unit" disabled>
        <option value="">All Units</option>
    </select>
</form>

<table id="lease-table">
    <thead>
        <tr>
            <th>Apartment</th>
            <th>Unit</th>
            <th>Tenant Name</th>
            <th>Contact Number</th>
            <th>Move-in Date</th>
            <th>Move-out Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
            while ($lease = sqlsrv_fetch_array($leaseStmt, SQLSRV_FETCH_ASSOC)) {
                $apt = htmlspecialchars($lease['apartmentName']);
                $unit = htmlspecialchars($lease['unitName']);
                $tenant = htmlspecialchars($lease['tenantName']);
                $contact = htmlspecialchars($lease['contactNumber']);
                $moveIn = $lease['moveIn'] ? $lease['moveIn']->format('Y-m-d') : '';
                $moveOut = $lease['moveOut'] ? $lease['moveOut']->format('Y-m-d') : '';

                echo "<tr data-apartment='$apt' data-unit='$unit'>";
                echo "<td>$apt</td>";
                echo "<td>$unit</td>";
                echo "<td>$tenant</td>";
                echo "<td>$contact</td>";
                echo "<td>$moveIn</td>";
                echo "<td>$moveOut</td>";
                echo "<td><button class='action-btn' onclick=\"openPaymentModal('$apt', '$unit', '$tenant')\">Payment</button></td>";
                echo "</tr>";
            }
        ?>
    </tbody>
</table>

<!-- Payment Modal -->
<div id="payment-modal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; justify-content:center; align-items:center; background-color:rgba(0,0,0,0.5); z-index:1000;">
    <div class="modal-content" style="background:white; padding:20px; border-radius:8px; width:300px; position:relative;">
        <span class="close-btn" onclick="closePaymentModal()" style="position:absolute; top:10px; right:15px; font-size:20px; cursor:pointer;">&times;</span>
        <h2>Submit Payment</h2>
        <form method="POST" action="submit_payment.php" id="payment-form">
            <input type="hidden" name="apartment" id="modal-apartment-hidden" />

            <label for="unit">Unit</label>
            <input type="text" id="modal-unit" name="unit" readonly style="width:100%;" />

            <label for="tenant">Tenant</label>
            <input type="text" id="modal-tenant" name="tenant" readonly style="width:100%;" />

            <label for="payment_date">Payment Date</label>
            <input type="date" id="payment_date" name="payment_date" required style="width:100%;" />

            <label for="amount">Amount</label>
            <input type="number" id="amount" name="amount" step="0.01" min="0" required style="width:100%;" />

            <label for="payment_method">Payment Method</label>
            <select id="payment_method" name="payment_method" required style="width:100%;">
                <option value="" disabled selected>Select Method</option>
                <option value="Cash">Cash</option>
                <option value="Gcash">Gcash</option>
                <option value="Bank Transfer">Bank Transfer</option>
            </select>

            <button type="submit" style="margin-top:10px; width:100%;">Submit Payment</button>
        </form>
    </div>
</div>

<script>
const unitsByApartment = <?= json_encode($unitsByApartment); ?>;
const unitsArchivedByApartment = <?= json_encode($unitsArchivedByApartment); ?>;
const filterApartment = document.getElementById('filter-apartment');
const filterUnit = document.getElementById('filter-unit');
const leaseTable = document.getElementById('lease-table').getElementsByTagName('tbody')[0];

function updateUnitDropdown() {
    const selectedApt = filterApartment.value;
    filterUnit.innerHTML = '<option value="">All Units</option>';

    if (selectedApt === '__archived__') {
        for (const apt in unitsArchivedByApartment) {
            unitsArchivedByApartment[apt].forEach(unit => {
                const option = document.createElement('option');
                option.value = unit;
                option.textContent = `${apt} - ${unit}`;
                filterUnit.appendChild(option);
            });
        }
        filterUnit.disabled = false;
    } else if (unitsByApartment[selectedApt]) {
        unitsByApartment[selectedApt].forEach(unit => {
            const option = document.createElement('option');
            option.value = unit;
            option.textContent = unit;
            filterUnit.appendChild(option);
        });
        filterUnit.disabled = false;
    } else {
        filterUnit.disabled = true;
    }
}

filterApartment.addEventListener('change', () => {
    updateUnitDropdown();
    filterLeaseTable();
});

filterUnit.addEventListener('change', () => {
    filterLeaseTable();
});

function filterLeaseTable() {
    const aptFilter = filterApartment.value.toLowerCase();
    const unitFilter = filterUnit.value.toLowerCase();

    Array.from(leaseTable.rows).forEach(row => {
        const apt = row.getAttribute('data-apartment').toLowerCase();
        const unit = row.getAttribute('data-unit').toLowerCase();

        let show = false;

        if (aptFilter === '') {
            show = true;
        } else if (aptFilter === '__archived__') {
            show = true;
        } else {
            show = apt === aptFilter;
        }

        if (show) {
            if (unitFilter === '' || unit === unitFilter) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        } else {
            row.style.display = 'none';
        }
    });
}

updateUnitDropdown();
filterLeaseTable();

function openPaymentModal(apartment, unit, tenant) {
    const modal = document.getElementById('payment-modal');
    if (!modal) {
        alert("Payment modal element not found in HTML!");
        return;
    }
    document.getElementById('modal-apartment-hidden').value = apartment;
    document.getElementById('modal-unit').value = unit;
    document.getElementById('modal-tenant').value = tenant;
    document.getElementById('payment_date').value = new Date().toISOString().slice(0, 10);
    document.getElementById('amount').value = '';
    document.getElementById('payment_method').selectedIndex = 0;
    modal.style.display = 'flex';
}

function closePaymentModal() {
    document.getElementById('payment-modal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('payment-modal');
    if (event.target === modal) {
        closePaymentModal();
    }
};
</script>
<?php if (isset($_GET['payment_success'])): ?>
<script>
    <?php if ($_GET['payment_success'] == '1'): ?>
        alert("Payment submitted successfully! Transaction ID: <?= htmlspecialchars($_GET['transaction_id'] ?? '') ?>");
    <?php else: ?>
        alert("Payment failed. Please try again.");
    <?php endif; ?>
</script>
<?php endif; ?>
</body>
</html>
