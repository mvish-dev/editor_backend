# Backend Implementation Plan - Editor App (Laravel)

## Goal Description
Build a robust backend API using Laravel to support the React Editor application. The backend will handle authentication, user management, design storage, token economy, and support ticketing.

## User Review Required
> [!IMPORTANT]
> **SMS Provider**: Custom implementation using `pay4sms.in`.
> **Payment Gateway**: Razorpay and PhonePe (Adapters pattern for future switching).

## Proposed Architecture

### Tech Stack
-   **Framework**: Laravel 11.x
-   **Database**: MySQL 8.0+
-   **Authentication**: Laravel Sanctum (API Tokens)
-   **Permissions**: `spatie/laravel-permission`
-   **Storage**: Local (default) / S3 (configurable via .env)
-   **Payment**: Razorpay / PhonePe SDKs
-   **SMS**: Custom Service Integration

### Authentication Flow
1.  **Registration**:
    -   Input: `name`, `username`, `email`, `phone`, `password`, `profile_picture` (optional).
    -   Action: Store as "Inactive/Unverified", Send OTP to `phone`.
    -   Verify OTP -> Activate User -> Issue Token.
2.  **Login**:
    -   Input: `username` OR `email` + `password`.
    -   Output: Sanctum Token.
3.  **Forgot Password**:
    -   Input: Phone Number -> OTP -> Reset Password.

---

## Database Schema Design

### 1. Users & Roles
-   **users**
    -   `id` (BigInt, PK)
    -   `name` (String)
    -   `username` (String, Unique)
    -   `email` (String, Unique)
    -   `phone` (String, Unique)
    -   `password` (String)
    -   `profile_picture` (String, Nullable)
    -   `token_balance` (Integer, Default: 20)
    -   `is_active` (Boolean, Default: true)
    -   `created_at`, `updated_at`
-   **roles** (via Spatie)
    -   `admin`, `designer`, `user`
-   **permissions** (via Spatie)
    -   `manage_users`, `manage_templates`, `approve_templates`, `access_admin_panel`

### 2. Design & Canvas
-   **categories**
    -   `id` (BigInt, PK)
    -   `name` (String) - e.g., "Business Card", "Flyer"
    -   `slug` (String, Unique)
-   **designs**
    -   `id` (BigInt, PK)
    -   `user_id` (FK -> users.id)
    -   `category_id` (FK -> categories.id, nullable)
    -   `name` (String)
    -   `canvas_data` (JSON/LongText) - *Stores Fabric.js JSON*
    -   `image_url` (String) - *Preview/Generated Image*
    -   `is_template` (Boolean, Default: false)
    -   `status` (Enum: 'draft', 'pending', 'approved', 'rejected')
    -   `created_at`, `updated_at`

### 3. Tokens & Orders
-   **token_packages**
    -   `id` (PK)
    -   `name` (String)
    -   `tokens` (Integer)
    -   `price` (Decimal)
-   **orders**
    -   `id` (PK)
    -   `user_id` (FK)
    -   `package_id` (FK)
    -   `tokens_amount` (Integer)
    -   `amount` (Decimal)
    -   `status` (Enum: 'pending', 'completed', 'failed')
    -   `payment_id` (String)
    -   `created_at`
-   **token_transactions**
    -   `id` (PK)
    -   `user_id` (FK)
    -   `amount` (Integer)
    -   `type` (Enum: 'purchase', 'usage', 'admin_adjustment', 'refund')
    -   `description` (String)

### 4. Notifications (System)
-   **notifications** (Laravel Default Table)
    -   `id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`
    -   *Used for: New Order (Admin), Design Approved (Designer), Ticket Reply (User)*

### 5. Support System
-   **tickets**
    -   `id` (PK)
    -   `user_id` (FK)
    -   `subject` (String)
    -   `message` (Text)
    -   `status` (Enum: 'open', 'in_progress', 'resolved', 'closed')
    -   `created_at`, `updated_at`

### 5. Settings
-   **settings**
    -   `key` (String, Unique) - e.g., 'support_whatsapp', 'site_title'
    -   `value` (Text)

### 6. Database Seeding (Default Users)
> [!NOTE]
> Run `php artisan db:seed` to create these accounts. All have password: `12345678`.
> **OTP Verification is bypassed** for these seeded users (set `email_verified_at` and `phone_verified_at` to `now()`).

| Role | Name | Email | Username | Status |
| :--- | :--- | :--- | :--- | :--- |
| **Admin** | Admin User | `admin@eprinton.com` | `admin` | Active |
| **Designer** | Designer User | `designer@eprinton.com` | `designer` | Active |
| **User** | Demo User | `user@eprinton.com` | `user` | Active |

---

## API Endpoints Specification

### Auth
-   `POST /api/auth/register` (Submit Details -> Get OTP)
-   `POST /api/auth/verify-otp` (Verify & Activate)
-   `POST /api/auth/login`
-   `POST /api/auth/logout`
-   `POST /api/auth/password/forgot` (Send OTP)
-   `POST /api/auth/password/reset` (Verify & Reset)

### User
-   `GET /api/user` (Profile)
-   `PUT /api/user/password` (Change Password while logged in)
-   `GET /api/user/designs`
-   `GET /api/user/orders`
-   `GET /api/user/tickets`

### Design / Canvas
-   `POST /api/designs` (Save new)
-   `PUT /api/designs/{id}` (Update)
-   `GET /api/designs/{id}` (Load)
-   `DELETE /api/designs/{id}`
-   `POST /api/designs/generate` (Generate Image & Deduct Token)

### Templates (Public/Protected)
-   `GET /api/templates` (List approved)
-   `POST /api/templates` (Designer submit)
-   `PUT /api/templates/{id}`

### Admin
-   `GET /api/admin/users`
-   `POST /api/admin/users/{id}/tokens` (Adjust tokens)
-   `GET /api/admin/reports/orders`
-   `GET /api/admin/templates/pending`
-   `POST /api/admin/templates/{id}/approve`
-   `PUT /api/admin/settings`

## Service Implementation Details

### Custom SMS Service (`App\Services\SmsService`)
```php
class SmsService {
    protected $url = 'http://pay4sms.in';
    protected $token = 'c5ab429db8d4042074fc0c3cef75a07d'; // Store in .env

    public function sendMessage($credit, $sender, $message, $numbers) {
        // Implementation using Http::get as provided
    }
}
```

### Payment Strategy (`App\Services\Payment\PaymentGatewayInterface`)
-   Interface: `createOrder($amount)`, `verifySignature($data)`
-   Implementations: `RazorpayGateway`, `PhonePeGateway`
-   Manager: Switches based on configuration.

---

## Verification Plan

### Automated Tests (Laravel Feature Tests)
1.  **Auth Flow**: Test registration, login, and token generation.
2.  **Role Access**: Verify standard user cannot access `/api/admin` routes.
3.  **Token Logic**:
    -   Test purchasing adds tokens.
    -   Test generating image deducts tokens.
    -   Test verification of insufficient balance.

### Manual Verification
1.  **Postman Collection**: Create a collection to hit all endpoints.
2.  **Database Inspection**: Verify `canvas_data` is stored correctly as JSON.
