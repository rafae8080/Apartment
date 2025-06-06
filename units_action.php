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

// Check if 'action' and 'apartment_id' are set
if (!isset($_POST['action'], $_POST['apartment_id'])) {
    showResponse("Error", "Invalid request.");
    exit;
}

$action = $_POST['action'];
$apartmentId = $_POST['apartment_id'];

// Get apartment name for display
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

// Function to display the styled response page
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
            transition: background-color 0.3s ease;
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
        <p class="note">Apartment: {$GLOBALS['apartmentName']}</p>
        <button onclick="window.location.href='units.php?apartment=' + encodeURIComponent('{$GLOBALS['apartmentName']}')">Go Back to Units</button>
    </div>
</body>
</html>
HTML;
    exit;
}

if ($action === "add" || $action === "edit") {
    if (!isset($_POST['name'], $_POST['details'], $_POST['rate'])) {
        showResponse("Error", "Missing required fields.");
    }

    $name = $_POST['name'];
    $details = $_POST['details'];
    $rate = floatval($_POST['rate']);
    $unitId = $_POST['unit_id'] ?? null;

    // Handle image upload
    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExt = ['jpg','jpeg','png','gif'];
        if (!in_array($ext, $allowedExt)) {
            showResponse("Error", "Invalid image format. Allowed: jpg, jpeg, png, gif.");
        }
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        $target = $targetDir . uniqid("unit_") . "." . $ext;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            showResponse("Error", "Failed to upload image.");
        }
        $imagePath = $target;
    }

    if ($action === "add") {
        $sql = "INSERT INTO Units (name, details, rate, image_path, apartment_id) VALUES (?, ?, ?, ?, ?)";
        $params = [$name, $details, $rate, $imagePath, $apartmentId];
        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt) showResponse("Success", "Unit added successfully.");
        else showResponse("Error", "Failed to add unit.");
    }

    if ($action === "edit") {
        if (empty($unitId)) {
            showResponse("Error", "Invalid unit ID.");
        }
        // Keep old image if no new image uploaded
        if (!$imagePath) {
            $stmt = sqlsrv_query($conn, "SELECT image_path FROM Units WHERE id = ?", [$unitId]);
            if (!$stmt) {
                showResponse("Error", "Failed to fetch current image.");
            }
            $old = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            $imagePath = $old['image_path'];
        }
        $sql = "UPDATE Units SET name = ?, details = ?, rate = ?, image_path = ? WHERE id = ?";
        $params = [$name, $details, $rate, $imagePath, $unitId];
        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt) showResponse("Success", "Unit updated successfully.");
        else showResponse("Error", "Failed to update unit.");
    }
}

if ($action === "delete") {
    $unitId = $_POST['unit_id'] ?? null;
    if (empty($unitId)) {
        showResponse("Error", "Invalid unit ID.");
    }
    $sql = "DELETE FROM Units WHERE id = ?";
    $stmt = sqlsrv_query($conn, $sql, [$unitId]);
    if ($stmt) showResponse("Success", "Unit deleted.");
    else showResponse("Error", "Failed to delete unit.");
}
?>
