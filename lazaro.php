<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Burgos units</title>
    <link rel="stylesheet" href="css/units.css">
    <link rel="stylesheet" href="css/navbar.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <a href="index.php">RM</a>
        </div>
    </div>
    <div class="nav-links">
        <a href="index.php">Apartments</a>
        <a href="lease.php">Lease</a>
        <a href="#transactions">Transactions</a>
    </div>
    </nav>

    <div class="container" id="apartments">
        <h2 class="section-title">Lazaro Apartment Unit</h2>
        <div class="apartment-grid">

            <div class="apartment-card" onclick="openModal('Lazaro', 'Residential A','Details:', '2 rooms \n 1 bathroom \n own electric and water meter \n parking space \n 18,000')">
                <img src="images/Commercial.jpg" alt="Burgos Apartment">
                <div class="apartment-details">
                    <h3>Lazaro</h3>
                    <p> A</p>
                </div>
            </div>

            <div class="apartment-card" onclick="openModal('Lazaro', 'Residencial B','Details:', '2 rooms \n 1 bathroom \n own electric and water meter  \n parking space \n 18,000')">
                <img src="images/unit4.jpg" alt="Burgos Apartment">
                <div class="apartment-details">
                    <h3>Lazaro</h3>
                    <p>B</p>
                </div>
            </div>  
            <!-- Modal -->
            <div id="modal" class="modal">
                <div class="modal-content">
                    <span class="close-btn" onclick="closeModal()">&times;</span>
                    <h2 id="modal-title"></h2>
                    <p id="modal-address"></p>
                    <p id="modal-rate"></p>
                    <p id="modal-description"></p>
                    <div class="modal-actions">
                        <button class="add-btn" onclick="goToAddTenant()">Create Lease</button>
                        <button class="close-btn-2" onclick="closeModal()">Close</button>
                    </div>
                </div>
            </div>
    </div>
    <script src="javascript/unitInfo.js"></script>
        
</body>
</html>
