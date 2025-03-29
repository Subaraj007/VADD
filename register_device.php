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
$shopName = $data["shop_name"] ?? '';
$postalCode = $data["postal_code"] ?? '';
$deviceNumber = $data["device_number"] ?? '';

if (empty($fingerprint) || empty($shopName) || empty($postalCode) || empty($deviceNumber)) {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

// Update device with customer information
$stmt = $conn->prepare("UPDATE devices SET shop_name = ?, postal_code = ?, device_number = ?, registration_complete = TRUE WHERE fingerprint = ?");
$stmt->bind_param("ssss", $shopName, $postalCode, $deviceNumber, $fingerprint);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Device registered successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update device information"]);
}

$conn->close();
?>