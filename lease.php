<?php
// lease.php - Fixed version

$serverName = "DESKTOP-F68QS4T";
$connectionOptions = [
    "Database" => "LeaseManagementDB",
    "Uid" => "",
    "PWD" => ""
];
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) die(print_r(sqlsrv_errors(), true));
require_once 'session_init.php';
if (!isset($_SESSION['userEmail'])) {
    header("Location: login.php");
    exit();
}

// Get filter from URL
$filterApartmentName = $_GET['filter_apartment'] ?? '';

// Fetch all apartments (only active ones for dropdown)
$apartments = [];
$sql = "SELECT id, name FROM Apartments WHERE is_active = 1 ORDER BY name ASC";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die("Error fetching apartments: " . print_r(sqlsrv_errors(), true));
}
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $apartments[] = $row;
}

// Fetch all units grouped by apartment
$unitsByApartment = [];
$unitSql = "SELECT u.name, a.name as apartmentName 
            FROM units u 
            JOIN apartments a ON u.apartment_id = a.id 
            WHERE a.is_active = 1
            ORDER BY a.name, u.name";
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

// Build WHERE clause for leases
$where = "";
if ($filterApartmentName === '__archived__') {
    $where = "WHERE l.is_active = 0";
} elseif (!empty($filterApartmentName)) {
    $where = "WHERE a.name = '$filterApartmentName' AND l.is_active = 1";
} else {
    $where = "WHERE l.is_active = 1";
}

// Fetch leases
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
    $where
    ORDER BY a.name, u.name, l.tenantName
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
<link rel="stylesheet" href="css/transaction.css" />

</head>
<body>

<nav class="navbar">
    <div class="logo"><a href="index.php">RM</a></div>
    <div class="nav-links">
        <a href="index.php">Apartments</a>
        <a href="lease.php" class="active">Lease</a>
        <a href="transaction.php">Transactions</a>
        <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
    </div>
</nav>
    <div class="container">

<h1>Lease Overview</h1>

<form method="GET" action="lease.php" class="filters">
    <select id="filter-apartment" name="filter_apartment" onchange="this.form.submit()">
        <option value="" <?= empty($filterApartmentName) ? 'selected' : '' ?>>All Active Leases</option>
        <option value="__archived__" <?= $filterApartmentName === '__archived__' ? 'selected' : '' ?>>Archived Leases</option>
        <?php foreach ($apartments as $apartment): ?>
            <option value="<?= $apartment['name'] ?>" 
                <?= $filterApartmentName === $apartment['name'] ? 'selected' : '' ?>>
                <?= $apartment['name'] ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select id="filter-unit" disabled>
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
        <?php while ($lease = sqlsrv_fetch_array($leaseStmt, SQLSRV_FETCH_ASSOC)): ?>
            <?php
                $apt = $lease['apartmentName'];
                $unit = $lease['unitName'];
                $tenant = $lease['tenantName'];
                $contact = $lease['contactNumber'];
                $moveIn = $lease['moveIn'] ? $lease['moveIn']->format('Y-m-d') : '';
                $moveOut = $lease['moveOut'] ? $lease['moveOut']->format('Y-m-d') : '';
            ?>
            <tr data-apartment="<?= $apt ?>" data-unit="<?= $unit ?>">
                <td><?= $apt ?></td>
                <td><?= $unit ?></td>
                <td><?= $tenant ?></td>
                <td><?= $contact ?></td>
                <td><?= $moveIn ?></td>
                <td><?= $moveOut ?></td>
                <td>
                    <?php if ($filterApartmentName !== '__archived__'): ?>
                        <button class='action-btn' onclick="openPaymentModal('<?= $apt ?>', '<?= $unit ?>', '<?= $tenant ?>')">Payment</button>
                        <button class='action-btn terminate' onclick="if(confirm('Terminate this lease?')) window.location='?terminate=1&apt=<?= $apt ?>&unit=<?= $unit ?>&tenant=<?= $tenant ?>'">Terminate</button>
                    <?php else: ?>
                        <span><button class='action-btn delete' onclick="if(confirm('Delete this lease?')) window.location='?delete=1&apt=<?= $apt ?>&unit=<?= $unit ?>&tenant=<?= $tenant ?>'">Delete</button></span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Payment Modal -->
<div id="payment-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closePaymentModal()">&times;</span>
        <h2>Submit Payment</h2>
        <form method="POST" action="submit_payment.php">
            <input type="hidden" name="apartment" id="modal-apartment-hidden" />
            <label>Unit <input type="text" id="modal-unit" name="unit" readonly /></label>
            <label>Tenant <input type="text" id="modal-tenant" name="tenant" readonly /></label>
            <label>Payment Date <input type="date" id="payment_date" name="payment_date" required /></label>
            <label>Amount <input type="number" id="amount" name="amount" required /></label>
            <label>Payment Method 
                <select id="payment_method" name="payment_method" required>
                    <option value="Cash">Cash</option>
                    <option value="Gcash">Gcash</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                </select>
            </label>
            <button type="submit">Submit Payment</button>
        </form>
    </div>
</div>

<script>
// Simple unit filter
const unitsByApartment = <?= json_encode($unitsByApartment) ?>;
const filterApartment = document.getElementById('filter-apartment');
const filterUnit = document.getElementById('filter-unit');

filterApartment.addEventListener('change', function() {
    const selectedApt = this.value;
    filterUnit.innerHTML = '<option value="">All Units</option>';
    
    if (unitsByApartment[selectedApt]) {
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
});

// Simple payment modal functions
function openPaymentModal(apartment, unit, tenant) {
    document.getElementById('modal-apartment-hidden').value = apartment;
    document.getElementById('modal-unit').value = unit;
    document.getElementById('modal-tenant').value = tenant;
    document.getElementById('payment_date').value = new Date().toISOString().slice(0, 10);
    document.getElementById('payment-modal').style.display = 'flex';
}

function closePaymentModal() {
    document.getElementById('payment-modal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target === document.getElementById('payment-modal')) {
        closePaymentModal();
    }
};
</script>

<?php
// Handle terminate operation

if (isset($_GET['terminate']) && $_GET['terminate'] == '1') {
    $apt = $_GET['apt'] ?? '';
    $unit = $_GET['unit'] ?? '';
    $tenant = $_GET['tenant'] ?? '';
    
    if (!empty($apt) && !empty($unit) && !empty($tenant)) {
        $sql = "UPDATE l SET l.is_active = 0 
                FROM leases l
                JOIN apartments a ON l.apartment_id = a.id
                JOIN units u ON l.unit_id = u.id
                WHERE a.name = ? AND u.name = ? AND l.tenantName = ?";
        
        $stmt = sqlsrv_prepare($conn, $sql, array($apt, $unit, $tenant));
        
        if (sqlsrv_execute($stmt)) {
            echo "<script>alert('Contract terminated successfully.'); window.location.href=window.location.pathname;</script>";
        }
    }
    
}
?>

<?php
// Handle delete operation
if (isset($_GET['delete']) && $_GET['delete'] == '1') {
    $apt = $_GET['apt'] ?? '';
    $unit = $_GET['unit'] ?? '';
    $tenant = $_GET['tenant'] ?? '';
    
    if (!empty($apt) && !empty($unit) && !empty($tenant)) {
        $sql = "DELETE l
                FROM leases l
                JOIN apartments a ON l.apartment_id = a.id
                JOIN units u ON l.unit_id = u.id
                WHERE a.name = ? AND u.name = ? AND l.tenantName = ?";
        
        $stmt = sqlsrv_prepare($conn, $sql, array($apt, $unit, $tenant));
        
        if (sqlsrv_execute($stmt)) {
            echo "<script>alert('Lease deleted successfully.'); window.location.href=window.location.pathname;</script>";
        }
    }
}

?>
<script>
// Check for payment success on page load
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const paymentSuccess = urlParams.get('payment_success');
    const transactionId = urlParams.get('transaction_id');
    
    if (paymentSuccess === '1') {
        // Show success message
        alert(`Payment submitted successfully! Transaction ID: ${transactionId}`);
        
        // Close payment modal if it's open
        if (document.getElementById('payment-modal').style.display === 'flex') {
            closePaymentModal();
        }
        
        // Remove the success parameters from URL without reloading
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
});
// Check for payment success on page load
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const paymentSuccess = urlParams.get('payment_success');
    const transactionId = urlParams.get('transaction_id');
    
    if (paymentSuccess === '1') {
        // Show success message
        alert(`Payment submitted successfully! Transaction ID: ${transactionId}`);
        
        // Close payment modal if it's open
        if (document.getElementById('payment-modal').style.display === 'flex') {
            closePaymentModal();
        }
        
        // Remove the success parameters from URL without reloading
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
});
</script>

</body>
</html>