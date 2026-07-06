import '../../../core/network/api_endpoints.dart';
import '../../../core/network/dio_client.dart';

class DashboardRepository {
  final DioClient _dioClient;

  DashboardRepository({required DioClient dioClient}) : _dioClient = dioClient;

  Future<Map<String, dynamic>> getDashboardStats() async {
    final response = await _dioClient.get(ApiEndpoints.dashboardStats);
    final data = response.data['data'];
    if (data is Map<String, dynamic>) {
      return data;
    }
    return {};
  }
}
