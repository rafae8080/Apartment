<?php
// Connection settings
$serverName = "LAPTOP-0QN98R6Q"; // Your SQL Server name
$connectionOptions = array(
    "Database" => "LeaseManagementDB",
    "Uid" => "", // Leave empty for Windows Authentication
    "PWD" => "", // Leave empty for Windows Authentication
);

// Connect using PDO
try {
    $conn = new PDO("sqlsrv:server=$serverName;Database=LeaseManagementDB", null, null);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get form data
    $name = $_POST['name'];
    $number = $_POST['number'];
    $moveIn = $_POST['moveIn'];
    $moveOut = $_POST['moveOut'];

    // Prepare and execute query
    $sql = "INSERT INTO tenantData (name, number, moveIn, moveOut) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$name, $number, $moveIn, $moveOut]);

    echo "✅ Data inserted successfully!";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
