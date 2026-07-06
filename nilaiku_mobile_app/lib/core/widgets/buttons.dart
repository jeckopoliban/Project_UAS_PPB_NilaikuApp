import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../theme/app_theme.dart';

class GradientButton extends StatelessWidget {
  final String? label;
  final Widget? child;
  final VoidCallback? onPressed;
  final bool isLoading;
  final double? width;
  final double height;
  final TextStyle? textStyle;

  const GradientButton({
    super.key,
    this.label,
    this.child,
    required this.onPressed,
    this.isLoading = false,
    this.width,
    this.height = 48,
    this.textStyle,
  }) : assert(label != null || child != null, 'Either label or child must be provided');

  @override
  Widget build(BuildContext context) {
    final resolvedTextStyle = textStyle ??
        GoogleFonts.inter(
          fontSize: 16,
          fontWeight: FontWeight.w600,
          color: Colors.white,
        );

    final content = isLoading
        ? const SizedBox(
            width: 20,
            height: 20,
            child: CircularProgressIndicator(
              valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
              strokeWidth: 2,
            ),
          )
        : DefaultTextStyle(
            style: resolvedTextStyle,
            child: IconTheme(
              data: const IconThemeData(color: Colors.white),
              child: child ?? Text(label!, style: resolvedTextStyle),
            ),
          );

    return Container(
      width: width,
      height: height,
      decoration: BoxDecoration(
        gradient: AppColors.gradientButton,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: isLoading ? null : onPressed,
          borderRadius: BorderRadius.circular(999),
          child: Center(child: content),
        ),
      ),
    );
  }
}

class OutlineBlueButton extends StatelessWidget {
  final String label;
  final VoidCallback onPressed;
  final bool isLoading;
  final double? width;
  final double height;
  final TextStyle? textStyle;

  const OutlineBlueButton({
    super.key,
    required this.label,
    required this.onPressed,
    this.isLoading = false,
    this.width,
    this.height = 48,
    this.textStyle,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: width,
      height: height,
      decoration: BoxDecoration(
        border: Border.all(color: AppColors.brandBlue, width: 1.5),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: isLoading ? null : onPressed,
          borderRadius: BorderRadius.circular(999),
          child: Center(
            child: isLoading
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(
                      valueColor:
                          AlwaysStoppedAnimation<Color>(AppColors.brandBlue),
                      strokeWidth: 2,
                    ),
                  )
                : Text(
                    label,
                    style: textStyle ??
                        GoogleFonts.inter(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                          color: AppColors.brandBlue,
                        ),
                  ),
          ),
        ),
      ),
    );
  }
}
