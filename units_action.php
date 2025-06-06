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

if (!isset($_POST['action'], $_POST['apartment_id'])) {
    showResponse("Error", "Invalid request.");
    exit;
}

$action = $_POST['action'];
$apartmentId = $_POST['apartment_id'];

$stmt = sqlsrv_query($conn, "SELECT name FROM Apartments WHERE id = ?", [$apartmentId]);
if (!$stmt) {
    showResponse("Error", print_r(sqlsrv_errors(), true));
    exit;
}
$apartment = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if (!$apartment) {
    showResponse("Error", "Apartment not found.");
    exit;
}
$apartmentName = $apartment['name'];

switch ($action) {
    case 'delete':
        if (!isset($_POST['unit_id'])) {
            showResponse("Error", "Missing unit ID.");
            exit;
        }

        $unitId = $_POST['unit_id'];

        $deleteSql = "UPDATE Units SET is_active = 0 WHERE id = ?";
        $deleteStmt = sqlsrv_query($conn, $deleteSql, [$unitId]);

        if ($deleteStmt) {
            showResponse("success", "Unit successfully removed.");
        } else {
            showResponse("error", "Failed to remove unit.");
        }
        break;

    // other actions (add/edit) would go here...

    default:
        showResponse("Error", "Invalid action.");
        break;
}

function showResponse($status, $message) {
    $color = $status === "success" ? "#28a745" : "#dc3545";
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Unit Action Result</title>
    <link rel="stylesheet" href="css/navbar.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background: #fdfcfb;
        }
        .container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        p {
            font-size: 16px;
            color: #666;
        }
        .note {
            margin-top: 15px;
            font-style: italic;
            color: gray;
        }
        button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            background-color: $color;
            color: white;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo"><a href="index.php">RM</a></div>
        <div class="nav-links">
            <a href="index.php">Apartments</a>
            <a href="lease.php">Lease</a>
            <a href="transaction.php">Transactions</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>
    <div class="container">
        <h2>Unit Action Result</h2>
        <p><strong>Status:</strong> $status</p>
        <p>$message</p>
        <form action="units.php" method="get">
            <input type="hidden" name="apartment" value="{$GLOBALS['apartmentName']}">
            <button type="submit">Back to Units</button>
        </form>
    </div>
</body>
</html>
HTML;
}
