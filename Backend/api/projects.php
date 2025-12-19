<?php
/**
 * Projects API Endpoint
 * Direct access endpoint for projects
 * RV BUILDERS - Backend API
 */

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set CORS headers first
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Load configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/Response.php';

// Get database connection
try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception('Database connection returned null');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed. Please ensure MySQL is running and the database exists.',
        'error' => $e->getMessage(),
        'setup_instructions' => [
            '1. Start XAMPP Control Panel',
            '2. Start MySQL service',
            '3. Open phpMyAdmin (http://localhost/phpmyadmin)',
            '4. Create database: rv_builders',
            '5. Import: Backend/database/schema.sql'
        ]
    ]);
    exit;
}

// Load controller
require_once __DIR__ . '/../controllers/ProjectController.php';

$controller = new ProjectController($db);
$method = $_SERVER['REQUEST_METHOD'];

// Get project ID from query string if provided
$projectId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$imageId = isset($_GET['image_id']) ? (int)$_GET['image_id'] : null;
$action = $_GET['action'] ?? null;

try {
    switch ($method) {
        case 'GET':
            if ($action === 'featured') {
                $controller->featured();
            } elseif ($projectId) {
                $controller->show($projectId);
            } else {
                $controller->index();
            }
            break;

        case 'POST':
            if ($projectId && $action === 'images') {
                $controller->addImages($projectId);
            } elseif ($projectId) {
                // Update existing project
                $controller->update($projectId);
            } else {
                $controller->store();
            }
            break;

        case 'PUT':
            if ($projectId) {
                $controller->update($projectId);
            } else {
                Response::error('Project ID required', 400);
            }
            break;

        case 'DELETE':
            if ($action === 'image' && $imageId) {
                // Delete single image
                $controller->deleteImage($imageId);
            } elseif ($projectId) {
                $controller->destroy($projectId);
            } else {
                Response::error('Project ID required', 400);
            }
            break;

        default:
            Response::error('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $e->getMessage()
    ]);
}
