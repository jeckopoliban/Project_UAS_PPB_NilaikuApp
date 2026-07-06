import '../../../core/network/api_endpoints.dart';
import '../../../core/network/dio_client.dart';

class NilaiRepository {
  final DioClient _dioClient;

  NilaiRepository({required DioClient dioClient}) : _dioClient = dioClient;

  Future<List<Map<String, dynamic>>> getKomponenNilai(int mataKuliahId) async {
    final response = await _dioClient.get(
      ApiEndpoints.komponenNilai,
      queryParameters: {'mata_kuliah_id': mataKuliahId},
    );

    _ensureSuccess(response.data, response.statusCode);

    final data = response.data['data'];
    if (data is List) {
      return List<Map<String, dynamic>>.from(
        data.map((item) => Map<String, dynamic>.from(item as Map)),
      );
    }

    return [];
  }

  Future<void> updateNilai(
    int mataKuliahId,
    String namaKomponenPenilaian,
    List<Map<String, dynamic>> komponen,
  ) async {
    final response = await _dioClient.put(
      '${ApiEndpoints.mataKuliah}/$mataKuliahId',
      data: {
        'nama_komponen_penilaian': namaKomponenPenilaian,
        'items': komponen,
      },
    );

    _ensureSuccess(response.data, response.statusCode);
  }

  void _ensureSuccess(dynamic data, int? statusCode) {
    final isSuccessStatus =
        statusCode != null && statusCode >= 200 && statusCode < 300;
    final body = data is Map<String, dynamic> ? data : <String, dynamic>{};
    final isSuccessBody = body['success'] != false;

    if (!isSuccessStatus || !isSuccessBody) {
      throw Exception(
        body['message']?.toString() ?? 'Permintaan gagal diproses.',
      );
    }
  }
}
