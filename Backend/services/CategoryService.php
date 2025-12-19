<?php
/**
 * Category Service
 * Business logic layer for category operations
 * RV BUILDERS - Backend API
 */

require_once __DIR__ . '/../models/Category.php';

class CategoryService {
    private $categoryModel;

    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->categoryModel = new Category($db);
    }

    /**
     * Get all categories
     * @return array
     */
    public function getAllCategories() {
        $categories = $this->categoryModel->getAll();

        return [
            'success' => true,
            'data' => $categories,
            'count' => count($categories)
        ];
    }

    /**
     * Get category by slug
     * @param string $slug Category slug
     * @return array
     */
    public function getCategoryBySlug($slug) {
        $category = $this->categoryModel->getBySlug($slug);

        if (!$category) {
            return [
                'success' => false,
                'message' => 'Category not found',
                'error_code' => 404
            ];
        }

        return [
            'success' => true,
            'data' => $category
        ];
    }

    /**
     * Create new category
     * @param array $data Category data
     * @return array
     */
    public function createCategory($data) {
        if (empty($data['name'])) {
            return [
                'success' => false,
                'message' => 'Category name is required',
                'error_code' => 400
            ];
        }

        $this->categoryModel->name = $data['name'];
        $this->categoryModel->slug = $data['slug'] ?? $this->generateSlug($data['name']);
        $this->categoryModel->description = $data['description'] ?? '';
        $this->categoryModel->display_order = $data['display_order'] ?? 0;
        $this->categoryModel->is_active = $data['is_active'] ?? 1;

        $categoryId = $this->categoryModel->create();

        if ($categoryId) {
            return [
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $this->categoryModel->getById($categoryId)
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to create category',
            'error_code' => 500
        ];
    }

    /**
     * Generate slug from name
     * @param string $name Category name
     * @return string
     */
    private function generateSlug($name) {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}
