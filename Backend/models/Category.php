<?php
/**
 * Category Model
 * RV BUILDERS - Backend API
 */

class Category {
    private $conn;
    private $table = 'project_categories';

    public $id;
    public $name;
    public $slug;
    public $description;
    public $display_order;
    public $is_active;

    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get all active categories
     * @return array
     */
    public function getAll() {
        $sql = "SELECT * FROM {$this->table}
                WHERE is_active = 1
                ORDER BY display_order ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get category by slug
     * @param string $slug Category slug
     * @return array|false
     */
    public function getBySlug($slug) {
        $sql = "SELECT * FROM {$this->table} WHERE slug = :slug AND is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':slug', $slug);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Get category by ID
     * @param int $id Category ID
     * @return array|false
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Create new category
     * @return int|false Category ID or false
     */
    public function create() {
        $sql = "INSERT INTO {$this->table}
                (name, slug, description, display_order, is_active)
                VALUES
                (:name, :slug, :description, :display_order, :is_active)";

        $stmt = $this->conn->prepare($sql);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));

        $stmt->bindValue(':name', $this->name);
        $stmt->bindValue(':slug', $this->slug);
        $stmt->bindValue(':description', $this->description);
        $stmt->bindValue(':display_order', $this->display_order ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':is_active', $this->is_active ?? 1, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Update category
     * @return bool
     */
    public function update() {
        $sql = "UPDATE {$this->table} SET
                name = :name,
                slug = :slug,
                description = :description,
                display_order = :display_order,
                is_active = :is_active
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));

        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':name', $this->name);
        $stmt->bindValue(':slug', $this->slug);
        $stmt->bindValue(':description', $this->description);
        $stmt->bindValue(':display_order', $this->display_order ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':is_active', $this->is_active ?? 1, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Delete category
     * @param int $id Category ID
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
