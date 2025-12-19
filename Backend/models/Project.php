<?php
/**
 * Project Model
 * RV BUILDERS - Backend API
 */

class Project {
    private $conn;
    private $table = 'projects';
    private $imagesTable = 'project_images';

    // Project properties
    public $id;
    public $title;
    public $description;
    public $category;
    public $client_name;
    public $location;
    public $project_date;
    public $completion_status;
    public $featured;
    public $display_order;
    public $created_at;
    public $updated_at;
    public $images = [];

    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get all projects with their images
     * @param string|null $category Filter by category
     * @param int|null $limit Limit results
     * @param int $offset Offset for pagination
     * @return array
     */
    public function getAll($category = null, $limit = null, $offset = 0) {
        $sql = "SELECT p.*,
                       (SELECT COUNT(*) FROM {$this->imagesTable} WHERE project_id = p.id) as image_count
                FROM {$this->table} p
                WHERE 1=1";
        $params = [];

        if ($category && $category !== 'all') {
            $sql .= " AND p.category = :category";
            $params[':category'] = $category;
        }

        $sql .= " ORDER BY p.display_order ASC, p.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        if ($limit) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        $projects = $stmt->fetchAll();

        // Fetch images for each project
        foreach ($projects as &$project) {
            $project['images'] = $this->getProjectImages($project['id']);
        }

        return $projects;
    }

    /**
     * Get single project by ID with images
     * @param int $id Project ID
     * @return array|false
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $project = $stmt->fetch();

        if ($project) {
            $project['images'] = $this->getProjectImages($id);
        }

        return $project;
    }

    /**
     * Get featured projects
     * @param int $limit Number of projects
     * @return array
     */
    public function getFeatured($limit = 6) {
        $sql = "SELECT * FROM {$this->table}
                WHERE featured = 1
                ORDER BY display_order ASC, created_at DESC
                LIMIT :limit";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        $projects = $stmt->fetchAll();

        foreach ($projects as &$project) {
            $project['images'] = $this->getProjectImages($project['id']);
        }

        return $projects;
    }

    /**
     * Create new project
     * @return int|false Project ID or false
     */
    public function create() {
        $sql = "INSERT INTO {$this->table}
                (title, description, category, client_name, location, project_date, completion_status, featured, display_order)
                VALUES
                (:title, :description, :category, :client_name, :location, :project_date, :completion_status, :featured, :display_order)";

        $stmt = $this->conn->prepare($sql);

        // Sanitize inputs
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->client_name = htmlspecialchars(strip_tags($this->client_name ?? ''));
        $this->location = htmlspecialchars(strip_tags($this->location ?? ''));

        $stmt->bindValue(':title', $this->title);
        $stmt->bindValue(':description', $this->description);
        $stmt->bindValue(':category', $this->category);
        $stmt->bindValue(':client_name', $this->client_name);
        $stmt->bindValue(':location', $this->location);
        $stmt->bindValue(':project_date', $this->project_date);
        $stmt->bindValue(':completion_status', $this->completion_status ?? 'completed');
        $stmt->bindValue(':featured', $this->featured ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':display_order', $this->display_order ?? 0, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Update project
     * @return bool
     */
    public function update() {
        $sql = "UPDATE {$this->table} SET
                title = :title,
                description = :description,
                category = :category,
                client_name = :client_name,
                location = :location,
                project_date = :project_date,
                completion_status = :completion_status,
                featured = :featured,
                display_order = :display_order
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        // Sanitize inputs
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->client_name = htmlspecialchars(strip_tags($this->client_name ?? ''));
        $this->location = htmlspecialchars(strip_tags($this->location ?? ''));

        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':title', $this->title);
        $stmt->bindValue(':description', $this->description);
        $stmt->bindValue(':category', $this->category);
        $stmt->bindValue(':client_name', $this->client_name);
        $stmt->bindValue(':location', $this->location);
        $stmt->bindValue(':project_date', $this->project_date);
        $stmt->bindValue(':completion_status', $this->completion_status ?? 'completed');
        $stmt->bindValue(':featured', $this->featured ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':display_order', $this->display_order ?? 0, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Delete project and its images
     * @param int $id Project ID
     * @return bool
     */
    public function delete($id) {
        // First delete associated image files
        $images = $this->getProjectImages($id);
        foreach ($images as $image) {
            $filePath = PROJECT_IMAGES_PATH . $image['image_name'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Delete project (images will be deleted via CASCADE)
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Get images for a project
     * @param int $projectId Project ID
     * @return array
     */
    public function getProjectImages($projectId) {
        $sql = "SELECT * FROM {$this->imagesTable}
                WHERE project_id = :project_id
                ORDER BY is_primary DESC, display_order ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $stmt->execute();

        $images = $stmt->fetchAll();

        // Add full URL to each image
        foreach ($images as &$image) {
            $image['image_url'] = PROJECT_IMAGES_URL . $image['image_name'];
        }

        return $images;
    }

    /**
     * Add image to project
     * @param int $projectId Project ID
     * @param string $imagePath Relative path
     * @param string $imageName Stored filename
     * @param string $originalName Original filename
     * @param bool $isPrimary Is primary image
     * @return int|false Image ID or false
     */
    public function addImage($projectId, $imagePath, $imageName, $originalName, $isPrimary = false) {
        // Check if project already has 5 images
        $imageCount = $this->getImageCount($projectId);
        if ($imageCount >= MAX_IMAGES_PER_PROJECT) {
            throw new Exception("Maximum " . MAX_IMAGES_PER_PROJECT . " images allowed per project");
        }

        // If this is the first image, make it primary
        if ($imageCount == 0) {
            $isPrimary = true;
        }

        // If setting as primary, unset other primaries
        if ($isPrimary) {
            $this->unsetPrimaryImages($projectId);
        }

        $sql = "INSERT INTO {$this->imagesTable}
                (project_id, image_path, image_name, original_name, is_primary, display_order)
                VALUES
                (:project_id, :image_path, :image_name, :original_name, :is_primary, :display_order)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $stmt->bindValue(':image_path', $imagePath);
        $stmt->bindValue(':image_name', $imageName);
        $stmt->bindValue(':original_name', $originalName);
        $stmt->bindValue(':is_primary', $isPrimary ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':display_order', $imageCount + 1, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Delete image
     * @param int $imageId Image ID
     * @return bool
     */
    public function deleteImage($imageId) {
        // Get image info first
        $sql = "SELECT * FROM {$this->imagesTable} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $imageId, PDO::PARAM_INT);
        $stmt->execute();
        $image = $stmt->fetch();

        if (!$image) {
            return false;
        }

        // Delete file
        $filePath = PROJECT_IMAGES_PATH . $image['image_name'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete from database
        $sql = "DELETE FROM {$this->imagesTable} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $imageId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Get image count for a project
     * @param int $projectId Project ID
     * @return int
     */
    public function getImageCount($projectId) {
        $sql = "SELECT COUNT(*) as count FROM {$this->imagesTable} WHERE project_id = :project_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int)$result['count'];
    }

    /**
     * Unset all primary images for a project
     * @param int $projectId Project ID
     */
    private function unsetPrimaryImages($projectId) {
        $sql = "UPDATE {$this->imagesTable} SET is_primary = 0 WHERE project_id = :project_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Set primary image
     * @param int $imageId Image ID
     * @param int $projectId Project ID
     * @return bool
     */
    public function setPrimaryImage($imageId, $projectId) {
        $this->unsetPrimaryImages($projectId);

        $sql = "UPDATE {$this->imagesTable} SET is_primary = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $imageId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Get total project count
     * @param string|null $category Filter by category
     * @return int
     */
    public function getCount($category = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE 1=1";
        $params = [];

        if ($category && $category !== 'all') {
            $sql .= " AND category = :category";
            $params[':category'] = $category;
        }

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $result = $stmt->fetch();
        return (int)$result['count'];
    }
}
