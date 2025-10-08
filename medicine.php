<?php
require_once 'config.php';

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo json_encode(array("status" => "error", "message" => "Not authenticated"));
    exit;
}

$user_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();

$response = array();

try {
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
            // Get the raw POST data
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            // Log received data for debugging
            error_log("Received medicine data: " . print_r($data, true));
            
            if ($data && !empty($data['medicineName']) && !empty($data['dose']) && !empty($data['frequency']) && !empty($data['alertDate']) && !empty($data['alertTime'])) {
                
                $medicineName = $data['medicineName'];
                $dose = $data['dose'];
                $frequency = $data['frequency'];
                $alertDate = $data['alertDate'];
                $alertTime = $data['alertTime'];
                
                $query = "INSERT INTO medicines (medicine_name, dose, frequency, alert_date, alert_time, user_id) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$medicineName, $dose, $frequency, $alertDate, $alertTime, $user_id])) {
                    $medicineId = $db->lastInsertId();
                    $response = array(
                        "status" => "success", 
                        "message" => "Medicine added successfully",
                        "medicine_id" => $medicineId
                    );
                } else {
                    $errorInfo = $stmt->errorInfo();
                    $response = array("status" => "error", "message" => "Failed to add medicine: " . $errorInfo[2]);
                }
            } else {
                $response = array("status" => "error", "message" => "All fields are required");
            }
            break;

        case 'DELETE':
            // Get the raw DELETE data
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!empty($data['id'])) {
                $medicineId = $data['id'];
                
                // Verify medicine belongs to user
                $query = "SELECT id FROM medicines WHERE id = ? AND user_id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$medicineId, $user_id]);
                
                if ($stmt->rowCount() > 0) {
                    $query = "DELETE FROM medicines WHERE id = ?";
                    $stmt = $db->prepare($query);
                    
                    if ($stmt->execute([$medicineId])) {
                        $response = array("status" => "success", "message" => "Medicine deleted successfully");
                    } else {
                        $response = array("status" => "error", "message" => "Failed to delete medicine");
                    }
                } else {
                    $response = array("status" => "error", "message" => "Medicine not found or access denied");
                }
            } else {
                $response = array("status" => "error", "message" => "Medicine ID is required");
            }
            break;

        default:
            $response = array("status" => "error", "message" => "Method not allowed");
            break;
    }
} catch (Exception $e) {
    $response = array("status" => "error", "message" => "Server error: " . $e->getMessage());
}

echo json_encode($response);
?>