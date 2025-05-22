<!DOCTYPE html>
<html>
<head>
    <title>Simple Form</title>
<link rel="stylesheet" href="css/styles.css" />
<link rel="stylesheet" href="css/navbar.css">


    
</head>
<body>
  <nav class="navbar">
    <div class="logo">
      <a href="index.html">RM</a>
    </div>
    <div class="nav-links">
      <a href="index.php">Apartments</a>
      <a href="lease.html">Lease</a>
      <a href="#transactions">Transactions</a>
    </div>
  </nav>

  <div class="form-container">

    <h2>Submit Your Info</h2>
    <form method="post" action="submit.php">
      
        <div class="form-group">
        <label>Name:</label><br>
        <input type="text" name="name" required><br><br>
        </div>
        
        <div class="form-group">
        <label>Contact Number:</label><br>
        <input type="number" name="number" required><br><br>
        </div>

        <div class="form-group">
        <label>Move In Date:</label><br>
        <input type="date" name="moveIn" required><br><br>
        </div>

        <div class="form-group">
        <label>Move Out Date:</label><br>
        <input type="date" name="moveOut" required><br><br>
        </div>      

              <div class="form-actions">
        <button type="submit" class="create-btn">Create Lease</button>
        <button type="button" class="close-btn" onclick="window.location.href='index.html'">Close</button>
      </div>
    </form>
  </div>
</body>
</html>
