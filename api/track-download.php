<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false]);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $unique_id = $input['unique_id'] ?? '';

    if (empty($unique_id)) {
        throw new Exception('Missing unique_id');
    }

    $stmt = $db->prepare("UPDATE shared_cards SET downloads = downloads + 1 WHERE unique_id = :unique_id");
    $stmt->execute([':unique_id' => $unique_id]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
