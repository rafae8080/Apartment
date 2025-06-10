
        function openEditModal(id, name, address) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_address').value = address;
            document.getElementById('editModal').style.display = 'flex';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            if (event.target === addModal) {
                addModal.style.display = "none";
            }
            if (event.target === editModal) {
                editModal.style.display = "none";
            }
        }
