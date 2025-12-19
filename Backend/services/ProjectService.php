<?php
/**
 * Project Service
 * Business logic layer for project operations
 * RV BUILDERS - Backend API
 */

require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../utils/ImageUploader.php';

class ProjectService {
    private $projectModel;
    private $imageUploader;
    private $db;

    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->db = $db;
        $this->projectModel = new Project($db);
        $this->imageUploader = new ImageUploader();
    }

    /**
     * Get all projects
     * @param array $params Query parameters
     * @return array
     */
    public function getAllProjects($params = []) {
        $category = $params['category'] ?? null;
        $limit = isset($params['limit']) ? (int)$params['limit'] : null;
        $offset = isset($params['offset']) ? (int)$params['offset'] : 0;

        $projects = $this->projectModel->getAll($category, $limit, $offset);
        $total = $this->projectModel->getCount($category);

        return [
            'success' => true,
            'data' => $projects,
            'total' => $total,
            'count' => count($projects)
        ];
    }

    /**
     * Get single project by ID
     * @param int $id Project ID
     * @return array
     */
    public function getProject($id) {
        $project = $this->projectModel->getById($id);

        if (!$project) {
            return [
                'success' => false,
                'message' => 'Project not found',
                'error_code' => 404
            ];
        }

        return [
            'success' => true,
            'data' => $project
        ];
    }

    /**
     * Get featured projects
     * @param int $limit Number of projects
     * @return array
     */
    public function getFeaturedProjects($limit = 6) {
        $projects = $this->projectModel->getFeatured($limit);

        return [
            'success' => true,
            'data' => $projects,
            'count' => count($projects)
        ];
    }

    /**
     * Create new project
     * @param array $data Project data
     * @param array $files Uploaded files
     * @return array
     */
    public function createProject($data, $files = []) {
        // Validate required fields
        if (empty($data['title'])) {
            return [
                'success' => false,
                'message' => 'Project title is required',
                'error_code' => 400
            ];
        }

        if (empty($data['category'])) {
            return [
                'success' => false,
                'message' => 'Project category is required',
                'error_code' => 400
            ];
        }

        // Validate image count
        if (!empty($files['images'])) {
            $imageCount = is_array($files['images']['name']) ? count($files['images']['name']) : 1;
            if ($imageCount > MAX_IMAGES_PER_PROJECT) {
                return [
                    'success' => false,
                    'message' => 'Maximum ' . MAX_IMAGES_PER_PROJECT . ' images allowed per project',
                    'error_code' => 400
                ];
            }
        }

        try {
            $this->db->beginTransaction();

            // Set project properties
            $this->projectModel->title = $data['title'];
            $this->projectModel->description = $data['description'] ?? '';
            $this->projectModel->category = $data['category'];
            $this->projectModel->client_name = $data['client_name'] ?? '';
            $this->projectModel->location = $data['location'] ?? '';
            $this->projectModel->project_date = $data['project_date'] ?? null;
            $this->projectModel->completion_status = $data['completion_status'] ?? 'completed';
            $this->projectModel->featured = isset($data['featured']) ? (int)$data['featured'] : 0;
            $this->projectModel->display_order = isset($data['display_order']) ? (int)$data['display_order'] : 0;

            // Create project
            $projectId = $this->projectModel->create();

            if (!$projectId) {
                throw new Exception('Failed to create project');
            }

            // Upload images if provided
            $uploadedImages = [];
            if (!empty($files['images'])) {
                $uploadedImages = $this->uploadProjectImages($projectId, $files['images']);
            }

            $this->db->commit();

            // Get the created project with images
            $project = $this->projectModel->getById($projectId);

            return [
                'success' => true,
                'message' => 'Project created successfully',
                'data' => $project,
                'uploaded_images' => count($uploadedImages)
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 500
            ];
        }
    }

    /**
     * Update project
     * @param int $id Project ID
     * @param array $data Project data
     * @param array $files Uploaded files
     * @return array
     */
    public function updateProject($id, $data, $files = []) {
        // Check if project exists
        $existingProject = $this->projectModel->getById($id);
        if (!$existingProject) {
            return [
                'success' => false,
                'message' => 'Project not found',
                'error_code' => 404
            ];
        }

        // Validate image count if uploading new images
        if (!empty($files['images'])) {
            $currentImageCount = $this->projectModel->getImageCount($id);
            $newImageCount = is_array($files['images']['name']) ? count($files['images']['name']) : 1;

            if (($currentImageCount + $newImageCount) > MAX_IMAGES_PER_PROJECT) {
                return [
                    'success' => false,
                    'message' => 'Maximum ' . MAX_IMAGES_PER_PROJECT . ' images allowed. Current: ' . $currentImageCount,
                    'error_code' => 400
                ];
            }
        }

        try {
            $this->db->beginTransaction();

            // Set project properties
            $this->projectModel->id = $id;
            $this->projectModel->title = $data['title'] ?? $existingProject['title'];
            $this->projectModel->description = $data['description'] ?? $existingProject['description'];
            $this->projectModel->category = $data['category'] ?? $existingProject['category'];
            $this->projectModel->client_name = $data['client_name'] ?? $existingProject['client_name'];
            $this->projectModel->location = $data['location'] ?? $existingProject['location'];
            $this->projectModel->project_date = $data['project_date'] ?? $existingProject['project_date'];
            $this->projectModel->completion_status = $data['completion_status'] ?? $existingProject['completion_status'];
            $this->projectModel->featured = isset($data['featured']) ? (int)$data['featured'] : $existingProject['featured'];
            $this->projectModel->display_order = isset($data['display_order']) ? (int)$data['display_order'] : $existingProject['display_order'];

            // Update project
            if (!$this->projectModel->update()) {
                throw new Exception('Failed to update project');
            }

            // Upload new images if provided
            $uploadedImages = [];
            if (!empty($files['images'])) {
                $uploadedImages = $this->uploadProjectImages($id, $files['images']);
            }

            $this->db->commit();

            // Get the updated project with images
            $project = $this->projectModel->getById($id);

            return [
                'success' => true,
                'message' => 'Project updated successfully',
                'data' => $project,
                'uploaded_images' => count($uploadedImages)
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 500
            ];
        }
    }

    /**
     * Delete project
     * @param int $id Project ID
     * @return array
     */
    public function deleteProject($id) {
        $project = $this->projectModel->getById($id);
        if (!$project) {
            return [
                'success' => false,
                'message' => 'Project not found',
                'error_code' => 404
            ];
        }

        if ($this->projectModel->delete($id)) {
            return [
                'success' => true,
                'message' => 'Project deleted successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to delete project',
            'error_code' => 500
        ];
    }

    /**
     * Upload images to a project
     * @param int $projectId Project ID
     * @param array $images Files array
     * @return array Uploaded image info
     */
    private function uploadProjectImages($projectId, $images) {
        $uploaded = [];

        // Handle both single and multiple file uploads
        if (is_array($images['name'])) {
            $fileCount = count($images['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                if ($images['error'][$i] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $images['name'][$i],
                        'type' => $images['type'][$i],
                        'tmp_name' => $images['tmp_name'][$i],
                        'error' => $images['error'][$i],
                        'size' => $images['size'][$i]
                    ];

                    $result = $this->imageUploader->upload($file, 'projects');
                    if ($result['success']) {
                        $isPrimary = ($i === 0 && $this->projectModel->getImageCount($projectId) === 0);
                        $imageId = $this->projectModel->addImage(
                            $projectId,
                            $result['path'],
                            $result['filename'],
                            $result['original_name'],
                            $isPrimary
                        );

                        if ($imageId) {
                            $uploaded[] = [
                                'id' => $imageId,
                                'filename' => $result['filename'],
                                'url' => $result['url']
                            ];
                        }
                    }
                }
            }
        } else {
            if ($images['error'] === UPLOAD_ERR_OK) {
                $result = $this->imageUploader->upload($images, 'projects');
                if ($result['success']) {
                    $isPrimary = ($this->projectModel->getImageCount($projectId) === 0);
                    $imageId = $this->projectModel->addImage(
                        $projectId,
                        $result['path'],
                        $result['filename'],
                        $result['original_name'],
                        $isPrimary
                    );

                    if ($imageId) {
                        $uploaded[] = [
                            'id' => $imageId,
                            'filename' => $result['filename'],
                            'url' => $result['url']
                        ];
                    }
                }
            }
        }

        return $uploaded;
    }

    /**
     * Add images to existing project
     * @param int $projectId Project ID
     * @param array $files Uploaded files
     * @return array
     */
    public function addImages($projectId, $files) {
        $project = $this->projectModel->getById($projectId);
        if (!$project) {
            return [
                'success' => false,
                'message' => 'Project not found',
                'error_code' => 404
            ];
        }

        if (empty($files['images'])) {
            return [
                'success' => false,
                'message' => 'No images provided',
                'error_code' => 400
            ];
        }

        // Validate image count
        $currentCount = $this->projectModel->getImageCount($projectId);
        $newCount = is_array($files['images']['name']) ? count($files['images']['name']) : 1;

        if (($currentCount + $newCount) > MAX_IMAGES_PER_PROJECT) {
            return [
                'success' => false,
                'message' => 'Maximum ' . MAX_IMAGES_PER_PROJECT . ' images allowed. Current: ' . $currentCount . ', Attempting to add: ' . $newCount,
                'error_code' => 400
            ];
        }

        try {
            $uploaded = $this->uploadProjectImages($projectId, $files['images']);

            return [
                'success' => true,
                'message' => count($uploaded) . ' image(s) uploaded successfully',
                'data' => $uploaded
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 500
            ];
        }
    }

    /**
     * Delete image from project
     * @param int $imageId Image ID
     * @return array
     */
    public function deleteImage($imageId) {
        if ($this->projectModel->deleteImage($imageId)) {
            return [
                'success' => true,
                'message' => 'Image deleted successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to delete image or image not found',
            'error_code' => 404
        ];
    }

    /**
     * Set primary image for project
     * @param int $projectId Project ID
     * @param int $imageId Image ID
     * @return array
     */
    public function setPrimaryImage($projectId, $imageId) {
        if ($this->projectModel->setPrimaryImage($imageId, $projectId)) {
            return [
                'success' => true,
                'message' => 'Primary image set successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to set primary image',
            'error_code' => 500
        ];
    }
}
