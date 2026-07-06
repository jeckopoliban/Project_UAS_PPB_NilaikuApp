import 'dart:io';

import 'package:dio/dio.dart';
import 'package:dio/io.dart';

HttpClient createHttpClient() {
  final client = HttpClient();
  client.badCertificateCallback = (cert, host, port) => true;
  return client;
}

IOHttpClientAdapter? resolveHttpClientAdapter(Dio dio) {
  final adapter = dio.httpClientAdapter;
  return adapter is IOHttpClientAdapter ? adapter : null;
}
