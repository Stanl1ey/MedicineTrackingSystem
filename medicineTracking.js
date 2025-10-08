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
            window.location.href = "login.html";
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
        window.location.href = "login.html";
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

        if (data.status === 'success') {
            medicineTableBody.innerHTML = '';
            
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
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error loading medicines:', error);
        alert('Failed to load medicines');
    }
}

// Handle adding medicine
const medicineForm = document.getElementById("medicineForm");
if (medicineForm) {
    medicineForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        const medicineName = document.getElementById("medicineName").value;
        const dose = document.getElementById("dose").value;
        const frequency = document.getElementById("frequency").value;
        const alertDate = document.getElementById("alertDate").value;
        const alertTime = document.getElementById("alertTime").value;

        try {
            const response = await fetch(`${API_BASE}/medicines.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    medicineName,
                    dose,
                    frequency,
                    alertDate,
                    alertTime
                })
            });

            const data = await response.json();
            
            if (data.status === 'success') {
                medicineForm.reset();
                await loadMedicines(); // Reload the medicines list
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Error adding medicine:', error);
            alert('Failed to add medicine');
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
                    } else {
                        throw new Error(data.message);
                    }
                } catch (error) {
                    console.error('Error deleting medicine:', error);
                    alert('Failed to delete medicine');
                }
            }
        }
    });
}

// Handle logout
const logoutButton = document.getElementById("logoutButton");
if (logoutButton) {
    logoutButton.addEventListener("click", async function () {
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
                alert("You have been logged out.");
                window.location.href = "login.html";
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Logout error:', error);
            alert("You have been logged out.");
            window.location.href = "login.html";
        }
    });
}

// Initialize the app when DOM is loaded
document.addEventListener('DOMContentLoaded', initializeApp);