<?php
/**
 * Category Controller
 * Handles HTTP requests for category operations
 * RV BUILDERS - Backend API
 */

require_once __DIR__ . '/../services/CategoryService.php';

class CategoryController {
    private $categoryService;

    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->categoryService = new CategoryService($db);
    }

    /**
     * Get all categories
     * GET /api/categories
     */
    public function index() {
        $result = $this->categoryService->getAllCategories();
        $this->sendResponse($result);
    }

    /**
     * Get category by slug
     * GET /api/categories/{slug}
     * @param string $slug Category slug
     */
    public function show($slug) {
        $result = $this->categoryService->getCategoryBySlug($slug);
        $statusCode = $result['success'] ? 200 : ($result['error_code'] ?? 500);
        $this->sendResponse($result, $statusCode);
    }

    /**
     * Create new category
     * POST /api/categories
     */
    public function store() {
        $data = $this->getRequestData();
        $result = $this->categoryService->createCategory($data);
        $statusCode = $result['success'] ? 201 : ($result['error_code'] ?? 500);
        $this->sendResponse($result, $statusCode);
    }

    /**
     * Get request data from JSON or POST
     * @return array
     */
    private function getRequestData() {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            return json_decode($json, true) ?? [];
        }

        return $_POST;
    }

    /**
     * Send JSON response
     * @param array $data Response data
     * @param int $statusCode HTTP status code
     */
    private function sendResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
