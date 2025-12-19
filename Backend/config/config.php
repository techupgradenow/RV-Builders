<?php
/**
 * Application Configuration
 * RV BUILDERS - Backend API
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Define base paths
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/uploads/');
define('PROJECT_IMAGES_PATH', UPLOAD_PATH . 'projects/');

// Detect base URL automatically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$scriptPath = dirname(dirname($_SERVER['SCRIPT_NAME']));
$scriptPath = str_replace('\\', '/', $scriptPath); // Convert Windows backslashes to forward slashes
$scriptPath = rtrim($scriptPath, '/');

define('BASE_URL', $protocol . '://' . $host . $scriptPath);
define('UPLOAD_URL', BASE_URL . '/uploads/');
define('PROJECT_IMAGES_URL', UPLOAD_URL . 'projects/');

// Image upload settings
define('MAX_IMAGES_PER_PROJECT', 5);
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// CORS settings
define('ALLOWED_ORIGINS', ['http://localhost', 'http://127.0.0.1']);

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Create logs directory if not exists
if (!file_exists(BASE_PATH . '/logs')) {
    mkdir(BASE_PATH . '/logs', 0755, true);
}

// Create uploads directory if not exists
if (!file_exists(PROJECT_IMAGES_PATH)) {
    mkdir(PROJECT_IMAGES_PATH, 0755, true);
}
