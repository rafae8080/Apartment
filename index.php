<?php
// Connection settings
$serverName = "LAPTOP-0QN98R6Q"; // Your SQL Server name
$connectionOptions = array(
    "Database" => "LeaseManagementDB",
    "Uid" => "", // Leave empty for Windows Authentication
    "PWD" => "", // Leave empty for Windows Authentication
);

// Connect to SQL Server
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch apartment data
$sql = "SELECT name, address, page_link FROM Apartments";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

require_once 'session_init.php';
if (!isset($_SESSION['userEmail'])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apartment Dashboard</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <a href="index.php">RM</a>
        </div>
        <div class="nav-links">
            <a href="#apartments">Apartments</a>
            <a href="lease.php">Lease</a>
            <a href="#transactions">Transactions</a>
        </div>
    </nav>

    <div class="container" id="apartments">
        <h2 class="section-title">Available Apartments</h2>
        <div class="apartment-grid">
            <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
                <div class="apartment-card">
                    <a href="<?= htmlspecialchars($row['page_link']) ?>">
                        <img src="images/apartment sample.jpg" alt="Apartment Image">
                        <div class="apartment-details">
                            <h3><?= htmlspecialchars($row['name']) ?></h3>
                            <p><?= htmlspecialchars($row['address']) ?></p>
                        </div>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</body>
</html>
