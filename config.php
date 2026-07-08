<?php
// config.php - Remote Database Configuration

// Remote database credentials
$servername = "dbadmin.dcism.org";
$username = "s25101180_IM2";        // Your username with underscore
$password = "YOUR_PASSWORD_HERE";    // ⚠️ CHANGE THIS
$dbname = "s25101180_IM2";           // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        'error' => 'Database connection failed',
        'message' => $conn->connect_error
    ]));
}

$conn->set_charset("utf8mb4");

// Helper function for JSON responses
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Headers: Content-Type');
    echo json_encode($data);
    exit;
}
?>
