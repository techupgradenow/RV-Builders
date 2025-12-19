<?php
/**
 * Response Utility
 * Standardized JSON response handler
 * RV BUILDERS - Backend API
 */

class Response {
    /**
     * Send success response
     * @param mixed $data Response data
     * @param string $message Success message
     * @param int $statusCode HTTP status code
     */
    public static function success($data = null, $message = 'Success', $statusCode = 200) {
        self::send([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Send error response
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param array $errors Additional error details
     */
    public static function error($message = 'Error', $statusCode = 400, $errors = []) {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        self::send($response, $statusCode);
    }

    /**
     * Send JSON response
     * @param array $data Response data
     * @param int $statusCode HTTP status code
     */
    public static function send($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send not found response
     * @param string $message Not found message
     */
    public static function notFound($message = 'Resource not found') {
        self::error($message, 404);
    }

    /**
     * Send validation error response
     * @param array $errors Validation errors
     */
    public static function validationError($errors) {
        self::error('Validation failed', 422, $errors);
    }

    /**
     * Send unauthorized response
     * @param string $message Unauthorized message
     */
    public static function unauthorized($message = 'Unauthorized') {
        self::error($message, 401);
    }

    /**
     * Send server error response
     * @param string $message Error message
     */
    public static function serverError($message = 'Internal server error') {
        self::error($message, 500);
    }
}
