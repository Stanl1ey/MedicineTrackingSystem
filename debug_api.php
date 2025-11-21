<?php
session_start();
$_SESSION['user_id'] = 1; // Temporary for testing

require 'includes/db.php';

// Test database connection
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}

echo "✅ Database connected successfully!<br><br>";

// Test query - get all medicines for user 1
$sql = "SELECT * FROM medicine_history WHERE user_id = 1 ORDER BY start_time ASC";
$result = $conn->query($sql);

if ($result) {
    echo "📊 Found " . $result->num_rows . " medicines for user 1:<br><br>";
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $current_time = time();
            $start_time = strtotime($row['start_time']);
            $end_time = strtotime($row['end_time']);
            $is_active = ($start_time <= $current_time && $current_time <= $end_time);
            
            echo "💊 <strong>" . $row['medicine_name'] . "</strong><br>";
            echo "Dosage: " . $row['dosage'] . "<br>";
            echo "Start: " . $row['start_time'] . " (" . date('Y-m-d H:i:s', $start_time) . ")<br>";
            echo "End: " . $row['end_time'] . " (" . date('Y-m-d H:i:s', $end_time) . ")<br>";
            echo "Current: " . date('Y-m-d H:i:s', $current_time) . "<br>";
            echo "Status: " . ($is_active ? "✅ ACTIVE" : "❌ INACTIVE") . "<br>";
            echo "Taken: " . ($row['taken_time'] ? $row['taken_time'] : "Not taken") . "<br>";
            echo "<hr>";
        }
    } else {
        echo "No medicines found in database.<br>";
    }
} else {
    echo "❌ Query failed: " . $conn->error . "<br>";
}

// Test the active reminders query
echo "<br><strong>Testing Active Reminders Query:</strong><br>";
$current_time = date('Y-m-d H:i:s');
$sql = "SELECT * FROM medicine_history 
        WHERE user_id = 1 
        AND start_time <= ? 
        AND end_time >= ? 
        AND taken_time IS NULL 
        ORDER BY start_time ASC";
        
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("ss", $current_time, $current_time);
    $stmt->execute();
    $result = $stmt->get_result();

    $active_reminders = [];
    while ($row = $result->fetch_assoc()) {
        $active_reminders[] = $row;
    }
    
    echo "🎯 Found " . count($active_reminders) . " active reminders:<br>";
    foreach ($active_reminders as $reminder) {
        echo "- " . $reminder['medicine_name'] . " (ID: " . $reminder['id'] . ")<br>";
    }
    
    $stmt->close();
} else {
    echo "❌ Prepared statement failed: " . $conn->error . "<br>";
}

$conn->close();
?>