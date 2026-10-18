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

The app uses the published API by default: `https://invision-me.com/api/v1`.
Override it at build or run time with `--dart-define=API_BASE_URL=...`, for
example when targeting a local backend:

```sh
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8080/api/v1
```

For a physical device, use a backend address reachable from that device.
