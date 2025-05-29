<?php
// transaction.php

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
sqlsrv_execute($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction History</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        form input, form select {
            margin-right: 10px; padding: 6px;
        }
        .filter-form {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<h2>Transaction History</h2>

<form method="get" class="filter-form">
    <input type="text" name="apartment" placeholder="Apartment" value="<?= htmlspecialchars($apartment) ?>">
    <input type="text" name="unit" placeholder="Unit" value="<?= htmlspecialchars($unit) ?>">
    <input type="text" name="tenant" placeholder="Tenant" value="<?= htmlspecialchars($tenant) ?>">
    <input type="date" name="payment_date" value="<?= htmlspecialchars($payment_date) ?>">
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
    echo "<tr>
<td>" . substr(htmlspecialchars($row['transaction_id']), 0, 8) . "...</td>
            <td>" . htmlspecialchars($row['apartment']) . "</td>
            <td>" . htmlspecialchars($row['unit']) . "</td>
            <td>" . htmlspecialchars($row['tenant']) . "</td>
            <td>" . $date . "</td>
            <td>" . htmlspecialchars($row['amount']) . "</td>
            <td>" . htmlspecialchars($row['payment_method']) . "</td>
          </tr>";
}

    ?>
    </tbody>
</table>

</body>
</html>
