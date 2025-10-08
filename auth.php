<?php
require_once 'config.php';

$database = new Database();
$db = $database->getConnection();

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get raw JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    
    // Debug: Log received data
    error_log("Received data: " . print_r($data, true));

    if (isset($data->action)) {
        switch($data->action) {
            case 'register':
                if (!empty($data->username) && !empty($data->password)) {
                    try {
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
                    } catch (Exception $e) {
                        $response = array("status" => "error", "message" => "Database error: " . $e->getMessage());
                    }
                } else {
                    $response = array("status" => "error", "message" => "Username and password are required");
                }
                break;

            case 'login':
                if (!empty($data->username) && !empty($data->password)) {
                    try {
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
                    } catch (Exception $e) {
                        $response = array("status" => "error", "message" => "Database error: " . $e->getMessage());
                    }
                } else {
                    $response = array("status" => "error", "message" => "Username and password are required");
                }
                break;

            case 'check_auth':
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
                // Clear all session variables
                $_SESSION = array();
                
                // Destroy the session
                if (session_destroy()) {
                    $response = array("status" => "success", "message" => "Logged out successfully");
                } else {
                    $response = array("status" => "error", "message" => "Logout failed");
                }
                break;

            default:
                $response = array("status" => "error", "message" => "Invalid action");
                break;
        }
    } else {
        $response = array("status" => "error", "message" => "No action specified");
    }
} else {
    $response = array("status" => "error", "message" => "Invalid request method");
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>