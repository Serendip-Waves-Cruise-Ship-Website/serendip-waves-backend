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
            // Get all meals or a specific meal
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                $stmt = $db->prepare("SELECT * FROM meals WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    echo json_encode(['success' => true, 'data' => $result->fetch_assoc()]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Meal not found']);
                }
            } else {
                $sql = "SELECT * FROM meals WHERE 1=1";
                $params = [];
                $types = "";
                
                if (isset($_GET['category'])) {
                    $sql .= " AND category = ?";
                    $params[] = $_GET['category'];
                    $types .= "s";
                }
                if (isset($_GET['available']) && $_GET['available'] == '1') {
                    $sql .= " AND is_available = 1";
                }
                
                $sql .= " ORDER BY category, name";
                
                if (!empty($params)) {
                    $stmt = $db->prepare($sql);
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $result = $stmt->get_result();
                } else {
                    $result = $db->query($sql);
                }
                
                $meals = [];
                while ($row = $result->fetch_assoc()) {
                    $meals[] = $row;
                }
                echo json_encode(['success' => true, 'data' => $meals]);
            }
            break;

        case 'POST':
            // Create a new meal (admin/staff only)
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

            $stmt = $db->prepare("INSERT INTO meals (name, description, category, price, image_url, is_available, dietary_info) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            $description = isset($input['description']) ? $input['description'] : null;
            $price = isset($input['price']) ? $input['price'] : null;
            $image_url = isset($input['image_url']) ? $input['image_url'] : null;
            $is_available = isset($input['is_available']) ? $input['is_available'] : 1;
            $dietary_info = isset($input['dietary_info']) ? $input['dietary_info'] : null;
            
            $stmt->bind_param("sssdsis", 
                $input['name'],
                $description,
                $input['category'],
                $price,
                $image_url,
                $is_available,
                $dietary_info
            );
            
            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(['success' => true, 'message' => 'Meal created successfully', 'meal_id' => $db->getLastInsertId()]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create meal']);
            }
            break;

        case 'PUT':
            // Update a meal (admin/staff only)
            if (!$user->hasRole('admin') && !$user->hasRole('staff')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden']);
                exit();
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Meal ID is required']);
                exit();
            }

            $updates = [];
            $params = [];
            $types = "";

            $allowedFields = ['name', 'description', 'category', 'price', 'image_url', 'is_available', 'dietary_info'];
            
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $input[$field];
                    
                    if ($field === 'is_available') {
                        $types .= 'i';
                    } elseif ($field === 'price') {
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

            $sql = "UPDATE meals SET " . implode(", ", $updates) . " WHERE id = ?";
            $params[] = $input['id'];
            $types .= "i";

            $stmt = $db->prepare($sql);
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Meal updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update meal']);
            }
            break;

        case 'DELETE':
            // Delete a meal (admin only)
            if (!$user->hasRole('admin')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden']);
                exit();
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Meal ID is required']);
                exit();
            }

            $stmt = $db->prepare("DELETE FROM meals WHERE id = ?");
            $stmt->bind_param("i", $input['id']);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Meal deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete meal']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    error_log("Meals endpoint error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
