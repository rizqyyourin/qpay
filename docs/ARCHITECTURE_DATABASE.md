# Database Architecture - QPAY

## Overview
Database QPAY didesain dengan pendekatan relasional yang efisien untuk mendukung manajemen pengguna, produk, inventory, dan transaksi penjualan.

## Database Engine
- **System**: MySQL 5.7+ / MariaDB 10.3+
- **Character Set**: utf8mb4
- **Collation**: utf8mb4_unicode_ci
- **Default Port**: 3306

## Entity Relationship Diagram (ERD)

```
┌──────────────┐
│    Users     │
├──────────────┤
│ id (PK)      │
│ name         │
│ email (UQ)   │
│ phone        │
│ password     │
│ created_at   │
│ updated_at   │
└──────────────┘
       │
       │ 1:N
       ├─────────────────────┐
       │                     │
       ▼                     ▼
┌──────────────┐      ┌──────────────┐
│   Orders     │      │  Products    │
├──────────────┤      ├──────────────┤
│ id (PK)      │      │ id (PK)      │
│ user_id (FK) │      │ name         │
│ total        │      │ description  │
│ status       │      │ price        │
│ created_at   │      │ stock        │
│ updated_at   │      │ category_id  │
└──────────────┘      │ created_at   │
       │              │ updated_at   │
       │ 1:N          └──────────────┘
       │                     ▲
       │                     │ N:M (through OrderItems)
       │                     │
       ▼                     │
┌──────────────┐             │
│  OrderItems  │─────────────┘
├──────────────┤
│ id (PK)      │
│ order_id (FK)│
│ product_id   │
│ quantity     │
│ price        │
│ created_at   │
└──────────────┘

┌──────────────┐
│ Categories   │
├──────────────┤
│ id (PK)      │
│ name         │
│ slug         │
│ created_at   │
└──────────────┘
```

## Table Specifications

### 1. users
Menyimpan informasi pengguna dan kredensial autentikasi.

**Columns**:
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PK, Auto Increment | Unique user identifier |
| name | string(255) | NOT NULL | User full name |
| email | string(255) | NOT NULL, UNIQUE | Email address for login |
| email_verified_at | timestamp | NULLABLE | Email verification timestamp |
| phone | string(20) | NULLABLE | User phone number |
| password | string(255) | NOT NULL | Hashed password (bcrypt) |
| remember_token | string(100) | NULLABLE | Remember me token |
| created_at | timestamp | DEFAULT current | Account creation timestamp |
| updated_at | timestamp | DEFAULT current | Last account update |

**Indexes**:
```sql
PRIMARY KEY (id)
UNIQUE KEY (email)
INDEX (created_at)
```

**Key Constraints**:
- Email harus unik di seluruh sistem
- Password di-hash dengan bcrypt
- phone adalah opsional

### 2. products
Menyimpan katalog produk dan informasi inventory.

**Columns**:
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PK, Auto Increment | Unique product ID |
| name | string(255) | NOT NULL | Product name |
| description | text | NULLABLE | Detailed product description |
| price | decimal(10, 2) | NOT NULL | Selling price |
| cost | decimal(10, 2) | NULLABLE | Cost price |
| stock | int unsigned | DEFAULT 0 | Current inventory quantity |
| category_id | bigint unsigned | FK, NOT NULL | Reference to categories |
| sku | string(100) | NULLABLE | Stock keeping unit |
| barcode | string(100) | NULLABLE | Product barcode |
| image | string(255) | NULLABLE | Product image path |
| is_active | boolean | DEFAULT true | Product availability status |
| created_at | timestamp | DEFAULT current | Product creation |
| updated_at | timestamp | DEFAULT current | Last product update |

**Indexes**:
```sql
PRIMARY KEY (id)
FOREIGN KEY (category_id) REFERENCES categories(id)
INDEX (category_id)
INDEX (sku)
INDEX (barcode)
UNIQUE KEY (sku)
```

**Key Constraints**:
- price dan cost > 0
- stock >= 0
- category_id harus ada di tabel categories

### 3. categories
Menyimpan kategori produk.

**Columns**:
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PK, Auto Increment | Unique category ID |
| name | string(255) | NOT NULL | Category name |
| slug | string(255) | NOT NULL, UNIQUE | URL-friendly identifier |
| description | text | NULLABLE | Category description |
| created_at | timestamp | DEFAULT current | Creation timestamp |
| updated_at | timestamp | DEFAULT current | Last update |

**Indexes**:
```sql
PRIMARY KEY (id)
UNIQUE KEY (slug)
```

### 4. orders
Menyimpan informasi pesanan penjualan.

**Columns**:
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PK, Auto Increment | Unique order ID |
| user_id | bigint unsigned | FK, NOT NULL | Reference to users |
| order_number | string(100) | NOT NULL, UNIQUE | Order reference number |
| total | decimal(12, 2) | NOT NULL | Total order amount |
| subtotal | decimal(12, 2) | NOT NULL | Subtotal before tax/discount |
| tax | decimal(12, 2) | DEFAULT 0 | Tax amount |
| discount | decimal(12, 2) | DEFAULT 0 | Discount amount |
| status | enum | DEFAULT 'pending' | Order status |
| payment_status | enum | DEFAULT 'unpaid' | Payment status |
| notes | text | NULLABLE | Additional notes |
| created_at | timestamp | DEFAULT current | Order creation |
| updated_at | timestamp | DEFAULT current | Last update |

**Status Enums**:
- `pending` - Order baru, belum diproses
- `confirmed` - Order dikonfirmasi
- `processing` - Sedang diproses
- `shipped` - Sudah dikirim
- `delivered` - Sudah diterima
- `cancelled` - Dibatalkan
- `refunded` - Dikembalikan

**Payment Status Enums**:
- `unpaid` - Belum dibayar
- `paid` - Sudah dibayar
- `partial` - Pembayaran sebagian
- `refunded` - Dikembalikan

**Indexes**:
```sql
PRIMARY KEY (id)
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
INDEX (user_id)
INDEX (status)
INDEX (payment_status)
UNIQUE KEY (order_number)
```

### 5. order_items
Menyimpan detail item dalam setiap pesanan.

**Columns**:
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PK, Auto Increment | Unique item ID |
| order_id | bigint unsigned | FK, NOT NULL | Reference to orders |
| product_id | bigint unsigned | FK, NOT NULL | Reference to products |
| quantity | int unsigned | NOT NULL | Item quantity |
| price | decimal(10, 2) | NOT NULL | Price per unit at order time |
| subtotal | decimal(12, 2) | NOT NULL | quantity × price |
| created_at | timestamp | DEFAULT current | Creation timestamp |
| updated_at | timestamp | DEFAULT current | Last update |

**Indexes**:
```sql
PRIMARY KEY (id)
FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
FOREIGN KEY (product_id) REFERENCES products(id)
INDEX (order_id)
INDEX (product_id)
```

**Key Constraints**:
- quantity > 0
- price dan subtotal > 0
- Cascade delete: menghapus order akan menghapus semua order_items

### 6. payments
Menyimpan riwayat pembayaran.

**Columns**:
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PK, Auto Increment | Unique payment ID |
| order_id | bigint unsigned | FK, NOT NULL | Reference to orders |
| amount | decimal(12, 2) | NOT NULL | Payment amount |
| payment_method | enum | NOT NULL | Payment method used |
| status | enum | DEFAULT 'pending' | Payment status |
| reference_code | string(100) | NULLABLE | Payment gateway reference |
| notes | text | NULLABLE | Payment notes |
| created_at | timestamp | DEFAULT current | Payment timestamp |
| updated_at | timestamp | DEFAULT current | Last update |

**Payment Method Enums**:
- `cash` - Cash payment
- `debit_card` - Debit card
- `credit_card` - Credit card
- `bank_transfer` - Bank transfer
- `e_wallet` - E-wallet (OVO, GCash, dll)
- `check` - Check payment

**Indexes**:
```sql
PRIMARY KEY (id)
FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
INDEX (order_id)
INDEX (status)
UNIQUE KEY (reference_code)
```

## Migrations (Chronological Order)

### 1. Create users table
```
Laravel default users migration
- Includes: id, name, email, password, remember_token, timestamps
```

### 2. Create products table
```
Stores product information
- Adds: description, price, cost, stock, category_id, sku, barcode
```

### 3. Create categories table
```
Product categories
- Adds: name, slug, description
```

### 4. Create orders table
```
Order management
- Adds: user_id, order_number, total, status, payment_status
```

### 5. Create order_items table
```
Order line items
- Adds: order_id, product_id, quantity, price, subtotal
```

### 6. Create payments table
```
Payment tracking
- Adds: order_id, amount, payment_method, status, reference_code
```

## Relationships (Eloquent ORM)

### User Model
```php
// User has many Orders
hasMany(Order::class)

// User has many Products (if admin)
hasMany(Product::class)
```

### Product Model
```php
// Product belongs to Category
belongsTo(Category::class)

// Product has many OrderItems
hasMany(OrderItem::class)

// Product belongs to many Orders (through OrderItems)
belongsToMany(Order::class, 'order_items')
```

### Category Model
```php
// Category has many Products
hasMany(Product::class)
```

### Order Model
```php
// Order belongs to User
belongsTo(User::class)

// Order has many OrderItems
hasMany(OrderItem::class)

// Order belongs to many Products (through OrderItems)
belongsToMany(Product::class, 'order_items')

// Order has many Payments
hasMany(Payment::class)
```

### OrderItem Model
```php
// OrderItem belongs to Order
belongsTo(Order::class)

// OrderItem belongs to Product
belongsTo(Product::class)
```

### Payment Model
```php
// Payment belongs to Order
belongsTo(Order::class)
```

## Indexes Strategy

**Primary Keys**:
- Semua tabel menggunakan auto-increment bigint unsigned sebagai PK
- Memungkinkan scaling hingga 9.2 quintillion records

**Foreign Keys**:
- Semua FK memiliki index untuk optimasi JOIN queries
- Cascade delete pada order_items untuk integritas data

**Unique Keys**:
- email (users) - Mencegah duplikasi email
- sku (products) - Mencegah duplikasi SKU produk
- order_number (orders) - Unique order reference
- reference_code (payments) - Unique payment reference

**Performance Indexes**:
- status fields pada orders dan payments untuk filtering
- created_at pada users untuk sorting chronologically

## Data Integrity Rules

1. **Referential Integrity**:
   - Foreign key constraints mencegah orphaned records
   - Cascade delete pada order_items saat order dihapus

2. **Business Rules**:
   - Email harus unik (tidak boleh ada duplikasi)
   - Order number harus unik (setiap order punya reference unik)
   - Product price tidak boleh negatif
   - Order quantity harus > 0

3. **Data Validation**:
   - Email format validation
   - Price harus decimal(10,2) - min: 0.01, max: 99,999,999.99
   - Status harus sesuai enum values

## Query Examples

### Get user dengan semua orders
```sql
SELECT u.*, o.* FROM users u
LEFT JOIN orders o ON u.id = o.user_id
WHERE u.id = ?;
```

### Get order dengan detail items dan products
```sql
SELECT o.*, oi.*, p.* FROM orders o
JOIN order_items oi ON o.id = oi.order_id
JOIN products p ON oi.product_id = p.id
WHERE o.id = ?;
```

### Get products by category
```sql
SELECT p.* FROM products p
JOIN categories c ON p.category_id = c.id
WHERE c.slug = ?;
```

### Get total revenue per user
```sql
SELECT u.id, u.name, SUM(o.total) as total_revenue
FROM users u
LEFT JOIN orders o ON u.id = o.user_id
GROUP BY u.id
ORDER BY total_revenue DESC;
```

## Backup & Maintenance

### Backup Strategy
- Daily automated backups
- Full database backup + incremental logs
- Retention: 30 days minimum
- Test restore procedures quarterly

### Maintenance Tasks
- Optimize tables monthly: `OPTIMIZE TABLE table_name`
- Check for unused indexes
- Monitor table sizes and growth
- Defragmentation untuk large tables

## Performance Considerations

1. **Connection Pooling**: Gunakan untuk aplikasi dengan banyak koneksi
2. **Query Caching**: Cache query results menggunakan Redis
3. **Indexes**: Monitor slow query log dan tambah indexes jika diperlukan
4. **Partitioning**: Untuk tabel orders yang besar, gunakan date-based partitioning
5. **Replication**: Setup master-slave untuk high availability

## Security Measures

1. **User Access Control**:
   - Database user dengan limited privileges
   - Separate read-only user untuk reporting
   - Secure password storage (bcrypt)

2. **Data Protection**:
   - Encryption at rest untuk sensitive data
   - SSL connection untuk database (if remote)
   - Regular security audits

3. **Audit Trail**:
   - Timestamps (created_at, updated_at) pada semua tables
   - Soft deletes untuk important records
   - Activity logging untuk sensitive operations
