<?php
require_once '../config.php';

// Check if user is admin
if (!is_logged_in() || !is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("SELECT * FROM destinations WHERE id = ?");
    $stmt->execute([$id]);
    $destination = $stmt->fetch();
    
    if ($destination) {
        header('Content-Type: application/json');
        echo json_encode($destination);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Destination not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID not provided']);
}
?>
