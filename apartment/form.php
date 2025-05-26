<?php
$apartment = isset($_GET['apartment']) ? htmlspecialchars($_GET['apartment']) : '';
$unit = isset($_GET['unit']) ? htmlspecialchars($_GET['unit']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Crete Lease</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/dashboard.css">
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

<div class="form-container">
<h2>Create Lease</h2>
<form method="post" action="submit.php">
        <div class="form-group">
    <input type="hidden" name="apartment" value="<?= $apartment ?>">
    <input type="hidden" name="unit" value="<?= $unit ?>">
      </div>
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
