# 📱 Gym Management System - Mobile App API Documentation

## 🔗 Base URL

### Development:
```
http://127.0.0.1:8000/api/v1
```

### Production:
```
https://gms.jbtech.com.np/api/v1
```

**Note:** Each gym has its own subdomain. Use the appropriate tenant domain.

---

## 🔐 Authentication

All API endpoints (except login) require authentication using Bearer Token.

### Headers Required:
```http
Authorization: Bearer YOUR_TOKEN_HERE
Accept: application/json
Content-Type: application/json
```

### Login Endpoint

**POST** `/api/v1/login`

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Login successful",
  "token": "1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yz",
  "token_type": "Bearer",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "role": "GymAdmin",
      "gym_id": 1,
      "gym": {
        "id": 1,
        "name": "Fitness Center",
        "status": "active"
      },
      "permissions": ["members.view", "members.create", ...]
    }
  }
}
```

**Error Response (401 Unauthorized):**
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

### Logout Endpoint

**POST** `/api/v1/logout`

**Headers:**
```http
Authorization: Bearer YOUR_TOKEN
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### Get Current User

**GET** `/api/v1/user`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": "GymAdmin",
    "gym_id": 1
  }
}
```

---

## 👥 Members API

### List Members

**GET** `/api/v1/members`

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 15, max: 100)
- `search` (optional): Search in name, email, or phone
- `status` (optional): Filter by status (`active` or `inactive`)

**Example:**
```
GET /api/v1/members?page=1&per_page=15&search=john&status=active
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "members": [
      {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "1234567890",
        "profile_photo": "http://example.com/storage/profile-photos/member_123.jpg",
        "status": "active",
        "active": true,
        "membership_plan": {
          "id": 1,
          "name": "Premium Plan",
          "price": 5000.0,
          "duration_days": 30
        },
        "membership_status": "active",
        "membership_expiry_date": "2024-02-01",
        "gym_id": 1,
        "created_at": "2024-01-01 10:00:00",
        "updated_at": "2024-01-15 14:30:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total": 100,
      "per_page": 15,
      "last_page": 7,
      "has_more_pages": true,
      "from": 1,
      "to": 15
    }
  }
}
```

### Get Single Member

**GET** `/api/v1/members/{id}`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "member": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "1234567890",
      "profile_photo": "http://example.com/storage/profile-photos/member_123.jpg",
      "status": "active",
      "active": true,
      "membership_plan": {
        "id": 1,
        "name": "Premium Plan",
        "price": 5000.0,
        "duration_days": 30
      },
      "membership_status": "active",
      "membership_expiry_date": "2024-02-01",
      "gym_id": 1,
      "created_at": "2024-01-01 10:00:00",
      "updated_at": "2024-01-15 14:30:00"
    }
  }
}
```

**Error Response (404 Not Found):**
```json
{
  "success": false,
  "message": "Member not found"
}
```

### Get Member for Editing

**GET** `/api/v1/members/{id}/edit`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "member": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "1234567890",
      "status": "active",
      ...
    },
    "editable_fields": [
      "name",
      "email",
      "phone",
      "password",
      "profile_photo",
      "active"
    ]
  }
}
```

### Create Member

**POST** `/api/v1/members`

**Request Body:**
```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "9876543210",
  "active": true
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Member created successfully",
  "data": {
    "member": {
      "id": 2,
      "name": "Jane Doe",
      "email": "jane@example.com",
      "phone": "9876543210",
      "status": "active",
      ...
    }
  }
}
```

**Error Response (422 Validation Error):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Update Member

**PUT** `/api/v1/members/{id}`

**Request Body (all fields optional):**
```json
{
  "name": "Updated Name",
  "email": "updated@example.com",
  "phone": "9999999999",
  "password": "newpassword123",
  "password_confirmation": "newpassword123",
  "active": true
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Member updated successfully",
  "data": {
    "member": {
      "id": 1,
      "name": "Updated Name",
      "email": "updated@example.com",
      ...
    }
  }
}
```

### Delete Member

**DELETE** `/api/v1/members/{id}`

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Member deleted successfully"
}
```

### Update Member Status

**POST** `/api/v1/members/{id}/status`

**Request Body:**
```json
{
  "active": false
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Member status updated successfully",
  "data": {
    "member": {
      "id": 1,
      "status": "inactive",
      "active": false,
      ...
    }
  }
}
```

---

## 🏋️ Trainers API

### List Trainers

**GET** `/api/v1/trainers`

**Query Parameters:**
- `page` (optional): Page number
- `per_page` (optional): Items per page
- `search` (optional): Search in name, email, or phone
- `status` (optional): Filter by status (`active` or `inactive`)

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "trainers": [
      {
        "id": 1,
        "name": "Trainer Name",
        "email": "trainer@example.com",
        "phone": "1234567890",
        "status": "active",
        "gym_id": 1,
        ...
      }
    ]
  }
}
```

### Get Single Trainer

**GET** `/api/v1/trainers/{id}`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "trainer": {
      "id": 1,
      "name": "Trainer Name",
      "email": "trainer@example.com",
      ...
    }
  }
}
```

### Create Trainer

**POST** `/api/v1/trainers`

**Request Body:**
```json
{
  "name": "New Trainer",
  "email": "newtrainer@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "1234567890",
  "active": true
}
```

### Update Trainer

**PUT** `/api/v1/trainers/{id}`

### Delete Trainer

**DELETE** `/api/v1/trainers/{id}`

### Update Trainer Status

**POST** `/api/v1/trainers/{id}/status`

**Request Body:**
```json
{
  "active": false
}
```

---

## 👔 Staff API

### List Staff

**GET** `/api/v1/staff`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "staff": [
      {
        "id": 1,
        "name": "Staff Name",
        "email": "staff@example.com",
        "phone": "1234567890",
        "status": "active",
        "staff_type": "Receptionist",
        "gym_id": 1,
        ...
      }
    ]
  }
}
```

### Get Single Staff

**GET** `/api/v1/staff/{id}`

### Create Staff

**POST** `/api/v1/staff`

**Request Body:**
```json
{
  "name": "New Staff",
  "email": "newstaff@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "1234567890",
  "staff_type": "Receptionist",
  "marital_status": "single",
  "active": true
}
```

### Update Staff

**PUT** `/api/v1/staff/{id}`

### Delete Staff

**DELETE** `/api/v1/staff/{id}`

### Update Staff Status

**POST** `/api/v1/staff/{id}/status`

---

## 💳 Membership Plans API

### List Membership Plans

**GET** `/api/v1/membership-plans`

**Query Parameters:**
- `is_active` (optional): Filter by active status (true/false)
- `search` (optional): Search in name or description

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "membership_plans": [
      {
        "id": 1,
        "name": "Premium Plan",
        "description": "Premium membership plan",
        "price": 5000.0,
        "duration_days": 30,
        "features": ["Gym Access", "Class Booking", "3 Classes/Week"],
        "status": "active",
        "is_active": true,
        "allows_class_booking": true,
        "allowed_bookings_per_week": 3,
        "has_discount": false,
        "discounted_price": 5000.0,
        "is_discount_active": false,
        "created_at": "2024-01-01 10:00:00",
        "updated_at": "2024-01-15 14:30:00"
      }
    ]
  }
}
```

### Get Single Membership Plan

**GET** `/api/v1/membership-plans/{id}`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "membership_plan": {
      "id": 1,
      "name": "Premium Plan",
      "description": "Premium membership plan",
      "price": 5000.0,
      "duration_days": 30,
      "features": ["Gym Access", "Class Booking", "3 Classes/Week"],
      "status": "active",
      ...
    }
  }
}
```

### Create Membership Plan

**POST** `/api/v1/membership-plans`

**Request Body:**
```json
{
  "name": "Basic Plan",
  "description": "Basic membership plan",
  "price": 3000,
  "duration_days": 30,
  "is_active": true,
  "allows_class_booking": true,
  "allowed_bookings_per_week": 2,
  "has_discount": false
}
```

### Update Membership Plan

**PUT** `/api/v1/membership-plans/{id}`

### Delete Membership Plan

**DELETE** `/api/v1/membership-plans/{id}`

### Update Membership Plan Status

**POST** `/api/v1/membership-plans/{id}/status`

**Request Body:**
```json
{
  "is_active": false
}
```

---

## 📊 Dashboard API

### Get Dashboard Data

**GET** `/api/v1/dashboard`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "stats": {
      "totalMembers": 150,
      "totalTrainers": 10,
      "totalClasses": 25,
      "todayBookings": 5,
      "todayAttendance": 20,
      "monthlyRevenue": 50000
    },
    "recentActivities": [...],
    "upcomingClasses": [...],
    "pendingBookings": [...]
  }
}
```

**Note:** Dashboard data varies based on user role (Admin, Trainer, Member).

---

## 📋 Profile API

### Get Profile

**GET** `/api/v1/profile`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "1234567890",
      "role": "GymAdmin",
      "gym_id": 1,
      ...
    }
  }
}
```

### Get Profile Edit Data

**GET** `/api/v1/profile/edit`

### Update Profile

**PUT** `/api/v1/profile`

**Request Body:**
```json
{
  "name": "Updated Name",
  "phone": "9999999999",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

---

## 🏃 Workout Plans API

### List Workout Plans

**GET** `/api/v1/workout-plans`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "workout_plans": [
      {
        "id": 1,
        "name": "Strength Training",
        "description": "Strength training plan",
        "trainer_id": 1,
        "member_id": null,
        "is_default": true,
        ...
      }
    ]
  }
}
```

### Get Single Workout Plan

**GET** `/api/v1/workout-plans/{id}`

### Create Workout Plan

**POST** `/api/v1/workout-plans`

### Update Workout Plan

**PUT** `/api/v1/workout-plans/{id}`

### Delete Workout Plan

**DELETE** `/api/v1/workout-plans/{id}`

### Assign Workout Plan to Member

**POST** `/api/v1/workout-plans/{id}/assign`

**Request Body:**
```json
{
  "member_id": 1
}
```

---

## 🥗 Diet Plans API

### List Diet Plans

**GET** `/api/v1/diet-plans`

### Get Single Diet Plan

**GET** `/api/v1/diet-plans/{id}`

### Create Diet Plan

**POST** `/api/v1/diet-plans`

### Update Diet Plan

**PUT** `/api/v1/diet-plans/{id}`

### Delete Diet Plan

**DELETE** `/api/v1/diet-plans/{id}`

### Assign Diet Plan to Member

**POST** `/api/v1/diet-plans/{id}/assign`

---

## 🏋️ Classes API

### List Classes

**GET** `/api/v1/classes`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "classes": [
      {
        "id": 1,
        "name": "Yoga Class",
        "description": "Morning yoga session",
        "trainer_id": 1,
        "capacity": 20,
        "current_bookings": 15,
        "start_time": "2024-01-20 08:00:00",
        "end_time": "2024-01-20 09:00:00",
        "status": "Active",
        ...
      }
    ]
  }
}
```

### Get Single Class

**GET** `/api/v1/classes/{id}`

### Create Class

**POST** `/api/v1/classes`

**Request Body:**
```json
{
  "name": "New Class",
  "description": "Class description",
  "trainer_id": 1,
  "capacity": 20,
  "start_time": "2024-01-20 08:00:00",
  "end_time": "2024-01-20 09:00:00",
  "location": "Main Hall",
  "status": "Active"
}
```

### Update Class

**PUT** `/api/v1/classes/{id}`

### Delete Class

**DELETE** `/api/v1/classes/{id}`

### Update Class Status

**POST** `/api/v1/classes/{id}/status`

---

## 📅 Bookings API

### List Bookings

**GET** `/api/v1/bookings`

### Get Single Booking

**GET** `/api/v1/bookings/{id}`

### Create Booking

**POST** `/api/v1/bookings`

**Request Body:**
```json
{
  "member_id": 1,
  "class_id": 1,
  "booking_date": "2024-01-20"
}
```

### Update Booking

**PUT** `/api/v1/bookings/{id}`

### Delete Booking

**DELETE** `/api/v1/bookings/{id}`

### Approve Booking

**POST** `/api/v1/bookings/{id}/approve`

### Reject Booking

**POST** `/api/v1/bookings/{id}/reject`

### Get Member Bookings

**GET** `/api/v1/bookings/member/{member_id}`

---

## ✅ Attendance API

### List Attendance Records

**GET** `/api/v1/attendances`

### Get Single Attendance

**GET** `/api/v1/attendances/{id}`

### Create Attendance (Check-in)

**POST** `/api/v1/check-in`

**Request Body:**
```json
{
  "member_id": 1,
  "class_id": null
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Checked in successfully",
  "data": {
    "attendance": {
      "id": 1,
      "member_id": 1,
      "check_in_time": "2024-01-20 08:00:00",
      ...
    }
  }
}
```

### Check Out

**POST** `/api/v1/check-out/{attendance_id}`

---

## 💰 Payments API

### List Payments

**GET** `/api/v1/payments`

### Get Single Payment

**GET** `/api/v1/payments/{id}`

### Create Payment

**POST** `/api/v1/payments`

**Request Body:**
```json
{
  "member_id": 1,
  "membership_plan_id": 1,
  "amount": 5000,
  "payment_method": "Cash",
  "payment_date": "2024-01-20",
  "notes": "Monthly payment"
}
```

### Update Payment

**PUT** `/api/v1/payments/{id}`

### Delete Payment

**DELETE** `/api/v1/payments/{id}`

### Get Payment Invoice

**GET** `/api/v1/payments/{id}/invoice`

### Get Member Payments

**GET** `/api/v1/payments/member/{member_id}`

---

## 🔔 Notifications API

### List Notifications

**GET** `/api/v1/notifications`

### Get Single Notification

**GET** `/api/v1/notifications/{id}`

### Create Notification

**POST** `/api/v1/notifications`

### Get My Notifications

**GET** `/api/v1/my-notifications`

### Mark Notification as Read

**POST** `/api/v1/notifications/{id}/read`

### Mark All as Read

**POST** `/api/v1/notifications/read-all`

---

## 📅 Events API

### List Events

**GET** `/api/v1/events`

### Get Single Event

**GET** `/api/v1/events/{id}`

### Create Event

**POST** `/api/v1/events`

### Update Event

**PUT** `/api/v1/events/{id}`

### Update Event Response

**POST** `/api/v1/events/{id}/response`

**Request Body:**
```json
{
  "response": "Attending"
}
```

**Response options:** `Attending`, `Not Attending`, `Not Sure`

---

## ⏸️ Pause Requests API

### List Pause Requests

**GET** `/api/v1/pause-requests`

### Get Single Pause Request

**GET** `/api/v1/pause-requests/{id}`

### Create Pause Request

**POST** `/api/v1/pause-requests`

### Approve Pause Request

**POST** `/api/v1/pause-requests/{id}/approve`

### Reject Pause Request

**POST** `/api/v1/pause-requests/{id}/reject`

---

## 💼 Salaries API

### List Salaries

**GET** `/api/v1/salaries`

### Get Single Salary

**GET** `/api/v1/salaries/{id}`

### Create Salary

**POST** `/api/v1/salaries`

### Update Salary

**PUT** `/api/v1/salaries/{id}`

### Toggle Salary Status

**POST** `/api/v1/salaries/{id}/toggle-status`

---

## 📊 Reports API

### Attendance Report

**GET** `/api/v1/reports/attendance?start_date=2024-01-01&end_date=2024-01-31`

### Classes Report

**GET** `/api/v1/reports/classes?start_date=2024-01-01&end_date=2024-01-31`

### Payments Report

**GET** `/api/v1/reports/payments?start_date=2024-01-01&end_date=2024-01-31`

### Members Report

**GET** `/api/v1/reports/members`

---

## ⚙️ Settings API

### Get Settings

**GET** `/api/v1/settings`

### Update Settings

**PUT** `/api/v1/settings`

---

## 📱 Error Handling

### Standard Error Response Format

All errors follow this format:

```json
{
  "success": false,
  "message": "Error message here",
  "errors": {
    "field_name": ["Error message for this field"]
  }
}
```

### HTTP Status Codes

- `200 OK` - Request successful
- `201 Created` - Resource created successfully
- `400 Bad Request` - Invalid request
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Permission denied
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation error
- `500 Internal Server Error` - Server error

### Common Error Responses

**401 Unauthorized:**
```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

**403 Forbidden:**
```json
{
  "success": false,
  "message": "You do not have permission to perform this action."
}
```

**404 Not Found:**
```json
{
  "success": false,
  "message": "Resource not found"
}
```

**422 Validation Error:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

---

## 📄 Pagination

Most list endpoints support pagination.

### Pagination Parameters

- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15, max: 100)

### Pagination Response Format

```json
{
  "success": true,
  "data": {
    "items": [...],
    "pagination": {
      "current_page": 1,
      "total": 100,
      "per_page": 15,
      "last_page": 7,
      "has_more_pages": true,
      "from": 1,
      "to": 15
    }
  }
}
```

### Example Pagination Request

```
GET /api/v1/members?page=2&per_page=20
```

---

## 🔍 Search & Filtering

Many endpoints support search and filtering.

### Search Parameters

- `search` - Search in relevant fields (name, email, phone, etc.)

### Filter Parameters

- `status` - Filter by status (`active`, `inactive`)
- `is_active` - Filter by active status (boolean)
- `start_date` - Filter by start date (for reports)
- `end_date` - Filter by end date (for reports)

### Example Search Request

```
GET /api/v1/members?search=john&status=active&page=1&per_page=15
```

---

## 📤 File Uploads

### Profile Photo Upload

When uploading files, use `multipart/form-data` instead of `application/json`.

**Example (Create Member with Photo):**

```http
POST /api/v1/members
Content-Type: multipart/form-data
Authorization: Bearer YOUR_TOKEN

name: John Doe
email: john@example.com
password: password123
password_confirmation: password123
phone: 1234567890
profile_photo: [FILE]
```

**Supported Image Formats:**
- JPEG (.jpg, .jpeg)
- PNG (.png)
- GIF (.gif)

**Max File Size:** 2MB

---

## 🔄 Response Format Standards

### Success Response

All successful responses follow this format:

```json
{
  "success": true,
  "message": "Operation successful message",
  "data": {
    // Response data here
  }
}
```

### List Response

```json
{
  "success": true,
  "data": {
    "resource_name": [...],
    "pagination": {...}
  }
}
```

### Single Resource Response

```json
{
  "success": true,
  "data": {
    "resource_name": {...}
  }
}
```

---

## 📝 Code Examples

### Flutter/Dart Example

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class ApiService {
  static const String baseUrl = 'https://gms.jbtech.com.np/api/v1';
  String? token;

  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      token = data['token'];
      return data;
    } else {
      throw Exception('Login failed');
    }
  }

  Future<Map<String, dynamic>> getMembers({int page = 1, int perPage = 15}) async {
    final response = await http.get(
      Uri.parse('$baseUrl/members?page=$page&per_page=$perPage'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Failed to load members');
    }
  }

  Future<Map<String, dynamic>> createMember(Map<String, dynamic> memberData) async {
    final response = await http.post(
      Uri.parse('$baseUrl/members'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode(memberData),
    );

    if (response.statusCode == 201) {
      return jsonDecode(response.body);
    } else {
      final error = jsonDecode(response.body);
      throw Exception(error['message'] ?? 'Failed to create member');
    }
  }
}
```

### React Native/JavaScript Example

```javascript
const API_BASE_URL = 'https://gms.jbtech.com.np/api/v1';

class ApiService {
  constructor() {
    this.token = null;
  }

  async login(email, password) {
    const response = await fetch(`${API_BASE_URL}/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ email, password }),
    });

    const data = await response.json();
    if (data.success) {
      this.token = data.token;
      return data;
    } else {
      throw new Error(data.message || 'Login failed');
    }
  }

  async getMembers(page = 1, perPage = 15) {
    const response = await fetch(
      `${API_BASE_URL}/members?page=${page}&per_page=${perPage}`,
      {
        headers: {
          'Authorization': `Bearer ${this.token}`,
          'Accept': 'application/json',
        },
      }
    );

    const data = await response.json();
    if (data.success) {
      return data;
    } else {
      throw new Error(data.message || 'Failed to load members');
    }
  }

  async createMember(memberData) {
    const response = await fetch(`${API_BASE_URL}/members`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(memberData),
    });

    const data = await response.json();
    if (data.success) {
      return data;
    } else {
      throw new Error(data.message || 'Failed to create member');
    }
  }
}

export default new ApiService();
```

---

## 🔒 Security Best Practices

1. **Always use HTTPS** in production
2. **Store tokens securely** - Use secure storage (Keychain, Keystore)
3. **Never expose tokens** in logs or error messages
4. **Implement token refresh** if available
5. **Validate all inputs** on client side before sending
6. **Handle errors gracefully** - Show user-friendly messages
7. **Implement request timeout** - Don't wait indefinitely
8. **Cache data appropriately** - Reduce API calls

---

## 📞 Support

For API support or questions:
- Email: support@example.com
- Documentation: https://docs.example.com
- Status Page: https://status.example.com

---

## 📋 Quick Reference

### Most Used Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/login` | POST | Login and get token |
| `/api/v1/members` | GET | List members |
| `/api/v1/members` | POST | Create member |
| `/api/v1/members/{id}` | GET | Get member |
| `/api/v1/members/{id}` | PUT | Update member |
| `/api/v1/members/{id}/edit` | GET | Get member for editing |
| `/api/v1/membership-plans` | GET | List membership plans |
| `/api/v1/dashboard` | GET | Get dashboard data |

---

**Last Updated:** January 2024  
**API Version:** v1  
**Status:** ✅ Production Ready


