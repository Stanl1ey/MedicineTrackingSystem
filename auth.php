<?php
require_once 'config.php';

$database = new Database();
$db = $database->getConnection();

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (isset($data->action)) {
        switch($data->action) {
            case 'register':
                // Register user
                if (!empty($data->username) && !empty($data->password)) {
                    $query = "SELECT id FROM users WHERE username = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$data->username]);
                    
                    if ($stmt->rowCount() > 0) {
                        $response = array("status" => "error", "message" => "Username already exists");
                    } else {
                        $hashed_password = password_hash($data->password, PASSWORD_DEFAULT);
                        $query = "INSERT INTO users (username, password) VALUES (?, ?)";
                        $stmt = $db->prepare($query);
                        
                        if ($stmt->execute([$data->username, $hashed_password])) {
                            $response = array("status" => "success", "message" => "Registration successful!");
                        } else {
                            $response = array("status" => "error", "message" => "Registration failed");
                        }
                    }
                }
                break;

            case 'login':
                // Login user
                if (!empty($data->username) && !empty($data->password)) {
                    $query = "SELECT id, username, password, profile_pic FROM users WHERE username = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$data->username]);
                    
                    if ($stmt->rowCount() == 1) {
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                        if (password_verify($data->password, $user['password'])) {
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['profile_pic'] = $user['profile_pic'];
                            
                            $response = array(
                                "status" => "success", 
                                "message" => "Login successful!",
                                "user" => array(
                                    "id" => $user['id'],
                                    "username" => $user['username'],
                                    "profile_pic" => $user['profile_pic']
                                )
                            );
                        } else {
                            $response = array("status" => "error", "message" => "Invalid credentials");
                        }
                    } else {
                        $response = array("status" => "error", "message" => "Invalid credentials");
                    }
                }
                break;

            case 'check_auth':
                // Check if user is logged in
                if (isset($_SESSION['user_id'])) {
                    $response = array(
                        "status" => "success",
                        "user" => array(
                            "id" => $_SESSION['user_id'],
                            "username" => $_SESSION['username'],
                            "profile_pic" => $_SESSION['profile_pic']
                        )
                    );
                } else {
                    $response = array("status" => "error", "message" => "Not authenticated");
                }
                break;

            case 'logout':
                // Logout user
                session_destroy();
                $response = array("status" => "success", "message" => "Logged out successfully");
                break;
        }
    }
}

echo json_encode($response);
?>