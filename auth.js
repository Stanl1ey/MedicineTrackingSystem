const API_BASE = 'http://localhost/medicine_tracker';

// Check if user is logged in
async function checkAuth() {
    try {
        const response = await fetch(`${API_BASE}/auth.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: 'check_auth' })
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            window.location.href = "medicineTracking.html";
        }
    } catch (error) {
        console.error('Auth check failed:', error);
    }
}

// Login form
const loginForm = document.getElementById("loginForm");
if (loginForm) {
    loginForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        const loginUsername = document.getElementById("loginUsername").value;
        const loginPassword = document.getElementById("loginPassword").value;

        // Show loading state
        const submitBtn = loginForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Logging in...';
        submitBtn.disabled = true;

        try {
            const response = await fetch(`${API_BASE}/auth.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'login',
                    username: loginUsername,
                    password: loginPassword
                })
            });

            const data = await response.json();

            if (data.status === 'success') {
                alert("Login successful!");
                window.location.href = "medicineTracking.html";
            } else {
                alert(data.message || "Invalid credentials, please try again.");
            }
        } catch (error) {
            console.error('Login error:', error);
            alert("Network error. Please check your connection and try again.");
        } finally {
            // Reset button state
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    });
}

// Register form
const registerForm = document.getElementById("registerForm");
if (registerForm) {
    registerForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        const username = document.getElementById("username").value;
        const password = document.getElementById("password").value;

        // Show loading state
        const submitBtn = registerForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Registering...';
        submitBtn.disabled = true;

        try {
            const response = await fetch(`${API_BASE}/auth.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ 
                    action: 'register',
                    username: username, 
                    password: password 
                })
            });

            const data = await response.json();

            if (data.status === 'success') {
                alert("Registration successful! You can now login.");
                window.location.href = "index.html"; // Redirect to login page
            } else {
                alert(data.message || "Registration failed. Please try again.");
            }
        } catch (error) {
            console.error('Registration error:', error);
            alert("Network error. Please check your connection and try again.");
        } finally {
            // Reset button state
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    });
}

// Initialize auth check
if (window.location.pathname.includes('index.html') || 
    window.location.pathname.includes('login.html') || 
    window.location.pathname.includes('register.html')) {
    checkAuth();
}