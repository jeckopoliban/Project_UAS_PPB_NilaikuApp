import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/dio_client.dart';
import '../../../core/storage/secure_storage.dart';
import '../../dashboard/providers/dashboard_provider.dart';
import '../../grading/providers/grading_provider.dart';
import '../../mata_kuliah/providers/mata_kuliah_provider.dart';
import '../../nilai/providers/nilai_provider.dart';
import '../../nilai_saya/providers/nilai_saya_provider.dart';
import '../../profile/providers/profile_provider.dart';
import '../../rekapitulasi/providers/rekapitulasi_provider.dart';
import '../../semester/providers/semester_provider.dart';
import '../data/auth_repository.dart';

// Providers for dependencies
final secureStorageProvider = Provider<SecureStorage>((ref) {
  return SecureStorage();
});

final dioClientProvider = Provider<DioClient>((ref) {
  final secureStorage = ref.watch(secureStorageProvider);
  return DioClient(secureStorage: secureStorage);
});

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  final dioClient = ref.watch(dioClientProvider);
  final secureStorage = ref.watch(secureStorageProvider);
  return AuthRepository(dioClient: dioClient, secureStorage: secureStorage);
});

// Auth state
class AuthState {
  final bool isLoggedIn;
  final String? error;
  final String? userName;
  final String? userEmail;
  final String? userRole;

  const AuthState({
    this.isLoggedIn = false,
    this.error,
    this.userName,
    this.userEmail,
    this.userRole,
  });

  factory AuthState.initial() => const AuthState();

  AuthState copyWith({
    bool? isLoggedIn,
    String? error,
    String? userName,
    String? userEmail,
    String? userRole,
  }) {
    return AuthState(
      isLoggedIn: isLoggedIn ?? this.isLoggedIn,
      error: error ?? this.error,
      userName: userName ?? this.userName,
      userEmail: userEmail ?? this.userEmail,
      userRole: userRole ?? this.userRole,
    );
  }
}

class AuthNotifier extends AsyncNotifier<AuthState> {
  AuthRepository get _authRepository => ref.read(authRepositoryProvider);
  SecureStorage get _secureStorage => ref.read(secureStorageProvider);
  DateTime? _lastLoginAt;

  @override
  Future<AuthState> build() async {
    final token = await _secureStorage.getToken();
    if (token != null) {
      final userName = await _secureStorage.getUserName();
      final userEmail = await _secureStorage.getUserEmail();
      final userRole = await _secureStorage.getUserRole();

      return AuthState(
        isLoggedIn: true,
        userName: userName,
        userEmail: userEmail,
        userRole: userRole,
      );
    }

    return AuthState.initial();
  }

  Future<bool> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    state = const AsyncValue.loading();

    final result = await _authRepository.register(
      name: name,
      email: email,
      password: password,
      passwordConfirmation: passwordConfirmation,
    );

    if (result['success'] == true) {
      final data = result['data'] as Map<String, dynamic>;
      final user = data['user'] as Map<String, dynamic>;

      await _invalidateUserScopedProviders();

      state = AsyncValue.data(
        AuthState(
          isLoggedIn: true,
          userName: user['name'] ?? '',
          userEmail: user['email'] ?? '',
          userRole: user['role'] ?? 'mahasiswa',
        ),
      );
      _lastLoginAt = DateTime.now();
      return true;
    }

    state = AsyncValue.data(
      AuthState(error: result['message'] ?? 'Registrasi gagal'),
    );
    return false;
  }

  /// Perform logout only when [confirmed] is true or when [force] is true
  /// (force can be used by internal callers that must bypass confirmation).
  Future<void> logout({bool confirmed = false, bool force = false}) async {
    if (kDebugMode) {
      print(
        'AuthNotifier.logout called (confirmed: $confirmed, force: $force)',
      );
      // Print stack trace to locate the caller
      // ignore: avoid_print
      print(StackTrace.current);
    }

    // prevent immediate automatic logout right after login
    // Only block unconfirmed/non-forced logout attempts within the first few seconds.
    if (!confirmed && !force && _lastLoginAt != null) {
      final since = DateTime.now().difference(_lastLoginAt!);
      if (since.inSeconds < 5) {
        if (kDebugMode) {
          print(
            'AuthNotifier.logout aborted: within protected post-login window (${since.inMilliseconds}ms)',
          );
        }
        return;
      }
    }

    if (!confirmed && !force) {
      if (kDebugMode) {
        print('AuthNotifier.logout aborted: not confirmed and not forced');
      }
      return;
    }

    // Flip auth state immediately so login screen is interactive without
    // waiting for network/logout API completion.
    state = AsyncValue.data(AuthState.initial());

    await _authRepository.logout();
    await _invalidateUserScopedProviders();
    if (kDebugMode) {
      print('AuthNotifier.logout finished and academic providers invalidated');
    }
  }

  Future<void> _invalidateUserScopedProviders() async {
    ref.invalidate(dashboardProvider);
    ref.invalidate(semesterProvider);
    ref.invalidate(mataKuliahProvider);
    ref.invalidate(nilaiSayaProvider);
    ref.invalidate(nilaiProvider);
    ref.invalidate(gradingProvider);
    ref.invalidate(profileProvider);
    ref.invalidate(rekapitulasiProvider);
    ref.invalidate(ipIpkProvider);
  }

  Future<bool> login({required String email, required String password}) async {
    state = const AsyncValue.loading();
    try {
      final result = await _authRepository.login(
        email: email,
        password: password,
      );

      if (result['success'] == true) {
        final data = result['data'] as Map<String, dynamic>;
        final user = data['user'] as Map<String, dynamic>;

        await _invalidateUserScopedProviders();

        state = AsyncValue.data(
          AuthState(
            isLoggedIn: true,
            userName: user['name'] ?? '',
            userEmail: user['email'] ?? '',
            userRole: user['role'] ?? 'mahasiswa',
          ),
        );
        _lastLoginAt = DateTime.now();
        return true;
      }

      state = AsyncValue.data(
        AuthState(error: result['message']?.toString() ?? 'Login gagal'),
      );
      return false;
    } catch (error, stack) {
      state = AsyncValue.data(
        AuthState(error: error.toString().replaceFirst('Exception: ', '')),
      );
      if (stack.toString().isNotEmpty) {
        // keep the stack for debugging without blocking the UI reset
      }
      return false;
    }
  }

  void setError(String message) {
    final current = state.maybeWhen(
      data: (value) => value,
      orElse: AuthState.initial,
    );
    state = AsyncValue.data(current.copyWith(error: message));
  }

  void clearError() {
    final current = state.maybeWhen(
      data: (value) => value,
      orElse: AuthState.initial,
    );
    state = AsyncValue.data(current.copyWith(error: null));
  }
}

final authProvider = AsyncNotifierProvider<AuthNotifier, AuthState>(
  AuthNotifier.new,
);
