# Gym Management System (GMS)

A comprehensive gym management system built with Laravel, featuring multi-tenancy support, role-based access control, and a complete API for web and mobile applications.

## Features

- **Multi-Tenancy**: Support for multiple gyms with isolated data
- **User Management**: Members, Trainers, and Staff management
- **Membership Plans**: Flexible membership plan management
- **Class & Booking System**: Gym class scheduling and booking
- **Attendance Tracking**: Check-in/check-out system
- **Payment Management**: Payment processing and tracking
- **Workout & Diet Plans**: Plan creation and assignment
- **Notifications**: Real-time notifications system
- **Events Management**: Event creation and RSVP
- **Reports**: Comprehensive reporting system
- **Bulk SMS**: SMS notification system
- **Salary Management**: Staff salary and payroll management
- **RESTful API**: Complete API for mobile app integration

## Technology Stack

- **Backend**: Laravel 10
- **Database**: MySQL
- **Authentication**: Laravel Sanctum (API), Session (Web)
- **Multi-Tenancy**: Stancl/Tenancy
- **Frontend**: Bootstrap 4, AdminLTE 3, jQuery

## Requirements

- PHP >= 8.1
- MySQL >= 5.7
- Composer
- Node.js & NPM (for assets)

## Installation

1. Clone the repository:
```bash
git clone git@github.com:jiwanbayalkoti/gms.git
cd gms
```

2. Install dependencies:
```bash
composer install
npm install
```

3. Configure environment:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure database in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gym_management
DB_USERNAME=root
DB_PASSWORD=
```

5. Run migrations:
```bash
php artisan migrate
php artisan db:seed
```

6. Create storage link:
```bash
php artisan storage:link
```

7. Start development server:
```bash
php artisan serve
```

## API Documentation

The system includes a complete RESTful API for mobile app integration.

**Base URL**: `http://your-domain.com/api/v1`

**Authentication**: Bearer Token (Sanctum)

See API endpoints documentation for details.

## License

Proprietary - All rights reserved

## Author

Jiwan Bayalkoti
