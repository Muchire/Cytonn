<?php
// config/jwt.php - JWT Authentication Class
require_once '/home/vivian/Documents/Cytonn/backend/vendor/autoload.php'; // You'll need to install firebase/php-jwt via Composer

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTAuth {
    private $secret_key;
    private $algorithm = 'HS256';
    private $expiration_time = 3600; // 1 hour in seconds

    public function __construct() {
        // In production, store this in environment variables
        $this->secret_key = $_ENV['JWT_SECRET'] ?? 'your-super-secret-key-change-this-in-production';
    }

    // Generate JWT token
    public function generateToken($user_data) {
        $payload = [
            'iss' => 'your-app-name', // Issuer
            'aud' => 'your-app-users', // Audience
            'iat' => time(), // Issued at
            'exp' => time() + $this->expiration_time, // Expiration
            'user_id' => $user_data['id'],
            'username' => $user_data['username'],
            'email' => $user_data['email'],
            'role' => $user_data['role']
        ];

        return JWT::encode($payload, $this->secret_key, $this->algorithm);
    }

    // Verify JWT token
    public function verifyToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->secret_key, $this->algorithm));
            return (array) $decoded;
        } catch (Exception $e) {
            return false;
        }
    }

    // Get token from request headers
    public function getTokenFromHeaders() {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            $auth_header = $headers['Authorization'];
            if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }

    // Middleware to check authentication
    public function authenticate() {
        $token = $this->getTokenFromHeaders();
        
        if (!$token) {
            $this->sendUnauthorizedResponse('Token not provided');
        }

        $decoded = $this->verifyToken($token);
        
        if (!$decoded) {
            $this->sendUnauthorizedResponse('Invalid or expired token');
        }

        return $decoded;
    }

    // Check if user has required role
    public function authorize($required_roles, $user_data) {
        if (!is_array($required_roles)) {
            $required_roles = [$required_roles];
        }

        if (!in_array($user_data['role'], $required_roles)) {
            $this->sendForbiddenResponse('Insufficient permissions');
        }

        return true;
    }

    // Check if user can access their own data or if they're admin
    public function canAccessUserData($user_id, $current_user) {
        return $current_user['role'] === 'admin' || $current_user['user_id'] == $user_id;
    }

    // Send unauthorized response
    private function sendUnauthorizedResponse($message) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }

    // Send forbidden response
    private function sendForbiddenResponse($message) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
}
?>