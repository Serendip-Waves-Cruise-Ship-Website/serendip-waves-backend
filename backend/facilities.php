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
            // Get all facilities or a specific facility
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                $stmt = $db->prepare("SELECT f.*, s.name as ship_name FROM facilities f 
                                      LEFT JOIN ships s ON f.ship_id = s.id WHERE f.id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    echo json_encode(['success' => true, 'data' => $result->fetch_assoc()]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Facility not found']);
                }
            } else {
                $sql = "SELECT f.*, s.name as ship_name FROM facilities f 
                        LEFT JOIN ships s ON f.ship_id = s.id WHERE 1=1";
                $params = [];
                $types = "";
                
                if (isset($_GET['ship_id'])) {
                    $sql .= " AND f.ship_id = ?";
                    $params[] = intval($_GET['ship_id']);
                    $types .= "i";
                }
                if (isset($_GET['category'])) {
                    $sql .= " AND f.category = ?";
                    $params[] = $_GET['category'];
                    $types .= "s";
                }
                if (isset($_GET['available']) && $_GET['available'] == '1') {
                    $sql .= " AND f.is_available = 1";
                }
                
                $sql .= " ORDER BY f.category, f.name";
                
                if (!empty($params)) {
                    $stmt = $db->prepare($sql);
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $result = $stmt->get_result();
                } else {
                    $result = $db->query($sql);
                }
                
                $facilities = [];
                while ($row = $result->fetch_assoc()) {
                    $facilities[] = $row;
                }
                echo json_encode(['success' => true, 'data' => $facilities]);
            }
            break;

        case 'POST':
            // Create a new facility (admin/staff only)
            if (!$user->hasRole('admin') && !$user->hasRole('staff')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden']);
                exit();
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['name']) || !isset($input['category'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Name and category are required']);
                exit();
            }

            $stmt = $db->prepare("INSERT INTO facilities (ship_id, name, description, category, image_url, is_available, operating_hours) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            $ship_id = isset($input['ship_id']) ? $input['ship_id'] : null;
            $description = isset($input['description']) ? $input['description'] : null;
            $image_url = isset($input['image_url']) ? $input['image_url'] : null;
            $is_available = isset($input['is_available']) ? $input['is_available'] : 1;
            $operating_hours = isset($input['operating_hours']) ? $input['operating_hours'] : null;
            
            $stmt->bind_param("issssss", 
                $ship_id,
                $input['name'],
                $description,
                $input['category'],
                $image_url,
                $is_available,
                $operating_hours
            );
            
            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(['success' => true, 'message' => 'Facility created successfully', 'facility_id' => $db->getLastInsertId()]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create facility']);
            }
            break;

        case 'PUT':
            // Update a facility (admin/staff only)
            if (!$user->hasRole('admin') && !$user->hasRole('staff')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden']);
                exit();
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Facility ID is required']);
                exit();
            }

            $updates = [];
            $params = [];
            $types = "";

            $allowedFields = ['ship_id', 'name', 'description', 'category', 'image_url', 'is_available', 'operating_hours'];
            
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $input[$field];
                    
                    if (in_array($field, ['ship_id', 'is_available'])) {
                        $types .= 'i';
                    } else {
                        $types .= 's';
                    }
                }
            }

            if (empty($updates)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No fields to update']);
                exit();
            }

            $sql = "UPDATE facilities SET " . implode(", ", $updates) . " WHERE id = ?";
            $params[] = $input['id'];
            $types .= "i";

            $stmt = $db->prepare($sql);
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Facility updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update facility']);
            }
            break;

        case 'DELETE':
            // Delete a facility (admin only)
            if (!$user->hasRole('admin')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden']);
                exit();
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Facility ID is required']);
                exit();
            }

            $stmt = $db->prepare("DELETE FROM facilities WHERE id = ?");
            $stmt->bind_param("i", $input['id']);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Facility deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete facility']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    error_log("Facilities endpoint error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
