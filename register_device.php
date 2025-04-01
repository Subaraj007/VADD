<?php
require_once 'Database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

try {
    // Database connection using singleton
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $data = json_decode(file_get_contents(filename: "php://input"), true);
    $required = ['fingerprint', 'shop_name', 'postal_code', 'device_number'];

    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode(["status" => "error", "message" => "$field is required"]);
            exit;
        }
    }

    $stmt = $conn->prepare("UPDATE devices SET shop_name=?, postal_code=?, device_number=?, registration_complete=TRUE WHERE fingerprint=?");
    $stmt->bind_param("ssss", $data['shop_name'], $data['postal_code'], $data['device_number'], $data['fingerprint']);

    echo $stmt->execute() ? 
        json_encode(["status" => "success"]) : 
        json_encode(["status" => "error", "message" => "Registration failed"]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>