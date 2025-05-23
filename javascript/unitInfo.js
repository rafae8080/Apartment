function openModal(apartmentName, unitName, detailsLabel, description) {
    // Show the modal
    const modal = document.getElementById('modal');
    modal.style.display = 'block';

    // Fill modal content
    document.getElementById('modal-title').textContent = unitName;
    document.getElementById('modal-address').textContent = 'Apartment: ' + apartmentName;
    document.getElementById('modal-description').textContent = detailsLabel;
    document.getElementById('modal-rate').textContent = description;
}

function closeModal() {
    const modal = document.getElementById('modal');
    modal.style.display = 'none';
}

function goToAddTenant() {
    // You can change this to a dynamic link or route as needed
    window.location.href = "lease.php";
}
