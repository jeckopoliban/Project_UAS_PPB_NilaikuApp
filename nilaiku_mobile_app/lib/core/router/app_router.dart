import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../features/auth/presentation/login_page.dart';
import '../../features/auth/presentation/register_page.dart';
import '../../features/dashboard/presentation/dashboard_page.dart';
import '../../features/auth/providers/auth_provider.dart';
import '../../features/grading/presentation/grading_list_page.dart';
import '../../features/mata_kuliah/presentation/mata_kuliah_list_page.dart';
import '../../features/semester/presentation/semester_list_page.dart';
import '../../features/nilai/presentation/input_nilai_page.dart';
import '../../features/nilai_saya/presentation/nilai_saya_list_page.dart';
import '../../features/profile/presentation/profile_page.dart';
import '../../features/profile/presentation/edit_profile_page.dart';
import '../../features/rekapitulasi/presentation/rekapitulasi_page.dart';
import '../../features/rekapitulasi/presentation/ip_ipk_page.dart';
import 'app_shell.dart';

class _RouterRefreshNotifier extends ChangeNotifier {
  _RouterRefreshNotifier(Ref ref) {
    ref.listen<AsyncValue<AuthState>>(authProvider, (previous, next) {
      notifyListeners();
    });
  }
}

final routerRefreshProvider = Provider<_RouterRefreshNotifier>((ref) {
  return _RouterRefreshNotifier(ref);
});

final routerProvider = Provider<GoRouter>((ref) {
  debugPrint('routerProvider rebuild triggered');
  final refreshListenable = ref.watch(routerRefreshProvider);
  return GoRouter(
    refreshListenable: refreshListenable,
    initialLocation: '/login',
    redirect: (context, state) async {
      final authAsyncState = ref.watch(authProvider);
      final isLoggedIn = authAsyncState.when(
        data: (value) => value.isLoggedIn,
        loading: () => false,
        error: (error, stackTrace) => false,
      );

      final isLoggingIn = state.matchedLocation == '/login';
      final isRegistering = state.matchedLocation == '/register';
      final isOnProtectedRoute =
          state.matchedLocation.startsWith('/dashboard') ||
          state.matchedLocation.startsWith('/semester') ||
          state.matchedLocation.startsWith('/mata-kuliah') ||
          state.matchedLocation.startsWith('/nilai-saya') ||
          state.matchedLocation.startsWith('/grading') ||
          state.matchedLocation.startsWith('/profile');

      if (isLoggedIn) {
        if (isLoggingIn || isRegistering) {
          return '/dashboard';
        }
        return null;
      }

      if (!isLoggingIn && !isRegistering && isOnProtectedRoute) {
        return '/login';
      }
      return null;
    },
    routes: [
      GoRoute(
        path: '/login',
        name: 'login',
        pageBuilder: (context, state) => const MaterialPage(child: LoginPage()),
      ),
      GoRoute(
        path: '/register',
        name: 'register',
        pageBuilder: (context, state) =>
            const MaterialPage(child: RegisterPage()),
      ),
      ShellRoute(
        builder: (context, state, child) => AppShell(child: child),
        routes: [
          GoRoute(
            path: '/dashboard',
            name: 'dashboard',
            pageBuilder: (context, state) =>
                const MaterialPage(child: DashboardPage()),
          ),
          GoRoute(
            path: '/semester',
            name: 'semester',
            pageBuilder: (context, state) =>
                const MaterialPage(child: SemesterListPage()),
          ),
          GoRoute(
            path: '/mata-kuliah',
            name: 'mataKuliah',
            pageBuilder: (context, state) =>
                const MaterialPage(child: MataKuliahListPage()),
          ),
          GoRoute(
            path: '/grading',
            name: 'grading',
            pageBuilder: (context, state) =>
                const MaterialPage(child: GradingListPage()),
          ),
          GoRoute(
            path: '/mata-kuliah/:id/nilai',
            name: 'inputNilai',
            builder: (context, state) {
              final id = int.parse(state.pathParameters['id']!);
              final namaMataKuliah = state.extra is String
                  ? state.extra as String
                  : 'Mata Kuliah #$id';
              return InputNilaiPage(
                mataKuliahId: id,
                namaMataKuliah: namaMataKuliah,
              );
            },
          ),
          GoRoute(
            path: '/nilai-saya',
            name: 'nilaiSaya',
            pageBuilder: (context, state) =>
                const MaterialPage(child: NilaiSayaListPage()),
          ),
          GoRoute(
            path: '/rekapitulasi',
            name: 'rekapitulasi',
            pageBuilder: (context, state) =>
                const MaterialPage(child: RekapitulasiPage()),
          ),
          GoRoute(
            path: '/ip-ipk',
            name: 'ipIpk',
            pageBuilder: (context, state) =>
                const MaterialPage(child: IpIpkPage()),
          ),
          GoRoute(
            path: '/profile',
            name: 'profile',
            pageBuilder: (context, state) =>
                const MaterialPage(child: ProfilePage()),
          ),
          GoRoute(
            path: '/profile/edit',
            name: 'editProfile',
            pageBuilder: (context, state) =>
                const MaterialPage(child: EditProfilePage()),
          ),
        ],
      ),
    ],
    errorPageBuilder: (context, state) => MaterialPage(
      child: Scaffold(body: Center(child: Text('Error: ${state.error}'))),
    ),
  );
});
