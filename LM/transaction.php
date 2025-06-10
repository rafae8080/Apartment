<?php
// transaction.php

$serverName = "LAPTOP-0QN98R6Q";
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

// Filter values
$apartment = $_GET['apartment'] ?? '';
$unit = $_GET['unit'] ?? '';
$tenant = $_GET['tenant'] ?? '';
$payment_date = $_GET['payment_date'] ?? '';

$sql = "SELECT transaction_id, apartment, unit, tenant, payment_date, amount, payment_method FROM transactions WHERE 1=1";
$params = [];

if (!empty($apartment)) {
    $sql .= " AND apartment LIKE ?";
    $params[] = "%$apartment%";
}
if (!empty($unit)) {
    $sql .= " AND unit LIKE ?";
    $params[] = "%$unit%";
}
if (!empty($tenant)) {
    $sql .= " AND tenant LIKE ?";
    $params[] = "%$tenant%";
}
if (!empty($payment_date)) {
    $sql .= " AND CONVERT(date, payment_date) = ?";
    $params[] = $payment_date;
}

$stmt = sqlsrv_prepare($conn, $sql, $params);
if (!$stmt) {
    die("SQL prepare failed: " . print_r(sqlsrv_errors(), true));
}
sqlsrv_execute($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Transaction History</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/navbar.css" />
    <link rel="stylesheet" href="css/transaction.css" />
</head>
<body>
    <nav class="navbar">
        <div class="logo"><a href="index.php">RM</a></div>
        <div class="nav-links">
            <a href="index.php">Apartments</a>
            <a href="lease.php">Lease</a>
            <a href="transaction.php" class="active">Transactions</a>
            <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>

        </div>
    </nav>

    <div class="container">
        <h2>Transaction History</h2>

        <form method="get" class="filter-form" action="transaction.php">
            <input type="text" name="apartment" placeholder="Apartment" value="<?= htmlspecialchars($apartment) ?>" />
            <input type="text" name="unit" placeholder="Unit" value="<?= htmlspecialchars($unit) ?>" />
            <input type="text" name="tenant" placeholder="Tenant" value="<?= htmlspecialchars($tenant) ?>" />
            <input type="date" name="payment_date" value="<?= htmlspecialchars($payment_date) ?>" />
            <button type="submit">Search</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Apartment</th>
                    <th>Unit</th>
                    <th>Tenant</th>
                    <th>Date of Payment</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $date = $row['payment_date'] ? $row['payment_date']->format('Y-m-d') : '';
                    $amountFormatted = 'Php ' . number_format($row['amount'], 2);
                    echo "<tr>
                        <td>" . substr(htmlspecialchars($row['transaction_id']), 0, 8) . "...</td>
                        <td>" . htmlspecialchars($row['apartment']) . "</td>
                        <td>" . htmlspecialchars($row['unit']) . "</td>
                        <td>" . htmlspecialchars($row['tenant']) . "</td>
                        <td>" . $date . "</td>
                        <td>" . $amountFormatted . "</td>
                        <td>" . htmlspecialchars($row['payment_method']) . "</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>