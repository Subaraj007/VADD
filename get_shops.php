<?php
require_once 'Database.php';

header("Content-Type: application/json");

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT StoreId, StoreName FROM stores WHERE IsBlock = 0");
    $stmt->execute();
    $result = $stmt->get_result();

    $shops = [];
    while ($row = $result->fetch_assoc()) {
        $shops[] = $row;
    }

    echo json_encode($shops);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>