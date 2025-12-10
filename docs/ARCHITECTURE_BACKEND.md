# Backend Architecture - QPAY

## Overview
QPAY adalah aplikasi POS (Point of Sale) yang dibangun dengan Laravel 11, menggunakan arsitektur MVC dengan Livewire v3 untuk komponen interaktif.

## Technology Stack
- **Framework**: Laravel 11
- **Language**: PHP 8.4.14
- **Database**: MySQL/MariaDB
- **ORM**: Eloquent
- **Frontend Integration**: Livewire v3
- **Authentication**: Laravel Sanctum & Session-based

## Directory Structure

```
app/
├── Console/              # Artisan commands
├── Facades/             # Custom facades
├── Http/
│   ├── Controllers/     # Route handlers
│   │   ├── ProfileController.php
│   │   ├── AuthenticatedSessionController.php
│   │   └── ...
│   └── Middleware/      # Custom middleware
├── Livewire/           # Livewire components
├── Models/             # Eloquent models
│   ├── User.php
│   ├── Product.php
│   ├── Order.php
│   └── ...
├── Providers/          # Service providers
└── Services/           # Business logic services

database/
├── migrations/         # Database schema
├── factories/         # Model factories for testing
└── seeders/          # Database seeders
```

## Core Models

### User Model
- Handles user authentication and profile management
- Relations: Orders, Profile

**Key Methods**:
- `edit()` - Display profile form
- `update()` - Update user profile (name, email, phone)
- `updatePassword()` - Change user password
- `destroy()` - Delete user account

### Product Model
- Manages product inventory
- Fields: name, description, price, stock, category

### Order Model
- Tracks customer orders and transactions
- Relations: OrderItems, Customer

### OrderItem Model
- Individual items within an order
- Relations: Order, Product

## API Routes (routes/api.php)
```php
// Protected routes with Sanctum middleware
Route::middleware('auth:sanctum')->group(function () {
    // API endpoints for frontend consumption
});
```

## Web Routes (routes/web.php)
```php
// Authentication routes
Route::middleware('guest')->group(function () {
    // Login, register, password reset
});

// Protected routes
Route::middleware('auth')->group(function () {
    // Dashboard, Profile, Orders, Products
    
    // Profile management
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::post('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/password', [ProfileController::class, 'updatePassword'])->name('password');
        Route::post('/delete', [ProfileController::class, 'destroy'])->name('destroy');
    });
});
```

## ProfileController

**Purpose**: Manage user account and profile operations

**Methods**:

1. **edit(Request $request): View**
   - Display profile edit form
   - Pass user data to view

2. **update(Request $request): RedirectResponse**
   - Validate: name, email (unique), phone
   - Update user record
   - Reset email_verified_at if email changes
   - Return success redirect

3. **updatePassword(Request $request): RedirectResponse**
   - Verify current password
   - Validate new password confirmation
   - Hash and update password
   - Return success redirect

4. **destroy(Request $request): RedirectResponse**
   - Verify password confirmation
   - Delete user record
   - Logout user
   - Redirect to home

## Validation Rules

### Profile Update
```php
'name' => 'required|string|max:255',
'email' => 'required|email|unique:users,email,' . $user->id,
'phone' => 'nullable|string|max:20'
```

### Password Update
```php
'current_password' => 'required|current_password',
'new_password' => 'required|string|confirmed|min:8|strong_password',
'new_password_confirmation' => 'required|string'
```

### Account Deletion
```php
'password' => 'required|current_password'
```

## Authentication Flow

1. User logs in with email/password
2. Session is created via `AuthenticatedSessionController`
3. User can access protected routes with `auth` middleware
4. Session destroyed on logout

## Database Migrations
- Create users table
- Create products table
- Create orders table
- Create order_items table
- Create additional tables for business logic

## Error Handling
- Validation errors returned to form with error messages
- 404 errors for non-existent resources
- 403 Forbidden for unauthorized access
- CSRF protection on all POST requests

## Security Features
- CSRF token protection (@csrf in forms)
- Password hashing with bcrypt
- Current password verification for sensitive operations
- Email unique constraint
- SQL injection protection via Eloquent ORM
- Authorization checks via middleware
