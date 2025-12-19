<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Main Classes/User.php';

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit();
}

// Validate required fields
if (!isset($input['username']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit();
}

try {
    $user = new User();
    $result = $user->login($input['username'], $input['password']);
    
    if ($result['success']) {
        http_response_code(200);
    } else {
        http_response_code(401);
    }
    
    echo json_encode($result);
} catch (Exception $e) {
    error_log("Login endpoint error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
