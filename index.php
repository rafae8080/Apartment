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

    $pageLink = "units.php?apartment=" . urlencode($name);

    if ($imagePath !== null) {
        $updateSql = "UPDATE Apartments SET name = ?, address = ?, image_path = ?, page_link = ? WHERE id = ?";
        $params = [$name, $address, $imagePath, $pageLink, $editId];
    } else {
        $updateSql = "UPDATE Apartments SET name = ?, address = ?, page_link = ? WHERE id = ?";
        $params = [$name, $address, $pageLink, $editId];
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
    <style>
        .add-card {
            display: flex;
            justify-content: center;
            align-items: center;
            border: 2px dashed #aaa;
            border-radius: 8px;
            padding: 40px;
            cursor: pointer;
            font-weight: bold;
            color: #555;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.4);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: #fff;
            padding: 20px;
            width: 350px;
            border-radius: 8px;
        }
        .modal input, .modal textarea {
            width: 100%;
            margin-bottom: 12px;
            padding: 8px;
        }
        .modal input[type="submit"], button {
            background-color: #2C3E50;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 4px;
            margin-right: 5px;
                        width: 100%;

        }
        .modal input[type="submit"]:hover, button:hover {
            background-color: #2C3E50;
        }
        .apartment-card {
            position: relative;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .apartment-card button {
            position: relative;
            top: 5px;
            margin-top: 8px;
        }
        .apartment-card form {
            display: inline-block;
        }
        .user-section {
            position: fixed;
            bottom: 20px;
            right: 20px;
            border: 1px solid black;
            padding: 10px;
            background: #fff;
            border-radius: 8px;
        }
        .user-section .user-email, .user-name {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            word-wrap: break-word;
        }
        .logout-btn {
            background-color: #2C3E50;
            border: none;
            padding: 8px 12px;
            color: white;
            cursor: pointer;
            border-radius: 4px;
            font-weight: 600;
            width: 100%;
        }
        .logout-btn:hover {
            background-color: #1a2531;
        }
        a {
    text-decoration: none;
    color: inherit;
}

    </style>
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
                    
                    <?php if($_SESSION['userRole'] === 'admin') : ?>
                    <!-- Edit and Delete buttons -->
                    <button onclick="openEditModal(<?= $row['id'] ?>, '<?= addslashes($row['name']) ?>', '<?= addslashes($row['address']) ?>')">Edit</button>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this apartment?');">
                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>" />
                        <button type="submit">Delete</button>
                    </form>
                    <?php endif; ?>

                </div>
            <?php } ?>
 
            <?php if($_SESSION['userRole'] === 'admin') : ?>
            <!-- Add Apartment Button -->
            <div class="apartment-card add-card" onclick="document.getElementById('addModal').style.display='flex'">
                + Add Apartment
            </div>
            <?php endif; ?>
       </div>
    </div>

    <?php if($_SESSION['userRole'] === 'admin') : ?>
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
    <?php endif; ?>

    <!-- FOR LOGOUT -->
    <div class="user-section">
        <span class="user-name"><?= htmlspecialchars($_SESSION['userName']); ?></span>
        <span class="user-role"><?= htmlspecialchars($_SESSION['userRole']); ?></span>
        <span class="user-email"><?= htmlspecialchars($_SESSION['userEmail']); ?></span>
        <form action="logout.php" method="post">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>

    <script>
        function openEditModal(id, name, address) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_address').value = address;
            document.getElementById('editModal').style.display = 'flex';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            if (event.target === addModal) {
                addModal.style.display = "none";
            }
            if (event.target === editModal) {
                editModal.style.display = "none";
            }
        }
    </script>
</body>
</html>