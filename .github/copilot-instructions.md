# DKMN - Online Bus Ticket Booking System

## Architecture Overview

This is a **monorepo** with a Laravel 11 backend (`DKMN_BE/`) and Vue 3 frontend (`DKMN_FE/`), built for a Vietnamese bus ticket booking platform.

**Data Flow:**

1. Client searches trips → Backend queries `chuyen_dis` with `trams` and `tinh_thanhs` relations
2. Client selects seats → `Ghe` records marked as `'da_dat'` and linked to `DonHang` via `ChiTietDonHang`
3. Payment initiated → Creates `Ticket` (PENDING) with 10-min hold via `TicketHoldService`
4. Payment completed → Ticket status → PAID, seat released if expired via `TicketHoldService::releaseExpiredForTrip()`
5. Activity logged to `nhat_ky_hoat_dongs` via `ActivityLogService`

**Key Domain Models:**

- `ChuyenDi` (Trip): Connects `Tram` (Station) start/end, has many `Ghe` (Seats)
- `DonHang` (Order): Belongs to `NguoiDung` (User), has one `Ticket`, many `ThanhToan` (Payments) and `ChiTietDonHang` (Order Details with passenger info)
- `Ticket`: Payment lifecycle (PENDING → PAID/CANCELLED/REFUNDED), links order and trip
- `Payment`: Online payment tracking (VietQR/MoMo), reconciled via bank webhooks

## Vietnamese Naming Convention

**ALL database columns, models, and many legacy controllers use Vietnamese snake_case:**

- Tables: `nguoi_dungs`, `chuyen_dis`, `don_hangs`, `chi_tiet_don_hangs`
- Eloquent timestamps: `const CREATED_AT = 'ngay_tao'; const UPDATED_AT = 'ngay_cap_nhat';`
- Foreign keys: `nguoi_dung_id`, `chuyen_di_id`, `don_hang_id`
- Status fields: `trang_thai` (e.g., `'cho_duyet'`, `'da_xac_nhan'`, `'da_huy'`)
- User role: `vai_tro` (e.g., `'quan_tri'` = admin, `'khach_hang'` = customer)

**When creating new features:**

- Continue Vietnamese column names for consistency (e.g., `ten_moi`, `mo_ta_chi_tiet`)
- Models can use English names if preferred, but respect existing Vietnamese fillable arrays

## Authentication & Authorization

- **Auth**: Laravel Sanctum tokens via `auth:sanctum` middleware
- **Role check**: `EnsureRole` middleware with `role:quan_tri` for admin routes (checks `nguoi_dungs.vai_tro`)
- **API routes structure**:
  - `POST /api/nguoi-dung/dang-nhap` (login, no auth)
  - `GET /api/dkmn/me` (authenticated client endpoints)
  - `GET /api/admin/*` (admin only, with `['auth:sanctum', 'role:quan_tri']`)

## Payment System Architecture

**Multi-provider QR payments (VietQR/MoMo)** managed via:

- `PaymentProviderFactory`: Resolves providers from `config/payments.php`
- `GenericQrProvider`: Generates QR codes with `ORD-{code}` in description
- **Webhook flow**: Bank APIs (Timo/MSB) → `PaymentWebhookController` → matches description regex → marks `Payment` as COMPLETED → triggers `PaymentSuccessNotifier` → sends email/notification

**Ticket hold mechanism**:

- `TicketHoldService::holdMinutes()` (default 10 min from `config/payments.php`)
- Auto-release expired via `TicketHoldService::expireTicket()` which frees seats (`ghes.trang_thai = 'trong'`) and cancels ticket

**Fare calculation** (`PaymentService::computeFare()`):

1. Base fare from `tickets.total_amount_vnd` OR sum of `chi_tiet_don_hangs.gia_ghe`
2. Apply discount/surcharge from ticket or overrides
3. Clamp with `PriceNormalizer::clamp()`

## Development Workflow

**Start backend** (in `DKMN_BE/`):

```bash
php artisan serve              # Start server (http://localhost:8000)
php artisan queue:listen       # Process payment webhooks/notifications
php artisan migrate            # Run migrations
```

**Start frontend** (in `DKMN_FE/`):

```bash
npm run dev                    # Vite dev server with proxy to backend
```

**Concurrently** (from `DKMN_BE/`):

```bash
composer dev                   # Runs server + queue + logs + Vite in parallel
```

**Testing**:

```bash
php artisan test               # PHPUnit tests (see tests/Unit/, tests/Feature/)
```

## Controller Patterns

**Admin controllers** (`app/Http/Controllers/Admin/`):

- Follow resource pattern: `index()` (list with filters), `show()`, `store()`, `update()`, `destroy()`
- Return paginated JSON: `$this->respondWithPagination($paginator, $data)`
- Use private `transform*()` methods to format responses (see `OrderAdminController`)
- **Vietnamese comments required** for main methods (see `VIETNAMESE_COMMENTS_SUMMARY.md`)

**Client controllers** (`app/Http/Controllers/Client/`):

- Enforce ownership: `if ((int) $donHang->nguoi_dung_id !== (int) $user->id) return 403`
- Use eager loading: `->with(['chuyenDi.tramDi.tinhThanh', 'ticket', 'thanhToans'])`

**Webhook controllers** (`PaymentWebhookController`):

- Validate signatures from external banks
- Match transactions via regex: `config('payments.bank.description_regex')` (default `/(ORD[0-9A-Z\-]+)/i`)
- Delegate to `PaymentReconcileService` to mark payments completed

## Service Layer

**Key services** (in `app/Services/`):

- `PaymentService`: Fare computation, checksum generation
- `PaymentProviderFactory`: Provider resolution (VietQR/MoMo/ZaloPay)
- `RevenueService`: Revenue aggregation (online + manual payments)
- `TicketHoldService`: Seat hold/expiration logic
- `ActivityLogService`: Audit trail via `nhat_ky_hoat_dongs` (user actions, payments)

**Pattern**: Services are constructor-injected into controllers, use `private readonly` for dependencies (PHP 8.2+)

## Frontend Integration

**API client** (`src/services/api.js`):

- Auto-detects backend URL in dev/prod (Vite proxy or `window.location`)
- Default base: `/api` in dev, `/DKMN_BE/public/api` when deployed under subfolder
- Token stored in `localStorage`, sent via `Authorization: Bearer {token}`

**Router** (`src/router/index.js`):

- `meta: { layout: 'client' }` for public pages
- `meta: { layout: 'admin', requiresAdmin: true }` for admin pages (guards check `email === 'admin@dkmn.com'` — replace with role check)

## Testing Notes

- **Unit tests**: Use `CreatesTicketData` trait (in `tests/Concerns/`) to seed realistic orders/tickets
- **PHPUnit 11.5+**: Tests extend `Tests\TestCase`, use `RefreshDatabase` for DB isolation
- **Coverage**: Run `php artisan test --coverage` (requires Xdebug/PCOV)

## Common Gotchas

1. **Forgetting timestamp constants**: New models need `const CREATED_AT = 'ngay_tao'; const UPDATED_AT = 'ngay_cap_nhat';`
2. **Eager loading**: Always `->with()` relations to avoid N+1 (check query logs with `php artisan pail`)
3. **Seat locking**: After creating `ChiTietDonHang`, update `Ghe` status to `'da_dat'` AND set expiration logic
4. **Webhook security**: Never skip signature validation in `PaymentWebhookController::handleSepay()`
5. **Price clamping**: Use `PriceNormalizer::clamp()` for all VND amounts to ensure non-negative integers

## File Organization

- **Controllers**: Split by audience (`Admin/`, `Client/`, `Payment/`), not by entity
- **Services**: Business logic outside controllers (payment, revenue, notifications)
- **Models**: Thin Eloquent models with relationships, constants for status enums
- **Support**: Helper classes like `PriceNormalizer` (not framework-specific)

## Database Relationships

**Most common Eloquent patterns**:

```php
ChuyenDi::with(['tramDi', 'tramDen', 'nhaVanHanh', 'ghes'])->find($id);
DonHang::with(['nguoiDung', 'chuyenDi', 'ticket', 'thanhToans', 'chiTietDonHang.ghe'])->get();
Ticket::where('trip_id', $tripId)->with('donHang')->get();
```

**Key joins** for reports:

- Revenue: JOIN `payments` (online) + `thanh_toans` (manual) by date range
- Activity logs: JOIN `nhat_ky_hoat_dongs` with `nguoi_dungs` for user actions
