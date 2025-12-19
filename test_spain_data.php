<?php
header('Content-Type: text/plain');

$conn = new mysqli("localhost", "root", "", "serendip");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "=== SPAIN ITINERARY DATA ===\n\n";
$result = $conn->query("SELECT * FROM itineraries WHERE route = 'Spain'");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    print_r($row);
} else {
    echo "NO Spain itinerary found\n";
}

echo "\n\n=== SPAIN PRICING DATA ===\n\n";
$result = $conn->query("SELECT * FROM cabin_type_pricing WHERE route = 'Spain'");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    print_r($row);
} else {
    echo "NO Spain pricing found\n";
}

echo "\n\n=== SHIP FOR SPAIN (Ship ID: 5) ===\n\n";
$result = $conn->query("SELECT * FROM ship_details WHERE ship_id = 5");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    print_r($row);
} else {
    echo "NO ship found\n";
}

$conn->close();
?>
