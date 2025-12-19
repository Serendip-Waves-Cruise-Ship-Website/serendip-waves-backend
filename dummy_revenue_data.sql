-- =====================================================
-- DUMMY DATA FOR SERENDIP CRUISE REVENUE DASHBOARD
-- =====================================================
-- Run this SQL to populate your database with realistic test data

-- Note: Adjust booking_id, customer_id, ship_id values based on your existing data

-- 1. Insert sample bookings with varied cabin types and prices
INSERT INTO booking_overview (full_name, gender, email, citizenship, age, room_type, cabin_number, adults, children, number_of_guests, card_type, card_number, total_price, ship_name, destination, booking_date, ship_id) VALUES
-- Suite bookings (high value)
('John Davidson', 'Male', 'john.d@email.com', 'USA', 45, 'Suite', 'S101', 2, 0, 2, 'Visa', '4532123456789012', 4500.00, 'Ocean Majesty', 'Caribbean Paradise', '2025-12-18 10:30:00', 1),
('Sarah Williams', 'Female', 'sarah.w@email.com', 'UK', 38, 'Suite', 'S201', 2, 0, 2, 'MasterCard', '5412345678901234', 5200.00, 'Serendip Star', 'Mediterranean Dream', '2025-12-19 14:20:00', 2),
('Michael Chen', 'Male', 'michael.c@email.com', 'Canada', 52, 'Suite', 'S102', 2, 1, 3, 'Amex', '371234567890123', 6800.00, 'Ocean Majesty', 'Alaska Adventure', '2025-12-20 09:15:00', 1),
('Emma Thompson', 'Female', 'emma.t@email.com', 'Australia', 41, 'Suite', 'S301', 2, 0, 2, 'Visa', '4532987654321098', 7500.00, 'Pacific Pearl', 'Asian Wonders', '2025-12-21 11:45:00', 3),

-- Balcony bookings (medium-high value)
('David Martinez', 'Male', 'david.m@email.com', 'Spain', 35, 'Balcony', 'B201', 2, 0, 2, 'MasterCard', '5498765432109876', 3200.00, 'Ocean Majesty', 'Caribbean Paradise', '2025-12-22 13:30:00', 1),
('Lisa Anderson', 'Female', 'lisa.a@email.com', 'USA', 42, 'Balcony', 'B301', 2, 2, 4, 'Visa', '4539876543210987', 4800.00, 'Serendip Star', 'Mediterranean Dream', '2025-12-23 15:10:00', 2),
('Robert Johnson', 'Male', 'robert.j@email.com', 'USA', 48, 'Balcony', 'B202', 2, 0, 2, 'Amex', '378765432109876', 3400.00, 'Ocean Majesty', 'Alaska Adventure', '2025-12-24 10:00:00', 1),
('Jennifer Lee', 'Female', 'jennifer.l@email.com', 'Singapore', 39, 'Balcony', 'B401', 2, 1, 3, 'MasterCard', '5487654321098765', 4200.00, 'Pacific Pearl', 'Asian Wonders', '2025-12-25 12:20:00', 3),
('William Brown', 'Male', 'william.b@email.com', 'Norway', 44, 'Balcony', 'B302', 2, 0, 2, 'Visa', '4523456789012345', 3600.00, 'Serendip Star', 'Norwegian Fjords', '2025-12-26 16:30:00', 2),

-- Ocean View bookings (medium value)
('Amanda Taylor', 'Female', 'amanda.t@email.com', 'Canada', 33, 'Ocean View', 'OV301', 2, 0, 2, 'MasterCard', '5434567890123456', 2400.00, 'Ocean Majesty', 'Caribbean Paradise', '2025-12-27 09:45:00', 1),
('Christopher White', 'Male', 'chris.w@email.com', 'UK', 37, 'Ocean View', 'OV401', 2, 0, 2, 'Visa', '4556789012345678', 2600.00, 'Serendip Star', 'Mediterranean Dream', '2025-12-28 14:15:00', 2),
('Michelle Garcia', 'Female', 'michelle.g@email.com', 'Mexico', 40, 'Ocean View', 'OV302', 2, 1, 3, 'Amex', '374567890123456', 3300.00, 'Ocean Majesty', 'Alaska Adventure', '2025-12-29 11:30:00', 1),
('Daniel Rodriguez', 'Male', 'daniel.r@email.com', 'Brazil', 36, 'Ocean View', 'OV501', 2, 0, 2, 'MasterCard', '5456789012345678', 2800.00, 'Pacific Pearl', 'Asian Wonders', '2025-12-30 13:00:00', 3),
('Jessica Miller', 'Female', 'jessica.m@email.com', 'Denmark', 43, 'Ocean View', 'OV402', 2, 2, 4, 'Visa', '4567890123456789', 3200.00, 'Serendip Star', 'Norwegian Fjords', '2026-01-02 10:20:00', 2),

-- Interior bookings (lower value, higher volume)
('Matthew Davis', 'Male', 'matthew.d@email.com', 'USA', 29, 'Interior', 'I401', 2, 0, 2, 'Visa', '4578901234567890', 1800.00, 'Ocean Majesty', 'Caribbean Paradise', '2026-01-03 15:45:00', 1),
('Ashley Wilson', 'Female', 'ashley.w@email.com', 'Ireland', 31, 'Interior', 'I501', 2, 0, 2, 'MasterCard', '5467890123456789', 1900.00, 'Serendip Star', 'Mediterranean Dream', '2026-01-04 12:30:00', 2),
('James Moore', 'Male', 'james.m@email.com', 'USA', 34, 'Interior', 'I402', 2, 0, 2, 'Amex', '375678901234567', 2000.00, 'Ocean Majesty', 'Alaska Adventure', '2026-01-05 09:00:00', 1),
('Emily Jackson', 'Female', 'emily.j@email.com', 'Japan', 28, 'Interior', 'I601', 2, 1, 3, 'Visa', '4589012345678901', 2400.00, 'Pacific Pearl', 'Asian Wonders', '2026-01-06 14:45:00', 3),
('Joshua Thomas', 'Male', 'joshua.t@email.com', 'Sweden', 32, 'Interior', 'I502', 2, 0, 2, 'MasterCard', '5478901234567890', 1850.00, 'Serendip Star', 'Norwegian Fjords', '2026-01-07 11:15:00', 2),
('Stephanie Martin', 'Female', 'stephanie.m@email.com', 'France', 30, 'Interior', 'I403', 2, 2, 4, 'Visa', '4590123456789012', 2800.00, 'Ocean Majesty', 'Caribbean Paradise', '2026-01-08 16:00:00', 1),

-- Additional bookings for better trends (December 2025 - February 2026)
('Brandon Lee', 'Male', 'brandon.l@email.com', 'South Korea', 46, 'Suite', 'S103', 2, 0, 2, 'Amex', '376789012345678', 4800.00, 'Ocean Majesty', 'Caribbean Paradise', '2026-01-10 10:30:00', 1),
('Nicole Harris', 'Female', 'nicole.h@email.com', 'USA', 37, 'Balcony', 'B303', 2, 1, 3, 'Visa', '4501234567890123', 3900.00, 'Serendip Star', 'Mediterranean Dream', '2026-01-15 13:45:00', 2),
('Kevin Clark', 'Male', 'kevin.c@email.com', 'Canada', 39, 'Ocean View', 'OV303', 2, 0, 2, 'MasterCard', '5489012345678901', 2700.00, 'Ocean Majesty', 'Alaska Adventure', '2026-01-20 09:30:00', 1),
('Rachel Lewis', 'Female', 'rachel.l@email.com', 'New Zealand', 35, 'Interior', 'I602', 2, 0, 2, 'Visa', '4512345678901234', 2100.00, 'Pacific Pearl', 'Asian Wonders', '2026-01-25 15:20:00', 3),
('Justin Walker', 'Male', 'justin.w@email.com', 'Finland', 50, 'Suite', 'S202', 2, 0, 2, 'Amex', '377890123456789', 5500.00, 'Serendip Star', 'Norwegian Fjords', '2026-02-01 11:00:00', 2);

-- 2. Insert facility preferences (services revenue)
-- Note: Facility preferences use different structure with JSON data
-- Skipping facility_preferences for now - can be added manually via UI

-- 3. Update cabin_type_pricing table if needed
-- Skipping cabin_type_pricing - using existing data

-- 4. Make sure facilities table has prices
-- Skipping - using existing facility data

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================
-- Run these to verify data was inserted correctly:

-- Check total bookings and revenue
SELECT 
    COUNT(*) as total_bookings,
    SUM(total_price) as total_revenue,
    AVG(total_price) as avg_booking_value
FROM booking_overview;

-- Check revenue by cabin type
SELECT 
    room_type,
    COUNT(*) as bookings,
    SUM(total_price) as revenue
FROM booking_overview
GROUP BY room_type;

-- Check services revenue
SELECT 
    selected_facilities,
    COUNT(*) as bookings,
    SUM(total_cost) as total_revenue
FROM facility_preferences
WHERE payment_status = 'paid'
GROUP BY selected_facilities
LIMIT 10;

-- Check monthly trends
SELECT 
    DATE_FORMAT(booking_date, '%b %Y') as month,
    COUNT(*) as bookings,
    SUM(total_price) as revenue
FROM booking_overview
GROUP BY DATE_FORMAT(booking_date, '%Y-%m')
ORDER BY DATE_FORMAT(booking_date, '%Y-%m') ASC;
