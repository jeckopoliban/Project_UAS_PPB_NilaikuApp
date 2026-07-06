import '../../../core/network/api_endpoints.dart';
import '../../../core/network/dio_client.dart';

class SemesterRepository {
  final DioClient _dioClient;

  SemesterRepository({required DioClient dioClient}) : _dioClient = dioClient;

  Future<List<Map<String, dynamic>>> getSemesters() async {
    final response = await _dioClient.get(ApiEndpoints.tahunAkademik);
    return List<Map<String, dynamic>>.from(
      response.data['data'] as List<dynamic>,
    );
  }

  Future<Map<String, dynamic>> createSemester({
    required String nama,
    required bool statusAktif,
  }) async {
    final response = await _dioClient.post(
      ApiEndpoints.tahunAkademik,
      data: {'nama': nama, 'status_aktif': statusAktif},
    );
    return Map<String, dynamic>.from(
      response.data['data'] as Map<String, dynamic>,
    );
  }

  Future<Map<String, dynamic>> updateSemester({
    required int id,
    required String nama,
    required bool statusAktif,
  }) async {
    final response = await _dioClient.put(
      '${ApiEndpoints.tahunAkademik}/$id',
      data: {'nama': nama, 'status_aktif': statusAktif},
    );
    return Map<String, dynamic>.from(
      response.data['data'] as Map<String, dynamic>,
    );
  }

  Future<void> deleteSemester(int id) async {
    await _dioClient.delete('${ApiEndpoints.tahunAkademik}/$id');
  }
}
