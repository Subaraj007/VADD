<?php
require 'database.php';

$storeId = $_GET['storeId'] ?? '';
$deviceId = $_GET['deviceId'] ?? '';
$uniqueId = $_GET['uniqueId'] ?? '';

if (!$storeId || !$deviceId || !$uniqueId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit;
}

$stmt = $pdo->prepare("SELECT MediaPath FROM RegisteredDevices WHERE UniqueId = ? AND StoreId = ? AND DeviceId = ?");
$stmt->execute([$uniqueId, $storeId, $deviceId]);
$row = $stmt->fetch();

if ($row) {
    echo json_encode(['status' => 'success', 'videoPath' => $row['MediaPath']]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No matching device found']);
}
