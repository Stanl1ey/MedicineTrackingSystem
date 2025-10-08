<?php
// CORS headers at the VERY TOP
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

require_once 'config.php';

$user_id = checkAuth();
$database = new Database();
$db = $database->getConnection();

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (isset($data->action)) {
        switch($data->action) {
            case 'update_profile_pic':
                if (!empty($data->profile_pic)) {
                    $query = "UPDATE users SET profile_pic = ? WHERE id = ?";
                    $stmt = $db->prepare($query);
                    
                    if ($stmt->execute([$data->profile_pic, $user_id])) {
                        $_SESSION['profile_pic'] = $data->profile_pic;
                        $response = array("status" => "success", "message" => "Profile picture updated");
                    } else {
                        $response = array("status" => "error", "message" => "Failed to update profile picture");
                    }
                }
                break;

            case 'get_profile':
                $query = "SELECT id, username, profile_pic, created_at FROM users WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $response = array("status" => "success", "user" => $user);
                break;
        }
    }
}

echo json_encode($response);
?>