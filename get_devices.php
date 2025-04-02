<?php
require_once 'Database.php';

header("Content-Type: application/json");

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $storeId = $_GET['storeId'] ?? '';
    if (empty($storeId)) {
        throw new Exception("Store ID is required");
    }

    $stmt = $conn->prepare("SELECT Id, DeviceName FROM devices WHERE StoreId = ? AND IsRegister = 0");
    $stmt->bind_param("s", $storeId);
    $stmt->execute();
    $result = $stmt->get_result();

    $devices = [];
    while ($row = $result->fetch_assoc()) {
        $devices[] = $row;
    }

    echo json_encode($devices);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>