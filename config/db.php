<?php
// ==========================================
// Database Configuration
// AI Resume Analyzer
// ==========================================

$host = "localhost";
$username = "root";
$password = "";
$database = "resume_analyzer";

$conn = new mysqli($host, $username, $password, $database);

// Check Connection
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Set Character Encoding
$conn->set_charset("utf8");
?>