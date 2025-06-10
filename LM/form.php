<?php
$apartment = isset($_GET['apartment']) ? htmlspecialchars($_GET['apartment']) : '';
$unit = isset($_GET['unit']) ? htmlspecialchars($_GET['unit']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Lease</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/form.css">
   <style>
    body {
  margin: 0;
  padding: 0;
  position: relative;
  min-height: 100vh;
  background-image: url('uploads/background.jpg');
  background-repeat: no-repeat;
  background-attachment: fixed;
  background-size: 100% 100%;
}

body::before {
  content: "";
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: black; /* or any overlay color */
  opacity: 0.6; /* simulate background opacity */
  z-index: -1;
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
            <a href="transactions.php">Transactions</a>
            <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>

        </div>
    </nav>

    <div class="form-container">
        <h2>Create Lease</h2>
        <form method="post" action="submit.php">
            <input type="hidden" name="apartment" value="<?= $apartment ?>">
            <input type="hidden" name="unit" value="<?= $unit ?>">

            <div class="form-group">
                <label>Apartment:</label>
                <input type="text" value="<?= $apartment ?>" readonly>
            </div>

            <div class="form-group">
                <label>Unit:</label>
                <input type="text" value="<?= $unit ?>" readonly>
            </div>

            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>Contact Number:</label>
                <input type="text" name="number" required>
            </div>

            <div class="form-group">
                <label>Move In Date:</label>
                <input type="date" name="moveIn" required>
            </div>

            <div class="form-group">
                <label>Move Out Date:</label>
                <input type="date" name="moveOut" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="create-btn">Create Lease</button>
                <button type="button" class="close-btn" onclick="window.location.href='index.php'">Close</button>
            </div>
        </form>
    </div>
</body>
</html>
