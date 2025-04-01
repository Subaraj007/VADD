<?php
require_once 'Database.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header("Cache-Control: no-store, no-cache, must-revalidate");

// Database connection (modified section only)
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
} catch (Exception $e) {
    die(json_encode(["status" => "error", "message" => "Database connection failed"]));
}

// REST OF YOUR EXISTING CODE REMAINS UNCHANGED
$data = json_decode(file_get_contents("php://input"), true);
$fingerprint = $data["fingerprint"] ?? '';

if (empty($fingerprint)) {
    echo json_encode(["status" => "error", "message" => "Invalid fingerprint"]);
    exit;
}

// Check device status
$stmt = $conn->prepare("SELECT id, is_approved, registration_complete, videos FROM devices WHERE fingerprint = ?");
$stmt->bind_param("s", $fingerprint);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    if (!$row['registration_complete']) {
        echo json_encode(["status" => "Unregistered"]);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT 1 FROM approved_ids WHERE device_id = ?");
    $stmt->bind_param("i", $row['id']);
    $stmt->execute();
    
    echo json_encode([
        "status" => ($stmt->get_result()->num_rows > 0 || $row['is_approved']) ? "Authorized" : "Unauthorized",
        "custom_videos" => $row['videos'] ? json_decode($row['videos']) : null
    ]);
} else {
    // Register new fingerprint
    $stmt = $conn->prepare("INSERT INTO devices (fingerprint, created_at) VALUES (?, NOW())");
    if ($stmt->bind_param("s", $fingerprint) && $stmt->execute()) {
        echo json_encode(["status" => "NewDevice", "message" => "Please register your device"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to store fingerprint"]);
    }
}

// No need to manually close connection - handled by Database class
?>