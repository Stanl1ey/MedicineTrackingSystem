<?php
session_start();
require 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle form submission for new medicine
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['medicine_name'])) {
    $medicine_name = trim($_POST['medicine_name']);
    $dosage = trim($_POST['dosage']);
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    
    if (!empty($medicine_name) && !empty($dosage) && !empty($start_time) && !empty($end_time)) {
        if (strtotime($end_time) <= strtotime($start_time)) {
            $error = "End time must be after start time";
        } else {
            // Convert to proper datetime format for MySQL
            $start_time = date('Y-m-d H:i:s', strtotime($start_time));
            $end_time = date('Y-m-d H:i:s', strtotime($end_time));
            
            $sql = "INSERT INTO medicine_history (user_id, medicine_name, dosage, start_time, end_time) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("issss", $user_id, $medicine_name, $dosage, $start_time, $end_time);
                
                if ($stmt->execute()) {
                    $success = "Medicine reminder added successfully!";
                } else {
                    $error = "Failed to add reminder. Please try again.";
                }
                $stmt->close();
            } else {
                $error = "Database error: " . $conn->error;
            }
        }
    } else {
        $error = "Please fill in all fields";
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql = "DELETE FROM medicine_history WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ii", $delete_id, $user_id);
        
        if ($stmt->execute()) {
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Failed to delete reminder.";
        }
        $stmt->close();
    } else {
        $error = "Database error: " . $conn->error;
    }
}

// Handle move to history (when popup is closed)
if (isset($_GET['move_to_history'])) {
    $move_id = intval($_GET['move_to_history']);
    $current_time = date('Y-m-d H:i:s');
    $sql = "UPDATE medicine_history SET taken_time = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sii", $current_time, $move_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: dashboard.php");
    exit();
}

// Get user's upcoming medicine reminders (not taken and end time in future)
$upcoming_medicines = [];
$upcoming_sql = "SELECT * FROM medicine_history WHERE user_id = ? AND taken_time IS NULL AND end_time > NOW() ORDER BY start_time ASC";
$stmt = $conn->prepare($upcoming_sql);
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $upcoming_result = $stmt->get_result();
    $upcoming_medicines = $upcoming_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Get user's medicine history (taken or past end time)
$history_medicines = [];
$history_sql = "SELECT * FROM medicine_history WHERE user_id = ? AND (taken_time IS NOT NULL OR end_time <= NOW()) ORDER BY COALESCE(taken_time, end_time) DESC";
$stmt = $conn->prepare($history_sql);
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $history_result = $stmt->get_result();
    $history_medicines = $history_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Helper function to format datetime for display
function formatDateTimeForDisplay($datetime) {
    return date('M j, Y g:i A', strtotime($datetime));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Tracker</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>💊 Medicine Tracker <span class="api-indicator">RxNav API Enabled</span></h1>
            <div>
                Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>

        <?php if (!empty($success)): ?>
            <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="time-help">
            <strong>💡 How it works:</strong> Set your medicine reminder times below. The popup will automatically appear when the current time is between your start and end times.
            <br><strong>🔍 Medicine Search:</strong> Type any medicine name to search real-time data from RxNav API.
            <br><strong>⏰ Auto-History:</strong> Closing the reminder popup will automatically move the medicine to history.
        </div>

        <div class="section">
            <h2>Add New Medicine</h2>
            <form method="POST" id="medicineForm">
                <div class="form-group medicine-search-container">
                    <label>Medicine Name: <small>(Type to search real FDA data)</small></label>
                    <input type="text" name="medicine_name" required placeholder="Enter medicine name (type to search FDA database)" id="medicineNameInput">
                    <div class="search-results" id="searchResults"></div>
                </div>
                
                <div class="form-row">
                    <div class="form-column">
                        <div class="form-group">
                            <label>Dosage:</label>
                            <input type="text" name="dosage" required placeholder="e.g., 1 tablet, 500mg, 2 capsules" id="dosageInput">
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-column">
                        <div class="form-group">
                            <label>Start Time:</label>
                            <input type="datetime-local" name="start_time" required id="start_time">
                        </div>
                    </div>
                    <div class="form-column">
                        <div class="form-group">
                            <label>End Time:</label>
                            <input type="datetime-local" name="end_time" required id="end_time">
                        </div>
                    </div>
                </div>
                
                <button type="submit">Add Medicine</button>
                <button type="button" onclick="setTestTimes()" style="background: #28a745; margin-left: 10px;">Set Quick Test Times</button>
            </form>
        </div>

        <div class="section">
            <h2>Upcoming Reminders</h2>
            <?php if (empty($upcoming_medicines)): ?>
                <p>No upcoming reminders scheduled.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Medicine Name</th>
                            <th>Dosage</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcoming_medicines as $medicine): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($medicine['medicine_name']); ?></td>
                                <td><?php echo htmlspecialchars($medicine['dosage']); ?></td>
                                <td><?php echo formatDateTimeForDisplay($medicine['start_time']); ?></td>
                                <td><?php echo formatDateTimeForDisplay($medicine['end_time']); ?></td>
                                <td>
                                    <a href="?delete_id=<?php echo $medicine['id']; ?>" 
                                       class="btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this reminder?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>Medicine History</h2>
            <?php if (empty($history_medicines)): ?>
                <p>No past medicine history found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Medicine Name</th>
                            <th>Dosage</th>
                            <th>Time Window</th>
                            <th>Completion Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history_medicines as $medicine): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($medicine['medicine_name']); ?></td>
                                <td><?php echo htmlspecialchars($medicine['dosage']); ?></td>
                                <td>
                                    <?php echo formatDateTimeForDisplay($medicine['start_time']); ?> -<br>
                                    <?php echo formatDateTimeForDisplay($medicine['end_time']); ?>
                                </td>
                                <td>
                                    <?php if ($medicine['taken_time']): ?>
                                        <span style="color: green;">✓ Taken at <?php echo formatDateTimeForDisplay($medicine['taken_time']); ?></span>
                                    <?php else: ?>
                                        <span style="color: #666;">Time window expired</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Updated Popup - Only Close Button -->
    <div id="alertPopup">
        <div class="popup-content">
            <div style="font-size: 3rem; margin-bottom: 20px;">💊</div>
            <h3 id="alertMessage">Time for your medicine!</h3>
            <div id="popupDetails" style="margin: 15px 0; text-align: left; background: #f8f9fa; padding: 15px; border-radius: 8px;"></div>
            <div class="popup-buttons">
                <button onclick="markAsTakenAndClose()" class="btn-taken" style="background: #28a745;">Close & Move to History</button>
            </div>
        </div>
    </div>

    <!-- Test Popup Button -->
    <button class="test-popup-btn" onclick="testPopup()">Test Popup</button>

    <script src="js/script.js"></script>
</body>
</html>
<?php
$conn->close();
?>