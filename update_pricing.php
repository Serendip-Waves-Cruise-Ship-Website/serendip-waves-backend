<?php
header('Content-Type: text/plain');

$conn = new mysqli("localhost", "root", "", "serendip");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "=== UPDATING CABIN_TYPE_PRICING TO REASONABLE PRICES ===\n\n";

// Update all pricing to reasonable ranges
// Interior: 200-400
// Ocean View: 300-500
// Balcony: 400-700
// Suite: 600-1200

$updates = [
    ['ship_id' => 1, 'route' => 'Brazil', 'interior' => 250, 'ocean' => 350, 'balcony' => 500, 'suite' => 800],
    ['ship_id' => 9, 'route' => 'France', 'interior' => 400, 'ocean' => 500, 'balcony' => 700, 'suite' => 1200],
    ['ship_id' => 7, 'route' => 'Egypt', 'interior' => 280, 'ocean' => 380, 'balcony' => 550, 'suite' => 900],
    ['ship_id' => 5, 'route' => 'Spain', 'interior' => 350, 'ocean' => 450, 'balcony' => 650, 'suite' => 1000],
    ['ship_id' => 10, 'route' => 'Italy', 'interior' => 380, 'ocean' => 480, 'balcony' => 680, 'suite' => 1100],
    ['ship_id' => 4, 'route' => 'Australia', 'interior' => 320, 'ocean' => 470, 'balcony' => 680, 'suite' => 1150],
    ['ship_id' => 2, 'route' => 'Japan', 'interior' => 290, 'ocean' => 420, 'balcony' => 590, 'suite' => 980],
    ['ship_id' => 3, 'route' => 'Alaska', 'interior' => 310, 'ocean' => 460, 'balcony' => 640, 'suite' => 1050],
    ['ship_id' => 12, 'route' => 'Caribbean', 'interior' => 330, 'ocean' => 480, 'balcony' => 670, 'suite' => 1120],
    ['ship_id' => 8, 'route' => 'Norway', 'interior' => 360, 'ocean' => 490, 'balcony' => 690, 'suite' => 1180],
    ['ship_id' => 6, 'route' => 'Greece', 'interior' => 340, 'ocean' => 470, 'balcony' => 660, 'suite' => 1080],
];

foreach ($updates as $update) {
    $sql = "UPDATE cabin_type_pricing 
            SET interior_price = {$update['interior']}, 
                ocean_view_price = {$update['ocean']}, 
                balcony_price = {$update['balcony']}, 
                suite_price = {$update['suite']} 
            WHERE ship_id = {$update['ship_id']} AND route = '{$update['route']}'";
    
    if ($conn->query($sql)) {
        echo "✅ Updated {$update['route']}: Interior \${$update['interior']}, Ocean View \${$update['ocean']}, Balcony \${$update['balcony']}, Suite \${$update['suite']}\n";
    } else {
        echo "❌ Failed to update {$update['route']}: " . $conn->error . "\n";
    }
}

echo "\n\n=== UPDATED PRICING TABLE ===\n\n";
$result = $conn->query("SELECT ship_id, route, interior_price, ocean_view_price, balcony_price, suite_price FROM cabin_type_pricing");
while ($row = $result->fetch_assoc()) {
    echo "Ship {$row['ship_id']} - {$row['route']}: Interior \${$row['interior_price']}, Ocean View \${$row['ocean_view_price']}, Balcony \${$row['balcony_price']}, Suite \${$row['suite_price']}\n";
}

$conn->close();
echo "\n\n✅ ALL PRICES UPDATED SUCCESSFULLY!";
?>
