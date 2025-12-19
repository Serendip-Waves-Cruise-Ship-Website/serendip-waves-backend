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
            // Get all itineraries or a specific itinerary
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                $stmt = $db->prepare("SELECT i.*, s.name as ship_name FROM itineraries i 
                                      LEFT JOIN ships s ON i.ship_id = s.id WHERE i.id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    echo json_encode(['success' => true, 'data' => $result->fetch_assoc()]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Itinerary not found']);
                }
            } else {
                $sql = "SELECT i.*, s.name as ship_name FROM itineraries i 
                        LEFT JOIN ships s ON i.ship_id = s.id WHERE 1=1";
                
                if (isset($_GET['ship_id'])) {
                    $sql .= " AND i.ship_id = " . intval($_GET['ship_id']);
                }
                if (isset($_GET['active']) && $_GET['active'] == '1') {
                    $sql .= " AND i.is_active = 1";
                }
                if (isset($_GET['upcoming']) && $_GET['upcoming'] == '1') {
                    $sql .= " AND i.departure_date >= CURDATE()";
                }
                
                $sql .= " ORDER BY i.departure_date";
                
                $result = $db->query($sql);
                $itineraries = [];
                while ($row = $result->fetch_assoc()) {
                    $itineraries[] = $row;
                }
                echo json_encode(['success' => true, 'data' => $itineraries]);
            }
            break;

        case 'POST':
            // Create a new itinerary (admin/staff only)
            if (!$user->hasRole('admin') && !$user->hasRole('staff')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden']);
                exit();
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['ship_id']) || !isset($input['name']) || !isset($input['departure_port']) || 
                !isset($input['arrival_port']) || !isset($input['departure_date']) || !isset($input['arrival_date']) ||
                !isset($input['duration_days']) || !isset($input['price_per_person']) || !isset($input['available_slots'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Required fields missing']);
                exit();
            }

            $stmt = $db->prepare("INSERT INTO itineraries (ship_id, name, description, departure_port, arrival_port, 
                                  departure_date, arrival_date, duration_days, price_per_person, available_slots, is_active) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $is_active = isset($input['is_active']) ? $input['is_active'] : 1;
            $description = isset($input['description']) ? $input['description'] : null;
            
            $stmt->bind_param("issssssiidi", 
                $input['ship_id'],
                $input['name'],
                $description,
                $input['departure_port'],
                $input['arrival_port'],
                $input['departure_date'],
                $input['arrival_date'],
                $input['duration_days'],
                $input['price_per_person'],
                $input['available_slots'],
                $is_active
            );
            
            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(['success' => true, 'message' => 'Itinerary created successfully', 'itinerary_id' => $db->getLastInsertId()]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create itinerary']);
            }
            break;

        case 'PUT':
            // Update an itinerary (admin/staff only)
            if (!$user->hasRole('admin') && !$user->hasRole('staff')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden']);
                exit();
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Itinerary ID is required']);
                exit();
            }

            $updates = [];
            $params = [];
            $types = "";

            $allowedFields = ['ship_id', 'name', 'description', 'departure_port', 'arrival_port', 
                             'departure_date', 'arrival_date', 'duration_days', 'price_per_person', 'available_slots', 'is_active'];
            
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $input[$field];
                    
                    if (in_array($field, ['ship_id', 'duration_days', 'available_slots', 'is_active'])) {
                        $types .= 'i';
                    } elseif ($field === 'price_per_person') {
                        $types .= 'd';
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

            $sql = "UPDATE itineraries SET " . implode(", ", $updates) . " WHERE id = ?";
            $params[] = $input['id'];
            $types .= "i";

            $stmt = $db->prepare($sql);
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Itinerary updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update itinerary']);
            }
            break;

        case 'DELETE':
            // Delete an itinerary (admin only)
            if (!$user->hasRole('admin')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden']);
                exit();
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Itinerary ID is required']);
                exit();
            }

            $stmt = $db->prepare("DELETE FROM itineraries WHERE id = ?");
            $stmt->bind_param("i", $input['id']);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Itinerary deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete itinerary']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    error_log("Itineraries endpoint error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
