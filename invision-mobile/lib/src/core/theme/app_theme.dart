import 'package:flutter/material.dart';

// ─────────────────────────────────────────────────────────────────────────────
// Invision Design System — extracted from Stitch "Field Force Mobile CRM"
// Fonts:  Manrope (headlines) · Inter (body / labels)
// Colors: Material 3 tokens (light scheme only)
// ─────────────────────────────────────────────────────────────────────────────

class AppColors {
  AppColors._();

  // Invision field palette: evergreen, warm paper and kiln orange.
  static const primary = Color(0xFF173B34);
  static const primaryContainer = Color(0xFF326B5A);
  static const onPrimary = Color(0xFFFFFFFF);
  static const onPrimaryContainer = Color(0xFFFFFFFF);
  static const primaryFixedDim = Color(0xFFA9C9BA);
  static const inversePrimary = Color(0xFFA9C9BA);

  // Secondary (green)
  static const secondary = Color(0xFF286C4A);
  static const secondaryContainer = Color(0xFFD8EADD);
  static const onSecondary = Color(0xFFFFFFFF);
  static const onSecondaryContainer = Color(0xFF20523A);

  // Tertiary (orange)
  static const tertiary = Color(0xFFB95732);
  static const tertiaryContainer = Color(0xFFF3DFD4);
  static const onTertiary = Color(0xFFFFFFFF);
  static const onTertiaryContainer = Color(0xFFFFFFFF);

  // Error
  static const error = Color(0xFFBA1A1A);
  static const errorContainer = Color(0xFFFFDAD6);
  static const onError = Color(0xFFFFFFFF);
  static const onErrorContainer = Color(0xFF93000A);

  // Surface / Background
  static const background = Color(0xFFF3F0E8);
  static const onBackground = Color(0xFF172F2B);
  static const surface = Color(0xFFF3F0E8);
  static const onSurface = Color(0xFF172F2B);
  static const surfaceVariant = Color(0xFFD9D8CF);
  static const onSurfaceVariant = Color(0xFF59615D);
  static const surfaceContainerLowest = Color(0xFFFBFAF6);
  static const surfaceContainerLow = Color(0xFFEDEAE1);
  static const surfaceContainer = Color(0xFFE7E4DA);
  static const surfaceContainerHigh = Color(0xFFDEDBD1);
  static const surfaceContainerHighest = Color(0xFFD5D2C8);
  static const surfaceTint = Color(0xFF173B34);
  static const surfaceDim = Color(0xFFD3D0C7);

  static const heroBackground = Color(0xFF173B34);
  static const onHero = Color(0xFFFCFAF4);
  static const heroMuted = Color(0xFFC7D4CD);

  // Outline
  static const outline = Color(0xFF707872);
  static const outlineVariant = Color(0xFFC8C9C0);

  // Inverse
  static const inverseSurface = Color(0xFF263A35);
  static const inverseOnSurface = Color(0xFFF0EEE6);
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
        backgroundColor: AppColors.surface.withValues(alpha: 0.85),
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

      listTileTheme: ListTileThemeData(
        iconColor: AppColors.onSurfaceVariant,
        textColor: AppColors.onSurface,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16),
        minVerticalPadding: 10,
        shape: RoundedRectangleBorder(borderRadius: _borderRadius),
      ),

      dialogTheme: DialogThemeData(
        backgroundColor: AppColors.surfaceContainerLowest,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(borderRadius: _borderRadius),
        titleTextStyle: AppTextStyles.headlineSmall.copyWith(
          color: AppColors.onSurface,
        ),
        contentTextStyle: AppTextStyles.bodyMedium.copyWith(
          color: AppColors.onSurfaceVariant,
        ),
      ),

      bottomSheetTheme: const BottomSheetThemeData(
        backgroundColor: AppColors.surfaceContainerLowest,
        surfaceTintColor: Colors.transparent,
        showDragHandle: true,
        dragHandleColor: AppColors.outlineVariant,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
        ),
      ),

      snackBarTheme: SnackBarThemeData(
        backgroundColor: AppColors.inverseSurface,
        contentTextStyle: AppTextStyles.bodyMedium.copyWith(
          color: AppColors.inverseOnSurface,
        ),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: _borderRadius),
      ),

      progressIndicatorTheme: const ProgressIndicatorThemeData(
        color: AppColors.primary,
        linearTrackColor: AppColors.surfaceContainerHigh,
        circularTrackColor: AppColors.surfaceContainerHigh,
      ),

      floatingActionButtonTheme: const FloatingActionButtonThemeData(
        backgroundColor: AppColors.primary,
        foregroundColor: AppColors.onPrimary,
        elevation: 0,
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
          borderSide: const BorderSide(
            color: AppColors.primaryContainer,
            width: 2,
          ),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: _borderRadius,
          borderSide: const BorderSide(color: AppColors.error, width: 1.5),
        ),
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 16,
          vertical: 16,
        ),
        hintStyle: AppTextStyles.bodyMedium.copyWith(color: AppColors.outline),
        labelStyle: AppTextStyles.labelMedium.copyWith(
          color: AppColors.onSurfaceVariant,
        ),
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
        indicatorColor: AppColors.primaryContainer.withValues(alpha: 0.2),
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
