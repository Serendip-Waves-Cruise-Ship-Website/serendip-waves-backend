<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/DbConnector.php';

class User {
    private $db;
    private $userId;
    private $username;
    private $email;
    private $role;

    public function __construct() {
        $this->db = DbConnector::getInstance();
        $this->startSession();
    }

    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login($username, $password) {
        try {
            $stmt = $this->db->prepare("SELECT id, username, email, password, role FROM users WHERE username = ? AND is_active = 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                if (password_verify($password, $user['password'])) {
                    $this->userId = $user['id'];
                    $this->username = $user['username'];
                    $this->email = $user['email'];
                    $this->role = $user['role'];

                    $_SESSION['user_id'] = $this->userId;
                    $_SESSION['username'] = $this->username;
                    $_SESSION['email'] = $this->email;
                    $_SESSION['role'] = $this->role;
                    $_SESSION['last_activity'] = time();

                    // Update last login
                    $updateStmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $updateStmt->bind_param("i", $this->userId);
                    $updateStmt->execute();

                    return [
                        'success' => true,
                        'message' => 'Login successful',
                        'user' => [
                            'id' => $this->userId,
                            'username' => $this->username,
                            'email' => $this->email,
                            'role' => $this->role
                        ]
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during login'
            ];
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        return [
            'success' => true,
            'message' => 'Logout successful'
        ];
    }

    public function isAuthenticated() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_activity'])) {
            return false;
        }

        // Check session timeout
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            $this->logout();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    public function getUsername() {
        return $_SESSION['username'] ?? null;
    }

    public function getEmail() {
        return $_SESSION['email'] ?? null;
    }

    public function getRole() {
        return $_SESSION['role'] ?? null;
    }

    public function hasRole($role) {
        return $this->getRole() === $role;
    }

    public function register($username, $email, $password, $role = 'user') {
        try {
            // Check if username exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                return [
                    'success' => false,
                    'message' => 'Username already exists'
                ];
            }

            // Check if email exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                return [
                    'success' => false,
                    'message' => 'Email already exists'
                ];
            }

            // Create user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO users (username, email, password, role, is_active, created_at) VALUES (?, ?, ?, ?, 1, NOW())");
            $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'User registered successfully',
                    'user_id' => $this->db->getLastInsertId()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to register user'
            ];
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during registration'
            ];
        }
    }
}
?>
