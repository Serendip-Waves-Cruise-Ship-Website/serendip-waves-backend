<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Main Classes/User.php';
require_once __DIR__ . '/../Main Classes/CabinManager.php';

// Authenticate user
$user = new User();
if (!$user->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$cabinManager = new CabinManager();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Get all cabins or a specific cabin
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                $result = $cabinManager->getCabinById($id);
                
                if ($result['success']) {
                    echo json_encode($result);
                } else {
                    http_response_code(404);
                    echo json_encode($result);
                }
            } else {
                $filters = [];
                if (isset($_GET['ship_id'])) {
                    $filters['ship_id'] = intval($_GET['ship_id']);
                }
                if (isset($_GET['type'])) {
                    $filters['type'] = $_GET['type'];
                }
                if (isset($_GET['available'])) {
                    $filters['available'] = intval($_GET['available']);
                }
                
                $result = $cabinManager->getCabins($filters);
                echo json_encode($result);
            }
            break;

        case 'POST':
            // Check if this is an availability check
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (isset($input['action']) && $input['action'] === 'check_availability') {
                if (!isset($input['cabin_id']) || !isset($input['start_date']) || !isset($input['end_date'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Cabin ID, start date, and end date are required']);
                    exit();
                }
                
                $result = $cabinManager->checkAvailability($input['cabin_id'], $input['start_date'], $input['end_date']);
                echo json_encode($result);
                exit();
            }

            // Create a new cabin (admin/staff only)
            if (!$user->hasRole('admin') && !$user->hasRole('staff')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden']);
                exit();
            }

            if (!isset($input['ship_id']) || !isset($input['cabin_number']) || !isset($input['type']) || 
                !isset($input['capacity']) || !isset($input['price_per_night'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Required fields missing']);
                exit();
            }

            $input['is_available'] = isset($input['is_available']) ? $input['is_available'] : 1;
            $result = $cabinManager->createCabin($input);
            
            if ($result['success']) {
                http_response_code(201);
            } else {
                http_response_code(500);
            }
            echo json_encode($result);
            break;

        case 'PUT':
            // Update a cabin (admin/staff only)
            if (!$user->hasRole('admin') && !$user->hasRole('staff')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden']);
                exit();
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cabin ID is required']);
                exit();
            }

            $cabinId = $input['id'];
            unset($input['id']);
            
            $result = $cabinManager->updateCabin($cabinId, $input);
            
            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(500);
                echo json_encode($result);
            }
            break;

        case 'DELETE':
            // Delete a cabin (admin only)
            if (!$user->hasRole('admin')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden']);
                exit();
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cabin ID is required']);
                exit();
            }

            $result = $cabinManager->deleteCabin($input['id']);
            
            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(500);
                echo json_encode($result);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    error_log("Cabins endpoint error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
