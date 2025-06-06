<?php
// lease.php

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

// Fetch apartments for dropdown
$apartments = [];
$sql = "SELECT id, name FROM Apartments WHERE is_active = 1 ORDER BY name ASC";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die("Error fetching apartments: " . print_r(sqlsrv_errors(), true));
}
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $apartments[] = $row;
}

// Fetch all units grouped by apartment for JS use
$unitsByApartment = [];
$unitSql = "SELECT u.id, u.name, a.name as apartmentName FROM units u JOIN apartments a ON u.apartment_id = a.id WHERE a.is_active = 1 ORDER BY a.name, u.name";
$unitStmt = sqlsrv_query($conn, $unitSql);
if ($unitStmt !== false) {
    while ($unitRow = sqlsrv_fetch_array($unitStmt, SQLSRV_FETCH_ASSOC)) {
        $aptName = $unitRow['apartmentName'];
        if (!isset($unitsByApartment[$aptName])) {
            $unitsByApartment[$aptName] = [];
        }
        $unitsByApartment[$aptName][] = $unitRow['name'];
    }
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

<div class="filters">
    <select id="filter-apartment">
        <option value="">All Apartments</option>
        <?php foreach($apartments as $apartment): ?>
            <option value="<?= htmlspecialchars($apartment['name']) ?>"><?= htmlspecialchars($apartment['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <select id="filter-unit" disabled>
        <option value="">All Units</option>
    </select>
</div>

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
        // Fetch all lease data joined with apartment and unit names
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
            ORDER BY a.name, u.name, l.tenantName
        ";
        $leaseStmt = sqlsrv_query($conn, $leaseSql);
        if ($leaseStmt === false) {
            echo "<tr><td colspan='7'>Error loading leases: " . print_r(sqlsrv_errors(), true) . "</td></tr>";
        } else {
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
        }
        ?>
    </tbody>
</table>

<!-- Payment Modal -->
<div id="payment-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closePaymentModal()">&times;</span>
        <h2>Submit Payment</h2>
        <form method="POST" action="submit_payment.php" id="payment-form">
            <!-- Hidden apartment input to ensure value is sent -->
            <input type="hidden" name="apartment" id="modal-apartment-hidden" />

            <label for="unit">Unit</label>
            <input type="text" id="modal-unit" name="unit" readonly />

            <label for="tenant">Tenant</label>
            <input type="text" id="modal-tenant" name="tenant" readonly />

            <label for="payment_date">Payment Date</label>
            <input type="date" id="payment_date" name="payment_date" required />

            <label for="amount">Amount</label>
            <input type="number" id="amount" name="amount" step="0.01" min="0" required />

            <label for="payment_method">Payment Method</label>
            <select id="payment_method" name="payment_method" required>
                <option value="" disabled selected>Select Method</option>
                <option value="Cash">Cash</option>
                <option value="Gcash">Gcash</option>
                <option value="Bank Transfer">Bank Transfer</option>
            </select>

            <button type="submit">Submit Payment</button>
        </form>
    </div>
</div>

<script>
// Store units by apartment from PHP
const unitsByApartment = <?= json_encode($unitsByApartment); ?>;

// Elements
const filterApartment = document.getElementById('filter-apartment');
const filterUnit = document.getElementById('filter-unit');
const leaseTable = document.getElementById('lease-table').getElementsByTagName('tbody')[0];

filterApartment.addEventListener('change', () => {
    const selectedApt = filterApartment.value;
    filterUnit.innerHTML = '<option value="">All Units</option>';

    if (selectedApt && unitsByApartment[selectedApt]) {
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

        const show = 
            (aptFilter === '' || apt === aptFilter) &&
            (unitFilter === '' || unit === unitFilter);
        row.style.display = show ? '' : 'none';
    });
}

function openPaymentModal(apartment, unit, tenant) {
    document.getElementById('modal-apartment-hidden').value = apartment;
    document.getElementById('modal-unit').value = unit;
    document.getElementById('modal-tenant').value = tenant;
    document.getElementById('payment_date').value = new Date().toISOString().slice(0, 10); // default today
    document.getElementById('amount').value = '';
    document.getElementById('payment_method').selectedIndex = 0;

    document.getElementById('payment-modal').style.display = 'flex';
}

function closePaymentModal() {
    document.getElementById('payment-modal').style.display = 'none';
}

// Close modal if clicked outside content
window.onclick = function(event) {
    const modal = document.getElementById('payment-modal');
    if (event.target === modal) {
        closePaymentModal();
    }
};

// On page load, disable unit filter if no apartment selected
if (!filterApartment.value) {
    filterUnit.disabled = true;
}
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
