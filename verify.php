<?php
require_once 'Database.php';

header("Content-Type: application/json");

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $data = json_decode(file_get_contents("php://input"), true);
    $uniqueId = $data['UniqueId'] ?? '';

    if (empty($uniqueId)) {
        echo json_encode(["status" => "error", "message" => "UniqueId is required"]);
        exit;
    }

    // Check if device is approved and has media path
    $stmt = $conn->prepare("
        SELECT ad.StoreId, ad.DeviceName, ad.MediaPath 
        FROM approvedevices ad
        WHERE ad.UniqueId = ? AND ad.IsActive = 1
    ");
    $stmt->bind_param("s", $uniqueId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $device = $result->fetch_assoc();
        
        // Prepare response with store and device info for redirection
        $response = [
            "status" => "Authorized",
            "storeId" => $device['StoreId'],
            "deviceId" => $device['DeviceName'], // Using DeviceName as device number
            "mediaPath" => $device['MediaPath']
        ];
        
        echo json_encode($response);
    } else {
        // Check if device exists but not approved
        $stmt = $conn->prepare("SELECT 1 FROM devices WHERE UniqueId = ?");
        $stmt->bind_param("s", $uniqueId);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            echo json_encode(["status" => "Pending", "message" => "Device registered but not yet approved"]);
        } else {
            echo json_encode(["status" => "NewDevice", "message" => "Please register your device"]);
        }
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>