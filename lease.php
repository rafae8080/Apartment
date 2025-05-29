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

$apartments = ['Burgos', 'Lazaro', 'Cupang', 'Sylvestre', 'San Luis'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Lease Overview</title>
    <link rel="stylesheet" href="css/lease.css">
    <link rel="stylesheet" href="css/payment.css">

</head>
<body>
    <h2>Lease Overview</h2>
    <div class="tabs">
        <?php foreach ($apartments as $index => $apartment): ?>
            <button class="tab<?= $index === 0 ? ' active' : '' ?>" onclick="showTab('<?= $apartment ?>', this)"><?= $apartment ?></button>
        <?php endforeach; ?>
    </div>

    <?php foreach ($apartments as $index => $apartment): ?>
        <div id="<?= $apartment ?>" class="tab-content<?= $index === 0 ? ' active' : '' ?>">
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
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
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
        function showTab(tabName, btn) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(tabName).classList.add('active');
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
