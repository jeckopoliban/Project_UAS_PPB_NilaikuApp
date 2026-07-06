import '../../../core/network/api_endpoints.dart';
import '../../../core/network/dio_client.dart';

class RekapitulasiRepository {
  final DioClient _dioClient;

  RekapitulasiRepository({required DioClient dioClient})
    : _dioClient = dioClient;

  Future<List<Map<String, dynamic>>> getRekapitulasi({
    required int tahunAkademikId,
  }) async {
    final response = await _dioClient.get(
      ApiEndpoints.rekapitulasi,
      queryParameters: {'tahun_akademik_id': tahunAkademikId},
    );

    final data = response.data['data'];
    if (data is Map<String, dynamic> && data['mata_kuliah'] is List) {
      return List<Map<String, dynamic>>.from(
        (data['mata_kuliah'] as List<dynamic>).map((item) {
          final map = Map<String, dynamic>.from(item as Map<dynamic, dynamic>);
          return {
            'id': map['id'],
            'nama_mk': map['nama_mk'],
            'sks': map['sks'],
            'nilai_akhir': map['nilai_akhir'],
            'huruf_mutu': map['huruf_mutu'],
            'indeks': map['indeks'],
            'status': map['status_lengkap'],
          };
        }),
      );
    }

    return [];
  }

  Future<Map<String, dynamic>> getIpIpk({int? tahunAkademikId}) async {
    final response = await _dioClient.get(
      ApiEndpoints.ipsIpk,
      queryParameters: tahunAkademikId != null
          ? {'tahun_akademik_id': tahunAkademikId}
          : null,
    );

    final data = response.data['data'];
    if (data is Map<String, dynamic>) {
      return Map<String, dynamic>.from(data);
    }

    return {};
  }

  Future<String> getSignedPdfUrl({required int tahunAkademikId}) async {
    final response = await _dioClient.get(
      ApiEndpoints.rekapitulasiPdfUrl,
      queryParameters: {'tahun_akademik_id': tahunAkademikId},
    );

    final data = response.data['data'];
    if (data is Map<String, dynamic> && data['url'] is String) {
      return data['url'] as String;
    }

    throw Exception('Tidak menerima URL PDF yang valid');
  }
}
