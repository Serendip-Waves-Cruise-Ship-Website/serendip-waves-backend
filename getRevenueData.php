<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "serendip");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

try {
    // 1. Total Revenue
    $totalRevenueQuery = "SELECT COALESCE(SUM(total_price), 0) as total_revenue FROM booking_overview";
    $totalRevenueResult = $conn->query($totalRevenueQuery);
    $totalRevenue = $totalRevenueResult->fetch_assoc()['total_revenue'];
    
    // 2. Revenue by Cabin Type
    $cabinRevenueQuery = "SELECT 
        room_type as cabin_type,
        COALESCE(SUM(total_price), 0) as revenue,
        COUNT(*) as bookings_count
    FROM booking_overview 
    WHERE room_type IS NOT NULL
    GROUP BY room_type";
    $cabinRevenueResult = $conn->query($cabinRevenueQuery);
    $cabinRevenue = [];
    while ($row = $cabinRevenueResult->fetch_assoc()) {
        $cabinRevenue[] = $row;
    }
    
    // 3. Revenue by Services (Facilities)
    $servicesRevenueQuery = "SELECT 
        'All Facilities' as facility_name,
        COALESCE(SUM(total_cost), 0) as total_revenue,
        COUNT(*) as total_bookings
    FROM facility_preferences 
    WHERE status = 'paid'";
    $servicesRevenueResult = $conn->query($servicesRevenueQuery);
    $servicesRevenue = [];
    $row = $servicesRevenueResult->fetch_assoc();
    if ($row && $row['total_revenue'] > 0) {
        $servicesRevenue[] = $row;
    }
    
    // 4. Monthly Revenue Trends (last 12 months)
    $monthlyRevenueQuery = "SELECT 
        DATE_FORMAT(booking_date, '%Y-%m') as month,
        DATE_FORMAT(booking_date, '%b %Y') as month_label,
        COALESCE(SUM(total_price), 0) as revenue,
        COUNT(*) as bookings_count
    FROM booking_overview 
    WHERE booking_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(booking_date, '%Y-%m'), DATE_FORMAT(booking_date, '%b %Y')
    ORDER BY month ASC";
    $monthlyRevenueResult = $conn->query($monthlyRevenueQuery);
    $monthlyRevenue = [];
    while ($row = $monthlyRevenueResult->fetch_assoc()) {
        $monthlyRevenue[] = $row;
    }
    
    // 5. Revenue by Ship/Cruise
    $shipRevenueQuery = "SELECT 
        ship_name,
        COALESCE(SUM(total_price), 0) as revenue,
        COUNT(*) as bookings_count
    FROM booking_overview 
    WHERE ship_name IS NOT NULL
    GROUP BY ship_name
    ORDER BY revenue DESC";
    $shipRevenueResult = $conn->query($shipRevenueQuery);
    $shipRevenue = [];
    while ($row = $shipRevenueResult->fetch_assoc()) {
        $shipRevenue[] = $row;
    }
    
    // 6. Recent High-Value Bookings (top 10)
    $topBookingsQuery = "SELECT 
        booking_id,
        full_name as customer_name,
        ship_name,
        room_type as cabin_type,
        total_price as total_cost,
        booking_date
    FROM booking_overview 
    ORDER BY total_price DESC
    LIMIT 10";
    $topBookingsResult = $conn->query($topBookingsQuery);
    $topBookings = [];
    while ($row = $topBookingsResult->fetch_assoc()) {
        $topBookings[] = $row;
    }
    
    // 7. Revenue Statistics
    $avgBookingQuery = "SELECT 
        AVG(total_price) as avg_booking_value,
        MIN(total_price) as min_booking_value,
        MAX(total_price) as max_booking_value
    FROM booking_overview";
    $avgBookingResult = $conn->query($avgBookingQuery);
    $revenueStats = $avgBookingResult->fetch_assoc();
    
    // Return comprehensive revenue data
    echo json_encode([
        "success" => true,
        "data" => [
            "total_revenue" => floatval($totalRevenue),
            "cabin_revenue" => $cabinRevenue,
            "services_revenue" => $servicesRevenue,
            "monthly_revenue" => $monthlyRevenue,
            "ship_revenue" => $shipRevenue,
            "top_bookings" => $topBookings,
            "statistics" => [
                "avg_booking_value" => floatval($revenueStats['avg_booking_value'] ?? 0),
                "min_booking_value" => floatval($revenueStats['min_booking_value'] ?? 0),
                "max_booking_value" => floatval($revenueStats['max_booking_value'] ?? 0)
            ]
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error fetching revenue data: " . $e->getMessage()
    ]);
}

$conn->close();
?>
