<?php
session_start();
header('Content-Type: application/json');

error_reporting(0);
ini_set('display_errors', 0);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$reminder_id = isset($_POST['reminder_id']) ? intval($_POST['reminder_id']) : 0;

require '../includes/db.php';

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit();
}

// For test reminders (ID 999), just return success
if ($reminder_id === 999) {
    echo json_encode(['success' => true, 'message' => 'Test reminder marked as taken']);
    exit();
}

// Update the taken_time for the reminder
$current_time = date('Y-m-d H:i:s');
$sql = "UPDATE medicine_history SET taken_time = ? WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("sii", $current_time, $reminder_id, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>