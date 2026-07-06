import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../theme/app_theme.dart';

class AppShell extends StatelessWidget {
  final Widget child;

  const AppShell({super.key, required this.child});

  int _currentIndex(String location) {
    if (location.startsWith('/nilai-saya')) return 1;
    if (location.startsWith('/rekapitulasi')) return 2;
    if (location.startsWith('/profile')) return 3;
    return 0;
  }

  @override
  Widget build(BuildContext context) {
    final location = GoRouterState.of(context).matchedLocation;
    final currentIndex = _currentIndex(location);

    return Scaffold(
      body: child,
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: currentIndex,
        selectedItemColor: AppColors.brandBlue,
        unselectedItemColor: AppColors.textMuted,
        type: BottomNavigationBarType.fixed,
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.dashboard),
            label: 'Dashboard',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.fact_check),
            label: 'Nilai Saya',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.bar_chart),
            label: 'Rekapitulasi',
          ),
          BottomNavigationBarItem(icon: Icon(Icons.person), label: 'Profil'),
        ],
        onTap: (index) {
          switch (index) {
            case 0:
              context.go('/dashboard');
              break;
            case 1:
              context.go('/nilai-saya');
              break;
            case 2:
              context.go('/rekapitulasi');
              break;
            case 3:
              context.go('/profile');
              break;
          }
        },
      ),
    );
  }
}
