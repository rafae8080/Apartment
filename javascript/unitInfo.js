function openModal(apartment, unitName, label, detailsWithRate) {
    const modal = document.getElementById("modal");
    const title = document.getElementById("modal-title");
    const address = document.getElementById("modal-address");
    const description = document.getElementById("modal-description");
    const rate = document.getElementById("modal-rate");

    // Separate details from the last line (which is the rate)
    const lines = detailsWithRate.split('\n');
    const rateLine = lines.pop(); // Last line = rate
    const detailText = lines.join('<br>'); // Rest are details

    // Populate modal content
    title.textContent = `${apartment} - ${unitName}`;
    address.textContent = label;
    description.innerHTML = detailText;
    rate.textContent = `₱${rateLine} / month`;

    // Show modal
    document.getElementById('modal').style.display = 'flex';
}

function closeModal() {
    document.getElementById("modal").style.display = "none";
}

function goToAddTenant() {
    window.location.href = "form.php";
}

// Close modal when clicking outside of it
window.onclick = function (event) {
    const modal = document.getElementById("modal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
};
