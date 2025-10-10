const API_BASE = 'http://localhost/medicine_tracker';

let currentUser = null;

// Check authentication and load data
async function initializeApp() {
    try {
        const response = await fetch(`${API_BASE}/auth.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: 'check_auth' })
        });

        const data = await response.json();
        
        if (data.status !== 'success') {
            window.location.href = "index.html";
            return;
        }

        currentUser = data.user;

        // Set username in UI
        const usernameElement = document.getElementById('usernameDisplay');
        if (usernameElement) {
            usernameElement.textContent = currentUser.username;
        }

        // Load profile picture
        await loadProfilePicture();
        
        // Load medicines
        await loadMedicines();
    } catch (error) {
        console.error('Auth check failed:', error);
        window.location.href = "index.html";
    }
}

// Load profile picture
async function loadProfilePicture() {
    const profilePicDisplay = document.getElementById("profilePicDisplay");

    if (!profilePicDisplay) return;

    try {
        const response = await fetch(`${API_BASE}/users.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: 'get_profile' })
        });

        if (response.ok) {
            const data = await response.json();
            if (data.status === 'success' && data.user.profile_pic) {
                profilePicDisplay.src = data.user.profile_pic;
            }
        }
    } catch (error) {
        console.error('Failed to load profile picture:', error);
    }
}

// Handle profile picture upload
const profilePicInput = document.getElementById("profilePicInput");
if (profilePicInput) {
    profilePicInput.addEventListener("change", async function (e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validate file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Please select an image smaller than 2MB');
            return;
        }

        const reader = new FileReader();
        reader.onload = async function () {
            const profilePicDisplay = document.getElementById("profilePicDisplay");
            profilePicDisplay.src = reader.result;

            try {
                const response = await fetch(`${API_BASE}/users.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ 
                        action: 'update_profile_pic',
                        profile_pic: reader.result 
                    })
                });

                const data = await response.json();
                if (data.status !== 'success') {
                    throw new Error(data.message);
                } else {
                    alert('Profile picture updated successfully!');
                }
            } catch (error) {
                console.error('Error updating profile picture:', error);
                alert('Failed to update profile picture');
            }
        };
        reader.readAsDataURL(file);
    });
}

// Load medicines from database
async function loadMedicines() {
    const medicineTableBody = document.querySelector("#medicineTable tbody");

    if (!medicineTableBody) return;

    try {
        const response = await fetch(`${API_BASE}/medicines.php`);
        const data = await response.json();

        console.log("Medicines response:", data); // Debug log

        if (data.status === 'success') {
            medicineTableBody.innerHTML = '';
            
            if (data.medicines && data.medicines.length > 0) {
                data.medicines.forEach(medicine => {
                    const newRow = document.createElement("tr");
                    newRow.innerHTML = `
                        <td>${medicine.medicine_name}</td>
                        <td>${medicine.dose}</td>
                        <td>${medicine.frequency}</td>
                        <td>${medicine.alert_date} ${medicine.alert_time}</td>
                        <td><button class="deleteBtn" data-id="${medicine.id}">Delete</button></td>
                    `;
                    medicineTableBody.appendChild(newRow);
                });
            } else {
                medicineTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No medicines found. Add your first medicine above.</td></tr>';
            }
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error loading medicines:', error);
        medicineTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: red;">Failed to load medicines</td></tr>';
    }
}

// Handle adding medicine
const medicineForm = document.getElementById("medicineForm");
if (medicineForm) {
    medicineForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        const medicineName = document.getElementById("medicineName").value.trim();
        const dose = document.getElementById("dose").value.trim();
        const frequency = document.getElementById("frequency").value.trim();
        const alertDate = document.getElementById("alertDate").value;
        const alertTime = document.getElementById("alertTime").value;

        // Validation
        if (!medicineName || !dose || !frequency || !alertDate || !alertTime) {
            alert('Please fill in all fields');
            return;
        }

        // Show loading state
        const submitBtn = medicineForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Adding...';
        submitBtn.disabled = true;

        try {
            const medicineData = {
                medicineName: medicineName,
                dose: dose,
                frequency: frequency,
                alertDate: alertDate,
                alertTime: alertTime
            };

            console.log("Sending medicine data:", medicineData); // Debug log

            const response = await fetch(`${API_BASE}/medicines.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(medicineData)
            });

            const data = await response.json();
            console.log("Add medicine response:", data); // Debug log

            if (data.status === 'success') {
                medicineForm.reset();
                await loadMedicines(); // Reload the medicines list
                alert('Medicine added successfully!');
            } else {
                throw new Error(data.message || 'Failed to add medicine');
            }
        } catch (error) {
            console.error('Error adding medicine:', error);
            alert('Error adding medicine: ' + error.message);
        } finally {
            // Reset button state
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    });
}

// Handle deleting medicine
const medicineTableBody = document.querySelector("#medicineTable tbody");
if (medicineTableBody) {
    medicineTableBody.addEventListener("click", async function (e) {
        if (e.target.classList.contains("deleteBtn")) {
            const medicineId = e.target.getAttribute('data-id');

            if (confirm("Are you sure you want to delete this medicine?")) {
                try {
                    const response = await fetch(`${API_BASE}/medicines.php`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id: medicineId })
                    });

                    const data = await response.json();

                    if (data.status === 'success') {
                        await loadMedicines(); // Reload the medicines list
                        alert('Medicine deleted successfully!');
                    } else {
                        throw new Error(data.message);
                    }
                } catch (error) {
                    console.error('Error deleting medicine:', error);
                    alert('Failed to delete medicine: ' + error.message);
                }
            }
        }
    });
}

// Handle logout
const logoutButton = document.getElementById("logoutButton");
if (logoutButton) {
    logoutButton.addEventListener("click", async function () {
        if (confirm("Are you sure you want to logout?")) {
            try {
                const response = await fetch(`${API_BASE}/auth.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ action: 'logout' })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    alert("You have been logged out successfully.");
                    window.location.href = "index.html";
                } else {
                    // Even if server logout fails, redirect to login page
                    console.error('Logout server error:', data.message);
                    alert("You have been logged out.");
                    window.location.href = "index.html";
                }
            } catch (error) {
                console.error('Logout network error:', error);
                // If there's a network error, still redirect to login page
                alert("You have been logged out.");
                window.location.href = "index.html";
            }
        }
    });
}

// Initialize the app when DOM is loaded
document.addEventListener('DOMContentLoaded', initializeApp);
