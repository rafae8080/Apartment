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
    <style>

        .form-container {
            max-width: 500px;
            margin: 80px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #444;
        }

        input[type="text"],
        input[type="date"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="date"]:focus {
            border-color: #007BFF;
            outline: none;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .create-btn,
        .close-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .create-btn {
            background-color: #007BFF;
            color: #fff;
        }

        .create-btn:hover {
            background-color: #0056b3;
        }

        .close-btn {
            background-color: #e0e0e0;
            color: #333;
        }

        .close-btn:hover {
            background-color: #c2c2c2;
        }

        .navbar {
            background-color: #1f2937;
            color: #fff;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar .logo a {
            color: #fff;
            font-size: 20px;
            text-decoration: none;
            font-weight: bold;
        }

        .navbar .nav-links a {
            color: #d1d5db;
            text-decoration: none;
            margin-left: 20px;
            font-weight: 500;
        }

        .navbar .nav-links a:hover {
            color: #ffffff;
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
