import 'package:flutter/material.dart';

// ─────────────────────────────────────────────────────────────────────────────
// Invision Design System — extracted from Stitch "Field Force Mobile CRM"
// Fonts:  Manrope (headlines) · Inter (body / labels)
// Colors: Material 3 tokens (light scheme only)
// ─────────────────────────────────────────────────────────────────────────────

class AppColors {
  AppColors._();

  // Primary (blue)
  static const primary = Color(0xFF005BBF);
  static const primaryContainer = Color(0xFF1A73E8);
  static const onPrimary = Color(0xFFFFFFFF);
  static const onPrimaryContainer = Color(0xFFFFFFFF);
  static const primaryFixedDim = Color(0xFFADC7FF);
  static const inversePrimary = Color(0xFFADC7FF);

  // Secondary (green)
  static const secondary = Color(0xFF1B6D24);
  static const secondaryContainer = Color(0xFFA0F399);
  static const onSecondary = Color(0xFFFFFFFF);
  static const onSecondaryContainer = Color(0xFF217128);

  // Tertiary (orange)
  static const tertiary = Color(0xFFA83900);
  static const tertiaryContainer = Color(0xFFD14900);
  static const onTertiary = Color(0xFFFFFFFF);
  static const onTertiaryContainer = Color(0xFFFFFFFF);

  // Error
  static const error = Color(0xFFBA1A1A);
  static const errorContainer = Color(0xFFFFDAD6);
  static const onError = Color(0xFFFFFFFF);
  static const onErrorContainer = Color(0xFF93000A);

  // Surface / Background
  static const background = Color(0xFFF3FAFF);
  static const onBackground = Color(0xFF071E27);
  static const surface = Color(0xFFF3FAFF);
  static const onSurface = Color(0xFF071E27);
  static const surfaceVariant = Color(0xFFCFE6F2);
  static const onSurfaceVariant = Color(0xFF414754);
  static const surfaceContainerLowest = Color(0xFFFFFFFF);
  static const surfaceContainerLow = Color(0xFFE6F6FF);
  static const surfaceContainer = Color(0xFFDBF1FE);
  static const surfaceContainerHigh = Color(0xFFD5ECF8);
  static const surfaceContainerHighest = Color(0xFFCFE6F2);
  static const surfaceTint = Color(0xFF005BC0);
  static const surfaceDim = Color(0xFFC7DDE9);

  // Outline
  static const outline = Color(0xFF727785);
  static const outlineVariant = Color(0xFFC1C6D6);

  // Inverse
  static const inverseSurface = Color(0xFF1E333C);
  static const inverseOnSurface = Color(0xFFDFF4FF);
}

class AppTextStyles {
  AppTextStyles._();

  static const _manrope = 'Manrope';
  static const _inter = 'Inter';

  // Display (hero numbers)
  static const displayLarge = TextStyle(
    fontFamily: _manrope,
    fontWeight: FontWeight.w800,
    fontSize: 36,
    letterSpacing: -1.5,
    height: 1.1,
  );

  static const displayMedium = TextStyle(
    fontFamily: _manrope,
    fontWeight: FontWeight.w700,
    fontSize: 28,
    letterSpacing: -0.5,
  );

  // Headlines
  static const headlineLarge = TextStyle(
    fontFamily: _manrope,
    fontWeight: FontWeight.w800,
    fontSize: 24,
    letterSpacing: -0.5,
  );

  static const headlineMedium = TextStyle(
    fontFamily: _manrope,
    fontWeight: FontWeight.w700,
    fontSize: 20,
    letterSpacing: -0.3,
  );

  static const headlineSmall = TextStyle(
    fontFamily: _manrope,
    fontWeight: FontWeight.w700,
    fontSize: 18,
  );

  // Title
  static const titleLarge = TextStyle(
    fontFamily: _manrope,
    fontWeight: FontWeight.w700,
    fontSize: 16,
    letterSpacing: -0.2,
  );

  static const titleMedium = TextStyle(
    fontFamily: _inter,
    fontWeight: FontWeight.w600,
    fontSize: 15,
  );

  static const titleSmall = TextStyle(
    fontFamily: _inter,
    fontWeight: FontWeight.w600,
    fontSize: 13,
  );

  // Body
  static const bodyLarge = TextStyle(
    fontFamily: _inter,
    fontWeight: FontWeight.w400,
    fontSize: 16,
  );

  static const bodyMedium = TextStyle(
    fontFamily: _inter,
    fontWeight: FontWeight.w400,
    fontSize: 14,
  );

  static const bodySmall = TextStyle(
    fontFamily: _inter,
    fontWeight: FontWeight.w400,
    fontSize: 12,
  );

  // Label
  static const labelLarge = TextStyle(
    fontFamily: _inter,
    fontWeight: FontWeight.w600,
    fontSize: 14,
  );

  static const labelMedium = TextStyle(
    fontFamily: _inter,
    fontWeight: FontWeight.w600,
    fontSize: 12,
  );

  static const labelSmall = TextStyle(
    fontFamily: _inter,
    fontWeight: FontWeight.w700,
    fontSize: 10,
    letterSpacing: 0.8,
  );
}

class AppTheme {
  AppTheme._();

  static const _borderRadius = BorderRadius.all(Radius.circular(12));
  static const _pillRadius = BorderRadius.all(Radius.circular(100));

  static ThemeData get light {
    final cs = ColorScheme(
      brightness: Brightness.light,
      primary: AppColors.primary,
      onPrimary: AppColors.onPrimary,
      primaryContainer: AppColors.primaryContainer,
      onPrimaryContainer: AppColors.onPrimaryContainer,
      secondary: AppColors.secondary,
      onSecondary: AppColors.onSecondary,
      secondaryContainer: AppColors.secondaryContainer,
      onSecondaryContainer: AppColors.onSecondaryContainer,
      tertiary: AppColors.tertiary,
      onTertiary: AppColors.onTertiary,
      tertiaryContainer: AppColors.tertiaryContainer,
      onTertiaryContainer: AppColors.onTertiaryContainer,
      error: AppColors.error,
      onError: AppColors.onError,
      errorContainer: AppColors.errorContainer,
      onErrorContainer: AppColors.onErrorContainer,
      surface: AppColors.surface,
      onSurface: AppColors.onSurface,
      surfaceContainerHighest: AppColors.surfaceContainerHighest,
      onSurfaceVariant: AppColors.onSurfaceVariant,
      outline: AppColors.outline,
      outlineVariant: AppColors.outlineVariant,
      shadow: Colors.black,
      scrim: Colors.black,
      inverseSurface: AppColors.inverseSurface,
      onInverseSurface: AppColors.inverseOnSurface,
      inversePrimary: AppColors.inversePrimary,
      surfaceTint: AppColors.surfaceTint,
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: cs,
      scaffoldBackgroundColor: AppColors.background,
      fontFamily: 'Inter',
      textTheme: const TextTheme(
        displayLarge: AppTextStyles.displayLarge,
        displayMedium: AppTextStyles.displayMedium,
        headlineLarge: AppTextStyles.headlineLarge,
        headlineMedium: AppTextStyles.headlineMedium,
        headlineSmall: AppTextStyles.headlineSmall,
        titleLarge: AppTextStyles.titleLarge,
        titleMedium: AppTextStyles.titleMedium,
        titleSmall: AppTextStyles.titleSmall,
        bodyLarge: AppTextStyles.bodyLarge,
        bodyMedium: AppTextStyles.bodyMedium,
        bodySmall: AppTextStyles.bodySmall,
        labelLarge: AppTextStyles.labelLarge,
        labelMedium: AppTextStyles.labelMedium,
        labelSmall: AppTextStyles.labelSmall,
      ),

      // AppBar
      appBarTheme: AppBarTheme(
        backgroundColor: AppColors.surface.withOpacity(0.85),
        foregroundColor: AppColors.onSurface,
        elevation: 0,
        scrolledUnderElevation: 0,
        titleTextStyle: AppTextStyles.headlineMedium.copyWith(
          color: AppColors.onSurface,
        ),
        iconTheme: const IconThemeData(color: AppColors.primary),
      ),

      // Card
      cardTheme: CardThemeData(
        color: AppColors.surfaceContainerLowest,
        elevation: 0,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(borderRadius: _borderRadius),
      ),

      // FilledButton
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: AppColors.primary,
          foregroundColor: AppColors.onPrimary,
          minimumSize: const Size(double.infinity, 52),
          shape: const RoundedRectangleBorder(borderRadius: _borderRadius),
          textStyle: AppTextStyles.labelLarge,
        ),
      ),

      // OutlinedButton
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: AppColors.primary,
          minimumSize: const Size(double.infinity, 52),
          side: const BorderSide(color: AppColors.outlineVariant),
          shape: const RoundedRectangleBorder(borderRadius: _borderRadius),
          textStyle: AppTextStyles.labelLarge,
        ),
      ),

      // TextButton
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: AppColors.primary,
          textStyle: AppTextStyles.labelLarge,
        ),
      ),

      // InputDecoration
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: AppColors.surfaceContainerLow,
        border: OutlineInputBorder(
          borderRadius: _borderRadius,
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: _borderRadius,
          borderSide: BorderSide.none,
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: _borderRadius,
          borderSide: const BorderSide(color: AppColors.primaryContainer, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: _borderRadius,
          borderSide: const BorderSide(color: AppColors.error, width: 1.5),
        ),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        hintStyle: AppTextStyles.bodyMedium.copyWith(color: AppColors.outline),
        labelStyle: AppTextStyles.labelMedium.copyWith(color: AppColors.onSurfaceVariant),
      ),

      // Chip
      chipTheme: ChipThemeData(
        backgroundColor: AppColors.surfaceContainerLow,
        selectedColor: AppColors.primaryContainer,
        labelStyle: AppTextStyles.labelMedium,
        side: BorderSide.none,
        shape: const RoundedRectangleBorder(borderRadius: _pillRadius),
      ),

      // NavigationBar
      navigationBarTheme: NavigationBarThemeData(
        backgroundColor: AppColors.surfaceContainerLowest,
        indicatorColor: AppColors.primaryContainer.withOpacity(0.2),
        labelTextStyle: WidgetStateProperty.all(AppTextStyles.labelMedium),
        iconTheme: WidgetStateProperty.resolveWith((states) {
          if (states.contains(WidgetState.selected)) {
            return const IconThemeData(color: AppColors.primary);
          }
          return const IconThemeData(color: AppColors.onSurfaceVariant);
        }),
        elevation: 0,
      ),

      // Divider
      dividerTheme: const DividerThemeData(
        color: AppColors.outlineVariant,
        thickness: 1,
        space: 1,
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Convenience extensions
// ─────────────────────────────────────────────────────────────────────────────
extension AppColorsOnContext on BuildContext {
  ColorScheme get cs => Theme.of(this).colorScheme;
  TextTheme get tt => Theme.of(this).textTheme;
}
