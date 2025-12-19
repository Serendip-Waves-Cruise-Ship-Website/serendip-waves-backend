<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/DbConnector.php';

class CabinManager {
    private $db;

    public function __construct() {
        $this->db = DbConnector::getInstance();
    }

    public function getCabins($filters = []) {
        try {
            $sql = "SELECT c.*, s.name as ship_name FROM cabins c 
                    LEFT JOIN ships s ON c.ship_id = s.id 
                    WHERE 1=1";
            $params = [];
            $types = "";

            if (isset($filters['ship_id'])) {
                $sql .= " AND c.ship_id = ?";
                $params[] = $filters['ship_id'];
                $types .= "i";
            }

            if (isset($filters['type'])) {
                $sql .= " AND c.type = ?";
                $params[] = $filters['type'];
                $types .= "s";
            }

            if (isset($filters['available'])) {
                $sql .= " AND c.is_available = ?";
                $params[] = $filters['available'];
                $types .= "i";
            }

            $sql .= " ORDER BY c.cabin_number";

            $stmt = $this->db->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            $cabins = [];
            while ($row = $result->fetch_assoc()) {
                $cabins[] = $row;
            }

            return [
                'success' => true,
                'data' => $cabins
            ];
        } catch (Exception $e) {
            error_log("Get cabins error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve cabins'
            ];
        }
    }

    public function getCabinById($cabinId) {
        try {
            $stmt = $this->db->prepare("SELECT c.*, s.name as ship_name FROM cabins c 
                                        LEFT JOIN ships s ON c.ship_id = s.id 
                                        WHERE c.id = ?");
            $stmt->bind_param("i", $cabinId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                return [
                    'success' => true,
                    'data' => $result->fetch_assoc()
                ];
            }

            return [
                'success' => false,
                'message' => 'Cabin not found'
            ];
        } catch (Exception $e) {
            error_log("Get cabin error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve cabin'
            ];
        }
    }

    public function createCabin($data) {
        try {
            $stmt = $this->db->prepare("INSERT INTO cabins (ship_id, cabin_number, type, capacity, price_per_night, description, is_available) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issidsi", 
                $data['ship_id'],
                $data['cabin_number'],
                $data['type'],
                $data['capacity'],
                $data['price_per_night'],
                $data['description'],
                $data['is_available']
            );

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Cabin created successfully',
                    'cabin_id' => $this->db->getLastInsertId()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to create cabin'
            ];
        } catch (Exception $e) {
            error_log("Create cabin error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create cabin'
            ];
        }
    }

    public function updateCabin($cabinId, $data) {
        try {
            $updates = [];
            $params = [];
            $types = "";

            $allowedFields = ['ship_id', 'cabin_number', 'type', 'capacity', 'price_per_night', 'description', 'is_available'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field];
                    
                    if ($field === 'ship_id' || $field === 'capacity' || $field === 'is_available') {
                        $types .= "i";
                    } elseif ($field === 'price_per_night') {
                        $types .= "d";
                    } else {
                        $types .= "s";
                    }
                }
            }

            if (empty($updates)) {
                return [
                    'success' => false,
                    'message' => 'No fields to update'
                ];
            }

            $sql = "UPDATE cabins SET " . implode(", ", $updates) . " WHERE id = ?";
            $params[] = $cabinId;
            $types .= "i";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Cabin updated successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to update cabin'
            ];
        } catch (Exception $e) {
            error_log("Update cabin error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update cabin'
            ];
        }
    }

    public function deleteCabin($cabinId) {
        try {
            // Check if cabin is in use
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM bookings WHERE cabin_id = ?");
            $stmt->bind_param("i", $cabinId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if ($result['count'] > 0) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete cabin with existing bookings'
                ];
            }

            $stmt = $this->db->prepare("DELETE FROM cabins WHERE id = ?");
            $stmt->bind_param("i", $cabinId);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Cabin deleted successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to delete cabin'
            ];
        } catch (Exception $e) {
            error_log("Delete cabin error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to delete cabin'
            ];
        }
    }

    public function checkAvailability($cabinId, $startDate, $endDate) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM bookings 
                                        WHERE cabin_id = ? 
                                        AND status != 'cancelled'
                                        AND ((check_in_date BETWEEN ? AND ?) 
                                        OR (check_out_date BETWEEN ? AND ?)
                                        OR (check_in_date <= ? AND check_out_date >= ?))");
            $stmt->bind_param("issssss", $cabinId, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            return [
                'success' => true,
                'available' => $result['count'] == 0
            ];
        } catch (Exception $e) {
            error_log("Check availability error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to check availability'
            ];
        }
    }
}
?>
