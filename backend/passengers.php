<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Main Classes/User.php';
require_once __DIR__ . '/../Main Classes/DbConnector.php';

// Authenticate user
$user = new User();
if (!$user->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$db = DbConnector::getInstance();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Get all passengers or a specific passenger
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                $stmt = $db->prepare("SELECT * FROM passengers WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    echo json_encode(['success' => true, 'data' => $result->fetch_assoc()]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Passenger not found']);
                }
            } else {
                $sql = "SELECT * FROM passengers WHERE 1=1";
                $params = [];
                $types = "";
                
                if (isset($_GET['user_id'])) {
                    $sql .= " AND user_id = ?";
                    $params[] = intval($_GET['user_id']);
                    $types .= "i";
                } elseif (!$user->hasRole('admin') && !$user->hasRole('staff')) {
                    // Regular users can only see their own passengers
                    $sql .= " AND user_id = ?";
                    $params[] = $user->getUserId();
                    $types .= "i";
                }
                
                $sql .= " ORDER BY last_name, first_name";
                
                if (!empty($params)) {
                    $stmt = $db->prepare($sql);
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $result = $stmt->get_result();
                } else {
                    $result = $db->query($sql);
                }
                
                $passengers = [];
                while ($row = $result->fetch_assoc()) {
                    $passengers[] = $row;
                }
                echo json_encode(['success' => true, 'data' => $passengers]);
            }
            break;

        case 'POST':
            // Create a new passenger
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['first_name']) || !isset($input['last_name']) || !isset($input['email'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'First name, last name, and email are required']);
                exit();
            }

            // Validate email format
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                exit();
            }

            $stmt = $db->prepare("INSERT INTO passengers (user_id, first_name, last_name, email, phone, date_of_birth, 
                                  passport_number, nationality, emergency_contact_name, emergency_contact_phone) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $user_id = isset($input['user_id']) ? $input['user_id'] : $user->getUserId();
            $phone = isset($input['phone']) ? $input['phone'] : null;
            $date_of_birth = isset($input['date_of_birth']) ? $input['date_of_birth'] : null;
            $passport_number = isset($input['passport_number']) ? $input['passport_number'] : null;
            $nationality = isset($input['nationality']) ? $input['nationality'] : null;
            $emergency_contact_name = isset($input['emergency_contact_name']) ? $input['emergency_contact_name'] : null;
            $emergency_contact_phone = isset($input['emergency_contact_phone']) ? $input['emergency_contact_phone'] : null;
            
            $stmt->bind_param("isssssssss", 
                $user_id,
                $input['first_name'],
                $input['last_name'],
                $input['email'],
                $phone,
                $date_of_birth,
                $passport_number,
                $nationality,
                $emergency_contact_name,
                $emergency_contact_phone
            );
            
            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(['success' => true, 'message' => 'Passenger created successfully', 'passenger_id' => $db->getLastInsertId()]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create passenger']);
            }
            break;

        case 'PUT':
            // Update a passenger
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Passenger ID is required']);
                exit();
            }

            // Check ownership unless admin/staff
            if (!$user->hasRole('admin') && !$user->hasRole('staff')) {
                $stmt = $db->prepare("SELECT user_id FROM passengers WHERE id = ?");
                $stmt->bind_param("i", $input['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Passenger not found']);
                    exit();
                }
                
                $passenger = $result->fetch_assoc();
                if ($passenger['user_id'] != $user->getUserId()) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Forbidden']);
                    exit();
                }
            }

            $updates = [];
            $params = [];
            $types = "";

            $allowedFields = ['first_name', 'last_name', 'email', 'phone', 'date_of_birth', 
                             'passport_number', 'nationality', 'emergency_contact_name', 'emergency_contact_phone'];
            
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $input[$field];
                    $types .= 's';
                }
            }

            if (empty($updates)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No fields to update']);
                exit();
            }

            $sql = "UPDATE passengers SET " . implode(", ", $updates) . " WHERE id = ?";
            $params[] = $input['id'];
            $types .= "i";

            $stmt = $db->prepare($sql);
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Passenger updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update passenger']);
            }
            break;

        case 'DELETE':
            // Delete a passenger
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Passenger ID is required']);
                exit();
            }

            // Check ownership unless admin
            if (!$user->hasRole('admin')) {
                $stmt = $db->prepare("SELECT user_id FROM passengers WHERE id = ?");
                $stmt->bind_param("i", $input['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Passenger not found']);
                    exit();
                }
                
                $passenger = $result->fetch_assoc();
                if ($passenger['user_id'] != $user->getUserId()) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Forbidden']);
                    exit();
                }
            }

            $stmt = $db->prepare("DELETE FROM passengers WHERE id = ?");
            $stmt->bind_param("i", $input['id']);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Passenger deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete passenger']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    error_log("Passengers endpoint error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
