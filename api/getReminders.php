<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$user_id = $_SESSION['user_id'];
require 'includes/db.php'; // ✅ FIXED PATH

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Get current time in proper format
$current_time = date('Y-m-d H:i:s');

// Get reminders that are currently active (current time between start_time and end_time) and not taken
$sql = "SELECT * FROM medicine_history 
        WHERE user_id = ? 
        AND start_time <= ? 
        AND end_time >= ? 
        AND taken_time IS NULL 
        ORDER BY start_time ASC";
        
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("iss", $user_id, $current_time, $current_time);
    $stmt->execute();
    $result = $stmt->get_result();

    $reminders = [];
    while ($row = $result->fetch_assoc()) {
        $reminders[] = $row;
    }
    
    $stmt->close();
} else {
    $reminders = [];
}

$conn->close();
echo json_encode($reminders);
?>