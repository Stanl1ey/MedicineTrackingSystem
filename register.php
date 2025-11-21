<?php
require 'includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (strlen($username) < 3) {
        $error = "Username must be at least 3 characters long";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Use prepared statement
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password_hash);
        
        if ($stmt->execute()) {
            header("Location: login.php?success=registered");
            exit();
        } else {
            $error = "Username already exists";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Medicine Tracker</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="auth-card">
            <h2>Create Account</h2>
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="username" required placeholder="Username">
                </div>
                <div class="form-group">
                    <input type="password" name="password" required placeholder="Password">
                </div>
                <button type="submit">Register</button>
            </form>
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>