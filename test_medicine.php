<?php
// Test script for medicine functionality
session_start();
$_SESSION['user_id'] = 1; // Temporarily set a user ID for testing
$_SESSION['username'] = 'testuser';

require_once 'config.php';

$database = new Database();
$db = $database->getConnection();

echo "<h1>Medicine Test Page</h1>";

// Test database connection
try {
    // Test if we can insert a medicine
    $testData = [
        'medicineName' => 'Test Medicine',
        'dose' => '500mg',
        'frequency' => 'Once daily',
        'alertDate' => '2024-01-01',
        'alertTime' => '08:00:00',
        'user_id' => 1
    ];
    
    $query = "INSERT INTO medicines (medicine_name, dose, frequency, alert_date, alert_time, user_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$testData['medicineName'], $testData['dose'], $testData['frequency'], $testData['alertDate'], $testData['alertTime'], $testData['user_id']])) {
        echo "<p style='color: green;'>✓ Medicine insertion test passed</p>";
        
        // Clean up test data
        $db->query("DELETE FROM medicines WHERE medicine_name = 'Test Medicine'");
    } else {
        echo "<p style='color: red;'>✗ Medicine insertion test failed</p>";
    }
    
    // Test if we can retrieve medicines
    $query = "SELECT COUNT(*) as count FROM medicines WHERE user_id = 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Total medicines for user: " . $result['count'] . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Test failed: " . $e->getMessage() . "</p>";
}

// Show current medicines
echo "<h2>Current Medicines in Database:</h2>";
$query = "SELECT * FROM medicines WHERE user_id = 1 ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($medicines) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Dose</th><th>Frequency</th><th>Alert Date</th><th>Alert Time</th></tr>";
    foreach ($medicines as $medicine) {
        echo "<tr>";
        echo "<td>{$medicine['id']}</td>";
        echo "<td>{$medicine['medicine_name']}</td>";
        echo "<td>{$medicine['dose']}</td>";
        echo "<td>{$medicine['frequency']}</td>";
        echo "<td>{$medicine['alert_date']}</td>";
        echo "<td>{$medicine['alert_time']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No medicines found in database.</p>";
}
?>