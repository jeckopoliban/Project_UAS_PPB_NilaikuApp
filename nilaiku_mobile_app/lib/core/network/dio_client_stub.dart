import 'package:dio/dio.dart';

Object createHttpClient() {
  throw UnsupportedError('HttpClient is not supported on web');
}

Object? resolveHttpClientAdapter(Dio dio) {
  return null;
}
