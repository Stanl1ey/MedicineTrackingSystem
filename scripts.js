// Check if the user is already logged in
const username = localStorage.getItem("username");

if (username) {
    // If logged in, redirect to the medicine tracking page
    window.location.href = "medicineTracking.html";
}

const loginForm = document.getElementById("loginForm");

loginForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const loginUsername = document.getElementById("loginUsername").value;
    const loginPassword = document.getElementById("loginPassword").value;

    const storedUsername = localStorage.getItem("username");
    const storedPassword = localStorage.getItem("password");

    if (loginUsername === storedUsername && loginPassword === storedPassword) {
        alert("Login successful!");
        window.location.href = "medicineTracking.html"; // Redirect to the medicine tracking page
    } else {
        alert("Invalid credentials, please try again.");
    }
});

const registerForm = document.getElementById("registerForm");

registerForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

    localStorage.setItem("username", username);
    localStorage.setItem("password", password);

    alert("Registration successful! You can now login.");
    window.location.href = "login.html"; // Redirect to login page
});

// Handle profile picture upload
const profilePicInput = document.getElementById("profilePicInput");
const profilePicDisplay = document.getElementById("profilePicDisplay");

profilePicInput.addEventListener("change", function (e) {
    const reader = new FileReader();
    reader.onload = function () {
        profilePicDisplay.src = reader.result;
        localStorage.setItem("profilePic", reader.result); // Store profile picture in localStorage
    };
    reader.readAsDataURL(e.target.files[0]);
});

// Set profile picture if stored
window.onload = function () {
    const storedPic = localStorage.getItem("profilePic");
    if (storedPic) {
        profilePicDisplay.src = storedPic;
    }
};

// Medicine History (tracking user actions)
const medicineHistory = JSON.parse(localStorage.getItem("medicineHistory")) || [];
function addHistory(action) {
    medicineHistory.push({
        action,
        timestamp: new Date().toLocaleString()
    });
    localStorage.setItem("medicineHistory", JSON.stringify(medicineHistory));
}

// Handle adding medicine
const medicineForm = document.getElementById("medicineForm");
const medicineTableBody = document.querySelector("#medicineTable tbody");

medicineForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const medicineName = document.getElementById("medicineName").value;
    const dose = document.getElementById("dose").value;
    const frequency = document.getElementById("frequency").value;
    const alertDate = document.getElementById("alertDate").value;
    const alertTime = document.getElementById("alertTime").value;

    const newRow = document.createElement("tr");

    newRow.innerHTML = `
        <td>${medicineName}</td>
        <td>${dose}</td>
        <td>${frequency}</td>
        <td>${alertDate} ${alertTime}</td>
        <td><button class="deleteBtn">Delete</button></td>
    `;

    // Add to table
    medicineTableBody.appendChild(newRow);
    medicineForm.reset();

    // Save medicine to localStorage
    saveMedicine({ medicineName, dose, frequency, alertDate, alertTime });

    // Add to history
    addHistory(`Added ${medicineName}`);
});

// Save medicine to localStorage
function saveMedicine(medicine) {
    let medicines = JSON.parse(localStorage.getItem("medicines")) || [];
    medicines.push(medicine);
    localStorage.setItem("medicines", JSON.stringify(medicines));
}

// Reminder logic for alerting before time
function checkReminders() {
    const currentDate = new Date();
    const medicines = JSON.parse(localStorage.getItem("medicines")) || [];

    medicines.forEach(medicine => {
        const alertDateTime = new Date(`${medicine.alertDate}T${medicine.alertTime}:00`);
        const alertTimeBefore = new Date(alertDateTime.getTime() - 30 * 60000); // 30 minutes before

        // Check if it's time for reminder
        if (currentDate >= alertTimeBefore && currentDate < alertDateTime) {
            showReminderPopup(medicine);
        }
    });
}

// Show reminder popup
function showReminderPopup(medicine) {
    const popup = document.createElement("div");
    popup.classList.add("popup");

    popup.innerHTML = `
        <h3>Reminder: Take your medication</h3>
        <p>You need to take your medicine: ${medicine.medicineName}</p>
        <button id="pickBtn">Picked</button>
        <button id="snoozeBtn">Snooze 10 min</button>
    `;

    document.body.appendChild(popup);

    // Handle "Pick" button (remove from table and storage)
    document.getElementById("pickBtn").addEventListener("click", function() {
        popup.remove();
        deleteMedicine(medicine.medicineName);
    });

    // Handle "Snooze" button (reschedule reminder for 10 minutes later)
    document.getElementById("snoozeBtn").addEventListener("click", function() {
        popup.remove();
        snoozeReminder(medicine.medicineName);
    });
}

// Delete medicine from localStorage and table
function deleteMedicine(medicineName) {
    let medicines = JSON.parse(localStorage.getItem("medicines")) || [];
    medicines = medicines.filter(medicine => medicine.medicineName !== medicineName);
    localStorage.setItem("medicines", JSON.stringify(medicines));

    // Remove from table
    const rows = medicineTableBody.querySelectorAll("tr");
    rows.forEach(row => {
        if (row.children[0].textContent === medicineName) {
            row.remove();
        }
    });

    // Add to history
    addHistory(`Removed ${medicineName}`);
}

// Snooze reminder: add the alert date 10 minutes later
function snoozeReminder(medicineName) {
    let medicines = JSON.parse(localStorage.getItem("medicines")) || [];
    medicines = medicines.map(medicine => {
        if (medicine.medicineName === medicineName) {
            const newAlertDate = new Date(medicine.alertDate);
            newAlertDate.setMinutes(newAlertDate.getMinutes() + 10);  // Add 10 minutes
            medicine.alertDate = newAlertDate.toISOString();
        }
        return medicine;
    });
    localStorage.setItem("medicines", JSON.stringify(medicines));

    // Reschedule the check
    setTimeout(() => showReminderPopup(medicine), 600000);  // 10 minutes later
}

// Check reminders every minute
setInterval(checkReminders, 60000); // Check every minute
