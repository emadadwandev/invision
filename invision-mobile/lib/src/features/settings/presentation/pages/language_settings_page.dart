import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/providers/locale_provider.dart';
import '../../../../core/theme/app_theme.dart';

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
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Language',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: ListView.separated(
        padding: const EdgeInsets.all(16),
        itemCount: _languages.length,
        separatorBuilder: (_, __) => const SizedBox(height: 8),
        itemBuilder: (context, index) {
          final lang = _languages[index];
          final selected = currentLocale.languageCode == lang.locale.languageCode;
          return GestureDetector(
            onTap: () {
              ref.read(localeProvider.notifier).state = lang.locale;
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('Language changed to ${lang.name}')),
              );
            },
            child: Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: selected ? AppColors.surfaceContainerLow : AppColors.surfaceContainerLowest,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(
                  color: selected ? AppColors.primary : AppColors.outlineVariant.withOpacity(0.5),
                  width: selected ? 2 : 1,
                ),
              ),
              child: Row(
                children: [
                  Container(
                    width: 36, height: 36,
                    decoration: BoxDecoration(
                      color: selected ? AppColors.primary : AppColors.surfaceContainerHigh,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Icon(
                      selected ? Icons.language_rounded : Icons.translate_rounded,
                      color: selected ? Colors.white : AppColors.outline,
                      size: 18,
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(lang.nativeName,
                            style: TextStyle(
                                fontWeight: FontWeight.w700,
                                color: selected ? AppColors.primary : AppColors.onSurface)),
                        Text(lang.name,
                            style: const TextStyle(
                                fontSize: 12, color: AppColors.onSurfaceVariant)),
                      ],
                    ),
                  ),
                  if (selected)
                    const Icon(Icons.check_circle_rounded, color: AppColors.primary, size: 20),
                ],
              ),
            ),
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
