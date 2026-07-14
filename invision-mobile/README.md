# Invision Mobile

Flutter mobile application for the Invision SaaS Field Force and Product Tracking platform.

## Quick Start

1. Install Flutter SDK (stable channel).
2. From this folder, run `flutter pub get`.
3. Run the app with `flutter run`.

## Starter Structure

- `lib/main.dart`: App bootstrap with Riverpod `ProviderScope`.
- `lib/src/app.dart`: Root `MaterialApp.router` setup.
- `lib/src/core/constants`: App-level constants.
- `lib/src/core/network`: Shared API client setup (Dio).
- `lib/src/core/routing`: Application routing configuration (GoRouter).
- `lib/src/features/auth`: Login feature starter.
- `lib/src/features/dashboard`: Dashboard feature starter.

## API Base URL

Current Android emulator API base URL is defined in `lib/src/core/constants/app_constants.dart`:

- `http://10.0.2.2:8080/api/v1`

Adjust this value for iOS simulator or physical devices as needed.
