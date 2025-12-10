# QPAY Architecture Summary

## System Overview

QPAY adalah aplikasi Point of Sale (POS) yang dirancang untuk mengelola penjualan, inventori, dan pesanan dengan antarmuka yang user-friendly. Sistem dibangun dengan arsitektur MVC modern menggunakan Laravel 11 dan Livewire v3.

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Client Browser                          │
│                   (Blade Templates)                         │
└────────────────────────┬────────────────────────────────────┘
                         │
                    HTTP/POST
                         │
┌────────────────────────▼────────────────────────────────────┐
│              Laravel 11 Web Application                      │
│  ┌────────────────────────────────────────────────────────┐ │
│  │         Routing & Middleware Layer                      │ │
│  │    (auth, CSRF, rate limiting)                         │ │
│  └──────────┬─────────────────────────┬──────────────────┘ │
│             │                         │                    │
│  ┌──────────▼──────────┐  ┌──────────▼──────────┐          │
│  │   Controllers       │  │  Livewire           │          │
│  │  (Business Logic)   │  │  Components         │          │
│  └──────────┬──────────┘  └──────────┬──────────┘          │
│             │                         │                    │
│  ┌──────────▼─────────────────────────▼──────────┐         │
│  │         Services Layer                        │         │
│  │    (Business Logic, Validation)               │         │
│  └──────────┬──────────────────────────┬──────────┘        │
│             │                          │                   │
│  ┌──────────▼──────────┐  ┌───────────▼──────────┐         │
│  │   Models (Eloquent) │  │  Database Queries    │         │
│  │   - User            │  │                      │         │
│  │   - Product         │  │                      │         │
│  │   - Order           │  │                      │         │
│  │   - OrderItem       │  │                      │         │
│  └──────────┬──────────┘  └───────────┬──────────┘        │
│             │                          │                   │
└─────────────┼──────────────────────────┼───────────────────┘
              │                          │
         Database Connection        File System
              │                          │
┌─────────────▼──────────────────────────▼───────────────────┐
│              MySQL/MariaDB + Storage                        │
│  - User Accounts & Authentication                           │
│  - Product Catalog & Inventory                              │
│  - Orders & Transactions                                    │
│  - Order Items & Details                                    │
│  - Application Settings                                     │
└─────────────────────────────────────────────────────────────┘
```

## Core Components

### 1. Authentication Layer
- **Type**: Session-based + Sanctum
- **Entry Point**: Login page (/login)
- **Protection**: Middleware 'auth' on protected routes
- **User Management**: Profile editing and password changes

### 2. Request Flow

#### Example: Update Profile

```
1. User fills profile form on /profile page
2. Clicks "Save Changes" button
3. Modal dialog appears asking confirmation
4. User confirms action
5. Form submits POST request to /profile/update
6. ProfileController::update() validates data
7. User model updated in database
8. Redirect back to /profile with success message
9. View displays updated user information
```

#### Validation Flow
```
Input → Validation Rules → Errors? 
  ├─ YES → Return to form with error messages
  └─ NO  → Process → Update database → Redirect with success
```

### 3. Data Models Relationships

```
User
├─ hasMany: Orders
├─ hasMany: Products
└─ profile: name, email, phone, password

Product
├─ hasMany: OrderItems
├─ belongsTo: Category
└─ fields: name, description, price, stock

Order
├─ belongsTo: User
├─ hasMany: OrderItems
├─ belongsToMany: Products (through OrderItems)
└─ fields: total, status, created_at

OrderItem
├─ belongsTo: Order
├─ belongsTo: Product
└─ fields: quantity, price
```

### 4. Frontend Architecture

**View Hierarchy**:
```
layouts/app.blade.php (Main Layout)
├─ Header (Navigation, User Menu)
├─ Main Content Area
│  └─ Page-specific views
│     ├─ profile/edit.blade.php
│     ├─ dashboard/index.blade.php
│     ├─ products/index.blade.php
│     └─ orders/index.blade.php
└─ Footer
```

**Styling Stack**:
```
Tailwind CSS (Utility Framework)
    ↓
DaisyUI (Component Library)
    ↓
Custom CSS (app.css)
    ↓
Rendered HTML
```

## Key Features

### 1. User Profile Management
- View and edit user information (name, email, phone)
- Change password with current password verification
- Delete account (requires password confirmation)
- Modal-based confirmations for all actions

### 2. Form Validation
- Server-side validation on all inputs
- Clear error messages displayed to user
- Email unique constraint
- Password strength requirements
- Current password verification for sensitive operations

### 3. Security
- CSRF token protection on all forms
- Password hashing with bcrypt
- Session-based authentication
- Authorization middleware
- SQL injection prevention via Eloquent ORM

### 4. User Experience
- Modal confirmation dialogs
- Real-time form validation feedback
- Responsive design (mobile, tablet, desktop)
- Consistent spacing and typography
- Circular avatar for user profile
- Clear visual hierarchy

## Technology Details

| Component | Technology | Version |
|-----------|-----------|---------|
| Framework | Laravel | 11 |
| Language | PHP | 8.4.14 |
| Database | MySQL/MariaDB | 5.7+ |
| CSS Framework | Tailwind CSS | 3.x |
| UI Components | DaisyUI | 4.x |
| Frontend Interactivity | Livewire | v3 |
| Authentication | Laravel Sanctum | Built-in |
| Testing | Laravel PHPUnit | Built-in |

## File Structure Summary

```
qpay/
├── app/
│   ├── Http/Controllers/ProfileController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Order.php
│   │   └── OrderItem.php
│   ├── Services/
│   └── Providers/
├── resources/
│   ├── views/
│   │   ├── layouts/app.blade.php
│   │   ├── profile/edit.blade.php
│   │   ├── dashboard/
│   │   ├── products/
│   │   └── orders/
│   ├── css/app.css
│   └── js/app.js
├── routes/
│   ├── web.php
│   └── api.php
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── tests/
│   ├── Feature/
│   │   └── ProfilePageTest.php
│   └── Unit/
├── config/
├── storage/
├── public/
└── vendor/
```

## API Endpoints

### Web Routes
```
GET  /login              - Login form
POST /login              - Process login
GET  /register           - Register form
POST /register           - Process registration
POST /logout             - Logout user

GET  /dashboard          - Dashboard (auth required)
GET  /profile            - Profile edit form (auth required)
POST /profile/update     - Update profile (auth required)
POST /profile/password   - Update password (auth required)
POST /profile/delete     - Delete account (auth required)
```

### API Routes (Sanctum Protected)
```
GET  /api/user           - Current user info
GET  /api/products       - List products
POST /api/orders         - Create order
```

## Development Workflow

1. **Setup**: Clone repo, `composer install`, `npm install`
2. **Environment**: Copy `.env.example` to `.env`, generate key
3. **Database**: Run migrations with `php artisan migrate`
4. **Development**: Run `php artisan serve` and `npm run dev`
5. **Testing**: Execute `php artisan test` to run test suite

## Performance Considerations

- Query optimization with eager loading
- Caching for frequently accessed data
- Minification of CSS/JS in production
- Database indexing on frequently queried columns
- Livewire lazy loading for large lists

## Security Checklist

- ✅ CSRF protection on all forms
- ✅ Password hashing (bcrypt)
- ✅ Input validation on all endpoints
- ✅ Authorization checks on protected routes
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ Current password verification for sensitive operations
- ✅ Email unique constraint
- ✅ XSS protection via Blade escaping

## Future Enhancements

- API documentation with Swagger/OpenAPI
- Real-time notifications with Pusher
- Advanced reporting and analytics
- Multi-user permissions and roles
- Audit logging for all transactions
- Inventory management system
- Payment gateway integration
