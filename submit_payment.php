<?php
$serverName = "DESKTOP-F68QS4T";
$connectionOptions = [
    "Database" => "LeaseManagementDB",
    "Uid" => "",
    "PWD" => ""
];

$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

// Get POST data and sanitize
$apartment = trim($_POST['apartment'] ?? '');
$unit = trim($_POST['unit'] ?? '');
$tenant = trim($_POST['tenant'] ?? '');
$payment_date = trim($_POST['payment_date'] ?? '');
$amount = trim($_POST['amount'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? '');

// Basic validation
if (!$apartment || !$unit || !$tenant || !$payment_date || !$amount || !$payment_method) {
    die("Missing required payment information.");
}

// Optionally, validate amount and date formats here

if (!sqlsrv_begin_transaction($conn)) {
    die("Transaction start failed: " . print_r(sqlsrv_errors(), true));
}

try {
    $sql = "
        INSERT INTO transactions (apartment, unit, tenant, payment_date, amount, payment_method)
        OUTPUT INSERTED.transaction_id
        VALUES (?, ?, ?, ?, ?, ?)
    ";
    $params = [$apartment, $unit, $tenant, $payment_date, $amount, $payment_method];
    $stmt = sqlsrv_prepare($conn, $sql, $params);

    if (!$stmt || !sqlsrv_execute($stmt)) {
        throw new Exception("Insert failed: " . print_r(sqlsrv_errors(), true));
    }

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $transaction_id = $row['transaction_id'];

    sqlsrv_commit($conn);

    // Redirect to lease page with success
    header("Location: lease.php?payment_success=1&transaction_id=" . $transaction_id);
    exit();

} catch (Exception $e) {
    sqlsrv_rollback($conn);
    die("Payment failed: " . $e->getMessage());
}
?>
