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

const logoutButton = document.getElementById("logoutButton");

logoutButton.addEventListener("click", function () {
    localStorage.removeItem("username");
    localStorage.removeItem("password");
    window.location.href = "login.html"; // Redirect to login page
});
