<?php
// Database configuration
// IMPORTANT: Change these values for production use!
// Use environment variables or a separate config file that is not committed to version control
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // WARNING: Set a strong password for production!
define('DB_NAME', 'serendip_waves');

// Session configuration
define('SESSION_TIMEOUT', 3600); // 1 hour

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-password');
define('SMTP_FROM', 'noreply@serendipwaves.com');
define('SMTP_FROM_NAME', 'Serendip Waves Cruise');

// Upload directories
define('SHIP_IMAGES_DIR', __DIR__ . '/ship_images/');
define('MEAL_IMAGES_DIR', __DIR__ . '/meal_images/');
define('FACILITY_IMAGES_DIR', __DIR__ . '/facility_images/');

// Allowed file types
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);
define('MAX_FILE_SIZE', 5242880); // 5MB

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>
