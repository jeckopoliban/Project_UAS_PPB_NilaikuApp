import 'package:dio/dio.dart';
import 'package:image_picker/image_picker.dart';

import '../../../core/network/api_endpoints.dart';
import '../../../core/network/dio_client.dart';

class ProfileRepository {
  final DioClient _dioClient;

  ProfileRepository({required DioClient dioClient}) : _dioClient = dioClient;

  Future<Map<String, dynamic>> getProfile() async {
    final response = await _dioClient.get(ApiEndpoints.me);
    _ensureSuccess(response.data, response.statusCode);
    final data = response.data['data'];
    if (data is Map<String, dynamic> && data['user'] is Map) {
      return Map<String, dynamic>.from(data['user'] as Map<dynamic, dynamic>);
    }
    return {};
  }

  Future<Map<String, dynamic>> updateProfile({
    String? name,
    String? email,
    String? nimNis,
    String? noHp,
    required String namaInstitusi,
    required String jenisInstitusi,
    String? programStudi,
    double? targetIpk,
    int? targetSks,
  }) async {
    final payload = <String, dynamic>{
      'nama_institusi': namaInstitusi,
      'jenis_institusi': jenisInstitusi,
    };

    if (name != null) {
      payload['name'] = name;
    }
    if (email != null) {
      payload['email'] = email;
    }
    if (nimNis != null) {
      payload['nim_nis'] = nimNis;
    }
    if (noHp != null) {
      payload['no_hp'] = noHp;
    }
    if (programStudi != null) {
      payload['program_studi'] = programStudi;
    }
    if (targetIpk != null) {
      payload['target_ipk'] = targetIpk;
    }
    if (targetSks != null) {
      payload['target_sks'] = targetSks;
    }

    final response = await _dioClient.put(ApiEndpoints.profile, data: payload);
    _ensureSuccess(response.data, response.statusCode);

    final data = response.data['data'];
    if (data is Map<String, dynamic> && data['profil'] is Map) {
      return Map<String, dynamic>.from(data['profil'] as Map<dynamic, dynamic>);
    }
    return {};
  }

  Future<void> uploadProfilePhoto(XFile file) async {
    final bytes = await file.readAsBytes();
    final fileName = file.name.isNotEmpty ? file.name : 'profile.jpg';

    final response = await _dioClient.put(
      ApiEndpoints.profile,
      data: FormData.fromMap({
        'foto_profil': MultipartFile.fromBytes(bytes, filename: fileName),
      }),
    );

    _ensureSuccess(response.data, response.statusCode);
  }

  Future<void> deleteProfilePhoto() async {
    final response = await _dioClient.put(
      ApiEndpoints.profile,
      data: {'hapus_foto_profil': true},
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
