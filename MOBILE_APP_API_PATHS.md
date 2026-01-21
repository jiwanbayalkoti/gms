# Mobile App API Paths - Gym Management System

## 🔗 API Base URL

### Development (Local):
```
http://127.0.0.1:8000/api/v1
```

### Production:
```
https://your-tenant-domain.com/api/v1
```

**Example:**
```
https://gym1.yourdomain.com/api/v1
```

---

## 📱 Complete API Endpoints

### Authentication
```
POST   /api/v1/login              - Login and get token
POST   /api/v1/logout             - Logout and revoke token
GET    /api/v1/user               - Get current user
```

### Dashboard
```
GET    /api/v1/dashboard          - Get dashboard data (role-based)
```

### Profile
```
GET    /api/v1/profile            - Get user profile
GET    /api/v1/profile/edit       - Get profile edit data
PUT    /api/v1/profile            - Update profile
```

### User Management
```
GET    /api/v1/user-management?category=members|trainers|staff
```

### Members
```
GET    /api/v1/members            - List members
POST   /api/v1/members            - Create member
GET    /api/v1/members/{id}       - Get member
PUT    /api/v1/members/{id}       - Update member
DELETE /api/v1/members/{id}       - Delete member
POST   /api/v1/members/{id}/status - Update member status
```

### Trainers
```
GET    /api/v1/trainers           - List trainers
POST   /api/v1/trainers           - Create trainer
GET    /api/v1/trainers/{id}      - Get trainer
PUT    /api/v1/trainers/{id}      - Update trainer
DELETE /api/v1/trainers/{id}      - Delete trainer
POST   /api/v1/trainers/{id}/status - Update trainer status
```

### Staff
```
GET    /api/v1/staff              - List staff
POST   /api/v1/staff              - Create staff
GET    /api/v1/staff/{id}         - Get staff
PUT    /api/v1/staff/{id}         - Update staff
DELETE /api/v1/staff/{id}         - Delete staff
POST   /api/v1/staff/{id}/status  - Update staff status
```

### Membership Plans
```
GET    /api/v1/membership-plans   - List plans
POST   /api/v1/membership-plans   - Create plan
GET    /api/v1/membership-plans/{id} - Get plan
PUT    /api/v1/membership-plans/{id} - Update plan
DELETE /api/v1/membership-plans/{id} - Delete plan
POST   /api/v1/membership-plans/{id}/status - Update status
```

### Workout Plans
```
GET    /api/v1/workout-plans      - List workout plans
POST   /api/v1/workout-plans     - Create workout plan
GET    /api/v1/workout-plans/{id} - Get workout plan
PUT    /api/v1/workout-plans/{id} - Update workout plan
DELETE /api/v1/workout-plans/{id} - Delete workout plan
GET    /api/v1/workout-plans/{id}/assign/{member?} - Get assign form
POST   /api/v1/workout-plans/{id}/assign - Assign to member
```

### Diet Plans
```
GET    /api/v1/diet-plans        - List diet plans
POST   /api/v1/diet-plans        - Create diet plan
GET    /api/v1/diet-plans/{id}   - Get diet plan
PUT    /api/v1/diet-plans/{id}   - Update diet plan
DELETE /api/v1/diet-plans/{id}   - Delete diet plan
GET    /api/v1/diet-plans/{id}/assign/{member?} - Get assign form
POST   /api/v1/diet-plans/{id}/assign - Assign to member
```

### Classes
```
GET    /api/v1/classes           - List classes
POST   /api/v1/classes           - Create class
GET    /api/v1/classes/{id}      - Get class
PUT    /api/v1/classes/{id}      - Update class
DELETE /api/v1/classes/{id}      - Delete class
POST   /api/v1/classes/{id}/status - Update class status
```

### Bookings
```
GET    /api/v1/bookings          - List bookings
POST   /api/v1/bookings          - Create booking
GET    /api/v1/bookings/{id}     - Get booking
PUT    /api/v1/bookings/{id}     - Update booking
DELETE /api/v1/bookings/{id}     - Delete booking
POST   /api/v1/bookings/{id}/approve - Approve booking
POST   /api/v1/bookings/{id}/reject - Reject booking
POST   /api/v1/bookings/{id}/status - Update booking status
GET    /api/v1/bookings/member/{member_id} - Get member bookings
```

### Attendance
```
GET    /api/v1/attendances       - List attendance records
POST   /api/v1/attendances       - Create attendance record
GET    /api/v1/attendances/{id}  - Get attendance record
PUT    /api/v1/attendances/{id}  - Update attendance record
DELETE /api/v1/attendances/{id}  - Delete attendance record
GET    /api/v1/check-in          - Get check-in form
POST   /api/v1/check-in          - Check in
POST   /api/v1/check-out/{id}    - Check out
```

### Payments
```
GET    /api/v1/payments          - List payments
POST   /api/v1/payments          - Create payment
GET    /api/v1/payments/{id}    - Get payment
PUT    /api/v1/payments/{id}    - Update payment
DELETE /api/v1/payments/{id}    - Delete payment
GET    /api/v1/payments/{id}/invoice - Get payment invoice
GET    /api/v1/payments/member/{member_id} - Get member payments
POST   /api/v1/payments/process/stripe - Process Stripe payment
```

### Notifications
```
GET    /api/v1/notifications     - List notifications
POST   /api/v1/notifications    - Create notification
GET    /api/v1/notifications/{id} - Get notification
PUT    /api/v1/notifications/{id} - Update notification
DELETE /api/v1/notifications/{id} - Delete notification
GET    /api/v1/my-notifications - Get my notifications
GET    /api/v1/notifications/urgent/list - Get urgent notifications
POST   /api/v1/notifications/{id}/read - Mark as read
POST   /api/v1/notifications/read-all - Mark all as read
POST   /api/v1/notifications/{id}/publish - Publish notification
POST   /api/v1/notifications/{id}/unpublish - Unpublish notification
```

### Events
```
GET    /api/v1/events            - List events
POST   /api/v1/events            - Create event
GET    /api/v1/events/{id}       - Get event
PUT    /api/v1/events/{id}       - Update event
DELETE /api/v1/events/{id}       - Delete event
POST   /api/v1/events/{id}/response - Update event response
POST   /api/v1/events/{id}/publish - Publish event
```

### Pause Requests
```
GET    /api/v1/pause-requests    - List pause requests
POST   /api/v1/pause-requests    - Create pause request
GET    /api/v1/pause-requests/{id} - Get pause request
POST   /api/v1/pause-requests/{id}/approve - Approve request
POST   /api/v1/pause-requests/{id}/reject - Reject request
```

### Salaries
```
GET    /api/v1/salaries          - List salaries
POST   /api/v1/salaries          - Create salary
GET    /api/v1/salaries/{id}     - Get salary
PUT    /api/v1/salaries/{id}     - Update salary
DELETE /api/v1/salaries/{id}     - Delete salary
POST   /api/v1/salaries/{id}/toggle-status - Toggle salary status
```

### Salary Payments
```
GET    /api/v1/salary-payments  - List salary payments
GET    /api/v1/salary-payments/generate - Get generate form
GET    /api/v1/salary-payments/get-next-period/{salary_id} - Get next period
POST   /api/v1/salary-payments/generate - Generate payroll
POST   /api/v1/salary-payments  - Create manual payment
GET    /api/v1/salary-payments/{id} - Get payment
GET    /api/v1/salary-payments/{id}/payslip - Get payslip
POST   /api/v1/salary-payments/{id}/mark-paid - Mark as paid
POST   /api/v1/salary-payments/{id}/update-status - Update status
```

### Settings
```
GET    /api/v1/settings          - Get settings
PUT    /api/v1/settings          - Update settings
```

### Reports
```
GET    /api/v1/reports/attendance?start_date=&end_date= - Attendance report
GET    /api/v1/reports/classes?start_date=&end_date= - Classes report
GET    /api/v1/reports/payments?start_date=&end_date= - Payments report
GET    /api/v1/reports/members - Members report
```

### Bulk SMS
```
GET    /api/v1/bulk-sms          - Get SMS page data
POST   /api/v1/bulk-sms/send     - Send SMS
GET    /api/v1/bulk-sms/statistics?date= - Get SMS statistics
```

---

## 🔐 Authentication

### Login Flow:
```http
POST /api/v1/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "token": "1|abc123def456...",
    "token_type": "Bearer",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com",
            "role": "Admin",
            "gym_id": 1
        }
    }
}
```

### Using Token:
```http
Authorization: Bearer 1|abc123def456...
```

---

## 📝 Example API Calls

### Get Dashboard:
```http
GET http://127.0.0.1:8000/api/v1/dashboard
Authorization: Bearer YOUR_TOKEN
```

### Get Members:
```http
GET http://127.0.0.1:8000/api/v1/members
Authorization: Bearer YOUR_TOKEN
```

### Create Member:
```http
POST http://127.0.0.1:8000/api/v1/members
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "phone": "1234567890"
}
```

---

## 🌐 Multi-Tenancy

**Important:** API must be accessed via tenant domain:

✅ **Correct:**
```
https://gym1.yourdomain.com/api/v1/login
```

❌ **Wrong:**
```
https://yourdomain.com/api/v1/login  (Central domain)
```

Each gym needs its own subdomain for API access.

---

## 📱 Mobile App Configuration

### Flutter Example:
```dart
class ApiConfig {
  static const String baseUrl = 'http://127.0.0.1:8000/api/v1';
  // Production: 'https://gym1.yourdomain.com/api/v1'
}
```

### React Native Example:
```javascript
const API_BASE_URL = 'http://127.0.0.1:8000/api/v1';
// Production: 'https://gym1.yourdomain.com/api/v1'
```

---

## ✅ Summary

**Base URL:** `/api/v1`  
**Total Endpoints:** 100+  
**Authentication:** Bearer Token (Sanctum)  
**Format:** JSON  
**Status:** ✅ Production Ready

---

**All API endpoints are ready for mobile app integration!**

