<?php  
session_start();

$serverName = "DESKTOP-F68QS4T";
$connectionOptions = [
    "Database" => "LeaseManagementDB",
    "Uid" => "",
    "PWD" => ""
];
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
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
                   FROM Leases l 
                   WHERE l.unit_id = u.id 
                     AND GETDATE() >= l.moveIn 
                     AND GETDATE() < DATEADD(DAY, 1, l.moveOut)
               ) THEN 'Taken'
               ELSE 'Available'
           END AS lease_status
    FROM Units u
    LEFT JOIN Leases l ON l.unit_id = u.id 
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
    <style>
 /* Base styles */
html, body {
    margin: 0;
    padding: 0;
    background-color: #f0f4ff;
    font-family: 'Poppins', sans-serif;
    color: #2c3e50;
    box-sizing: border-box;
}

/* Modal & Notification styles */
.modal, .notif {
    display: none;
    position: fixed;
    z-index: 999;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.4);
}

.modal-content, .notif-content {
    background: #ffffff;
    padding: 30px;
    margin: 8% auto;
    width: 90%;
    max-width: 500px;
    border-radius: 16px;
    box-shadow:
        0 8px 16px rgba(0, 0, 0, 0.15),
        0 4px 8px rgba(0, 0, 0, 0.08);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.close {
    cursor: pointer;
    font-size: 24px;
    color:rgb(107, 43, 49);
    font-weight: bold;
}

.notif-content {
    text-align: center;
    font-weight: 600;
    padding: 20px;
}

.notif.success {
    background: #d4edda;
    color: #155724;
}

.notif.error {
    background: #f8d7da;
    color: #721c24;
}

/* Card for Units */
.apartment-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    transition: transform 0.2s ease;
    cursor: pointer;
}

.apartment-card:hover {
    transform: translateY(-4px);
}

.apartment-card img {
    width: 100%;
    height: 220px;
    object-fit: cover;
}

/* Button styling */
.btn {
    padding: 10px 16px;
    border: none;
    border-radius: 12px;
    font-family: 'Poppins', sans-serif;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn.add {
    background-color: #768ba0;
    color: white;
    margin-bottom: 20px;
}

.btn.add:hover {
    background-color: #3a53a1;
}

.btn.edit {
    background-color: #768ba0;
    color: white;
}

.btn.edit:hover {
    background-color:rgb(154, 181, 209);
}

.btn.delete {
    background-color:rgb(92, 32, 38);
    color: white;
}

.btn.delete:hover {
    background-color: #b02a37;
}
/* Lease button */
.btn.lease{
    background-color: #278b3f;
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.5);
}

.btn.lease:hover {
    background-color: #218838;
}

/* Button group */
.btn-group {
    display: flex;
    gap: 12px;
    margin-top: 16px;
}

.btn-group button,
.btn-group form {
    flex: 1;
    margin: 0;
}

.btn-group form {
    display: flex;
}

.btn-group form button {
    flex: 1;
}

/* Form inputs */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px 14px;
    border: 1.8px solid #d6d9e6;
    border-radius: 12px;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: #4b6cb7;
    outline: none;
    box-shadow: 0 0 6px rgba(75, 108, 183, 0.5);
}

/* Responsive design */
@media (max-width: 768px) {
    .modal-content, .notif-content {
        width: 95%;
    }

    .btn-group {
        flex-direction: column;
    }

    .btn-group button,
    .btn-group form {
        width: 100%;
    }
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

<script>
    function openAddModal() {
        document.getElementById("modalTitle").textContent = "Add Unit";
        document.getElementById("formAction").value = "add";
        document.getElementById("unitId").value = "";
        document.getElementById("unitName").value = "";
        document.getElementById("unitDetails").value = "";
        document.getElementById("unitRate").value = "";
        document.getElementById("unitModal").style.display = "flex";
    }

    function openEditModal(unit) {
        document.getElementById("modalTitle").textContent = "Edit Unit";
        document.getElementById("formAction").value = "edit";
        document.getElementById("unitId").value = unit.id;
        document.getElementById("unitName").value = unit.name;
        document.getElementById("unitDetails").value = unit.details;
        document.getElementById("unitRate").value = unit.rate;
        document.getElementById("unitModal").style.display = "flex";
    }

    function closeModal() {
        document.getElementById("unitModal").style.display = "none";
    }

    // Show notifications if redirected with status
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const msg = urlParams.get('msg');
    if (status && msg) {
        const notif = document.getElementById("notif");
        const content = document.getElementById("notifMessage");
        notif.classList.add(status === "success" ? "success" : "error");
        content.textContent = decodeURIComponent(msg);
        notif.style.display = "block";
        setTimeout(() => { notif.style.display = "none"; }, 3000);
    }

    function openLeaseModal(apartmentName, unitName, details, rate) {
        document.getElementById('leaseModalTitle').textContent = unitName + " - " + apartmentName;
        document.getElementById('leaseModalDescription').textContent = "Details: " + details;
        document.getElementById('leaseModalRate').textContent = "Monthly Rate: ₱" + rate;
        window.selectedApartment = apartmentName;
        window.selectedUnit = unitName;
        document.getElementById('leaseModal').style.display = "flex";
    }

    function closeLeaseModal() {
        document.getElementById('leaseModal').style.display = "none";
    }

    function goToAddTenant() {
        const apartment = encodeURIComponent(window.selectedApartment);
        const unit = encodeURIComponent(window.selectedUnit);
        window.location.href = `form.php?apartment=${apartment}&unit=${unit}`;
    }
</script>

</body>
</html>
