# 🏪 WarungKu Web

> Website Belanja Online untuk Pelanggan Warung Kelontong

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.x-06B6D4?logo=tailwindcss)](https://tailwindcss.com)
[![Supabase](https://img.shields.io/badge/Supabase-PostgreSQL-3ECF8E?logo=supabase)](https://supabase.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

## 📋 Deskripsi

**WarungKu Web** adalah website e-commerce sederhana untuk pelanggan warung yang memungkinkan:

- 🛍️ **Browse Produk** - Lihat produk dengan foto, harga, dan status stok
- 🔍 **Pencarian & Filter** - Cari produk dan filter berdasarkan kategori
- 🛒 **Keranjang Belanja** - Tambah, ubah, dan hapus item di keranjang
- 💳 **Checkout** - Checkout dengan QRIS atau pembayaran tunai
- 📦 **Tracking Pesanan** - Lacak status pesanan real-time
- 📄 **Download Invoice** - Download invoice dalam format PDF

## 🏗️ Arsitektur

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── ShopController.php      # Product browsing
│   │   ├── CartController.php      # Cart management
│   │   ├── CheckoutController.php  # Checkout process
│   │   ├── TrackingController.php  # Order tracking
│   │   └── WebhookController.php   # Payment webhooks
│   └── Requests/                   # Form validations
│
├── Models/
│   ├── Item.php                    # Product model
│   ├── Category.php                # Category model
│   ├── Order.php                   # Order model
│   ├── OrderItem.php               # Order items
│   ├── HousingBlock.php            # Delivery areas
│   └── Setting.php                 # App settings
│
└── Services/
    ├── CartService.php             # Cart operations
    ├── CheckoutService.php         # Checkout logic
    └── StockService.php            # Stock management
```

## 🛠️ Tech Stack

| Technology   | Version | Purpose                 |
| ------------ | ------- | ----------------------- |
| Laravel      | 12.x    | PHP Framework           |
| PHP          | 8.2+    | Programming Language    |
| PostgreSQL   | Latest  | Database (via Supabase) |
| Tailwind CSS | 3.x     | Styling                 |
| Alpine.js    | 3.x     | JavaScript Interactions |
| Vite         | 5.x     | Build Tool              |

## 📦 Dependencies

### PHP (composer.json)

```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^12.0",
        "barryvdh/laravel-dompdf": "^3.0"
    }
}
```

### JavaScript (package.json)

```json
{
    "devDependencies": {
        "tailwindcss": "^3.4",
        "alpinejs": "^3.14",
        "vite": "^5.0"
    }
}
```

## 🚀 Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- PostgreSQL (Supabase)

### Installation

1. **Clone repository**

    ```bash
    git clone https://github.com/riofach/warungku_web.git
    cd warungku_web
    ```

2. **Install PHP dependencies**

    ```bash
    composer install
    ```

3. **Install Node dependencies**

    ```bash
    npm install
    ```

4. **Setup environment variables**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

    Edit `.env` dengan konfigurasi database Supabase:

    ```env
    DB_CONNECTION=pgsql
    DB_HOST=db.your-project.supabase.co
    DB_PORT=5432
    DB_DATABASE=postgres
    DB_USERNAME=postgres
    DB_PASSWORD=your-password
    ```

5. **Build assets**

    ```bash
    npm run build
    ```

6. **Run the server**

    ```bash
    php artisan serve
    ```

    Akses di: http://localhost:8000

### Development

```bash
# Run development server with hot reload
npm run dev

# In another terminal
php artisan serve
```

## 📱 Pages & Features

| Page         | Route                  | Description                          |
| ------------ | ---------------------- | ------------------------------------ |
| Home/Shop    | `/`                    | Product listing with search & filter |
| Cart         | `/cart`                | Shopping cart management             |
| Checkout     | `/checkout`            | Checkout form & payment              |
| Payment      | `/payment/qris/{code}` | QRIS payment page                    |
| Tracking     | `/tracking`            | Order tracking search                |
| Order Detail | `/tracking/{code}`     | Order status & timeline              |

## 🎨 Design System

Design system ini **match** dengan Flutter app untuk konsistensi UI:

### Colors (Tailwind Custom)

```css
--color-primary: #2563eb; /* Blue-600 */
--color-secondary: #10b981; /* Emerald-500 */
--color-error: #ef4444; /* Red-500 */
--color-warning: #f59e0b; /* Amber-500 */
--color-surface: #ffffff; /* White */
--color-background: #f1f5f9; /* Slate-100 */
```

### Typography

- **Font Family**: Inter (Google Fonts)
- Responsive text sizing
- Consistent with Flutter app

## 📁 Project Structure

```
warungku_web/
├── app/
│   ├── Http/Controllers/    # Request handlers
│   ├── Models/              # Eloquent models
│   └── Services/            # Business logic
├── resources/
│   ├── views/               # Blade templates
│   │   ├── layouts/         # Base layouts
│   │   ├── components/      # Reusable components
│   │   ├── shop/            # Shop pages
│   │   ├── cart/            # Cart pages
│   │   ├── checkout/        # Checkout pages
│   │   └── tracking/        # Tracking pages
│   ├── css/                 # Stylesheets
│   └── js/                  # JavaScript
├── routes/
│   └── web.php              # Web routes
├── public/                  # Public assets
├── .env.example             # Environment template
└── README.md                # This file
```

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage
```

## 🔐 Environment Variables

| Variable                 | Description             | Required |
| ------------------------ | ----------------------- | -------- |
| `APP_KEY`                | Application key         | ✅       |
| `DB_CONNECTION`          | Database driver (pgsql) | ✅       |
| `DB_HOST`                | Supabase database host  | ✅       |
| `DB_PORT`                | Database port (5432)    | ✅       |
| `DB_DATABASE`            | Database name           | ✅       |
| `DB_USERNAME`            | Database username       | ✅       |
| `DB_PASSWORD`            | Database password       | ✅       |
| `PAYMENT_GATEWAY_KEY`    | Payment gateway API key | ⚠️       |
| `PAYMENT_GATEWAY_SECRET` | Payment gateway secret  | ⚠️       |

## 📄 API Routes

| Method | Route              | Controller               | Description      |
| ------ | ------------------ | ------------------------ | ---------------- |
| GET    | `/`                | ShopController@index     | Product listing  |
| GET    | `/product/{item}`  | ShopController@show      | Product detail   |
| GET    | `/cart`            | CartController@index     | View cart        |
| POST   | `/cart/add`        | CartController@add       | Add to cart      |
| PATCH  | `/cart/{id}`       | CartController@update    | Update quantity  |
| DELETE | `/cart/{id}`       | CartController@remove    | Remove item      |
| GET    | `/checkout`        | CheckoutController@index | Checkout form    |
| POST   | `/checkout`        | CheckoutController@store | Process checkout |
| GET    | `/tracking`        | TrackingController@index | Tracking search  |
| GET    | `/tracking/{code}` | TrackingController@show  | Order detail     |

## 📄 Related Projects

- [WarungKu App](../warungku_app) - Admin mobile app (Flutter)
- [Supabase](https://supabase.com) - Backend as a Service

## ⚠️ Important Rules

### Stock Reduction Rule

```
┌─────────────────────────────────────────────────────────┐
│     STOCK HANYA DIKURANGI SETELAH PAYMENT SUCCESS       │
├─────────────────────────────────────────────────────────┤
│  Order Created (PENDING) → Stock NOT reduced ❌         │
│  Payment SUCCESS → REDUCE STOCK ✅                      │
│  Payment FAILED → Stock NOT reduced ❌                  │
└─────────────────────────────────────────────────────────┘
```

Stock reduction HANYA dilakukan di `WebhookController` setelah payment gateway mengkonfirmasi pembayaran berhasil.

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Fachrio Raditya** - Skripsi Project

---

<p align="center">
  Made with ❤️ using Laravel & Tailwind CSS
</p>
