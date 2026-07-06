import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

import '../../../core/network/api_endpoints.dart';
import '../../../core/network/dio_client.dart';
import '../../../core/storage/secure_storage.dart';

class AuthRepository {
  final DioClient _dioClient;
  final SecureStorage _secureStorage;

  AuthRepository({
    required DioClient dioClient,
    required SecureStorage secureStorage,
  }) : _dioClient = dioClient,
       _secureStorage = secureStorage;

  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    try {
      final response = await _dioClient.post(
        ApiEndpoints.login,
        data: {'email': email, 'password': password},
      );

      if (response.statusCode == 200) {
        final data = response.data as Map<String, dynamic>;

        if (data['success'] == true && data['data'] != null) {
          final token = data['data']['token'] as String?;
          final user = data['data']['user'] as Map<String, dynamic>?;

          if (token != null) {
            await _secureStorage.saveToken(token);
            final preview = token.length > 15
                ? '${token.substring(0, 15)}...'
                : token;
            debugPrint('Token saved: $preview');
            if (user != null) {
              await _secureStorage.saveUserName(user['name'] ?? '');
              await _secureStorage.saveUserEmail(user['email'] ?? '');
              await _secureStorage.saveUserRole(user['role'] ?? 'mahasiswa');
            }
          }

          return {
            'success': true,
            'message': data['message'] ?? 'Login berhasil',
            'data': data['data'],
          };
        }
      }

      final statusCode = response.statusCode;
      final message = response.data is Map<String, dynamic>
          ? response.data['message']?.toString()
          : null;

      return {
        'success': false,
        'message': statusCode == 401
            ? 'Email atau password salah, atau akun belum terdaftar.'
            : message ?? 'Login gagal',
        'errors': response.data is Map<String, dynamic>
            ? response.data['errors']
            : null,
      };
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) {
        return {
          'success': false,
          'message': 'Email atau password salah, atau akun belum terdaftar.',
          'error': e.message,
        };
      }

      return {
        'success': false,
        'message': _getErrorMessage(e),
        'error': e.message,
      };
    }
  }

  Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    try {
      final response = await _dioClient.post(
        ApiEndpoints.register,
        data: {
          'name': name,
          'email': email,
          'password': password,
          'password_confirmation': passwordConfirmation,
        },
      );

      if (response.statusCode == 201 || response.statusCode == 200) {
        final data = response.data as Map<String, dynamic>;

        if (data['success'] == true && data['data'] != null) {
          final token = data['data']['token'] as String?;
          final user = data['data']['user'] as Map<String, dynamic>?;

          if (token != null) {
            await _secureStorage.saveToken(token);
            if (user != null) {
              await _secureStorage.saveUserName(user['name'] ?? '');
              await _secureStorage.saveUserEmail(user['email'] ?? '');
              await _secureStorage.saveUserRole(user['role'] ?? 'mahasiswa');
            }
          }

          return {
            'success': true,
            'message': data['message'] ?? 'Registrasi berhasil',
            'data': data['data'],
          };
        }
      }

      return {
        'success': false,
        'message': response.data['message'] ?? 'Registrasi gagal',
        'errors': response.data['errors'],
      };
    } on DioException catch (e) {
      return {
        'success': false,
        'message': _getErrorMessage(e),
        'error': e.message,
      };
    }
  }

  Future<void> logout() async {
    try {
      await _dioClient.post(ApiEndpoints.logout);
    } catch (e) {
      // Ignore errors during logout
    } finally {
      await _secureStorage.deleteToken();
      await _secureStorage.clearAll();
    }
  }

  String _getErrorMessage(DioException error) {
    switch (error.type) {
      case DioExceptionType.connectionTimeout:
        return 'Koneksi timeout. Periksa koneksi internet Anda.';
      case DioExceptionType.sendTimeout:
        return 'Waktu pengiriman habis. Coba lagi.';
      case DioExceptionType.receiveTimeout:
        return 'Waktu penerimaan habis. Coba lagi.';
      case DioExceptionType.badResponse:
        if (error.response?.statusCode == 401) {
          return 'Email atau password salah, atau akun belum terdaftar.';
        }
        return 'Server error: ${error.response?.statusCode}';
      case DioExceptionType.cancel:
        return 'Permintaan dibatalkan.';
      case DioExceptionType.connectionError:
        return 'Gagal terhubung ke server. Periksa koneksi internet Anda.';
      case DioExceptionType.unknown:
        return error.message ?? 'Terjadi kesalahan yang tidak terduga.';
      default:
        return 'Terjadi kesalahan.';
    }
  }
}
