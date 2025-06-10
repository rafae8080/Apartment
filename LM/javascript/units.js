function openAddModal() {
        document.getElementById("modalTitle").textContent = "Add Unit";
        document.getElementById("formAction").value = "add";
        document.getElementById("unitId").value = "";
        document.getElementById("unitName").value = "";
        document.getElementById("unitDetails").value = "";
        document.getElementById("unitRate").value = "";
        document.getElementById("unitModal").style.display = "flex";
    }

    function openEditModal(unit) {
        document.getElementById("modalTitle").textContent = "Edit Unit";
        document.getElementById("formAction").value = "edit";
        document.getElementById("unitId").value = unit.id;
        document.getElementById("unitName").value = unit.name;
        document.getElementById("unitDetails").value = unit.details;
        document.getElementById("unitRate").value = unit.rate;
        document.getElementById("unitModal").style.display = "flex";
    }

    function closeModal() {
        document.getElementById("unitModal").style.display = "none";
    }

    // Show notifications if redirected with status
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const msg = urlParams.get('msg');
    if (status && msg) {
        const notif = document.getElementById("notif");
        const content = document.getElementById("notifMessage");
        notif.classList.add(status === "success" ? "success" : "error");
        content.textContent = decodeURIComponent(msg);
        notif.style.display = "block";
        setTimeout(() => { notif.style.display = "none"; }, 3000);
    }

    function openLeaseModal(apartmentName, unitName, details, rate) {
        document.getElementById('leaseModalTitle').textContent = unitName + " - " + apartmentName;
        document.getElementById('leaseModalDescription').textContent = "Details: " + details;
        document.getElementById('leaseModalRate').textContent = "Monthly Rate: ₱" + rate;
        window.selectedApartment = apartmentName;
        window.selectedUnit = unitName;
        document.getElementById('leaseModal').style.display = "flex";
    }

    function closeLeaseModal() {
        document.getElementById('leaseModal').style.display = "none";
    }

    function goToAddTenant() {
        const apartment = encodeURIComponent(window.selectedApartment);
        const unit = encodeURIComponent(window.selectedUnit);
        window.location.href = `form.php?apartment=${apartment}&unit=${unit}`;
    }