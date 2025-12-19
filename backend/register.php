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
if (!isset($input['username']) || !isset($input['email']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username, email, and password are required']);
    exit();
}

// Validate email format
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

// Validate password strength
if (strlen($input['password']) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
    exit();
}

try {
    $user = new User();
    $role = isset($input['role']) ? $input['role'] : 'user';
    $result = $user->register($input['username'], $input['email'], $input['password'], $role);
    
    if ($result['success']) {
        http_response_code(201);
    } else {
        http_response_code(400);
    }
    
    echo json_encode($result);
} catch (Exception $e) {
    error_log("Register endpoint error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
