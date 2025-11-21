<?php
session_start();
require 'includes/db.php';

echo "<h2>Time Debug Information</h2>";

// Check server time
echo "<h3>Server Information:</h3>";
echo "Server Time: " . date('Y-m-d H:i:s') . "<br>";
echo "Server Timezone: " . date_default_timezone_get() . "<br>";
echo "Unix Timestamp: " . time() . "<br><br>";

// Get all medicines for current user
$user_id = $_SESSION['user_id'] ?? 1;
$sql = "SELECT * FROM medicine_history WHERE user_id = ? ORDER BY start_time ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<h3>All Medicines in Database:</h3>";
while ($row = $result->fetch_assoc()) {
    $start = strtotime($row['start_time']);
    $end = strtotime($row['end_time']);
    $current = time();
    $is_active = ($current >= $start && $current <= $end);
    
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>";
    echo "<strong>" . $row['medicine_name'] . "</strong><br>";
    echo "Dosage: " . $row['dosage'] . "<br>";
    echo "Start: " . $row['start_time'] . " (" . date('Y-m-d H:i:s', $start) . ")<br>";
    echo "End: " . $row['end_time'] . " (" . date('Y-m-d H:i:s', $end) . ")<br>";
    echo "Current: " . date('Y-m-d H:i:s', $current) . "<br>";
    echo "Status: <strong style='color: " . ($is_active ? 'green' : 'red') . "'>" . 
          ($is_active ? 'ACTIVE' : 'INACTIVE') . "</strong><br>";
    echo "Taken: " . ($row['taken_time'] ? $row['taken_time'] : 'Not taken');
    echo "</div>";
}

$stmt->close();
$conn->close();
?>