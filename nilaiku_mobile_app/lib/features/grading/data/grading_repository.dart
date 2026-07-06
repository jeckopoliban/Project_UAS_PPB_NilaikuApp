import '../../../core/network/api_endpoints.dart';
import '../../../core/network/dio_client.dart';

class GradingRepository {
  final DioClient _dioClient;

  GradingRepository({required DioClient dioClient}) : _dioClient = dioClient;

  Future<List<Map<String, dynamic>>> getGradingTemplates() async {
    final response = await _dioClient.get(ApiEndpoints.gradingTemplates);
    return List<Map<String, dynamic>>.from(
      response.data['data'] as List<dynamic>,
    );
  }

  Future<Map<String, dynamic>> createGradingTemplate({
    required String namaTemplate,
    required List<Map<String, dynamic>> items,
  }) async {
    final response = await _dioClient.post(
      ApiEndpoints.gradingTemplates,
      data: {'nama_template': namaTemplate, 'items': items},
    );

    if (response.statusCode != null && response.statusCode! >= 400) {
      final message =
          response.data['message']?.toString() ??
          'Gagal membuat template grading.';
      final errors = response.data['errors'];
      if (errors is Map && errors.isNotEmpty) {
        final firstError = errors.values.first;
        if (firstError is List && firstError.isNotEmpty) {
          throw Exception(firstError.first.toString());
        }
      }
      throw Exception(message);
    }

    return Map<String, dynamic>.from(
      response.data['data'] as Map<String, dynamic>,
    );
  }

  Future<void> updateGradingTemplate({
    required int templateId,
    required String namaTemplate,
    required List<Map<String, dynamic>> items,
  }) async {
    final response = await _dioClient.put(
      '${ApiEndpoints.gradingTemplates}/$templateId',
      data: {'nama_template': namaTemplate, 'items': items},
    );

    if (response.statusCode != null && response.statusCode! >= 400) {
      final message =
          response.data['message']?.toString() ??
          'Gagal memperbarui template grading.';
      final errors = response.data['errors'];
      if (errors is Map && errors.isNotEmpty) {
        final firstError = errors.values.first;
        if (firstError is List && firstError.isNotEmpty) {
          throw Exception(firstError.first.toString());
        }
      }
      throw Exception(message);
    }
  }

  Future<void> deleteGradingTemplate(int templateId) async {
    await _dioClient.delete('${ApiEndpoints.gradingTemplates}/$templateId');
  }

  Future<void> applyGradingTemplate(
    int templateId, {
    List<int>? tahunAkademikIds,
    bool applyToAll = false,
  }) async {
    await _dioClient.post(
      '${ApiEndpoints.gradingTemplates}/$templateId/terapkan',
      data: {
        'tahun_akademik_ids': ?tahunAkademikIds,
        if (applyToAll) 'semua': true,
      },
    );
  }
}
