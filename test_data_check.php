<?php
header('Content-Type: text/plain');

$conn = new mysqli("localhost", "root", "", "serendip");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "=== CHECKING ITINERARIES TABLE ===\n\n";
$result = $conn->query("SELECT id, route, ship_id, ship_name, price FROM itineraries LIMIT 5");
while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']}, Route: {$row['route']}, Ship ID: {$row['ship_id']}, Ship Name: {$row['ship_name']}, Price: {$row['price']}\n";
}

echo "\n\n=== CHECKING SHIP_DETAILS TABLE ===\n\n";
$result = $conn->query("SELECT ship_id, ship_name, ship_image FROM ship_details LIMIT 5");
while ($row = $result->fetch_assoc()) {
    $image_status = empty($row['ship_image']) ? "EMPTY/NULL" : $row['ship_image'];
    echo "Ship ID: {$row['ship_id']}, Ship Name: {$row['ship_name']}, Image: {$image_status}\n";
}

echo "\n\n=== CHECKING CABIN_TYPE_PRICING TABLE ===\n\n";
$result = $conn->query("SELECT ship_id, route, interior_price, ocean_view_price, balcony_price, suite_price FROM cabin_type_pricing LIMIT 5");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Ship ID: {$row['ship_id']}, Route: {$row['route']}, Interior: {$row['interior_price']}, Ocean View: {$row['ocean_view_price']}, Balcony: {$row['balcony_price']}, Suite: {$row['suite_price']}\n";
    }
} else {
    echo "NO ROWS FOUND in cabin_type_pricing table\n";
}

$conn->close();
?>
