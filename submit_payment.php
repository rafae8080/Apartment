<?php
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

$apartment = $_POST['apartment'] ?? '';
$unit = $_POST['unit'] ?? '';
$tenant = $_POST['tenant'] ?? '';
$payment_date = $_POST['payment_date'] ?? '';
$amount = $_POST['amount'] ?? '';
$payment_method = $_POST['payment_method'] ?? '';

if (!$apartment || !$unit || !$tenant || !$payment_date || !$amount || !$payment_method) {
    die("Missing required payment information.");
}

if (!sqlsrv_begin_transaction($conn)) {
    die("Transaction start failed: " . print_r(sqlsrv_errors(), true));
}

try {
    // Prepare the INSERT with OUTPUT to get the generated transaction_id
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

    // Get the inserted transaction ID
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $transaction_id = $row['transaction_id'];

    sqlsrv_commit($conn);

    // Optionally show confirmation with the transaction ID
    echo "Payment successful. Transaction ID: " . $transaction_id;
    header("Location: lease.php");
    // You can redirect or log as needed
    // header("Location: lease.php");
    // exit;

} catch (Exception $e) {
    sqlsrv_rollback($conn);
    die("Payment failed: " . $e->getMessage());
}
?>
