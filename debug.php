<?php
session_start();
echo "<h1>Debug Information</h1>";

// Check database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=medicine_tracker", "root", "");
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Check if tables exist
    $tables = ['users', 'medicines'];
    foreach ($tables as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            echo "<p style='color: green;'>✓ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>✗ Table '$table' missing</p>";
        }
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}

// Check session
echo "<h2>Session Info</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check PHP error log path
echo "<h2>PHP Info</h2>";
echo "Error log: " . ini_get('error_log') . "<br>";
echo "Display errors: " . ini_get('display_errors') . "<br>";
?>