# QPAY - Point of Sale System

QPAY adalah aplikasi Point of Sale (POS) yang modern dan fleksibel, dibangun dengan Laravel 11 dan Livewire v3. Sistem ini dirancang untuk mengelola penjualan, inventori, dan transaksi dengan antarmuka yang intuitif dan user-friendly.

## 🌟 Features

### Core Features
- ✅ **User Management** - Registration, login, profile management
- ✅ **Product Catalog** - Manage products dengan kategori
- ✅ **Inventory Management** - Track stock dan availability
- ✅ **Shopping Cart** - Add to cart, update quantity, remove items
- ✅ **Checkout System** - Guest checkout tanpa akun
- ✅ **Order Management** - View order history dan status
- ✅ **Payment Processing** - Multiple payment methods
- ✅ **Barcode/QR Code** - Product identification
- ✅ **Reporting** - Sales reports dan analytics

### Advanced Features
- 🔒 **Security** - CSRF protection, password hashing, secure sessions
- 📱 **Responsive Design** - Mobile-friendly interface dengan Tailwind CSS
- 🎨 **Modern UI** - Beautiful components dengan DaisyUI
- ⚡ **Real-time Updates** - Interactive components dengan Livewire
- 🧪 **Comprehensive Testing** - Unit dan feature tests

## 🛠️ Tech Stack

| Component | Technology | Version |
|-----------|-----------|---------|
| Framework | Laravel | 11 |
| Language | PHP | 8.4.14+ |
| Database | MySQL/MariaDB | 5.7+ |
| CSS Framework | Tailwind CSS | 3.x |
| UI Components | DaisyUI | 4.x |
| Interactivity | Livewire | v3 |
| Frontend Build | Vite | Latest |
| Testing | PHPUnit/Pest | Latest |

## 📋 Requirements

- PHP 8.4+
- Composer
- Node.js 16+
- MySQL/MariaDB 5.7+
- Git

## 🚀 Installation

### 1. Clone Repository
```bash
git clone https://github.com/rizqyyourin/qpay.git
cd qpay
```

### 2. Install Dependencies
```bash
# PHP dependencies
composer install

# Node dependencies
npm install
```

### 3. Environment Setup
```bash
# Copy env file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database di .env
# DB_DATABASE=qpay_db
# DB_USERNAME=root
# DB_PASSWORD=password
```

### 4. Database Setup
```bash
# Run migrations
php artisan migrate

# (Optional) Seed database dengan sample data
php artisan db:seed
```

### 5. Build Assets
```bash
# Development
npm run dev

# Production
npm run build
```

### 6. Start Server
```bash
# Development server
php artisan serve

# Aplikasi akan running di http://localhost:8000
```

## 📁 Project Structure

```
qpay/
├── app/
│   ├── Http/Controllers/        # Request handlers
│   ├── Livewire/               # Livewire components
│   ├── Models/                 # Eloquent models
│   ├── Services/               # Business logic
│   └── Providers/              # Service providers
├── resources/
│   ├── views/                  # Blade templates
│   ├── js/                     # JavaScript
│   └── css/                    # Stylesheets
├── routes/
│   ├── web.php                 # Web routes
│   └── api.php                 # API routes
├── database/
│   ├── migrations/             # Database schemas
│   ├── factories/              # Model factories
│   └── seeders/                # Database seeders
├── tests/
│   ├── Feature/                # Feature tests
│   └── Unit/                   # Unit tests
├── docs/
│   ├── ARCHITECTURE_BACKEND.md    # Backend architecture
│   ├── ARCHITECTURE_FRONTEND.md   # Frontend architecture
│   ├── ARCHITECTURE_DATABASE.md   # Database schema
│   ├── PRODUCTION_DEPLOYMENT.md   # Production setup
│   └── SECURITY_CHECKLIST.md      # Security guide
└── config/
    └── ...                     # Configuration files
```

## 📚 Documentation

Dokumentasi lengkap tersedia di folder `docs/`:

- **[ARCHITECTURE_SUMMARY.md](docs/ARCHITECTURE_SUMMARY.md)** - Ringkasan arsitektur keseluruhan
- **[ARCHITECTURE_BACKEND.md](docs/ARCHITECTURE_BACKEND.md)** - Backend architecture & API
- **[ARCHITECTURE_FRONTEND.md](docs/ARCHITECTURE_FRONTEND.md)** - Frontend & UI components
- **[ARCHITECTURE_DATABASE.md](docs/ARCHITECTURE_DATABASE.md)** - Database schema & relationships
- **[PRODUCTION_DEPLOYMENT.md](docs/PRODUCTION_DEPLOYMENT.md)** - Production setup dengan HTTPS
- **[SECURITY_CHECKLIST.md](docs/SECURITY_CHECKLIST.md)** - Security best practices

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ProfilePageTest.php

# Run with coverage
php artisan test --coverage

# Run unit tests only
php artisan test tests/Unit

# Run feature tests only
php artisan test tests/Feature
```

### Test Coverage
- ✅ 19+ comprehensive tests untuk profile management
- ✅ API integration tests
- ✅ Cart management tests
- ✅ Checkout flow tests
- ✅ Payment processing tests
- ✅ Product browsing tests

## 🔐 Security Features

- ✅ **CSRF Protection** - Token validation pada semua form
- ✅ **Password Security** - Bcrypt hashing dengan BCRYPT_ROUNDS=12
- ✅ **Session Security** - HttpOnly, Secure, dan SameSite cookies
- ✅ **Input Validation** - Validation rules pada semua inputs
- ✅ **SQL Injection Prevention** - Eloquent ORM dengan parameterized queries
- ✅ **XSS Protection** - Blade template escaping
- ✅ **HTTPS** - SSL/TLS configuration untuk production
- ✅ **Rate Limiting** - Brute-force protection

## 🚢 Deployment

### Production Deployment
1. Setup SSL certificate (Let's Encrypt recommended)
2. Configure web server (Nginx atau Apache)
3. Update `.env` dengan production settings
4. Run deployment script: `./docs/deploy.sh`
5. Verify dengan SSL Labs test

Untuk detail lengkap, lihat [PRODUCTION_DEPLOYMENT.md](docs/PRODUCTION_DEPLOYMENT.md)

### Deployment Domain
```
Production URL: https://qpay.yourin.my.id
Protocol: HTTPS (TLS 1.2+)
```

## 📞 API Endpoints

### Authentication
```
POST   /login              - User login
POST   /register           - User registration
POST   /logout             - User logout
```

### Profile Management
```
GET    /profile            - View profile form
POST   /profile/update     - Update profile
POST   /profile/password   - Change password
POST   /profile/delete     - Delete account
```

### Products
```
GET    /api/products       - List all products
GET    /api/products/{id}  - Get product details
POST   /api/products       - Create product (admin)
```

### Orders
```
GET    /api/orders         - List user orders
POST   /api/orders         - Create order
GET    /api/orders/{id}    - Get order details
```

Untuk dokumentasi API lengkap, lihat `docs/ARCHITECTURE_BACKEND.md`

## 🎯 Key Routes

### Web Routes (Authenticated)
- `/dashboard` - Dashboard utama
- `/profile` - Profil user management
- `/products` - Katalog produk
- `/orders` - Riwayat pesanan
- `/pos` - POS terminal
- `/reports` - Sales reports

### Guest Routes
- `/` - Home page
- `/shop` - Product shop
- `/shop/product/{id}` - Product detail
- `/shop/guest-cart` - Guest shopping cart
- `/login` - Login page
- `/register` - Register page

## 📊 Database Schema

### Core Tables
- `users` - User accounts
- `products` - Product catalog
- `categories` - Product categories
- `orders` - Customer orders
- `order_items` - Order line items
- `payments` - Payment records
- `carts` - Shopping carts

Untuk ERD dan schema details, lihat [ARCHITECTURE_DATABASE.md](docs/ARCHITECTURE_DATABASE.md)

## 🔄 Development Workflow

```bash
# Create feature branch
git checkout -b feature/new-feature

# Make changes dan commit
git add .
git commit -m "Add new feature"

# Push ke GitHub
git push origin feature/new-feature

# Create Pull Request di GitHub
# Review → Merge ke main
```

## 📝 Configuration

### Important Environment Variables
```env
APP_ENV=production           # Set ke 'production' untuk production
APP_DEBUG=false             # Selalu false di production
APP_URL=https://qpay.yourin.my.id

SESSION_SECURE_COOKIES=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict

LOG_LEVEL=warning           # debug di development, warning di production
CACHE_STORE=redis          # Untuk better performance
```

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error**
```bash
# Check .env database configuration
# Ensure database server is running
# Run migrations: php artisan migrate
```

**Asset Not Loading**
```bash
# Rebuild assets
npm run build

# Clear Laravel cache
php artisan cache:clear
php artisan view:clear
```

**Permission Denied on Storage**
```bash
# Fix storage permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## 📄 License

This project is licensed under the MIT License - see LICENSE file for details.

## 👤 Author

**Rizqy Yourin**
- GitHub: [@rizqyyourin](https://github.com/rizqyyourin)
- Email: rizqyyourin6@gmail.com

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📞 Support

Untuk support atau questions, silakan:
1. Check [documentation](docs/)
2. Create issue di GitHub
3. Email ke rizqyyourin6@gmail.com

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - Web framework
- [Livewire](https://livewire.laravel.com) - Interactive components
- [DaisyUI](https://daisyui.com) - UI components
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS
- Community untuk inspiration dan support

---

**Status**: ✅ Production Ready

**Last Updated**: December 2025

**Repository**: https://github.com/rizqyyourin/qpay
