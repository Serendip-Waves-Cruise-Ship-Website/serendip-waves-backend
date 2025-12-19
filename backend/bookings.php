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

// Helper function to generate booking reference
function generateBookingReference() {
    return 'SW' . date('Ymd') . strtoupper(substr(uniqid(), -6));
}

try {
    switch ($method) {
        case 'GET':
            // Get all bookings or a specific booking
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                $stmt = $db->prepare("SELECT b.*, i.name as itinerary_name, c.cabin_number, s.name as ship_name 
                                      FROM bookings b 
                                      LEFT JOIN itineraries i ON b.itinerary_id = i.id 
                                      LEFT JOIN cabins c ON b.cabin_id = c.id 
                                      LEFT JOIN ships s ON c.ship_id = s.id 
                                      WHERE b.id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $booking = $result->fetch_assoc();
                    
                    // Get passengers for this booking
                    $stmt = $db->prepare("SELECT p.* FROM passengers p 
                                          INNER JOIN booking_passengers bp ON p.id = bp.passenger_id 
                                          WHERE bp.booking_id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $passengerResult = $stmt->get_result();
                    
                    $passengers = [];
                    while ($row = $passengerResult->fetch_assoc()) {
                        $passengers[] = $row;
                    }
                    
                    $booking['passengers'] = $passengers;
                    echo json_encode(['success' => true, 'data' => $booking]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Booking not found']);
                }
            } else {
                $sql = "SELECT b.*, i.name as itinerary_name, c.cabin_number, s.name as ship_name 
                        FROM bookings b 
                        LEFT JOIN itineraries i ON b.itinerary_id = i.id 
                        LEFT JOIN cabins c ON b.cabin_id = c.id 
                        LEFT JOIN ships s ON c.ship_id = s.id 
                        WHERE 1=1";
                $params = [];
                $types = "";
                
                if (!$user->hasRole('admin') && !$user->hasRole('staff')) {
                    // Regular users can only see their own bookings
                    $sql .= " AND b.user_id = ?";
                    $params[] = $user->getUserId();
                    $types .= "i";
                } elseif (isset($_GET['user_id'])) {
                    $sql .= " AND b.user_id = ?";
                    $params[] = intval($_GET['user_id']);
                    $types .= "i";
                }
                
                if (isset($_GET['status'])) {
                    $sql .= " AND b.status = ?";
                    $params[] = $_GET['status'];
                    $types .= "s";
                }
                
                $sql .= " ORDER BY b.created_at DESC";
                
                if (!empty($params)) {
                    $stmt = $db->prepare($sql);
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $result = $stmt->get_result();
                } else {
                    $result = $db->query($sql);
                }
                
                $bookings = [];
                while ($row = $result->fetch_assoc()) {
                    $bookings[] = $row;
                }
                echo json_encode(['success' => true, 'data' => $bookings]);
            }
            break;

        case 'POST':
            // Create a new booking
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['itinerary_id']) || !isset($input['cabin_id']) || 
                !isset($input['check_in_date']) || !isset($input['check_out_date']) || 
                !isset($input['number_of_passengers']) || !isset($input['total_amount'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Required fields missing']);
                exit();
            }

            // Validate cabin availability
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM bookings 
                                  WHERE cabin_id = ? 
                                  AND status != 'cancelled'
                                  AND ((check_in_date BETWEEN ? AND ?) 
                                  OR (check_out_date BETWEEN ? AND ?)
                                  OR (check_in_date <= ? AND check_out_date >= ?))");
            $stmt->bind_param("issssss", 
                $input['cabin_id'], 
                $input['check_in_date'], 
                $input['check_out_date'],
                $input['check_in_date'], 
                $input['check_out_date'],
                $input['check_in_date'], 
                $input['check_out_date']
            );
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if ($result['count'] > 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cabin is not available for the selected dates']);
                exit();
            }

            $db->beginTransaction();
            
            try {
                $booking_reference = generateBookingReference();
                $user_id = isset($input['user_id']) ? $input['user_id'] : $user->getUserId();
                $special_requests = isset($input['special_requests']) ? $input['special_requests'] : null;
                $status = isset($input['status']) ? $input['status'] : 'pending';
                $payment_status = isset($input['payment_status']) ? $input['payment_status'] : 'pending';
                
                $stmt = $db->prepare("INSERT INTO bookings (user_id, itinerary_id, cabin_id, booking_reference, 
                                      check_in_date, check_out_date, number_of_passengers, total_amount, 
                                      status, payment_status, special_requests) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->bind_param("iiissssdsss", 
                    $user_id,
                    $input['itinerary_id'],
                    $input['cabin_id'],
                    $booking_reference,
                    $input['check_in_date'],
                    $input['check_out_date'],
                    $input['number_of_passengers'],
                    $input['total_amount'],
                    $status,
                    $payment_status,
                    $special_requests
                );
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to create booking");
                }
                
                $booking_id = $db->getLastInsertId();
                
                // Link passengers to booking if provided
                if (isset($input['passenger_ids']) && is_array($input['passenger_ids'])) {
                    $stmt = $db->prepare("INSERT INTO booking_passengers (booking_id, passenger_id) VALUES (?, ?)");
                    
                    foreach ($input['passenger_ids'] as $passenger_id) {
                        $stmt->bind_param("ii", $booking_id, $passenger_id);
                        $stmt->execute();
                    }
                }
                
                $db->commit();
                
                http_response_code(201);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Booking created successfully', 
                    'booking_id' => $booking_id,
                    'booking_reference' => $booking_reference
                ]);
            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
            break;

        case 'PUT':
            // Update a booking
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
                exit();
            }

            // Check ownership unless admin/staff
            if (!$user->hasRole('admin') && !$user->hasRole('staff')) {
                $stmt = $db->prepare("SELECT user_id FROM bookings WHERE id = ?");
                $stmt->bind_param("i", $input['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Booking not found']);
                    exit();
                }
                
                $booking = $result->fetch_assoc();
                if ($booking['user_id'] != $user->getUserId()) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Forbidden']);
                    exit();
                }
            }

            $updates = [];
            $params = [];
            $types = "";

            $allowedFields = ['status', 'payment_status', 'special_requests', 'total_amount'];
            
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $input[$field];
                    
                    if ($field === 'total_amount') {
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

            $sql = "UPDATE bookings SET " . implode(", ", $updates) . " WHERE id = ?";
            $params[] = $input['id'];
            $types .= "i";

            $stmt = $db->prepare($sql);
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Booking updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update booking']);
            }
            break;

        case 'DELETE':
            // Cancel/delete a booking
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
                exit();
            }

            // Check ownership unless admin
            if (!$user->hasRole('admin')) {
                $stmt = $db->prepare("SELECT user_id FROM bookings WHERE id = ?");
                $stmt->bind_param("i", $input['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Booking not found']);
                    exit();
                }
                
                $booking = $result->fetch_assoc();
                if ($booking['user_id'] != $user->getUserId()) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Forbidden']);
                    exit();
                }
            }

            // Instead of deleting, we'll cancel the booking
            $stmt = $db->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
            $stmt->bind_param("i", $input['id']);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to cancel booking']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    error_log("Bookings endpoint error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
