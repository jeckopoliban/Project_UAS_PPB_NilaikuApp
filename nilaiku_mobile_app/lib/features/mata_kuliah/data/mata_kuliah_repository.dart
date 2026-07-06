import '../../../core/network/api_endpoints.dart';
import '../../../core/network/dio_client.dart';

class MataKuliahRepository {
  final DioClient _dioClient;

  MataKuliahRepository({required DioClient dioClient}) : _dioClient = dioClient;

  Future<List<Map<String, dynamic>>> getMataKuliah({
    int? tahunAkademikId,
  }) async {
    final response = await _dioClient.get(
      ApiEndpoints.mataKuliah,
      queryParameters: tahunAkademikId != null
          ? {'tahun_akademik_id': tahunAkademikId}
          : null,
    );
    return List<Map<String, dynamic>>.from(
      response.data['data'] as List<dynamic>,
    );
  }

  Future<Map<String, dynamic>> getMataKuliahById(int id) async {
    final response = await _dioClient.get('${ApiEndpoints.mataKuliah}/$id');
    return Map<String, dynamic>.from(
      response.data['data'] as Map<String, dynamic>,
    );
  }

  Future<Map<String, dynamic>> createMataKuliah({
    required int tahunAkademikId,
    required String namaMk,
    required int sks,
  }) async {
    final response = await _dioClient.post(
      ApiEndpoints.mataKuliah,
      data: {
        'tahun_akademik_id': tahunAkademikId,
        'nama_mk': namaMk,
        'sks': sks,
      },
    );
    return Map<String, dynamic>.from(
      response.data['data'] as Map<String, dynamic>,
    );
  }

  Future<Map<String, dynamic>> updateMataKuliah({
    required int id,
    required int tahunAkademikId,
    required String namaMk,
    required int sks,
  }) async {
    final response = await _dioClient.put(
      '${ApiEndpoints.mataKuliah}/$id',
      data: {
        'tahun_akademik_id': tahunAkademikId,
        'nama_mk': namaMk,
        'sks': sks,
      },
    );
    return Map<String, dynamic>.from(
      response.data['data'] as Map<String, dynamic>,
    );
  }

  Future<void> deleteMataKuliah(int id) async {
    await _dioClient.delete('${ApiEndpoints.mataKuliah}/$id');
  }
}
