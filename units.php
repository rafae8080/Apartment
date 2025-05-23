<?php
// Database connection
$serverName = "LAPTOP-0QN98R6Q";
$connectionOptions = array(
    "Database" => "LeaseManagementDB",
    "Uid" => "",
    "PWD" => "",
);
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Get apartment name from query string
$apartmentName = isset($_GET['apartment']) ? $_GET['apartment'] : '';
if (!$apartmentName) {
    die("No apartment specified.");
}

// Fetch apartment ID
$apartmentSql = "SELECT id FROM Apartments WHERE name = ?";
$apartmentStmt = sqlsrv_query($conn, $apartmentSql, array($apartmentName));
$apartment = sqlsrv_fetch_array($apartmentStmt, SQLSRV_FETCH_ASSOC);
if (!$apartment) {
    die("Apartment not found.");
}
$apartmentId = $apartment['id'];

// Fetch units belonging to that apartment
$unitsSql = "SELECT name, image_path, details, rate FROM Units WHERE apartment_id = ?";
$unitsStmt = sqlsrv_query($conn, $unitsSql, array($apartmentId));
if ($unitsStmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($apartmentName) ?> Units</title>
    <link rel="stylesheet" href="css/units.css">
    <link rel="stylesheet" href="css/navbar.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
        }
        .modal-actions {
            margin-top: 20px;
            text-align: right;
        }
        .add-btn, .close-btn-2 {
            padding: 10px 15px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <a href="index.php">RM</a>
        </div>
        <div class="nav-links">
            <a href="index.php">Apartments</a>
            <a href="lease.php">Lease</a>
            <a href="#transactions">Transactions</a>
        </div>
    </nav>

    <div class="container" id="apartments">
        <h2 class="section-title"><?= htmlspecialchars($apartmentName) ?> Apartment Units</h2>
        <div class="apartment-grid">
            <?php while ($unit = sqlsrv_fetch_array($unitsStmt, SQLSRV_FETCH_ASSOC)) { ?>
                <div class="apartment-card" onclick='openModal(
                    <?= json_encode($apartmentName) ?>,
                    <?= json_encode($unit["name"]) ?>,
                    <?= json_encode($unit["details"]) ?>,
                    <?= json_encode("₱" . number_format($unit["rate"], 2)) ?>
                )'>
                    <img src="<?= htmlspecialchars($unit['image_path']) ?>" alt="Apartment Unit">
                    <div class="apartment-details">
                        <h3><?= htmlspecialchars($apartmentName) ?></h3>
                        <p><?= htmlspecialchars($unit['name']) ?></p>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <h2 id="modal-title"></h2>
            <p id="modal-description"></p>
            <p id="modal-rate"></p>
            <div class="modal-actions">
                <button class="add-btn" onclick="goToAddTenant()">Create Lease</button>
                <button class="close-btn-2" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>

    <script>
        function openModal(apartmentName, unitName, details, rate) {
            document.getElementById('modal-title').textContent = unitName + " - " + apartmentName;
            document.getElementById('modal-description').textContent = "Details: " + details;
            document.getElementById('modal-rate').textContent = "Monthly Rate: " + rate;
    // Save current apartment and unit to global vars
    window.selectedApartment = apartmentName;
    window.selectedUnit = unitName;
                document.getElementById('modal').style.display = "block";

        }

        function closeModal() {
            document.getElementById('modal').style.display = "none";
        }

function goToAddTenant() {
    const apartment = encodeURIComponent(window.selectedApartment);
    const unit = encodeURIComponent(window.selectedUnit);
    window.location.href = `form.php?apartment=${apartment}&unit=${unit}`;
}
    </script>
</body>
</html>

