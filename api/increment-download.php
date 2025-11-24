<?php
// API endpoint to increment download count
header('Content-Type: application/json');

require_once '../config/config.php';
require_once '../config/database.php';

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $template_id = (int) ($data['template_id'] ?? 0);

    if ($template_id > 0) {
        $stmt = $db->prepare("UPDATE templates SET downloads = downloads + 1 WHERE id = :id");
        $stmt->execute([':id' => $template_id]);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid template ID']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
