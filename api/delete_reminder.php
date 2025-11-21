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
$delete_id = intval($_GET['delete_id']);

require '../includes/db.php';

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit();
}

$sql = "DELETE FROM medicine_history WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $delete_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$stmt->close();
$conn->close();
?>