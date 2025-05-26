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
// Get apartment
$apartmentName = $_GET['apartment'] ?? '';
if (!$apartmentName) die("No apartment specified.");

$apartmentSql = "SELECT id FROM Apartments WHERE name = ?";
$apartmentStmt = sqlsrv_query($conn, $apartmentSql, [$apartmentName]);
$apartment = sqlsrv_fetch_array($apartmentStmt, SQLSRV_FETCH_ASSOC);
if (!$apartment) die("Apartment not found.");
$apartmentId = $apartment['id'];

// Get units
$unitsSql = "SELECT * FROM Units WHERE apartment_id = ?";
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
        /* Modal Styles */
        .modal, .notif { display: none; position: fixed; z-index: 999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content, .notif-content {
            background: #fff; padding: 20px; margin: 10% auto; width: 90%; max-width: 500px; border-radius: 8px;
        }
        .notif-content { text-align: center; }
        .notif.success { background: rgba(0, 255, 0, 0.2); }
        .notif.error { background: rgba(255, 0, 0, 0.2); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; }
        .close { cursor: pointer; font-size: 24px; }

        /* Button styles */
        .btn { padding: 10px 15px; margin-top: 10px; border: none; background: #28a745; color: #fff; cursor: pointer; border-radius: 4px; }
        .btn.edit { background: #007bff; }
        .btn.delete { background: #dc3545; }
        .btn.add { background: #17a2b8; margin-bottom: 20px; }

        .apartment-card { cursor: pointer; }

        /* Simple layout */
        .form-group { margin-bottom: 10px; }
        .form-group label { display: block; font-weight: bold; }
        .form-group input, .form-group textarea { width: 100%; padding: 8px; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo"><a href="index.php">RM</a></div>
        <div class="nav-links">
            <a href="index.php">Apartments</a>
            <a href="lease.php">Lease</a>
            <a href="transaction.php">Transactions</a>
        </div>
    </nav>

    <div class="container">
        <h2><?= htmlspecialchars($apartmentName) ?> Units</h2>
        <button class="btn add" onclick="openAddModal()">Add Unit</button>
        <div class="apartment-grid">
            <?php while ($unit = sqlsrv_fetch_array($unitsStmt, SQLSRV_FETCH_ASSOC)) { ?>
                <div class="apartment-card">
                    <img src="<?= htmlspecialchars($unit['image_path']) ?>" alt="Unit Image">
                    <div class="apartment-details">
                        <h3><?= htmlspecialchars($unit['name']) ?></h3>
                        <p><?= htmlspecialchars($unit['details']) ?></p>
                        <p>₱<?= number_format($unit['rate'], 2) ?></p>
                        <button class="btn edit" onclick='openEditModal(<?= json_encode($unit) ?>)'>Edit</button>
                        <form method="POST" action="units_action.php" onsubmit="return confirm('Delete this unit?');" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="unit_id" value="<?= $unit['id'] ?>">
                            <input type="hidden" name="apartment_id" value="<?= $apartmentId ?>">
                            <button class="btn delete" type="submit">Delete</button>
                        </form>
                                                    <button class="btn" onclick="openLeaseModal('<?= htmlspecialchars($apartmentName) ?>', '<?= htmlspecialchars($unit['name']) ?>', '<?= htmlspecialchars($unit['details']) ?>', '<?= htmlspecialchars($unit['rate']) ?>')">Create Lease</button>

                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="unitModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add/Edit Unit</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" action="units_action.php" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction">
                <input type="hidden" name="unit_id" id="unitId">
                <input type="hidden" name="apartment_id" value="<?= $apartmentId ?>">

                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" id="unitName" required>
                </div>
                <div class="form-group">
                    <label>Details</label>
                    <textarea name="details" id="unitDetails" required></textarea>
                </div>
                <div class="form-group">
                    <label>Rate</label>
                    <input type="number" name="rate" id="unitRate" required>
                </div>
                <div class="form-group">
                    <label>Image</label>
                    <input type="file" name="image">
                </div>
                <button class="btn" type="submit">Save</button>
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
        <button class="btn" onclick="goToAddTenant()">Proceed to Lease Form</button>
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
            document.getElementById("unitModal").style.display = "block";
        }

        function openEditModal(unit) {
            document.getElementById("modalTitle").textContent = "Edit Unit";
            document.getElementById("formAction").value = "edit";
            document.getElementById("unitId").value = unit.id;
            document.getElementById("unitName").value = unit.name;
            document.getElementById("unitDetails").value = unit.details;
            document.getElementById("unitRate").value = unit.rate;
            document.getElementById("unitModal").style.display = "block";
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
        document.getElementById('leaseModal').style.display = "block";
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
