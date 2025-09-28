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

// Handle logout
const logoutButton = document.getElementById("logoutButton");

logoutButton.addEventListener("click", function () {
    localStorage.removeItem("username");
    localStorage.removeItem("password");
    alert("You have been logged out.");
    window.location.href = "login.html"; // Redirect to login page
});

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
});

// Save medicine to localStorage
function saveMedicine(medicine) {
    let medicines = JSON.parse(localStorage.getItem("medicines")) || [];
    medicines.push(medicine);
    localStorage.setItem("medicines", JSON.stringify(medicines));
}

// Delete medicine from localStorage and table
medicineTableBody.addEventListener("click", function (e) {
    if (e.target.classList.contains("deleteBtn")) {
        const row = e.target.closest("tr");
        const medicineName = row.children[0].textContent;
        deleteMedicine(medicineName);
        row.remove();
    }
});

// Delete medicine from localStorage
function deleteMedicine(medicineName) {
    let medicines = JSON.parse(localStorage.getItem("medicines")) || [];
    medicines = medicines.filter(medicine => medicine.medicineName !== medicineName);
    localStorage.setItem("medicines", JSON.stringify(medicines));
}
