<?php
// Include necessary configurations and database setup
require_once 'config.php';
session_start();

$database = new Database();
$db = $database->getConnection();

$response = array();

try {
    // Check user authentication
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "Not authenticated"]);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            // Get POST data
            $input = json_decode(file_get_contents('php://input'), true);

            if (!empty($input['medicineName']) && !empty($input['dose']) && !empty($input['frequency']) && !empty($input['alertDate']) && !empty($input['alertTime'])) {
                $medicineName = $input['medicineName'];
                $dose = $input['dose'];
                $frequency = $input['frequency'];
                $alertDate = $input['alertDate'];
                $alertTime = $input['alertTime'];

                // Insert query
                $query = "INSERT INTO medicines (medicine_name, dose, frequency, alert_date, alert_time, user_id) 
                          VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($query);

                if ($stmt->execute([$medicineName, $dose, $frequency, $alertDate, $alertTime, $user_id])) {
                    $medicineId = $db->lastInsertId();
                    echo json_encode(["status" => "success", "message" => "Medicine added successfully", "medicine_id" => $medicineId]);
                } else {
                    $errorInfo = $stmt->errorInfo();
                    echo json_encode(["status" => "error", "message" => "Failed to add medicine: " . $errorInfo[2]]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "All fields are required"]);
            }
            break;

        // Other methods (GET, DELETE) for handling data retrieval and deletion can be added here...
        
        default:
            echo json_encode(["status" => "error", "message" => "Method not allowed"]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
}
?>
    