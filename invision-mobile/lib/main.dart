import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'src/app.dart';
import 'src/features/auth/presentation/providers/auth_provider.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  final container = ProviderContainer();
  container.read(authProvider.notifier).tryRestoreSession();

  runApp(
    UncontrolledProviderScope(
      container: container,
      child: const InvisionApp(),
    ),
  );
}
