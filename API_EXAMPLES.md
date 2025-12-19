# API Usage Examples

This document provides practical examples for using the Serendip Waves Backend API.

## Base URL
```
http://localhost/serendip-waves-backend
```

## Authentication Flow

### 1. Register a New User
```bash
curl -X POST http://localhost/serendip-waves-backend/backend/register.php \
  -H "Content-Type: application/json" \
  -d '{
    "username": "johndoe",
    "email": "john@example.com",
    "password": "securepass123"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "user_id": 2
}
```

### 2. Login
```bash
curl -X POST http://localhost/serendip-waves-backend/backend/login.php \
  -H "Content-Type: application/json" \
  -c cookies.txt \
  -d '{
    "username": "johndoe",
    "password": "securepass123"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "user": {
    "id": 2,
    "username": "johndoe",
    "email": "john@example.com",
    "role": "user"
  }
}
```

## Ships Management

### Get All Ships
```bash
curl -X GET http://localhost/serendip-waves-backend/backend/ships.php \
  -b cookies.txt
```

### Create a Ship (Admin Only)
```bash
curl -X POST http://localhost/serendip-waves-backend/backend/ships.php \
  -H "Content-Type: application/json" \
  -b cookies.txt \
  -d '{
    "name": "Ocean Majesty",
    "description": "Luxury cruise ship with world-class amenities",
    "capacity": 2000,
    "image_url": "/ship_images/ocean_majesty.jpg",
    "is_active": 1
  }'
```

## Bookings

### Create a Booking
```bash
curl -X POST http://localhost/serendip-waves-backend/backend/bookings.php \
  -H "Content-Type: application/json" \
  -b cookies.txt \
  -d '{
    "itinerary_id": 1,
    "cabin_id": 5,
    "check_in_date": "2025-06-01",
    "check_out_date": "2025-06-07",
    "number_of_passengers": 2,
    "total_amount": 1500.00,
    "passenger_ids": [1, 2],
    "special_requests": "Anniversary celebration"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Booking created successfully",
  "booking_id": 1,
  "booking_reference": "SW202506011A2B3C"
}
```

### Get My Bookings
```bash
curl -X GET http://localhost/serendip-waves-backend/backend/bookings.php \
  -b cookies.txt
```

## File Upload

### Upload Ship Image
```bash
curl -X POST "http://localhost/serendip-waves-backend/backend/upload.php?type=ship" \
  -b cookies.txt \
  -F "file=@/path/to/image.jpg"
```

**Response:**
```json
{
  "success": true,
  "message": "File uploaded successfully",
  "filename": "abc123_1234567890.jpg",
  "url": "/ship_images/abc123_1234567890.jpg"
}
```

## Error Handling

All endpoints return errors in a consistent format:

```json
{
  "success": false,
  "message": "Error description here"
}
```

Common HTTP status codes:
- `200` - Success
- `201` - Created
- `400` - Bad Request (invalid input)
- `401` - Unauthorized (not logged in)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `405` - Method Not Allowed
- `500` - Internal Server Error

## Session Management

Sessions are maintained using PHP sessions. After login, the session cookie is automatically sent with subsequent requests. The session expires after 1 hour of inactivity (configurable in config.php).

## Notes

- All endpoints require authentication except `/backend/login.php` and `/backend/register.php`
- Some endpoints require admin or staff role for write operations
- All dates should be in YYYY-MM-DD format
- File uploads are limited to 5MB and must be JPEG or PNG images
