import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AppColors {
  static const brandBlueLight = Color(0xFF0070FF);
  static const brandBlue = Color(0xFF1428A0);
  static const bgPage = Color(0xFFF8F9FB);
  static const textHeading = Color(0xFF1A1A1A);
  static const textBody = Color(0xFF4B5563);
  static const textMuted = Color(0xFF9CA3AF);
  static const borderSubtle = Color(0xFFE5E7EB);
  static const successGreen = Color(0xFF1FA855);
  static const warningAmber = Color(0xFFF5A623);
  static const rose = Color(0xFFE11D48);

  static const gradientButton = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [brandBlueLight, brandBlue],
  );
}

final appTheme = ThemeData(
  brightness: Brightness.light,
  scaffoldBackgroundColor: AppColors.bgPage,
  fontFamily: GoogleFonts.inter().fontFamily,
  useMaterial3: true,
  colorScheme: const ColorScheme.light(
    primary: AppColors.brandBlue,
    secondary: AppColors.brandBlueLight,
    surface: Colors.white,
  ),
  cardTheme: CardThemeData(
    color: Colors.white,
    elevation: 2,
    shadowColor: Colors.black.withValues(alpha: 0.06),
    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
  ),
  inputDecorationTheme: InputDecorationTheme(
    filled: true,
    fillColor: Colors.white,
    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
    border: OutlineInputBorder(
      borderRadius: BorderRadius.circular(12),
      borderSide: const BorderSide(color: AppColors.borderSubtle),
    ),
    focusedBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(12),
      borderSide: const BorderSide(color: AppColors.brandBlue, width: 1.5),
    ),
    errorBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(12),
      borderSide: const BorderSide(color: AppColors.rose),
    ),
    focusedErrorBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(12),
      borderSide: const BorderSide(color: AppColors.rose, width: 1.5),
    ),
    hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 14),
  ),
  textTheme: TextTheme(
    displayLarge: GoogleFonts.inter(
      fontSize: 32,
      fontWeight: FontWeight.bold,
      color: AppColors.textHeading,
    ),
    headlineSmall: GoogleFonts.inter(
      fontSize: 18,
      fontWeight: FontWeight.w600,
      color: AppColors.textHeading,
    ),
    bodyLarge: GoogleFonts.inter(
      fontSize: 16,
      fontWeight: FontWeight.w500,
      color: AppColors.textBody,
    ),
    bodyMedium: GoogleFonts.inter(
      fontSize: 14,
      fontWeight: FontWeight.w400,
      color: AppColors.textBody,
    ),
    labelMedium: GoogleFonts.inter(
      fontSize: 12,
      fontWeight: FontWeight.w500,
      color: AppColors.textMuted,
    ),
  ),
  elevatedButtonTheme: ElevatedButtonThemeData(
    style: ElevatedButton.styleFrom(
      backgroundColor: AppColors.brandBlue,
      foregroundColor: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(999)),
      textStyle: GoogleFonts.inter(fontSize: 16, fontWeight: FontWeight.w600),
    ),
  ),
);
