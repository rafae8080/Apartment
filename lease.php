<?php
// lease.php

$serverName = "LAPTOP-0QN98R6Q";
$connectionOptions = [
    "Database" => "LeaseManagementDB",
    "Uid" => "",
    "PWD" => ""
];
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

$apartments = [];

// Fetch apartments
$sql = "SELECT name FROM Apartments ORDER BY id ASC";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die("Error fetching apartments: " . print_r(sqlsrv_errors(), true));
}

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $apartments[] = $row['name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Lease Overview</title>
  <link rel="stylesheet" href="css/lease.css" />
  <link rel="stylesheet" href="css/navbar.css">
    <style>

        /* Tabs */
        .apartment-tabs {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 25px 0 40px;
            font-family: 'Poppins', sans-serif;
        }
        .apartment-tab {
            padding: 12px 28px;
            border-radius: 12px;
            background-color: #273c75;
            color: #f9fbff;
            font-weight: 700;
            font-size: 16px;
            text-decoration: none;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 6px 14px rgba(39, 60, 117, 0.4);
            cursor: pointer;
            user-select: none;
        }
        .apartment-tab:hover,
        .apartment-tab.active {
            background-color: #4b6cb7;
            box-shadow: 0 8px 20px rgba(75, 108, 183, 0.6);
        }

        /* Tenant grid */
        .tenant-grid {
            display: none;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 28px;
            max-width: 1200px;
            margin: 0 auto 60px;
            padding: 0 20px;
            font-family: 'Poppins', sans-serif;
        }
        .tenant-grid.active {
            display: grid;
        }

        /* Tenant card */
        .tenant-card {
            background-color: #fff;
            padding: 28px 24px;
            border-radius: 16px;
            box-shadow: 0 8px 16px rgba(24, 40, 72, 0.12), 0 4px 8px rgba(24, 40, 72, 0.06);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 15px;
        }
        .tenant-card h3 {
            margin: 0 0 12px 0;
            color: #182848;
            font-size: 22px;
            font-weight: 700;
        }
        .tenant-card p {
            margin: 0;
            font-size: 15px;
            color: #5a6d85;
            line-height: 1.5;
        }

        /* Add Payment Button */
        .add-payment-btn {
            margin-top: 15px;
            padding: 12px 24px;
            background-color: #4b6cb7;
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.3s ease;
            align-self: flex-start;
            font-size: 16px;
            box-shadow: 0 6px 14px rgba(75, 108, 183, 0.5);
        }
        .add-payment-btn:hover {
            background-color: #3a53a1;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1100;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(24, 40, 72, 0.7);
            backdrop-filter: blur(4px);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: #fff;
            padding: 30px 35px;
            width: 380px;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(24, 40, 72, 0.2);
            font-family: 'Poppins', sans-serif;
            position: relative;
        }
        .close-btn {
            position: absolute;
            right: 15px;
            top: 15px;
            cursor: pointer;
            font-size: 22px;
            color: #182848;
            font-weight: 700;
        }
        .modal-content label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
            color: #5a6d85;
        }
        .modal-content input,
        .modal-content select {
            width: 100%;
            padding: 12px 14px;
            margin-top: 8px;
            border-radius: 12px;
            border: 1.8px solid #d6d9e6;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s ease;
        }
        .modal-content input:focus,
        .modal-content select:focus {
            border-color: #4b6cb7;
            outline: none;
        }
        .modal-content button[type="submit"] {
            margin-top: 22px;
            width: 100%;
            padding: 14px 0;
            background-color: #4b6cb7;
            color: white;
            font-weight: 700;
            border: none;
            border-radius: 16px;
            cursor: pointer;
            font-size: 17px;
            transition: background-color 0.3s ease;
        }
        .modal-content button[type="submit"]:hover {
            background-color: #3a53a1;
        }

        /* Success message */
        .success-message {
            background-color: #4BB543;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            max-width: 600px;
            margin: 20px auto;
            font-weight: 700;
            text-align: center;
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="logo"><a href="index.php">RM</a></div>
        <div class="nav-links">
            <a href="index.php">Apartments</a>
            <a href="lease.php" class="active">Lease</a>
            <a href="transaction.php">Transactions</a>
            <a href="logout.php">Logout</a>

        </div>
    </nav>

    <!-- Show success message if payment just succeeded -->
    <?php if (isset($_GET['payment_success']) && $_GET['payment_success'] == 1): ?>
        <div class="success-message">
            Payment successful! Transaction ID: <?= htmlspecialchars($_GET['transaction_id']) ?>
        </div>
    <?php endif; ?>

    <!-- Apartment tabs -->
    <div class="apartment-tabs">
        <?php foreach ($apartments as $index => $apartment): ?>
            <button class="apartment-tab<?= $index === 0 ? ' active' : '' ?>" onclick="showApartmentTab(event, '<?= htmlspecialchars($apartment) ?>')"><?= htmlspecialchars($apartment) ?></button>
        <?php endforeach; ?>
    </div>

    <!-- Tenant grids -->
    <?php foreach ($apartments as $index => $apartment): ?>
        <div id="<?= htmlspecialchars($apartment) ?>" class="tenant-grid<?= $index === 0 ? ' active' : '' ?>">
            <?php
            $sql = "
                SELECT 
                    l.tenantName, l.contactNumber, l.moveIn, l.moveOut,
                    u.name AS unitName
                FROM leases l
                JOIN apartments a ON l.apartment_id = a.id
                JOIN units u ON l.unit_id = u.id
                WHERE a.name = ?
            ";
            $stmt = sqlsrv_prepare($conn, $sql, [$apartment]);

            if (!$stmt || !sqlsrv_execute($stmt)) {
                echo "<p>Error loading leases: " . print_r(sqlsrv_errors(), true) . "</p>";
            } else {
                $hasLease = false;
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $hasLease = true;
                    $name = htmlspecialchars($row['tenantName']);
                    $contact = htmlspecialchars($row['contactNumber']);
                    $moveIn = $row['moveIn'] ? $row['moveIn']->format('Y-m-d') : '';
                    $moveOut = $row['moveOut'] ? $row['moveOut']->format('Y-m-d') : '';
                    $unit = htmlspecialchars($row['unitName']);

                    echo "<div class='tenant-card'>
                            <h3>$name</h3>
                            <p>Contact: $contact</p>
                            <p>Unit: $unit</p>
                            <p>Move-in: $moveIn</p>
                            <p>Move-out: $moveOut</p>
                            <button class='add-payment-btn' onclick=\"openPaymentModal('$apartment', '$unit', '$name')\">Add Payment</button>
                          </div>";
                }
                if (!$hasLease) {
                    echo "<p style='text-align:center; color:#5a6d85; font-family:\"Poppins\", sans-serif;'>No tenants found.</p>";
                }
            }
            ?>
        </div>
    <?php endforeach; ?>

    <!-- Payment Modal -->
    <div id="payment-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closePaymentModal()">&times;</span>
            <h3>Add Payment Info</h3>
            <form id="payment-form" method="post" action="submit_payment.php">
                <input type="hidden" id="modal-apartment" name="apartment" />
                <input type="hidden" id="modal-unit" name="unit" />
                <input type="hidden" id="modal-tenant" name="tenant" />

                <label for="payment_method">Payment Method:</label>
                <select name="payment_method" id="payment_method" required>
                    <option value="">Select</option>
                    <option value="On Hand">On Hand</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="GCash">GCash</option>
                </select>

                <label for="amount">Amount:</label>
                <input type="number" name="amount" id="amount" required min="1" />

                <label for="payment_date">Payment Date:</label>
                <input type="date" name="payment_date" id="payment_date" required />

                <button type="submit">Submit Payment</button>
            </form>
        </div>
    </div>

    <script>
        function showApartmentTab(event, apartmentName) {
            event.preventDefault();

            // Remove active class on all tabs
            document.querySelectorAll('.apartment-tab').forEach(tab => tab.classList.remove('active'));

            // Hide all tenant grids
            document.querySelectorAll('.tenant-grid').forEach(grid => grid.classList.remove('active'));

            // Show selected tenant grid
            document.getElementById(apartmentName).classList.add('active');

            // Set clicked tab active
            event.currentTarget.classList.add('active');
        }

        function openPaymentModal(apartment, unit, tenant) {
            document.getElementById('modal-apartment').value = apartment;
            document.getElementById('modal-unit').value = unit;
            document.getElementById('modal-tenant').value = tenant;
            document.getElementById('payment-modal').style.display = 'flex';
        }

        function closePaymentModal() {
            document.getElementById('payment-modal').style.display = 'none';
            document.getElementById('payment-form').reset();
        }

        window.onclick = function(event) {
            const modal = document.getElementById('payment-modal');
            if (event.target === modal) {
                closePaymentModal();
            }
        }
    </script>
</body>
</html>
