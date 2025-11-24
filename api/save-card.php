<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate input
    if (!isset($input['template_id']) || !isset($input['card_image'])) {
        throw new Exception('Missing required fields');
    }

    $template_id = (int) $input['template_id'];
    $card_image = $input['card_image'];
    $dedication_text = $input['dedication_text'] ?? '';
    $sender_name = $input['sender_name'] ?? '';
    $background_id = isset($input['background_id']) ? (int) $input['background_id'] : null;
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    // Generate unique ID
    $unique_id = bin2hex(random_bytes(16));

    // Save image
    $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $card_image));
    $image_filename = $unique_id . '.png';
    $image_path = __DIR__ . '/../uploads/shared/' . $image_filename;

    // Create directory if it doesn't exist
    if (!is_dir(__DIR__ . '/../uploads/shared')) {
        mkdir(__DIR__ . '/../uploads/shared', 0755, true);
    }

    if (!file_put_contents($image_path, $image_data)) {
        throw new Exception('Failed to save image');
    }

    // Save to database
    $stmt = $db->prepare("
        INSERT INTO shared_cards 
        (unique_id, template_id, user_id, card_image_url, dedication_text, sender_name, background_id, created_at) 
        VALUES 
        (:unique_id, :template_id, :user_id, :card_image_url, :dedication_text, :sender_name, :background_id, NOW())
    ");

    $stmt->execute([
        ':unique_id' => $unique_id,
        ':template_id' => $template_id,
        ':user_id' => $user_id,
        ':card_image_url' => 'uploads/shared/' . $image_filename,
        ':dedication_text' => $dedication_text,
        ':sender_name' => $sender_name,
        ':background_id' => $background_id
    ]);

    $share_url = SITE_URL . '/share.php?id=' . $unique_id;

    echo json_encode([
        'success' => true,
        'share_url' => $share_url,
        'unique_id' => $unique_id,
        'preview_url' => $share_url
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
