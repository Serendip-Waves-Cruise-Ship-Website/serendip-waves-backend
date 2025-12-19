# Serendip Waves Backend API

PHP/MySQL RESTful API for cruise management system. Handles bookings, passengers, inventory, preferences, and notifications.

## Features

- **Session-based Authentication**: Secure user authentication with session management
- **RESTful API**: JSON-based API endpoints for all operations
- **Database Management**: Comprehensive MySQL schema for cruise operations
- **Email Notifications**: PHPMailer integration for booking confirmations
- **File Uploads**: Support for ship, meal, and facility images
- **Role-based Access Control**: Admin, staff, and user roles

## Directory Structure

```
serendip-waves-backend/
├── backend/                  # API endpoints
│   ├── login.php
│   ├── logout.php
│   ├── register.php
│   ├── ships.php
│   ├── cabins.php
│   ├── itineraries.php
│   ├── meals.php
│   ├── facilities.php
│   ├── passengers.php
│   ├── bookings.php
│   └── upload.php
├── Main Classes/            # Core classes
│   ├── DbConnector.php     # Database connection
│   ├── User.php            # User authentication
│   ├── CabinManager.php    # Cabin management
│   └── EmailService.php    # Email handling
├── ship_images/            # Ship image uploads
├── meal_images/            # Meal image uploads
├── facility_images/        # Facility image uploads
├── config.php              # Configuration file
├── database.sql            # Database schema
├── composer.json           # Dependencies
└── index.php               # API index

```

## Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/Serendip-Waves-Cruise-Ship-Website/serendip-waves-backend.git
   cd serendip-waves-backend
   ```

2. **Install dependencies**:
   ```bash
   composer install
   ```

3. **Configure database**:
   - Update database credentials in `config.php`
   - Import the database schema:
     ```bash
     mysql -u root -p < database.sql
     ```

4. **Configure email** (optional):
   - Update SMTP settings in `config.php`

5. **Set permissions**:
   ```bash
   chmod 755 ship_images meal_images facility_images
   ```

## Database Schema

The system uses the following tables:
- `users` - User accounts and authentication
- `ships` - Cruise ships information
- `cabins` - Ship cabins inventory
- `itineraries` - Cruise itineraries
- `passengers` - Passenger information
- `bookings` - Booking records
- `booking_passengers` - Booking-passenger relationships
- `meals` - Meal options
- `facilities` - Ship facilities
- `meal_preferences` - Passenger meal preferences

## API Endpoints

### Authentication

**Login**
```
POST /backend/login.php
Content-Type: application/json

{
  "username": "admin",
  "password": "admin123"
}
```

**Register**
```
POST /backend/register.php
Content-Type: application/json

{
  "username": "newuser",
  "email": "user@example.com",
  "password": "password123"
}
```

**Logout**
```
POST /backend/logout.php
```

### Ships

**Get all ships**
```
GET /backend/ships.php
GET /backend/ships.php?active=1
```

**Get ship by ID**
```
GET /backend/ships.php?id=1
```

**Create ship** (admin/staff only)
```
POST /backend/ships.php
Content-Type: application/json

{
  "name": "Ocean Majesty",
  "description": "Luxury cruise ship",
  "capacity": 2000,
  "image_url": "/ship_images/ocean_majesty.jpg",
  "is_active": 1
}
```

### Cabins

**Get all cabins**
```
GET /backend/cabins.php
GET /backend/cabins.php?ship_id=1
GET /backend/cabins.php?type=suite
```

**Check availability**
```
POST /backend/cabins.php
Content-Type: application/json

{
  "action": "check_availability",
  "cabin_id": 1,
  "start_date": "2025-06-01",
  "end_date": "2025-06-07"
}
```

### Bookings

**Create booking**
```
POST /backend/bookings.php
Content-Type: application/json

{
  "itinerary_id": 1,
  "cabin_id": 5,
  "check_in_date": "2025-06-01",
  "check_out_date": "2025-06-07",
  "number_of_passengers": 2,
  "total_amount": 1500.00,
  "passenger_ids": [1, 2],
  "special_requests": "Anniversary celebration"
}
```

### File Upload

**Upload image**
```
POST /backend/upload.php?type=ship
Content-Type: multipart/form-data

file: [binary image data]
```

## Response Format

All endpoints return JSON responses in the following format:

**Success:**
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

**Error:**
```json
{
  "success": false,
  "message": "Error description"
}
```

## Authentication

The API uses session-based authentication. After logging in, the session is stored in PHP sessions and can be accessed from the client via sessionStorage or localStorage.

Session timeout is set to 1 hour (configurable in `config.php`).

## Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention using prepared statements
- File upload validation (type and size)
- Role-based access control
- CORS headers configuration
- Session timeout handling

## Default User

The database schema includes a default admin user:
- Username: `admin`
- Password: `admin123`
- Role: `admin`

**Important:** Change the default password in production!

## Development

To run the API locally, you need:
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer

## License

MIT
