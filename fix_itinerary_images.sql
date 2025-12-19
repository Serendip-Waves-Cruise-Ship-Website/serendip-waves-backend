-- Fix NULL country_image values for new itineraries
-- Using existing images based on destination country

-- Greek destinations
UPDATE itineraries SET country_image = 'country_images/6869e2b93cd24_Greece.jpg' 
WHERE route IN ('Aegean Islands', 'Greek Classics');

-- Alaska destinations
UPDATE itineraries SET country_image = 'country_images/6869e7b2363ae_Alaska.jpg' 
WHERE route IN ('Alaska Wilderness', 'Inside Passage');

-- Norway destinations
UPDATE itineraries SET country_image = 'country_images/6869e6af9b0ce_norway.jpg' 
WHERE route IN ('Arctic Circle', 'Norwegian Fjords');

-- Caribbean destinations
UPDATE itineraries SET country_image = 'country_images/6869e76c9845e_careebean (2).jpg' 
WHERE route IN ('Eastern Caribbean', 'Western Caribbean', 'Southern Caribbean');

-- Japan destinations
UPDATE itineraries SET country_image = 'country_images/6869e7f52286a_japan.jpg' 
WHERE route IN ('Sakura Season', 'Tokyo & Kyoto');

-- Australia destinations
UPDATE itineraries SET country_image = 'country_images/6869e832c1c71_Australia.jpg' 
WHERE route IN ('Coral Sea', 'Queensland Coast');

-- Italy destinations
UPDATE itineraries SET country_image = 'country_images/686e16e06a9b2.jpg' 
WHERE route IN ('Italian Riviera', 'Amalfi Coast');

-- Spain destinations
UPDATE itineraries SET country_image = 'country_images/686e180a62e9d.png' 
WHERE route IN ('Costa del Sol', 'Barcelona & Beyond');

-- Egypt destinations
UPDATE itineraries SET country_image = 'country_images/686e18a46147a.jpg' 
WHERE route IN ('Pyramids & Temples', 'Valley of Kings');

-- France destinations
UPDATE itineraries SET country_image = 'country_images/686e18d65e756.jpg' 
WHERE route IN ('French Riviera', 'Provence & Côte d''Azur');

-- Brazil destinations
UPDATE itineraries SET country_image = 'country_images/686e19470a687.jpg' 
WHERE route IN ('Rainforest Expedition', 'Wildlife Adventure');

-- Verify update
SELECT route, country_image FROM itineraries WHERE country_image IS NOT NULL ORDER BY route;
