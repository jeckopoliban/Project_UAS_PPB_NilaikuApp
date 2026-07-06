import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../../../core/theme/app_theme.dart';

class AuthScaffold extends StatelessWidget {
  final String title;
  final String subtitle;
  final String cardTitle;
  final Widget child;

  const AuthScaffold({
    super.key,
    required this.title,
    required this.subtitle,
    required this.cardTitle,
    required this.child,
  });

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topCenter,
              end: Alignment.bottomCenter,
              colors: [Color(0xFFEFF4FF), Color(0xFFF8F9FB)],
            ),
          ),
        ),
        Positioned(
          top: -70,
          left: -50,
          child: _DecorBlob(
            color: const Color.fromRGBO(20, 40, 160, 0.08),
            size: 180,
          ),
        ),
        Positioned(
          bottom: -80,
          right: -40,
          child: _DecorBlob(
            color: const Color.fromRGBO(0, 112, 255, 0.08),
            size: 210,
          ),
        ),
        SafeArea(
          child: LayoutBuilder(
            builder: (context, constraints) {
              return SingleChildScrollView(
                child: ConstrainedBox(
                  constraints: BoxConstraints(minHeight: constraints.maxHeight),
                  child: Center(
                    child: Padding(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 20,
                        vertical: 24,
                      ),
                      child: ConstrainedBox(
                        constraints: const BoxConstraints(maxWidth: 460),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const SizedBox(height: 12),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 18,
                                vertical: 10,
                              ),
                              decoration: BoxDecoration(
                                color: const Color.fromRGBO(
                                  255,
                                  255,
                                  255,
                                  0.72,
                                ),
                                borderRadius: BorderRadius.circular(999),
                                border: Border.all(
                                  color: const Color.fromRGBO(
                                    255,
                                    255,
                                    255,
                                    0.8,
                                  ),
                                ),
                              ),
                              child: Text(
                                title,
                                style: GoogleFonts.inter(
                                  fontSize: 30,
                                  fontWeight: FontWeight.w800,
                                  color: AppColors.brandBlue,
                                ),
                              ),
                            ),
                            const SizedBox(height: 10),
                            Text(
                              subtitle,
                              textAlign: TextAlign.center,
                              style: GoogleFonts.inter(
                                fontSize: 14,
                                fontWeight: FontWeight.w500,
                                color: AppColors.textMuted,
                              ),
                            ),
                            const SizedBox(height: 28),
                            Container(
                              width: double.infinity,
                              padding: const EdgeInsets.all(24),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(28),
                                border: Border.all(
                                  color: AppColors.borderSubtle,
                                ),
                                boxShadow: [
                                  BoxShadow(
                                    color: const Color.fromRGBO(0, 0, 0, 0.06),
                                    blurRadius: 30,
                                    offset: const Offset(0, 16),
                                  ),
                                ],
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.stretch,
                                children: [
                                  Text(
                                    cardTitle,
                                    style: GoogleFonts.inter(
                                      fontSize: 20,
                                      fontWeight: FontWeight.w700,
                                      color: AppColors.textHeading,
                                    ),
                                  ),
                                  const SizedBox(height: 10),
                                  Text(
                                    'Gunakan akun yang sudah terdaftar untuk masuk ke dashboard.',
                                    style: GoogleFonts.inter(
                                      fontSize: 13,
                                      height: 1.45,
                                      color: AppColors.textMuted,
                                    ),
                                  ),
                                  const SizedBox(height: 22),
                                  child,
                                ],
                              ),
                            ),
                            const SizedBox(height: 24),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}

class _DecorBlob extends StatelessWidget {
  final Color color;
  final double size;

  const _DecorBlob({required this.color, required this.size});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(color: color, shape: BoxShape.circle),
    );
  }
}
