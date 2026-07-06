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
    return List<Map<String, dynamic>>.from(
      response.data['data'] as List<dynamic>,
    );
  }

  Future<void> updateNilai(
    int mataKuliahId,
    String namaKomponenPenilaian,
    List<Map<String, dynamic>> komponen,
  ) async {
    await _dioClient.put(
      '${ApiEndpoints.mataKuliah}/$mataKuliahId',
      data: {
        'nama_komponen_penilaian': namaKomponenPenilaian,
        'items': komponen,
      },
    );
  }
}
