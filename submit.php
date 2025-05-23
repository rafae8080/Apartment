<?php
$serverName = "LAPTOP-0QN98R6Q";
$connectionOptions = array(
    "Database" => "LeaseManagementDB",
    "Uid" => "",
    "PWD" => "",
);
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

// Look up apartment ID
$apartmentSql = "SELECT id FROM Apartments WHERE name = ?";
$apartmentStmt = sqlsrv_query($conn, $apartmentSql, array($apartmentName));
$apartment = sqlsrv_fetch_array($apartmentStmt, SQLSRV_FETCH_ASSOC);
if (!$apartment) {
    die("Apartment not found.");
}
$apartmentId = $apartment['id'];

// Look up unit ID
$unitSql = "SELECT id FROM Units WHERE name = ? AND apartment_id = ?";
$unitStmt = sqlsrv_query($conn, $unitSql, array($unitName, $apartmentId));
$unit = sqlsrv_fetch_array($unitStmt, SQLSRV_FETCH_ASSOC);
if (!$unit) {
    die("Unit not found.");
}
$unitId = $unit['id'];

// Insert lease
$insertSql = "INSERT INTO Leases (tenantName, contactNumber, moveIn, moveOut, apartment_id, unit_id)
              VALUES (?, ?, ?, ?, ?, ?)";
$params = array($tenantName, $contactNumber, $moveIn, $moveOut, $apartmentId, $unitId);
$insertStmt = sqlsrv_query($conn, $insertSql, $params);

if ($insertStmt === false) {
    die(print_r(sqlsrv_errors(), true));
} else {
    echo "<script>alert('Lease successfully created!'); window.location.href = 'lease.php';</script>";
}
?>
