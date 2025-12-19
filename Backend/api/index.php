<?php
/**
 * API Entry Point
 * Main router for all API requests
 * RV BUILDERS - Backend API
 */

// Load configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/Response.php';

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');

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

// Parse request URI
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '/RV-Builders/Backend/api';
$path = str_replace($basePath, '', parse_url($requestUri, PHP_URL_PATH));
$path = trim($path, '/');
$segments = $path ? explode('/', $path) : [];

$method = $_SERVER['REQUEST_METHOD'];

// Route the request
try {
    // Projects routes
    if (empty($segments) || $segments[0] === 'projects') {
        require_once __DIR__ . '/../controllers/ProjectController.php';
        $controller = new ProjectController($db);

        if (empty($segments) || count($segments) === 1) {
            // /api/projects
            switch ($method) {
                case 'GET':
                    $controller->index();
                    break;
                case 'POST':
                    $controller->store();
                    break;
                default:
                    Response::error('Method not allowed', 405);
            }
        } elseif ($segments[1] === 'featured') {
            // /api/projects/featured
            if ($method === 'GET') {
                $controller->featured();
            } else {
                Response::error('Method not allowed', 405);
            }
        } elseif ($segments[1] === 'images' && isset($segments[2])) {
            // /api/projects/images/{imageId}
            $imageId = (int)$segments[2];
            if ($method === 'DELETE') {
                $controller->deleteImage($imageId);
            } else {
                Response::error('Method not allowed', 405);
            }
        } elseif (is_numeric($segments[1])) {
            $projectId = (int)$segments[1];

            if (count($segments) === 2) {
                // /api/projects/{id}
                switch ($method) {
                    case 'GET':
                        $controller->show($projectId);
                        break;
                    case 'PUT':
                    case 'POST': // Allow POST for update with file uploads
                        $controller->update($projectId);
                        break;
                    case 'DELETE':
                        $controller->destroy($projectId);
                        break;
                    default:
                        Response::error('Method not allowed', 405);
                }
            } elseif (isset($segments[2]) && $segments[2] === 'images') {
                if (count($segments) === 3) {
                    // /api/projects/{id}/images
                    if ($method === 'POST') {
                        $controller->addImages($projectId);
                    } else {
                        Response::error('Method not allowed', 405);
                    }
                } elseif (isset($segments[3]) && is_numeric($segments[3]) && isset($segments[4]) && $segments[4] === 'primary') {
                    // /api/projects/{id}/images/{imageId}/primary
                    $imageId = (int)$segments[3];
                    if ($method === 'PUT') {
                        $controller->setPrimaryImage($projectId, $imageId);
                    } else {
                        Response::error('Method not allowed', 405);
                    }
                } else {
                    Response::notFound('Route not found');
                }
            } else {
                Response::notFound('Route not found');
            }
        } else {
            Response::notFound('Route not found');
        }
    }
    // Categories routes
    elseif ($segments[0] === 'categories') {
        require_once __DIR__ . '/../controllers/CategoryController.php';
        $controller = new CategoryController($db);

        if (count($segments) === 1) {
            // /api/categories
            switch ($method) {
                case 'GET':
                    $controller->index();
                    break;
                case 'POST':
                    $controller->store();
                    break;
                default:
                    Response::error('Method not allowed', 405);
            }
        } elseif (count($segments) === 2) {
            // /api/categories/{slug}
            $slug = $segments[1];
            if ($method === 'GET') {
                $controller->show($slug);
            } else {
                Response::error('Method not allowed', 405);
            }
        } else {
            Response::notFound('Route not found');
        }
    }
    // Health check
    elseif ($segments[0] === 'health') {
        Response::success([
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'database' => $db ? 'connected' : 'disconnected'
        ], 'API is running');
    }
    else {
        Response::notFound('Route not found');
    }

} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    Response::serverError('An error occurred: ' . $e->getMessage());
}
