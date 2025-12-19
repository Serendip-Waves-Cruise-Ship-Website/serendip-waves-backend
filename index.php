<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$response = [
    'success' => true,
    'message' => 'Serendip Waves Backend API',
    'version' => '1.0.0',
    'endpoints' => [
        'authentication' => [
            'POST /backend/login.php' => 'User login',
            'POST /backend/logout.php' => 'User logout',
            'POST /backend/register.php' => 'User registration'
        ],
        'ships' => [
            'GET /backend/ships.php' => 'Get all ships',
            'GET /backend/ships.php?id={id}' => 'Get ship by ID',
            'POST /backend/ships.php' => 'Create new ship (admin/staff)',
            'PUT /backend/ships.php' => 'Update ship (admin/staff)',
            'DELETE /backend/ships.php' => 'Delete ship (admin)'
        ],
        'cabins' => [
            'GET /backend/cabins.php' => 'Get all cabins',
            'GET /backend/cabins.php?id={id}' => 'Get cabin by ID',
            'GET /backend/cabins.php?ship_id={id}' => 'Get cabins by ship',
            'POST /backend/cabins.php' => 'Create new cabin (admin/staff)',
            'POST /backend/cabins.php (action=check_availability)' => 'Check cabin availability',
            'PUT /backend/cabins.php' => 'Update cabin (admin/staff)',
            'DELETE /backend/cabins.php' => 'Delete cabin (admin)'
        ],
        'itineraries' => [
            'GET /backend/itineraries.php' => 'Get all itineraries',
            'GET /backend/itineraries.php?id={id}' => 'Get itinerary by ID',
            'GET /backend/itineraries.php?ship_id={id}' => 'Get itineraries by ship',
            'POST /backend/itineraries.php' => 'Create new itinerary (admin/staff)',
            'PUT /backend/itineraries.php' => 'Update itinerary (admin/staff)',
            'DELETE /backend/itineraries.php' => 'Delete itinerary (admin)'
        ],
        'meals' => [
            'GET /backend/meals.php' => 'Get all meals',
            'GET /backend/meals.php?id={id}' => 'Get meal by ID',
            'GET /backend/meals.php?category={category}' => 'Get meals by category',
            'POST /backend/meals.php' => 'Create new meal (admin/staff)',
            'PUT /backend/meals.php' => 'Update meal (admin/staff)',
            'DELETE /backend/meals.php' => 'Delete meal (admin)'
        ],
        'facilities' => [
            'GET /backend/facilities.php' => 'Get all facilities',
            'GET /backend/facilities.php?id={id}' => 'Get facility by ID',
            'GET /backend/facilities.php?ship_id={id}' => 'Get facilities by ship',
            'POST /backend/facilities.php' => 'Create new facility (admin/staff)',
            'PUT /backend/facilities.php' => 'Update facility (admin/staff)',
            'DELETE /backend/facilities.php' => 'Delete facility (admin)'
        ],
        'passengers' => [
            'GET /backend/passengers.php' => 'Get all passengers',
            'GET /backend/passengers.php?id={id}' => 'Get passenger by ID',
            'POST /backend/passengers.php' => 'Create new passenger',
            'PUT /backend/passengers.php' => 'Update passenger',
            'DELETE /backend/passengers.php' => 'Delete passenger'
        ],
        'bookings' => [
            'GET /backend/bookings.php' => 'Get all bookings',
            'GET /backend/bookings.php?id={id}' => 'Get booking by ID',
            'POST /backend/bookings.php' => 'Create new booking',
            'PUT /backend/bookings.php' => 'Update booking',
            'DELETE /backend/bookings.php' => 'Cancel booking'
        ],
        'upload' => [
            'POST /backend/upload.php?type=ship' => 'Upload ship image (admin/staff)',
            'POST /backend/upload.php?type=meal' => 'Upload meal image (admin/staff)',
            'POST /backend/upload.php?type=facility' => 'Upload facility image (admin/staff)'
        ]
    ],
    'documentation' => 'See README.md for detailed API documentation'
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
