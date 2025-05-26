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
$action = $_POST['action'];
$apartmentId = $_POST['apartment_id'];
$redirectBase = "units.php?apartment=";

// Get apartment name for redirect
$stmt = sqlsrv_query($conn, "SELECT name FROM Apartments WHERE id = ?", [$apartmentId]);
$apartment = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$apartmentName = urlencode($apartment['name']);

function redirect($status, $msg) {
    global $apartmentName;
    header("Location: units.php?apartment=$apartmentName&status=$status&msg=" . urlencode($msg));
    exit;
}

if ($action === "add" || $action === "edit") {
    $name = $_POST['name'];
    $details = $_POST['details'];
    $rate = $_POST['rate'];
    $unitId = $_POST['unit_id'] ?? null;

    // Handle image upload
    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $target = "uploads/" . uniqid("unit_") . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        $imagePath = $target;
    }

    if ($action === "add") {
        $sql = "INSERT INTO Units (name, details, rate, image_path, apartment_id) VALUES (?, ?, ?, ?, ?)";
        $params = [$name, $details, $rate, $imagePath, $apartmentId];
        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt) redirect("success", "Unit added successfully.");
        else redirect("error", "Failed to add unit.");
    }

    if ($action === "edit") {
        // Keep old image if no new image uploaded
        if (!$imagePath) {
            $stmt = sqlsrv_query($conn, "SELECT image_path FROM Units WHERE id = ?", [$unitId]);
            $old = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            $imagePath = $old['image_path'];
        }
        $sql = "UPDATE Units SET name = ?, details = ?, rate = ?, image_path = ? WHERE id = ?";
        $params = [$name, $details, $rate, $imagePath, $unitId];
        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt) redirect("success", "Unit updated successfully.");
        else redirect("error", "Failed to update unit.");
    }
}

if ($action === "delete") {
    $unitId = $_POST['unit_id'];
    $sql = "DELETE FROM Units WHERE id = ?";
    $stmt = sqlsrv_query($conn, $sql, [$unitId]);
    if ($stmt) redirect("success", "Unit deleted.");
    else redirect("error", "Failed to delete unit.");
}
?>
