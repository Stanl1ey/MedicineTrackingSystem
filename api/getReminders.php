<?php
session_start();
header('Content-Type: application/json');

// Turn off error reporting to prevent breaking JSON
error_reporting(0);
ini_set('display_errors', 0);

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$user_id = $_SESSION['user_id'];
require '../includes/db.php';

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Get current time in proper format
$current_time = date('Y-m-d H:i:s');

// DEBUG: Log current time for testing
error_log("=== REMINDER CHECK DEBUG ===");
error_log("Current Server Time: $current_time");
error_log("User ID: $user_id");

// FIXED: Get reminders that are currently active
// Using NOW() in MySQL to ensure timezone consistency
$sql = "SELECT * FROM medicine_history 
        WHERE user_id = ? 
        AND start_time <= NOW() 
        AND end_time >= NOW() 
        AND taken_time IS NULL 
        ORDER BY start_time ASC";
        
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $reminders = [];
    while ($row = $result->fetch_assoc()) {
        $reminders[] = $row;
        
        // DEBUG: Log each found reminder
        error_log("ACTIVE REMINDER: " . $row['medicine_name'] . 
                 " from " . $row['start_time'] . 
                 " to " . $row['end_time']);
    }
    
    // DEBUG: Log summary
    error_log("TOTAL ACTIVE REMINDERS: " . count($reminders));
    
    $stmt->close();
} else {
    error_log("SQL ERROR: " . $conn->error);
    $reminders = [];
}

$conn->close();

// DEBUG: Add debug info to response
if (isset($_GET['debug'])) {
    echo json_encode([
        'reminders' => $reminders,
        'debug_info' => [
            'current_time' => $current_time,
            'server_timezone' => date_default_timezone_get(),
            'total_found' => count($reminders)
        ]
    ]);
} else {
    echo json_encode($reminders);
}
?>