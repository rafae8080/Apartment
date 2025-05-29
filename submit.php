<?php
$serverName = "LAPTOP-0QN98R6Q";
$connectionOptions = [
    "Database" => "LeaseManagementDB",
    "Uid" => "",
    "PWD" => ""
];
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Sanitize input
$apartmentName = $_POST['apartment'] ?? '';
$unitName = $_POST['unit'] ?? '';
$tenantName = $_POST['name'] ?? '';
$contactNumber = $_POST['number'] ?? '';
$moveIn = $_POST['moveIn'] ?? '';
$moveOut = $_POST['moveOut'] ?? '';

if (!$apartmentName || !$unitName || !$tenantName || !$contactNumber || !$moveIn || !$moveOut) {
    die("Missing required fields.");
}

// Begin transaction
if (!sqlsrv_begin_transaction($conn)) {
    die("Transaction start failed: " . print_r(sqlsrv_errors(), true));
}

try {
    // Get apartment ID
    $apartmentSql = "SELECT id FROM Apartments WHERE name = ?";
    $apartmentStmt = sqlsrv_query($conn, $apartmentSql, [$apartmentName]);
    $apartment = sqlsrv_fetch_array($apartmentStmt, SQLSRV_FETCH_ASSOC);
    if (!$apartment) throw new Exception("Apartment not found.");
    $apartmentId = $apartment['id'];

    // Get unit ID
    $unitSql = "SELECT id FROM Units WHERE name = ? AND apartment_id = ?";
    $unitStmt = sqlsrv_query($conn, $unitSql, [$unitName, $apartmentId]);
    $unit = sqlsrv_fetch_array($unitStmt, SQLSRV_FETCH_ASSOC);
    if (!$unit) throw new Exception("Unit not found.");
    $unitId = $unit['id'];

    // Overlap check
    $overlapSql = "
        SELECT 1 FROM Leases
        WHERE unit_id = ? AND apartment_id = ? AND (
            (moveIn <= ? AND moveOut >= ?) OR
            (moveIn <= ? AND moveOut >= ?) OR
            (moveIn >= ? AND moveOut <= ?)
        )
    ";
    $overlapParams = [$unitId, $apartmentId, $moveIn, $moveIn, $moveOut, $moveOut, $moveIn, $moveOut];
    $overlapStmt = sqlsrv_query($conn, $overlapSql, $overlapParams);

    if (sqlsrv_fetch_array($overlapStmt, SQLSRV_FETCH_ASSOC)) {
        throw new Exception("This unit is already leased during the selected dates.");
    }

    // Insert lease
    $insertSql = "
        INSERT INTO Leases (tenantName, contactNumber, moveIn, moveOut, apartment_id, unit_id)
        VALUES (?, ?, ?, ?, ?, ?)
    ";
    $insertParams = [$tenantName, $contactNumber, $moveIn, $moveOut, $apartmentId, $unitId];
    $insertStmt = sqlsrv_query($conn, $insertSql, $insertParams);
    if (!$insertStmt) throw new Exception(print_r(sqlsrv_errors(), true));

    // Commit transaction
    sqlsrv_commit($conn);
    echo "<script>alert('Lease successfully created!'); window.location.href = 'lease.php';</script>";
    exit;

} catch (Exception $e) {
    sqlsrv_rollback($conn);
    echo "<script>alert('Lease creation failed: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    exit;
}
?>
