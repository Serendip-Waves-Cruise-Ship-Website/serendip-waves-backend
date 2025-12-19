<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Main Classes/User.php';

// Authenticate user
$user = new User();
if (!$user->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Only admin and staff can upload images
if (!$user->hasRole('admin') && !$user->hasRole('staff')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit();
}

// Get upload type
$type = isset($_GET['type']) ? $_GET['type'] : null;

$uploadDir = null;
switch ($type) {
    case 'ship':
        $uploadDir = SHIP_IMAGES_DIR;
        break;
    case 'meal':
        $uploadDir = MEAL_IMAGES_DIR;
        break;
    case 'facility':
        $uploadDir = FACILITY_IMAGES_DIR;
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid upload type']);
        exit();
}

// Check if file was uploaded
if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit();
}

$file = $_FILES['file'];

// Validate file size
if ($file['size'] > MAX_FILE_SIZE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File size exceeds maximum allowed size']);
    exit();
}

// Validate file type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG and PNG images are allowed']);
    exit();
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '_' . time() . '.' . $extension;
$filepath = $uploadDir . $filename;

// Create directory if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode([
        'success' => true,
        'message' => 'File uploaded successfully',
        'filename' => $filename,
        'url' => '/' . basename($uploadDir) . '/' . $filename
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
}
?>
