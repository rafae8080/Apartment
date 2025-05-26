<?php
$serverName = "LAPTOP-0QN98R6Q";
$connectionOptions = [
    "Database" => "LeaseManagementDB",
    "Uid" => "",
    "PWD" => "",
];
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) die(print_r(sqlsrv_errors(), true));

require_once 'session_init.php';
if (!isset($_SESSION['userEmail'])) {
    header("Location: login.php");
    exit();
}

// Insert logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apartment_name'])) {
    $name = $_POST['apartment_name'];
    $address = $_POST['apartment_address'];
    $imagePath = "";

    if (isset($_FILES['apartment_image']) && $_FILES['apartment_image']['error'] === UPLOAD_ERR_OK) {
        $imageName = basename($_FILES['apartment_image']['name']);
        $targetDir = "images/";
        $imagePath = $targetDir . time() . "_" . $imageName;
        move_uploaded_file($_FILES['apartment_image']['tmp_name'], $imagePath);
    }

    $pageLink = "units.php?apartment=" . urlencode($name);
    $insertSql = "INSERT INTO Apartments (name, address, page_link, image_path) VALUES (?, ?, ?, ?)";
    $insertStmt = sqlsrv_prepare($conn, $insertSql, [$name, $address, $pageLink, $imagePath]);
    if (sqlsrv_execute($insertStmt)) {
        // Redirect to avoid duplicate submission on refresh
header("Location: index.php");
        exit();
    } else {
        die(print_r(sqlsrv_errors(), true));
    }
}

// Delete logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];
    $deleteSql = "DELETE FROM Apartments WHERE id = ?";
    $deleteStmt = sqlsrv_prepare($conn, $deleteSql, [$deleteId]);
    if (sqlsrv_execute($deleteStmt)) {
        header("Location: index.php");
        exit();
    } else {
        die(print_r(sqlsrv_errors(), true));
    }
}

// Update logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $editId = $_POST['edit_id'];
    $name = $_POST['edit_name'];
    $address = $_POST['edit_address'];
    $imagePath = null;

    if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] === UPLOAD_ERR_OK) {
        $imageName = basename($_FILES['edit_image']['name']);
        $targetDir = "images/";
        $imagePath = $targetDir . time() . "_" . $imageName;
        move_uploaded_file($_FILES['edit_image']['tmp_name'], $imagePath);
    }

    if ($imagePath !== null) {
        $updateSql = "UPDATE Apartments SET name = ?, address = ?, image_path = ? WHERE id = ?";
        $params = [$name, $address, $imagePath, $editId];
    } else {
        $updateSql = "UPDATE Apartments SET name = ?, address = ? WHERE id = ?";
        $params = [$name, $address, $editId];
    }

    $updateStmt = sqlsrv_prepare($conn, $updateSql, $params);
    if (sqlsrv_execute($updateStmt)) {
        header("Location: index.php");
        exit();
    } else {
        die(print_r(sqlsrv_errors(), true));
    }
}

// Fetch updated apartment list
$sql = "SELECT id, name, address, page_link, image_path FROM Apartments";
$stmt = sqlsrv_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Apartment Dashboard</title>
    <link rel="stylesheet" href="css/navbar.css" />
    <link rel="stylesheet" href="css/dashboard.css" />
    <link rel="stylesheet" href="css/index.css" />

</head>
<body>
    <nav class="navbar">
        <div class="logo"><a href="index.php">RM</a></div>
        <div class="nav-links">
            <a href="#apartments">Apartments</a>
            <a href="lease.php">Lease</a>
            <a href="transaction.php">Transactions</a>
        </div>
    </nav>

    <div class="container" id="apartments">
        <h2 class="section-title">Available Apartments</h2>

        <div class="apartment-grid">
            <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
                <div class="apartment-card">
                    <a href="<?= htmlspecialchars($row['page_link']) ?>">
                        <img src="<?= htmlspecialchars($row['image_path'] ?? 'images/apartment sample.jpg') ?>" alt="Apartment Image" style="width:100%; height:auto; border-radius: 8px;" />
                        <div class="apartment-details">
                            <h3><?= htmlspecialchars($row['name']) ?></h3>
                            <p><?= htmlspecialchars($row['address']) ?></p>
                        </div>
                    </a>
                    <!-- Edit and Delete buttons -->
                    <button onclick="openEditModal(<?= $row['id'] ?>, '<?= addslashes($row['name']) ?>', '<?= addslashes($row['address']) ?>')">Edit</button>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this apartment?');">
                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>" />
                        <button type="submit">Delete</button>
                    </form>
                </div>
            <?php } ?>

            <!-- Add Apartment Button -->
            <div class="apartment-card add-card" onclick="document.getElementById('addModal').style.display='flex'">
                + Add Apartment
            </div>
        </div>
    </div>

    <!-- Modal for adding new apartment -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <form action="index.php" method="POST" enctype="multipart/form-data">
                <h3>Add Apartment</h3>
                <input type="text" name="apartment_name" placeholder="Building Name" required />
                <textarea name="apartment_address" placeholder="Address" required></textarea>
                <input type="file" name="apartment_image" accept="image/*" required />
                <input type="submit" value="Add Apartment" />
            </form>
        </div>
    </div>

    <!-- Modal for editing apartment -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <form action="index.php" method="POST" enctype="multipart/form-data" id="editForm">
                <h3>Edit Apartment</h3>
                <input type="hidden" name="edit_id" id="edit_id" />
                <input type="text" name="edit_name" id="edit_name" placeholder="Building Name" required />
                <textarea name="edit_address" id="edit_address" placeholder="Address" required></textarea>
                <label for="edit_image">Change Image (optional):</label>
                <input type="file" name="edit_image" id="edit_image" accept="image/*" />
                <input type="submit" value="Update Apartment" />
            </form>
        </div>
    </div>

    <script src="javascript/index.js"></script>

</body>
</html>
