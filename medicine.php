<?php
require_once 'config.php';

$user_id = checkAuth();
$database = new Database();
$db = $database->getConnection();

$response = array();

switch($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Get all medicines for user
        $query = "SELECT * FROM medicines WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id]);
        $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response = array("status" => "success", "medicines" => $medicines);
        break;

    case 'POST':
        // Add new medicine
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->medicineName) && !empty($data->dose) && !empty($data->frequency) && !empty($data->alertDate) && !empty($data->alertTime)) {
            $query = "INSERT INTO medicines (medicine_name, dose, frequency, alert_date, alert_time, user_id) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$data->medicineName, $data->dose, $data->frequency, $data->alertDate, $data->alertTime, $user_id])) {
                $response = array("status" => "success", "message" => "Medicine added successfully");
            } else {
                $response = array("status" => "error", "message" => "Failed to add medicine");
            }
        } else {
            $response = array("status" => "error", "message" => "All fields are required");
        }
        break;

    case 'DELETE':
        // Delete medicine
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->id)) {
            // Verify medicine belongs to user
            $query = "SELECT id FROM medicines WHERE id = ? AND user_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$data->id, $user_id]);
            
            if ($stmt->rowCount() > 0) {
                $query = "DELETE FROM medicines WHERE id = ?";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$data->id])) {
                    $response = array("status" => "success", "message" => "Medicine deleted successfully");
                } else {
                    $response = array("status" => "error", "message" => "Failed to delete medicine");
                }
            } else {
                $response = array("status" => "error", "message" => "Medicine not found or access denied");
            }
        }
        break;
}

echo json_encode($response);
?>