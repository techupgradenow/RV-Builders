<?php
/**
 * Image Uploader Utility
 * Handles image upload, validation, and processing
 * RV BUILDERS - Backend API
 */

class ImageUploader {
    private $allowedTypes;
    private $allowedExtensions;
    private $maxSize;
    private $uploadPath;

    /**
     * Constructor
     */
    public function __construct() {
        $this->allowedTypes = ALLOWED_IMAGE_TYPES;
        $this->allowedExtensions = ALLOWED_IMAGE_EXTENSIONS;
        $this->maxSize = MAX_IMAGE_SIZE;
        $this->uploadPath = UPLOAD_PATH;
    }

    /**
     * Upload a single image
     * @param array $file $_FILES array element
     * @param string $folder Subfolder name (e.g., 'projects')
     * @return array Result with success status and file info
     */
    public function upload($file, $folder = '') {
        // Validate file
        $validation = $this->validate($file);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message']
            ];
        }

        // Generate unique filename
        $extension = $this->getExtension($file['name']);
        $filename = $this->generateFilename($extension);

        // Determine upload directory
        $uploadDir = $this->uploadPath;
        if ($folder) {
            $uploadDir .= $folder . '/';
        }

        // Create directory if not exists
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filePath = $uploadDir . $filename;
        $relativePath = ($folder ? $folder . '/' : '') . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Determine URL based on folder
            $url = UPLOAD_URL . $relativePath;

            return [
                'success' => true,
                'filename' => $filename,
                'original_name' => $file['name'],
                'path' => $relativePath,
                'full_path' => $filePath,
                'url' => $url,
                'size' => $file['size'],
                'type' => $file['type']
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to move uploaded file'
        ];
    }

    /**
     * Upload multiple images
     * @param array $files $_FILES array with multiple files
     * @param string $folder Subfolder name
     * @return array Results for each file
     */
    public function uploadMultiple($files, $folder = '') {
        $results = [];

        if (!is_array($files['name'])) {
            // Single file
            $results[] = $this->upload($files, $folder);
        } else {
            // Multiple files
            $fileCount = count($files['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];

                $results[] = $this->upload($file, $folder);
            }
        }

        return $results;
    }

    /**
     * Validate uploaded file
     * @param array $file $_FILES array element
     * @return array Validation result
     */
    public function validate($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'valid' => false,
                'message' => $this->getUploadErrorMessage($file['error'])
            ];
        }

        // Check file size
        if ($file['size'] > $this->maxSize) {
            $maxMB = $this->maxSize / (1024 * 1024);
            return [
                'valid' => false,
                'message' => "File size exceeds maximum allowed ({$maxMB}MB)"
            ];
        }

        // Check file type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $this->allowedTypes)) {
            return [
                'valid' => false,
                'message' => 'Invalid file type. Allowed: ' . implode(', ', $this->allowedExtensions)
            ];
        }

        // Check file extension
        $extension = $this->getExtension($file['name']);
        if (!in_array($extension, $this->allowedExtensions)) {
            return [
                'valid' => false,
                'message' => 'Invalid file extension. Allowed: ' . implode(', ', $this->allowedExtensions)
            ];
        }

        // Check if it's actually an image
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return [
                'valid' => false,
                'message' => 'File is not a valid image'
            ];
        }

        return ['valid' => true];
    }

    /**
     * Generate unique filename
     * @param string $extension File extension
     * @return string Generated filename
     */
    private function generateFilename($extension) {
        $timestamp = date('Ymd_His');
        $random = bin2hex(random_bytes(8));
        return "img_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Get file extension
     * @param string $filename Original filename
     * @return string Lowercase extension
     */
    private function getExtension($filename) {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Get upload error message
     * @param int $errorCode PHP upload error code
     * @return string Error message
     */
    private function getUploadErrorMessage($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];

        return $errors[$errorCode] ?? 'Unknown upload error';
    }

    /**
     * Delete image file
     * @param string $filename Filename or path
     * @param string $folder Subfolder
     * @return bool
     */
    public function delete($filename, $folder = '') {
        $filePath = $this->uploadPath;
        if ($folder) {
            $filePath .= $folder . '/';
        }
        $filePath .= $filename;

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }
}
