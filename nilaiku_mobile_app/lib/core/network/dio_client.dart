import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

import 'api_endpoints.dart';
import 'dio_client_stub.dart'
    if (dart.library.io) 'dio_client_io.dart';
import '../storage/secure_storage.dart';

class DioClient {
  late final Dio _dio;
  final SecureStorage _secureStorage;

  DioClient({required SecureStorage secureStorage})
      : _secureStorage = secureStorage {
    _dio = Dio(
      BaseOptions(
        baseUrl: ApiEndpoints.baseUrl,
        connectTimeout: const Duration(seconds: 30),
        receiveTimeout: const Duration(seconds: 30),
        validateStatus: (status) => status != null && status < 500,
      ),
    );

    // Add interceptors
    _dio.interceptors.add(_AuthInterceptor(_secureStorage));
    _dio.interceptors.add(_LoggingInterceptor());

    // Configure SSL for local development with self-signed certificates
    if (!kReleaseMode && !kIsWeb) {
      _configureSslForDevelopment();
    }
  }

  void _configureSslForDevelopment() {
    final httpClient = createHttpClient();
    final adapter = resolveHttpClientAdapter(_dio);
    if (adapter != null) {
      (adapter as dynamic).onHttpClientCreate = (_) => httpClient;
    }
  }

  Dio get instance => _dio;

  Future<Response<T>> get<T>(
    String path, {
    Map<String, dynamic>? queryParameters,
    Options? options,
    CancelToken? cancelToken,
    ProgressCallback? onReceiveProgress,
  }) async {
    try {
      return await _dio.get<T>(
        path,
        queryParameters: queryParameters,
        options: options,
        cancelToken: cancelToken,
        onReceiveProgress: onReceiveProgress,
      );
    } catch (e) {
      rethrow;
    }
  }

  Future<Response<T>> post<T>(
    String path, {
    Object? data,
    Map<String, dynamic>? queryParameters,
    Options? options,
    CancelToken? cancelToken,
    ProgressCallback? onSendProgress,
    ProgressCallback? onReceiveProgress,
  }) async {
    try {
      return await _dio.post<T>(
        path,
        data: data,
        queryParameters: queryParameters,
        options: options,
        cancelToken: cancelToken,
        onSendProgress: onSendProgress,
        onReceiveProgress: onReceiveProgress,
      );
    } catch (e) {
      rethrow;
    }
  }

  Future<Response<T>> put<T>(
    String path, {
    Object? data,
    Map<String, dynamic>? queryParameters,
    Options? options,
    CancelToken? cancelToken,
    ProgressCallback? onSendProgress,
    ProgressCallback? onReceiveProgress,
  }) async {
    try {
      return await _dio.put<T>(
        path,
        data: data,
        queryParameters: queryParameters,
        options: options,
        cancelToken: cancelToken,
        onSendProgress: onSendProgress,
        onReceiveProgress: onReceiveProgress,
      );
    } catch (e) {
      rethrow;
    }
  }

  Future<Response<T>> delete<T>(
    String path, {
    Object? data,
    Map<String, dynamic>? queryParameters,
    Options? options,
    CancelToken? cancelToken,
  }) async {
    try {
      return await _dio.delete<T>(
        path,
        data: data,
        queryParameters: queryParameters,
        options: options,
        cancelToken: cancelToken,
      );
    } catch (e) {
      rethrow;
    }
  }
}

class _AuthInterceptor extends Interceptor {
  final SecureStorage _secureStorage;

  _AuthInterceptor(this._secureStorage);

  @override
  Future<void> onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    final token = await _secureStorage.getToken();

    if (token != null &&
        !options.path.contains('/login') &&
        !options.path.contains('/register')) {
      options.headers['Authorization'] = 'Bearer $token';
    }

    handler.next(options);
  }

  @override
  Future<void> onResponse(
    Response response,
    ResponseInterceptorHandler handler,
  ) async {
    handler.next(response);
  }

  @override
  Future<void> onError(
    DioException err,
    ErrorInterceptorHandler handler,
  ) async {
    if (err.response?.statusCode == 401) {
      debugPrint('AuthInterceptor received 401 for ${err.requestOptions.path}');
      // Token expired or invalid
      await _secureStorage.deleteToken();
      debugPrint('AuthInterceptor deleted token due to 401');
      // The app should handle logout and redirect to login in the UI layer
    }
    handler.next(err);
  }
}

class _LoggingInterceptor extends Interceptor {
  @override
  Future<void> onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    debugPrint(
      '--> ${options.method} ${options.path}',
    );
    if (options.data != null) {
      debugPrint('Body: ${options.data}');
    }
    if (options.queryParameters.isNotEmpty) {
      debugPrint('Query Params: ${options.queryParameters}');
    }
    handler.next(options);
  }

  @override
  Future<void> onResponse(
    Response response,
    ResponseInterceptorHandler handler,
  ) async {
    debugPrint(
      '<-- ${response.statusCode} ${response.requestOptions.path}',
    );
    handler.next(response);
  }

  @override
  Future<void> onError(
    DioException err,
    ErrorInterceptorHandler handler,
  ) async {
    debugPrint(
      '<-- ERROR ${err.response?.statusCode} ${err.requestOptions.path}',
    );
    debugPrint('Error: ${err.message}');
    handler.next(err);
  }
}
