<?php
// submit_payment.php

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

// Get POST data
$apartment = $_POST['apartment'] ?? '';
$unit = $_POST['unit'] ?? '';
$tenant = $_POST['tenant'] ?? '';
$payment_date = $_POST['payment_date'] ?? '';
$amount = $_POST['amount'] ?? '';
$payment_method = $_POST['payment_method'] ?? '';

// Validate required fields
if (!$apartment || !$unit || !$tenant || !$payment_date || !$amount || !$payment_method) {
    die("Missing required payment information.");
}

// Prepare SQL insert
$sql = "
    INSERT INTO transactions (apartment, unit, tenant, payment_date, amount, payment_method)
    VALUES (?, ?, ?, ?, ?, ?)
";
$params = [$apartment, $unit, $tenant, $payment_date, $amount, $payment_method];
$stmt = sqlsrv_prepare($conn, $sql, $params);

if (!$stmt || !sqlsrv_execute($stmt)) {
    die("Error saving payment: " . print_r(sqlsrv_errors(), true));
}

// Redirect back
header("Location: lease.php");
exit;
?>
