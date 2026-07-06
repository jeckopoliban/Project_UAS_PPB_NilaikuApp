import '../../../core/network/api_endpoints.dart';
import '../../../core/network/dio_client.dart';

class NilaiSayaRepository {
  final DioClient _dioClient;

  NilaiSayaRepository({required DioClient dioClient}) : _dioClient = dioClient;

  Future<List<Map<String, dynamic>>> getNilaiSaya({
    int? tahunAkademikId,
  }) async {
    final response = await _dioClient.get(
      ApiEndpoints.rekapitulasi,
      queryParameters: tahunAkademikId != null
          ? {'tahun_akademik_id': tahunAkademikId}
          : null,
    );

    final data = response.data['data'];
    if (data is Map<String, dynamic> && data['mata_kuliah'] is List) {
      final semesterName = data['nama_tahun_akademik']?.toString() ?? '-';
      return List<Map<String, dynamic>>.from(
        (data['mata_kuliah'] as List<dynamic>).map((item) {
          final map = Map<String, dynamic>.from(item as Map<dynamic, dynamic>);
          return {
            'id': map['id'],
            'nama_mk': map['nama_mk'],
            'semester': semesterName,
            'sks': map['sks'],
            'komponen_penilaian': map['nama_komponen_penilaian'],
            'nama_komponen_penilaian': map['nama_komponen_penilaian'],
            'nilai_akhir': map['nilai_akhir'],
            'huruf_mutu': map['huruf_mutu'],
            'nilai': map['nilai_akhir'],
            'grade': map['huruf_mutu'],
            'status': map['status_lengkap'] ?? 'Belum Lengkap',
          };
        }),
      );
    }

    if (data is List) {
      return List<Map<String, dynamic>>.from(data);
    }

    return [];
  }
}
