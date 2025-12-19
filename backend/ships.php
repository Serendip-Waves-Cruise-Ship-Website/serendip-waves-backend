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
            // Get all ships or a specific ship
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                $stmt = $db->prepare("SELECT * FROM ships WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    echo json_encode(['success' => true, 'data' => $result->fetch_assoc()]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Ship not found']);
                }
            } else {
                $sql = "SELECT * FROM ships";
                if (isset($_GET['active']) && $_GET['active'] == '1') {
                    $sql .= " WHERE is_active = 1";
                }
                $sql .= " ORDER BY name";
                
                $result = $db->query($sql);
                $ships = [];
                while ($row = $result->fetch_assoc()) {
                    $ships[] = $row;
                }
                echo json_encode(['success' => true, 'data' => $ships]);
            }
            break;

        case 'POST':
            // Create a new ship (admin only)
            if (!$user->hasRole('admin') && !$user->hasRole('staff')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden']);
                exit();
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['name']) || !isset($input['capacity'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Name and capacity are required']);
                exit();
            }

            $stmt = $db->prepare("INSERT INTO ships (name, description, capacity, image_url, is_active) VALUES (?, ?, ?, ?, ?)");
            $is_active = isset($input['is_active']) ? $input['is_active'] : 1;
            $image_url = isset($input['image_url']) ? $input['image_url'] : null;
            $description = isset($input['description']) ? $input['description'] : null;
            
            $stmt->bind_param("ssisi", $input['name'], $description, $input['capacity'], $image_url, $is_active);
            
            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(['success' => true, 'message' => 'Ship created successfully', 'ship_id' => $db->getLastInsertId()]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create ship']);
            }
            break;

        case 'PUT':
            // Update a ship (admin only)
            if (!$user->hasRole('admin') && !$user->hasRole('staff')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden']);
                exit();
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Ship ID is required']);
                exit();
            }

            $updates = [];
            $params = [];
            $types = "";

            $allowedFields = ['name', 'description', 'capacity', 'image_url', 'is_active'];
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $input[$field];
                    $types .= ($field === 'capacity' || $field === 'is_active') ? 'i' : 's';
                }
            }

            if (empty($updates)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No fields to update']);
                exit();
            }

            $sql = "UPDATE ships SET " . implode(", ", $updates) . " WHERE id = ?";
            $params[] = $input['id'];
            $types .= "i";

            $stmt = $db->prepare($sql);
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Ship updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update ship']);
            }
            break;

        case 'DELETE':
            // Delete a ship (admin only)
            if (!$user->hasRole('admin')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden']);
                exit();
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Ship ID is required']);
                exit();
            }

            $stmt = $db->prepare("DELETE FROM ships WHERE id = ?");
            $stmt->bind_param("i", $input['id']);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Ship deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete ship']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    error_log("Ships endpoint error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
