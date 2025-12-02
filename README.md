# DKMN - Hệ Thống Đặt Vé Xe Khách Trực Tuyến

## Giới thiệu

DKMN là hệ thống đặt vé xe khách trực tuyến, cho phép người dùng tìm kiếm, đặt vé và thanh toán cho các chuyến xe liên tỉnh một cách tiện lợi và nhanh chóng.

## Tính năng chính

### Dành cho Khách hàng

- 🔐 Đăng ký, đăng nhập, quản lý hồ sơ cá nhân
- 🔍 Tìm kiếm chuyến xe theo tuyến đường, thời gian
- 🎫 Đặt vé và chọn ghế trực tuyến
- 💳 Thanh toán đa dạng: VNPAY, QR Code (VietQR/SePay), Tiền mặt trên xe
- 📧 Nhận thông báo qua email và trong app
- ⭐ Đánh giá chuyến đi, gửi phản hồi
- 🎟️ Quản lý vé đã đặt, hủy vé

### Dành cho Quản trị viên

- 👥 Quản lý người dùng (khách hàng, admin)
- 🚌 Quản lý chuyến đi, tuyến đường, nhà vận hành
- 💰 Quản lý đơn hàng, thanh toán
- 📊 Báo cáo doanh thu, thống kê
- ⭐ Quản lý đánh giá, phản hồi
- 📢 Gửi thông báo đến khách hàng
- 🔧 Cấu hình hệ thống

## Công nghệ sử dụng

### Backend (DKMN_BE)

- **Framework:** Laravel 11.x
- **PHP:** 8.x+
- **Database:** MySQL
- **Authentication:** Laravel Sanctum
- **Payment Gateways:** VNPAY, VietQR, SePay
- **Email:** Laravel Mail (SMTP)

### Frontend (DKMN_FE)

- **Framework:** Vue 3
- **Build Tool:** Vite
- **UI Components:** Custom components
- **HTTP Client:** Axios
- **Routing:** Vue Router

## Cấu trúc dự án

```
DKMN/
├── DKMN_BE/                    # Backend Laravel
│   ├── app/
│   │   ├── Http/Controllers/   # Controllers (Admin, Client, Payment)
│   │   ├── Models/             # Eloquent Models
│   │   ├── Services/           # Business Logic Services
│   │   ├── Mail/               # Email Templates
│   │   └── Support/            # Helper Classes
│   ├── database/
│   │   ├── migrations/         # Database Migrations
│   │   └── seeders/            # Database Seeders
│   ├── routes/
│   │   ├── api.php             # API Routes
│   │   └── web.php             # Web Routes
│   └── config/                 # Configuration Files
│
├── DKMN_FE/                    # Frontend Vue 3
│   ├── src/
│   │   ├── components/         # Vue Components
│   │   ├── layout/             # Layout Components
│   │   ├── router/             # Vue Router
│   │   ├── services/           # API Services
│   │   └── assets/             # Static Assets
│   └── public/                 # Public Files
│
└── TÀI LIỆU/                   # Documentation
```

## Cài đặt

### Yêu cầu hệ thống

- PHP >= 8.1
- Composer
- Node.js >= 18.x
- MySQL >= 8.0
- Git

### Backend Setup

```bash
# Di chuyển vào thư mục backend
cd DKMN_BE

# Cài đặt dependencies
composer install

# Copy file environment
cp .env.example .env

# Generate application key
php artisan key:generate

# Cấu hình database trong file .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=dkmn
# DB_USERNAME=root
# DB_PASSWORD=

# Chạy migrations
php artisan migrate

# (Optional) Seed database
php artisan db:seed

# Khởi động server
php artisan serve
```

### Frontend Setup

```bash
# Di chuyển vào thư mục frontend
cd DKMN_FE

# Cài đặt dependencies
npm install

# Chạy development server
npm run dev

# Build cho production
npm run build
```

## Cấu hình thanh toán

### VNPAY

Thêm vào file `.env`:

```env
VNPAY_TMN_CODE=your_tmn_code
VNPAY_HASH_SECRET=your_hash_secret
VNPAY_PAYMENT_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
```

### SePay (Webhook)

```env
SEPAY_WEBHOOK_TOKEN=your_webhook_token
SEPAY_WEBHOOK_API_KEY=your_api_key
SEPAY_ALLOWED_IPS=ip1,ip2,ip3
SEPAY_REQUIRE_CODE=true
```

## API Documentation

API endpoints được tổ chức theo nhóm:

### Authentication

- `POST /api/register` - Đăng ký tài khoản
- `POST /api/login` - Đăng nhập
- `POST /api/logout` - Đăng xuất
- `POST /api/forgot-password` - Quên mật khẩu

### Trips (Chuyến đi)

- `GET /api/trips` - Danh sách chuyến đi
- `GET /api/trips/{id}` - Chi tiết chuyến đi
- `POST /api/trips` - Tạo chuyến đi (Admin)

### Orders (Đơn hàng)

- `POST /api/orders` - Tạo đơn hàng
- `GET /api/orders` - Danh sách đơn hàng
- `GET /api/orders/{id}` - Chi tiết đơn hàng

### Payments (Thanh toán)

- `POST /api/payments/vnpay/init` - Khởi tạo thanh toán VNPAY
- `POST /api/payments/qr/init` - Khởi tạo thanh toán QR
- `POST /api/payments/{id}/status` - Kiểm tra trạng thái thanh toán

### Admin

- `GET /api/admin/dashboard` - Dashboard thống kê
- `GET /api/admin/users` - Quản lý người dùng
- `GET /api/admin/orders` - Quản lý đơn hàng
- `GET /api/admin/payments` - Quản lý thanh toán

## Testing

```bash
# Chạy tests
php artisan test

# Chạy tests với coverage
php artisan test --coverage
```

## Đóng góp

Mọi đóng góp đều được chào đón! Vui lòng:

1. Fork repository
2. Tạo branch cho feature (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Tạo Pull Request

## License

Dự án này được phát triển cho mục đích học tập tại CMU - Software Engineering.

## Liên hệ

- Repository: [https://github.com/HoTrongKim/DKMN](https://github.com/HoTrongKim/DKMN)
- Email: ntanduy1122@gmail.com

## Ghi chú

- Dự án đang trong quá trình phát triển
- Một số tính năng có thể chưa hoàn thiện
- Vui lòng báo cáo bugs qua GitHub Issues

---

Phát triển với ❤️ bởi Team DKMN
