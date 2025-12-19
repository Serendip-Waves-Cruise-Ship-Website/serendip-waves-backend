-- Serendip Waves Database Schema

-- Create database
CREATE DATABASE IF NOT EXISTS serendip_waves;
USE serendip_waves;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff', 'user') DEFAULT 'user',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ships table
CREATE TABLE IF NOT EXISTS ships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    capacity INT NOT NULL,
    image_url VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cabins table
CREATE TABLE IF NOT EXISTS cabins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ship_id INT NOT NULL,
    cabin_number VARCHAR(20) NOT NULL,
    type ENUM('interior', 'oceanview', 'balcony', 'suite') NOT NULL,
    capacity INT NOT NULL,
    price_per_night DECIMAL(10, 2) NOT NULL,
    description TEXT,
    is_available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ship_id) REFERENCES ships(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cabin (ship_id, cabin_number),
    INDEX idx_ship_id (ship_id),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Itineraries table
CREATE TABLE IF NOT EXISTS itineraries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ship_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    departure_port VARCHAR(100) NOT NULL,
    arrival_port VARCHAR(100) NOT NULL,
    departure_date DATE NOT NULL,
    arrival_date DATE NOT NULL,
    duration_days INT NOT NULL,
    price_per_person DECIMAL(10, 2) NOT NULL,
    available_slots INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ship_id) REFERENCES ships(id) ON DELETE CASCADE,
    INDEX idx_ship_id (ship_id),
    INDEX idx_departure_date (departure_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Passengers table
CREATE TABLE IF NOT EXISTS passengers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    passport_number VARCHAR(50),
    nationality VARCHAR(50),
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    itinerary_id INT NOT NULL,
    cabin_id INT NOT NULL,
    booking_reference VARCHAR(20) UNIQUE NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    number_of_passengers INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    special_requests TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (itinerary_id) REFERENCES itineraries(id) ON DELETE CASCADE,
    FOREIGN KEY (cabin_id) REFERENCES cabins(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_booking_reference (booking_reference),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Booking passengers junction table
CREATE TABLE IF NOT EXISTS booking_passengers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    passenger_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (passenger_id) REFERENCES passengers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_booking_passenger (booking_id, passenger_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Meals table
CREATE TABLE IF NOT EXISTS meals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category ENUM('breakfast', 'lunch', 'dinner', 'snack', 'beverage') NOT NULL,
    price DECIMAL(10, 2),
    image_url VARCHAR(255),
    is_available TINYINT(1) DEFAULT 1,
    dietary_info VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Facilities table
CREATE TABLE IF NOT EXISTS facilities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ship_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category ENUM('entertainment', 'dining', 'fitness', 'spa', 'pool', 'other') NOT NULL,
    image_url VARCHAR(255),
    is_available TINYINT(1) DEFAULT 1,
    operating_hours VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ship_id) REFERENCES ships(id) ON DELETE CASCADE,
    INDEX idx_ship_id (ship_id),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Meal preferences table
CREATE TABLE IF NOT EXISTS meal_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    passenger_id INT NOT NULL,
    meal_id INT NOT NULL,
    booking_id INT NOT NULL,
    meal_date DATE NOT NULL,
    meal_time ENUM('breakfast', 'lunch', 'dinner') NOT NULL,
    quantity INT DEFAULT 1,
    special_instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (passenger_id) REFERENCES passengers(id) ON DELETE CASCADE,
    FOREIGN KEY (meal_id) REFERENCES meals(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, role, is_active) VALUES 
('admin', 'admin@serendipwaves.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);
