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