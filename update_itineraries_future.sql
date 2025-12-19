-- =====================================================
-- UPDATE ITINERARIES WITH FUTURE DATES & ADD NEW ROUTES
-- =====================================================
-- Current date: December 17, 2025
-- All cruises updated to 2026 sailing dates

-- Update existing itineraries to future dates (already done via command line)
-- Adding more itinerary options for booking variety

-- Insert additional future sailings for existing ships
INSERT INTO itineraries (ship_id, ship_name, route, departure_port, start_date, end_date, notes, price) VALUES

-- Mediterranean Dream - Multiple sailings with unique routes
(6, 'Mediterranean Dream', 'Aegean Islands', 'Colombo', '2026-05-15', '2026-05-25', 'Spring sailing through the Aegean Sea with stops in Santorini, Mykonos, and Athens', '349.00'),
(6, 'Mediterranean Dream', 'Greek Classics', 'Galle', '2026-07-10', '2026-07-20', 'Summer cruise featuring Greek island hopping and ancient historical sites', '399.00'),

-- Glacier Adventure - Alaska sailings
(3, 'Glacier Adventure', 'Alaska Wilderness', 'Trincomalee', '2026-06-20', '2026-06-29', 'Summer glacier viewing with wildlife watching opportunities', '699.00'),
(3, 'Glacier Adventure', 'Inside Passage', 'Colombo', '2026-08-15', '2026-08-24', 'Late summer Alaska cruise through the Inside Passage', '649.00'),

-- Northern Lights Explorer - Norway sailings  
(8, 'Northern Lights Explorer', 'Arctic Circle', 'Trincomalee', '2026-03-05', '2026-03-16', 'Northern Lights viewing cruise through Norwegian fjords', '449.00'),
(8, 'Northern Lights Explorer', 'Norwegian Fjords', 'Galle', '2026-09-10', '2026-09-21', 'Autumn fjord cruise with stunning fall colors', '399.00'),

-- Tropical Paradise - Caribbean sailings
(12, 'Tropical Paradise', 'Eastern Caribbean', 'Colombo', '2026-01-25', '2026-02-02', 'Winter escape to warm Caribbean islands', '549.00'),
(12, 'Tropical Paradise', 'Western Caribbean', 'Galle', '2026-04-18', '2026-04-26', 'Spring break Caribbean adventure', '599.00'),
(12, 'Tropical Paradise', 'Southern Caribbean', 'Colombo', '2026-07-05', '2026-07-13', 'Summer family vacation cruise', '649.00'),

-- Cherry Blossom Voyage - Japan sailings
(2, 'Cherry Blossom Voyage', 'Sakura Season', 'Galle', '2026-03-20', '2026-03-30', 'Peak cherry blossom season in Japan', '899.00'),
(2, 'Cherry Blossom Voyage', 'Tokyo & Kyoto', 'Colombo', '2026-10-05', '2026-10-15', 'Autumn colors and cultural immersion', '349.00'),

-- Great Barrier Reef - Australia sailings
(4, 'Great Barrier Reef', 'Coral Sea', 'Colombo', '2026-02-20', '2026-03-06', 'Summer diving and snorkeling adventure', '799.00'),
(4, 'Great Barrier Reef', 'Queensland Coast', 'Galle', '2026-08-08', '2026-08-22', 'Winter escape to Australian waters', '749.00'),

-- Roman Holiday - Italy sailings
(10, 'Roman Holiday', 'Italian Riviera', 'Galle', '2026-05-20', '2026-06-01', 'Italian Riviera and Mediterranean art tour', '899.00'),
(10, 'Roman Holiday', 'Amalfi Coast', 'Colombo', '2026-09-15', '2026-09-27', 'Autumn harvest cruise through Italian coast', '849.00'),

-- Iberian Explorer - Spain sailings
(5, 'Iberian Explorer', 'Costa del Sol', 'Colombo', '2026-04-25', '2026-05-05', 'Spanish coastal adventure with tapas and culture', '449.00'),
(5, 'Iberian Explorer', 'Barcelona & Beyond', 'Galle', '2026-08-20', '2026-08-30', 'Summer fiesta cruise along Spanish shores', '499.00'),

-- Nile Majesty - Egypt sailings
(7, 'Nile Majesty', 'Pyramids & Temples', 'Colombo', '2026-03-15', '2026-03-25', 'Ancient wonders cruise with pyramid tours', '699.00'),
(7, 'Nile Majesty', 'Valley of Kings', 'Galle', '2026-11-05', '2026-11-15', 'Fall exploration of Egyptian treasures', '649.00'),

-- Parisian Dream - France sailings
(9, 'Parisian Dream', 'French Riviera', 'Colombo', '2026-06-01', '2026-06-10', 'French Riviera romance cruise', '899.00'),
(9, 'Parisian Dream', 'Provence & Côte d\'Azur', 'Galle', '2026-10-12', '2026-10-21', 'Wine harvest tour of French coast', '849.00'),

-- Amazon Adventure - Brazil sailings
(1, 'Amazon Adventure', 'Rainforest Expedition', 'Galle', '2026-05-10', '2026-05-24', 'Amazon rainforest expedition cruise', '599.00'),
(1, 'Amazon Adventure', 'Wildlife Adventure', 'Colombo', '2026-09-05', '2026-09-19', 'Wildlife and nature adventure in the Amazon', '549.00');

-- =====================================================
-- VERIFICATION QUERY
-- =====================================================
SELECT 
    ship_name,
    route,
    departure_port,
    start_date,
    end_date,
    DATEDIFF(start_date, CURDATE()) as days_until_departure,
    price
FROM itineraries 
WHERE start_date >= CURDATE()
ORDER BY start_date ASC;
