# Flutter Google Sign-In API

Use Google Sign-In in your Flutter Android app and get an API token from the Laravel backend so you can call protected routes (favorites, cart, orders, profile, etc.).

## Backend

- **Endpoint:** `POST /api/v1/auth/google`
- **Body:** `{ "id_token": "<Google id_token>" }`
- **Response (200):**
  ```json
  {
    "success": true,
    "message": "Signed in with Google.",
    "customer": { "id": 1, "firstname": "...", "lastname": "...", "email": "...", "contact_no": null, "image": "...", "image_url": "...", "status": "active" },
    "token": "<session_token>"
  }
  ```
- Use the returned `token` as **`Authorization: Bearer <token>`** (or **`X-Session-Token: <token>`**) on all protected API requests.

## Laravel .env

- **Web** (browser Sign in with Google): `GOOGLE_CLIENT_ID` + `GOOGLE_CLIENT_SECRET` (Web application OAuth client).
- **Flutter Android:** `GOOGLE_ANDROID_CLIENT_ID` = your Android OAuth 2.0 Client ID (package name + SHA-1 in Google Cloud Console). The API uses this to verify the `id_token` from the app.

## Flutter (Android)

1. Add dependency: `google_sign_in` (and optionally `http` for calling the API).
2. In Google Cloud Console, create an **Android** OAuth 2.0 Client ID (package name from `android/app/build.gradle` `applicationId`, SHA-1 from `keytool -keystore ~/.android/debug.keystore -list -v`).
3. In your Flutter app:
   - Sign in with Google and get the **ID token** (not just the access token).
   - Call your Laravel API: `POST /api/v1/auth/google` with body `{ "id_token": "<id_token>" }`.
   - Store the returned `token` and use it for all subsequent API calls.

### Example (Dart)

```dart
import 'package:google_sign_in/google_sign_in.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

final String apiBase = 'http://YOUR_PC_IP:8000/api/v1';  // Use your PC IP when testing on a physical device

Future<Map<String, dynamic>?> signInWithGoogle() async {
  final googleSignIn = GoogleSignIn(
    // Optional: pass serverClientId (Web client ID) if you need an id_token for backend.
    // serverClientId: '718217481412-xxxx.apps.googleusercontent.com',  // Web client ID
  );
  final account = await googleSignIn.signIn();
  if (account == null) return null;

  final auth = await account.authentication;
  final idToken = auth.idToken;  // Required for Laravel API
  if (idToken == null) return null;

  final response = await http.post(
    Uri.parse('$apiBase/auth/google'),
    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
    body: jsonEncode({'id_token': idToken}),
  );

  final data = jsonDecode(response.body);
  if (response.statusCode == 200 && data['success'] == true) {
    return {
      'token': data['token'],
      'customer': data['customer'],
    };
  }
  return null;
}
```

After a successful call, send the token on every protected request:

```dart
final token = '...';  // from signInWithGoogle()
final res = await http.get(
  Uri.parse('$apiBase/favorites'),
  headers: {
    'Authorization': 'Bearer $token',
    'Accept': 'application/json',
  },
);
```

## Physical device

- Use your PC’s LAN IP for `apiBase` (e.g. `http://192.168.1.100:8000/api/v1`) and run Laravel with `php artisan serve --host=0.0.0.0` so the device can reach it.
- Ensure the Android OAuth client in Google Cloud has the correct package name and SHA-1 (debug keystore for development).
