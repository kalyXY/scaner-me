# 📡 API Documentation

## Base URL
```
http://localhost:8080/api
```

## Authentication
Currently, the API does not require authentication. This will be implemented in future versions.

## Response Format

All API responses follow a consistent JSON format:

### Success Response
```json
{
    "ok": true,
    "timestamp": "2024-01-15T10:30:00Z",
    "data": {
        // Response data here
    }
}
```

### Error Response
```json
{
    "ok": false,
    "error": "Error message",
    "timestamp": "2024-01-15T10:30:00Z",
    "errors": {
        // Validation errors (optional)
    }
}
```

## Endpoints

### 🏥 System Health

#### GET /api/health
Check system health status.

**Response:**
```json
{
    "ok": true,
    "status": "healthy",
    "timestamp": "2024-01-15T10:30:00Z",
    "checks": {
        "database": {
            "status": "ok",
            "message": "Database connection successful"
        },
        "filesystem": {
            "status": "ok",
            "message": "All directories accessible"
        },
        "php_extensions": {
            "status": "ok",
            "message": "All required extensions loaded"
        }
    },
    "version": "2.0.0"
}
```

### 📱 Attendance Management

#### POST /api/scan
Record student attendance via QR code scan.

**Request Body:**
```json
{
    "uuid": "00000000-0000-0000-0000-000000000001"
}
```

**Response:**
```json
{
    "ok": true,
    "timestamp": "2024-01-15T10:30:00Z",
    "data": {
        "student": {
            "id": 1,
            "uuid": "00000000-0000-0000-0000-000000000001",
            "name": "Alice Dupont"
        },
        "session": {
            "id": 5,
            "course": "Mathématiques",
            "date": "2024-01-15",
            "start_time": "10:00:00",
            "is_exam": false
        },
        "attendance": {
            "status": "present",
            "scanned_at": "2024-01-15T10:30:00Z"
        },
        "exam": null
    }
}
```

**Error Responses:**
- `400` - Invalid or missing UUID
- `404` - Student not found or no active session
- `422` - Validation errors

#### GET /api/sessions
Get sessions for a specific date.

**Query Parameters:**
- `date` (optional): Date in YYYY-MM-DD format. Defaults to today.

**Example:** `/api/sessions?date=2024-01-15`

**Response:**
```json
{
    "ok": true,
    "timestamp": "2024-01-15T10:30:00Z",
    "data": {
        "sessions": [
            {
                "id": 5,
                "course_name": "Mathématiques",
                "class_name": "3A",
                "session_date": "2024-01-15",
                "start_time": "10:00:00",
                "end_time": "12:00:00",
                "is_exam": 0,
                "enrolled_count": 25,
                "present_count": 20,
                "late_count": 3,
                "scanned_count": 23
            }
        ]
    }
}
```

#### GET /api/attendance/session
Get attendance records for a specific session.

**Query Parameters:**
- `session_id` (required): Session ID

**Example:** `/api/attendance/session?session_id=5`

**Response:**
```json
{
    "ok": true,
    "timestamp": "2024-01-15T10:30:00Z",
    "data": {
        "attendance": [
            {
                "uuid": "00000000-0000-0000-0000-000000000001",
                "first_name": "Alice",
                "last_name": "Dupont",
                "course_name": "Mathématiques",
                "session_date": "2024-01-15",
                "start_time": "10:00:00",
                "status": "present",
                "scanned_at": "2024-01-15T10:05:00"
            }
        ]
    }
}
```

#### GET /api/attendance/date
Get all attendance records for a specific date.

**Query Parameters:**
- `date` (optional): Date in YYYY-MM-DD format. Defaults to today.

**Example:** `/api/attendance/date?date=2024-01-15`

**Response:** Same format as `/api/attendance/session`

### 📊 Dashboard Data

#### GET /api/dashboard
Get dashboard data including sessions, payments, and exam authorizations.

**Query Parameters:**
- `date` (optional): Date in YYYY-MM-DD format. Defaults to today.

**Response:**
```json
{
    "ok": true,
    "timestamp": "2024-01-15T10:30:00Z",
    "data": {
        "date": "2024-01-15",
        "sessions": [...],
        "payments": [
            {
                "id": 1,
                "first_name": "Alice",
                "last_name": "Dupont",
                "type": "exam",
                "amount": "50.00",
                "currency": "EUR",
                "status": "paid",
                "paid_at": "2024-01-15T09:00:00"
            }
        ],
        "authorizations": [
            {
                "id": 1,
                "first_name": "Alice",
                "last_name": "Dupont",
                "exam_name": "Examen Final Mathématiques",
                "allowed": 1,
                "allowed_at": "2024-01-15T09:00:00"
            }
        ]
    }
}
```

### 📄 Data Export

#### GET /api/export/csv
Export attendance data as CSV.

**Query Parameters:**
- `date` (optional): Date in YYYY-MM-DD format
- `session_id` (optional): Specific session ID

**Example:** `/api/export/csv?date=2024-01-15`

**Response:** CSV file download with headers:
```
Student UUID,First Name,Last Name,Course,Date,Start Time,Status,Scanned At
```

## HTTP Status Codes

- `200` - Success
- `400` - Bad Request (invalid parameters)
- `401` - Unauthorized (future use)
- `404` - Not Found
- `422` - Unprocessable Entity (validation errors)
- `500` - Internal Server Error
- `503` - Service Unavailable (health check failed)

## Rate Limiting

Currently not implemented. Will be added in future versions.

## Examples with cURL

### Record Attendance
```bash
curl -X POST http://localhost:8080/api/scan \
  -H "Content-Type: application/json" \
  -d '{"uuid": "00000000-0000-0000-0000-000000000001"}'
```

### Get Today's Sessions
```bash
curl http://localhost:8080/api/sessions
```

### Get Specific Date Sessions
```bash
curl http://localhost:8080/api/sessions?date=2024-01-15
```

### Check System Health
```bash
curl http://localhost:8080/api/health
```

### Export CSV
```bash
curl -o attendance.csv http://localhost:8080/api/export/csv?date=2024-01-15
```

## WebSocket Support

Not currently implemented. May be added in future versions for real-time updates.

## Versioning

Current version: **2.0.0**

The API follows semantic versioning. Breaking changes will increment the major version number.

## Support

For API support:
1. Check the application logs: `make logs`
2. Verify system health: `curl http://localhost:8080/api/health`
3. Consult this documentation