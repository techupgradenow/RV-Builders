<?php
/**
 * Categories API Endpoint
 * Direct access endpoint for categories
 * RV BUILDERS - Backend API
 */

// Load configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../controllers/CategoryController.php';
require_once __DIR__ . '/../utils/Response.php';

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get database connection
try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    Response::serverError('Database connection failed');
}

$controller = new CategoryController($db);
$method = $_SERVER['REQUEST_METHOD'];

// Get slug from query string if provided
$slug = $_GET['slug'] ?? null;

try {
    switch ($method) {
        case 'GET':
            if ($slug) {
                $controller->show($slug);
            } else {
                $controller->index();
            }
            break;

        case 'POST':
            $controller->store();
            break;

        default:
            Response::error('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    Response::serverError('An error occurred');
}
