<?php
/**
 * Project Controller
 * Handles HTTP requests for project operations
 * RV BUILDERS - Backend API
 */

require_once __DIR__ . '/../services/ProjectService.php';

class ProjectController {
    private $projectService;

    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->projectService = new ProjectService($db);
    }

    /**
     * Get all projects
     * GET /api/projects
     * Query params: category, limit, offset
     */
    public function index() {
        $params = [
            'category' => $_GET['category'] ?? null,
            'limit' => $_GET['limit'] ?? null,
            'offset' => $_GET['offset'] ?? 0
        ];

        $result = $this->projectService->getAllProjects($params);
        $this->sendResponse($result);
    }

    /**
     * Get single project
     * GET /api/projects/{id}
     * @param int $id Project ID
     */
    public function show($id) {
        $result = $this->projectService->getProject($id);
        $statusCode = $result['success'] ? 200 : ($result['error_code'] ?? 500);
        $this->sendResponse($result, $statusCode);
    }

    /**
     * Get featured projects
     * GET /api/projects/featured
     */
    public function featured() {
        $limit = $_GET['limit'] ?? 6;
        $result = $this->projectService->getFeaturedProjects($limit);
        $this->sendResponse($result);
    }

    /**
     * Create new project
     * POST /api/projects
     */
    public function store() {
        $data = $this->getRequestData();
        $files = $_FILES ?? [];

        $result = $this->projectService->createProject($data, $files);
        $statusCode = $result['success'] ? 201 : ($result['error_code'] ?? 500);
        $this->sendResponse($result, $statusCode);
    }

    /**
     * Update project
     * PUT /api/projects/{id}
     * @param int $id Project ID
     */
    public function update($id) {
        $data = $this->getRequestData();
        $files = $_FILES ?? [];

        $result = $this->projectService->updateProject($id, $data, $files);
        $statusCode = $result['success'] ? 200 : ($result['error_code'] ?? 500);
        $this->sendResponse($result, $statusCode);
    }

    /**
     * Delete project
     * DELETE /api/projects/{id}
     * @param int $id Project ID
     */
    public function destroy($id) {
        $result = $this->projectService->deleteProject($id);
        $statusCode = $result['success'] ? 200 : ($result['error_code'] ?? 500);
        $this->sendResponse($result, $statusCode);
    }

    /**
     * Add images to project
     * POST /api/projects/{id}/images
     * @param int $id Project ID
     */
    public function addImages($id) {
        $files = $_FILES ?? [];

        $result = $this->projectService->addImages($id, $files);
        $statusCode = $result['success'] ? 201 : ($result['error_code'] ?? 500);
        $this->sendResponse($result, $statusCode);
    }

    /**
     * Delete image from project
     * DELETE /api/projects/images/{imageId}
     * @param int $imageId Image ID
     */
    public function deleteImage($imageId) {
        $result = $this->projectService->deleteImage($imageId);
        $statusCode = $result['success'] ? 200 : ($result['error_code'] ?? 500);
        $this->sendResponse($result, $statusCode);
    }

    /**
     * Set primary image for project
     * PUT /api/projects/{projectId}/images/{imageId}/primary
     * @param int $projectId Project ID
     * @param int $imageId Image ID
     */
    public function setPrimaryImage($projectId, $imageId) {
        $result = $this->projectService->setPrimaryImage($projectId, $imageId);
        $statusCode = $result['success'] ? 200 : ($result['error_code'] ?? 500);
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
