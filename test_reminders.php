<?php
session_start();
// Simulate being logged in for testing
$_SESSION['user_id'] = 1;
$_SESSION['logged_in'] = true;

include 'api/getReminders.php';
?>