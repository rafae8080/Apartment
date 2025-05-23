<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lease Management</title>
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/lease.css">
  <link rel="stylesheet" href="css/payment.css" />
  <link rel="stylesheet" href="css/dashboard.css" />
  <style>
    .modal {
      position: fixed;
      top: 0; left: 0; width: 100%; height: 100%;
      background-color: rgba(0,0,0,0.5);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
      display: none;
    }
    .modal-content {
      background: white;
      padding: 20px 25px;
      border-radius: 8px;
      width: 320px;
      position: relative;
    }
    .close-btn {
      position: absolute;
      top: 10px;
      right: 15px;
      cursor: pointer;
      font-size: 24px;
      font-weight: bold;
      color: #333;
    }
    .add-payment-btn {
      margin-top: 12px;
      padding: 8px 16px;
      background-color: #6374e8;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 600;
      transition: background-color 0.3s ease;
    }
    .add-payment-btn:hover {
      background-color: #4a5fcc;
    }
  </style>
</head>
<body>
<nav class="navbar">
  <div class="logo">
    <a href="index.html">RM</a>
  </div>
  <div class="nav-links">
    <a href="index.php">Apartments</a>
    <a href="lease.php">Lease</a>
    <a href="#transactions">Transactions</a>
  </div>
</nav>

<div class="container">
  <h2 class="section-title">Lease Overview</h2>
  <div class="tabs">
    <button class="tab active" onclick="showTab('Burgos', this)">Burgos</button>
    <button class="tab" onclick="showTab('Lazaro', this)">Lazaro</button>
    <button class="tab" onclick="showTab('Cupang', this)">Cupang</button>
    <button class="tab" onclick="showTab('Sylvestre', this)">Sylvestre</button>
    <button class="tab" onclick="showTab('Sanluis', this)">Sanluis</button>
  </div>

  <?php
  // SQL Server connection using Windows Authentication
  $serverName = "LAPTOP-0QN98R6Q";
  $connectionOptions = array(
      "Database" => "LeaseManagementDB",
      "Uid" => "",
      "PWD" => ""
  );
  $conn = sqlsrv_connect($serverName, $connectionOptions);

  if ($conn === false) {
      die("Connection failed: " . print_r(sqlsrv_errors(), true));
  }

  $apartments = ['Burgos', 'Lazaro', 'Cupang', 'Sylvestre', 'Sanluis'];

foreach ($apartments as $index => $apartment) {
    echo "<div id='$apartment' class='tab-content " . ($index === 0 ? "active" : "") . "'>";
    echo "<div class='tenant-grid'>";

    $sql = "
        SELECT 
            l.tenantName, l.contactNumber, l.moveIn, l.moveOut, 
            u.unitNumber
        FROM leases l
        JOIN apartments a ON l.apartment_id = a.apartmentID
        JOIN units u ON l.unit_id = u.unitID
        WHERE a.name = ?
    ";
    $params = array($apartment);
    $stmt = sqlsrv_prepare($conn, $sql, $params);

    if (!$stmt || !sqlsrv_execute($stmt)) {
        echo "<p>Error loading tenants: ";
        print_r(sqlsrv_errors(), true);
        echo "</p>";
    } else {
        $hasRows = false;
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $hasRows = true;
            $name = htmlspecialchars($row['tenantName']);
            $contact = htmlspecialchars($row['contactNumber']);
            $moveIn = $row['moveIn'] ? $row['moveIn']->format('Y-m-d') : '';
            $moveOut = $row['moveOut'] ? $row['moveOut']->format('Y-m-d') : '';
            $unit = htmlspecialchars($row['unitNumber']);

            echo "<div class='tenant-card'>
                    <h3>$name</h3>
                    <p>Contact: $contact</p>
                    <p>Move-in: $moveIn</p>
                    <p>Move-out: $moveOut</p>
                    <button class='add-payment-btn' onclick=\"openPaymentModal('$apartment', '$unit', '$name')\">Add Payment</button>
                  </div>";
        }
        if (!$hasRows) {
            echo "<p>No tenants yet.</p>";
        }
    }
    echo "</div>"; // tenant-grid
    echo "</div>"; // tab-content
}


  ?>
</div>

<!-- Payment Modal -->
<div id="payment-modal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closePaymentModal()">&times;</span>
    <h3>Add Payment Info</h3>
    <form id="payment-form" method="post" action="transaction.php">
      <input type="hidden" id="modal-apartment" name="apartment" value="">
      <input type="hidden" id="modal-unit" name="unit" value="">
      <input type="hidden" id="modal-tenant" name="tenant_name" value="">

      <label for="payment_mode">Payment Mode:</label>
      <select name="payment_method" id="payment_mode" required>
        <option value="">Select</option>
        <option value="Cash">Cash</option>
        <option value="Credit Card">Credit Card</option>
        <option value="Bank Transfer">Bank Transfer</option>
      </select>

      <label for="payment_date">Payment Date:</label>
      <input type="date" name="payment_date" id="payment_date" required />

      <label for="payment_amount">Payment Amount:</label>
      <input type="number" name="payment_amount" id="payment_amount" step="0.01" required />

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
