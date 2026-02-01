# Connecting Flutter to Laravel API (MySQL)

This guide explains how to connect your Flutter mobile app to the H&R Ice Cream Laravel API.

## 1. Laravel setup (MySQL + API)

### Database (MySQL)

1. Create a MySQL database, e.g. `hr_icecream`.
2. In your project `.env` set:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hr_icecream
DB_USERNAME=root
DB_PASSWORD=your_password
```

3. Run migrations:

```bash
php artisan migrate
```

### API base URL

- **Local (emulator / same machine):** `http://10.0.2.2:8000` (Android emulator) or `http://127.0.0.1:8000`
- **Real device on same network:** `http://YOUR_PC_IP:8000` (e.g. `http://192.168.1.100:8000`)
- **Production:** `https://yourdomain.com`

Start Laravel: `php artisan serve` (serves at `http://127.0.0.1:8000`).

---

## 2. API endpoints (no auth required)

All under prefix **`/api/v1`** (e.g. `http://127.0.0.1:8000/api/v1/flavors`).

| Method | Endpoint        | Description        |
|--------|-----------------|--------------------|
| GET    | `/api/v1/flavors`   | List all flavors   |
| GET    | `/api/v1/flavors/{id}` | Single flavor  |
| GET    | `/api/v1/gallons`   | List gallon sizes  |
| POST   | `/api/v1/login`     | Customer login     |
| POST   | `/api/v1/register`  | Customer register  |
| POST   | `/api/v1/verify-otp` | Verify OTP (registration) |
| POST   | `/api/v1/resend-otp`  | Resend OTP (registration) |
| POST   | `/api/v1/forgot-password` | Send OTP for password reset |
| POST   | `/api/v1/forgot-password/resend-otp` | Resend OTP (forgot password) |
| POST   | `/api/v1/forgot-password/verify-otp` | Verify OTP, get `reset_token` |
| POST   | `/api/v1/forgot-password/reset-password` | Set new password with `reset_token` |

### Forgot password flow (API)

1. **Send OTP** – `POST /api/v1/forgot-password`  
   Body: `{ "email": "user@example.com" }`  
   Response: `{ "success": true, "message": "...", "email": "user@example.com" }`

2. **Verify OTP** – `POST /api/v1/forgot-password/verify-otp`  
   Body: `{ "email": "user@example.com", "otp": "1234" }`  
   Response: `{ "success": true, "reset_token": "...", "expires_in_minutes": 15 }`

3. **Set new password** – `POST /api/v1/forgot-password/reset-password`  
   Body: `{ "reset_token": "...", "password": "newpass", "password_confirmation": "newpass" }`  
   Response: `{ "success": true, "message": "Your password has been updated. You can now log in." }`

Then the user can log in with `POST /api/v1/login` using the new password.

### Example: get flavors (Flutter)

```dart
final baseUrl = 'http://10.0.2.2:8000/api/v1';  // Android emulator

Future<List<dynamic>> getFlavors() async {
  final res = await http.get(Uri.parse('$baseUrl/flavors'));
  if (res.statusCode == 200) {
    final json = jsonDecode(res.body);
    return json['data'] as List;
  }
  throw Exception('Failed to load flavors');
}
```

### Example: login (Flutter)

```dart
Future<Map<String, dynamic>> login(String email, String password) async {
  final res = await http.post(
    Uri.parse('$baseUrl/login'),
    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
    body: jsonEncode({'email': email, 'password': password}),
  );
  final json = jsonDecode(res.body);
  if (res.statusCode == 200 && json['success'] == true) {
    return {
      'customer': json['customer'],
      'token': json['token'],  // use for protected routes
    };
  }
  throw Exception(json['message'] ?? 'Login failed');
}
```

---

## 3. Protected endpoints (with token)

For **orders** and **profile** you need a Bearer token from login/register.

1. **Install Laravel Sanctum** (one-time):

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

2. **Use Sanctum in Customer model** – in `app/Models/Customer.php` add:

```php
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasFactory, HasApiTokens;
    // ...
}
```

3. **Config** – in `config/auth.php` ensure you have a guard that uses the `customers` provider for API (or that Sanctum can resolve the token’s model). If you use the default `web` guard and only have Customer API users, your existing setup may be enough; otherwise add an `api` guard that uses a provider for `App\Models\Customer`.

After that, login/register will return a `token`. Use it in Flutter:

| Method | Endpoint           | Header              | Description      |
|--------|--------------------|---------------------|------------------|
| GET    | `/api/v1/me`       | `Authorization: Bearer {token}` | Current customer |
| GET    | `/api/v1/orders`   | `Authorization: Bearer {token}` | My orders        |
| POST   | `/api/v1/orders`   | `Authorization: Bearer {token}` | Create order     |
| GET    | `/api/v1/orders/{id}` | `Authorization: Bearer {token}` | Order detail  |
| POST   | `/api/v1/logout`   | `Authorization: Bearer {token}` | Logout          |

### Example: get my orders (Flutter)

```dart
Future<List<dynamic>> getMyOrders(String token) async {
  final res = await http.get(
    Uri.parse('$baseUrl/orders'),
    headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    },
  );
  if (res.statusCode == 200) {
    final json = jsonDecode(res.body);
    return json['data'] as List;
  }
  throw Exception('Failed to load orders');
}
```

### Example: create order (Flutter)

```dart
Future<Map<String, dynamic>> createOrder(String token, Map<String, dynamic> order) async {
  final res = await http.post(
    Uri.parse('$baseUrl/orders'),
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    },
    body: jsonEncode(order),
  );
  return jsonDecode(res.body);
}
```

Order body example: `product_name`, `product_type`, `gallon_size`, `delivery_date`, `delivery_time`, `delivery_address`, `amount`, `payment_method`.

---

## 4. Flutter dependencies

In `pubspec.yaml`:

```yaml
dependencies:
  http: ^1.2.0
  # or
  dio: ^5.4.0
```

Use `http` or `dio` for all requests; add `Authorization: Bearer $token` for protected routes.

---

## 5. CORS (if Flutter web or browser)

If you call the API from web or a browser, allow CORS in Laravel (e.g. in `bootstrap/app.php` or a CORS middleware) for your Flutter origin.

---

## 6. Summary

- **Database:** MySQL, configured in `.env`, then `php artisan migrate`.
- **Public API:** `GET /api/v1/flavors`, `GET /api/v1/flavors/{id}`, `GET /api/v1/gallons`, `POST /api/v1/login`, `POST /api/v1/register`, `POST /api/v1/verify-otp`, `POST /api/v1/resend-otp`, and forgot-password: `POST /api/v1/forgot-password`, `POST /api/v1/forgot-password/resend-otp`, `POST /api/v1/forgot-password/verify-otp`, `POST /api/v1/forgot-password/reset-password`.
- **Protected API:** Install Sanctum, add `HasApiTokens` to `Customer`, use `Authorization: Bearer {token}` for `/api/v1/me`, `/api/v1/orders`, `/api/v1/logout`.
- **Flutter:** Set `baseUrl` to your Laravel URL, use `http` or `dio`, send JSON and Bearer token as above.
