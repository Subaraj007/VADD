<?php
require_once 'Database.php';

header("Content-Type: application/json");

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $data = json_decode(file_get_contents("php://input"), true);
    $required = ['UniqueId', 'DeviceId', 'StoreId'];

    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode(["status" => "error", "message" => "$field is required"]);
            exit;
        }
    }

    // Begin transaction
    $conn->begin_transaction();

    try {
        // 1. First get device details before updating
        $stmt = $conn->prepare("SELECT DeviceName FROM devices WHERE Id = ? AND StoreId = ?");
        $stmt->bind_param("is", $data['DeviceId'], $data['StoreId']);
        $stmt->execute();
        $device = $stmt->get_result()->fetch_assoc();

        if (!$device) {
            throw new Exception("Device not found");
        }

        // 2. Update device with UniqueId
        $stmt = $conn->prepare("UPDATE devices SET UniqueId = ?, IsRegister = 1 WHERE Id = ?");
        $stmt->bind_param("si", $data['UniqueId'], $data['DeviceId']);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            throw new Exception("Device registration failed");
        }

        // 3. Add to approvedevices table (initially inactive)
        $stmt = $conn->prepare("
            INSERT INTO approvedevices 
            (UniqueId, StoreId, DeviceName, IsActive, EnterBy) 
            VALUES (?, ?, ?, 0, 'system')
            ON DUPLICATE KEY UPDATE 
            DeviceName = VALUES(DeviceName),
            StoreId = VALUES(StoreId)
        ");
        $stmt->bind_param("sss", 
            $data['UniqueId'], 
            $data['StoreId'], 
            $device['DeviceName']
        );
        $stmt->execute();

        $conn->commit();
        
        // Return device name for future redirect
        echo json_encode([
            "status" => "success",
            "message" => "Device registered successfully",
            "deviceName" => $device['DeviceName']
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
} catch (Exception $e) {
    echo json_encode([
        "status" => "error", 
        "message" => $e->getMessage()
    ]);
}
?>