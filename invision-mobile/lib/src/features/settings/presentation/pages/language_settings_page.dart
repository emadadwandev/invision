import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/providers/locale_provider.dart';

class LanguageSettingsPage extends ConsumerWidget {
  const LanguageSettingsPage({super.key});

  static const _languages = [
    _LangOption(locale: Locale('en'), name: 'English', nativeName: 'English'),
    _LangOption(locale: Locale('ar'), name: 'Arabic', nativeName: 'العربية'),
    _LangOption(locale: Locale('fr'), name: 'French', nativeName: 'Français'),
  ];

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final currentLocale = ref.watch(localeProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Language')),
      body: ListView.builder(
        itemCount: _languages.length,
        itemBuilder: (context, index) {
          final lang = _languages[index];
          final selected = currentLocale.languageCode == lang.locale.languageCode;
          return ListTile(
            leading: Icon(
              selected ? Icons.radio_button_checked : Icons.radio_button_off,
              color: selected ? Theme.of(context).colorScheme.primary : null,
            ),
            title: Text(lang.nativeName),
            subtitle: Text(lang.name),
            trailing: selected
                ? Icon(Icons.check, color: Theme.of(context).colorScheme.primary)
                : null,
            onTap: () {
              ref.read(localeProvider.notifier).state = lang.locale;
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('Language changed to ${lang.name}')),
              );
            },
          );
        },
      ),
    );
  }
}

class _LangOption {
  const _LangOption({
    required this.locale,
    required this.name,
    required this.nativeName,
  });
  final Locale locale;
  final String name;
  final String nativeName;
}
