<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "devicedb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]));
}

// Get request data
$data = json_decode(file_get_contents("php://input"), true);
$fingerprint = $data["fingerprint"] ?? '';

if (empty($fingerprint)) {
    echo json_encode(["status" => "error", "message" => "Invalid fingerprint"]);
    exit;
}

// Check if the fingerprint exists
$stmt = $conn->prepare("SELECT id, is_approved, registration_complete FROM devices WHERE fingerprint = ?");
$stmt->bind_param("s", $fingerprint);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Check if registration is complete
    if (!$row['registration_complete']) {
        echo json_encode(["status" => "Unregistered"]);
        exit;
    }
    
    // Check if device is approved
    $stmt = $conn->prepare("SELECT 1 FROM approved_ids WHERE device_id = ?");
    $stmt->bind_param("i", $row['id']);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(["status" => "Authorized"]);
    } elseif ($row['is_approved'] == TRUE) {
        echo json_encode(["status" => "Authorized"]);
    } else {
        echo json_encode(["status" => "Unauthorized", "message" => "Access denied. Please contact support for approval."]);
    }
} else {
    // Register new device (without customer info yet)
    $stmt = $conn->prepare("INSERT INTO devices (fingerprint, created_at, is_approved, registration_complete) VALUES (?, NOW(), FALSE, FALSE)");
    $stmt->bind_param("s", $fingerprint);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "NewDevice", "message" => "Please register your device"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to store fingerprint"]);
    }
}

$conn->close();
?>