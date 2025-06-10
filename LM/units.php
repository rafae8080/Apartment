<?php  
session_start();

$serverName = "LAPTOP-0QN98R6Q";
$connectionOptions = [
    "Database" => "LeaseManagementDB",
    "Uid" => "",
    "PWD" => ""
];
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) die(print_r(sqlsrv_errors(), true));
require_once 'session_init.php';
if (!isset($_SESSION['userEmail'])) {
    header("Location: login.php");
    exit();
}

$apartmentName = $_GET['apartment'] ?? '';
if (!$apartmentName) die("No apartment specified.");

$apartmentSql = "SELECT id FROM Apartments WHERE name = ?";
$apartmentStmt = sqlsrv_query($conn, $apartmentSql, [$apartmentName]);
$apartment = sqlsrv_fetch_array($apartmentStmt, SQLSRV_FETCH_ASSOC);
if (!$apartment) die("Apartment not found.");
$apartmentId = $apartment['id'];

$unitsSql = "
    SELECT u.*, 
           l.moveOut,
           CASE 
               WHEN EXISTS (
                   SELECT 1 
                   FROM Leases l2
                   WHERE l2.unit_id = u.id 
                     AND l2.is_active = 1
                     AND GETDATE() >= l2.moveIn 
                     AND GETDATE() < DATEADD(DAY, 1, l2.moveOut)
               ) THEN 'Taken'
               ELSE 'Available'
           END AS lease_status
    FROM Units u
    LEFT JOIN Leases l 
           ON l.unit_id = u.id 
          AND l.is_active = 1
          AND GETDATE() >= l.moveIn 
          AND GETDATE() < DATEADD(DAY, 1, l.moveOut)
    WHERE u.apartment_id = ? AND u.is_active = 1";


$unitsStmt = sqlsrv_query($conn, $unitsSql, [$apartmentId]);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($apartmentName) ?> Units</title>
    <link rel="stylesheet" href="css/units.css">
    <link rel="stylesheet" href="css/navbar.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo"><a href="index.php">RM</a></div>
        <div class="nav-links">
            <a href="index.php">Apartments</a>
            <a href="lease.php">Lease</a>
            <a href="transaction.php">Transactions</a>
            <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
        </div>
    </nav>

    <div class="container">
    <h2><?= htmlspecialchars($apartmentName) ?> Units</h2>
    
    <?php if (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'admin') : ?>
    <button class="btn add" onclick="openAddModal()">Add Unit</button>
    
    <?php endif; ?>
    <div class="apartment-grid">
        <?php while ($unit = sqlsrv_fetch_array($unitsStmt, SQLSRV_FETCH_ASSOC)) : 
            $status = $unit['lease_status'];
            $isTaken = ($status === 'Taken');

            $imagePath = !empty($unit['image_path']) && file_exists($unit['image_path']) 
                ? htmlspecialchars($unit['image_path']) 
                : "https://dummyimage.com/400x300/cccccc/000000&text=No+Image";
        ?>
            <div class="apartment-card">
                <img src="<?= $imagePath ?>" alt="Unit Image">
                <div class="apartment-details">
                    <h3><?= htmlspecialchars($unit['name']) ?></h3>
                    <p><?= htmlspecialchars($unit['details']) ?></p>
                    <p>₱<?= number_format($unit['rate'], 2) ?></p>
                    <p><strong>Status:</strong> <?= $status ?>
                        <?php if ($isTaken && isset($unit['moveOut'])): ?>
                            <br><span style="color: #e74c3c;">(Taken until <?= $unit['moveOut']->format('M d, Y') ?>)</span>
                        <?php endif; ?>
                    </p>

                    <?php if (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'admin') : ?>
                    <div class="btn-group">
                        <button class="btn edit" onclick='openEditModal(<?= json_encode($unit) ?>)'>Edit</button>
                        <form method="POST" action="units_action.php" onsubmit="return confirm('Delete this unit?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="unit_id" value="<?= $unit['id'] ?>">
                            <input type="hidden" name="apartment_id" value="<?= $apartmentId ?>">
                            <button class="btn delete" type="submit">Delete</button>
                        </form>
                        <button 
                            class="btn lease-btn" 
                            onclick="<?= $isTaken ? "alert('This unit is already taken.')" : "openLeaseModal('".htmlspecialchars($apartmentName)."', '".htmlspecialchars($unit['name'])."', '".htmlspecialchars($unit['details'])."', '".htmlspecialchars($unit['rate'])."')" ?>">
                            Create Lease
                        </button>
                    </div>
                    <?php else: ?>
                    <button 
                        class="btn lease-btn" 
                        onclick="<?= $isTaken ? "alert('This unit is already taken.')" : "openLeaseModal('".htmlspecialchars($apartmentName)."', '".htmlspecialchars($unit['name'])."', '".htmlspecialchars($unit['details'])."', '".htmlspecialchars($unit['rate'])."')" ?>">
                        Create Lease
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

   <!-- Add/Edit Modal -->
<div id="unitModal" class="modal">
  <div class="modal-content form-container">
    <div class="modal-header">
      <h3 id="modalTitle">Add/Edit Unit</h3>
      <span class="close" onclick="closeModal()">&times;</span>
    </div>
    <form method="POST" action="units_action.php" enctype="multipart/form-data">
      <input type="hidden" name="action" id="formAction">
      <input type="hidden" name="unit_id" id="unitId">
      <input type="hidden" name="apartment_id" value="<?= $apartmentId ?>">

      <div class="form-group">
        <label for="unitName">Name</label>
        <input type="text" name="name" id="unitName" required>
      </div>

      <div class="form-group">
        <label for="unitDetails">Details</label>
        <textarea name="details" id="unitDetails" rows="4" required></textarea>
      </div>

      <div class="form-group">
        <label for="unitRate">Rate</label>
        <input type="number" name="rate" id="unitRate" required>
      </div>

      <div class="form-group">
        <label for="unitImage">Image</label>
        <input type="file" name="image" id="unitImage">
      </div>

      <div class="form-actions">
        <button type="submit" class="create-btn">Save</button>
        <button type="button" class="close-btn" onclick="closeModal()">Close</button>
      </div>
    </form>
  </div>
</div>


    <!-- Notification -->
    <div id="notif" class="notif">
        <div class="notif-content" id="notifMessage"></div>
    </div>
    <!-- Lease Modal -->
    <div id="leaseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="leaseModalTitle"></h3>
                <span class="close" onclick="closeLeaseModal()">&times;</span>
            </div>
            <p id="leaseModalDescription"></p>
            <p id="leaseModalRate"></p>
            <button class="btn lease-btn" onclick="goToAddTenant()">Proceed to Lease Form</button>
        </div>
    </div>
<script src="javascript/units.js"></script>



</body>
</html>
