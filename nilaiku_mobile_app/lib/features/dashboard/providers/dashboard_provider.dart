import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../auth/providers/auth_provider.dart';
import '../data/dashboard_repository.dart';

final dashboardRepositoryProvider = Provider<DashboardRepository>((ref) {
  final dioClient = ref.watch(dioClientProvider);
  return DashboardRepository(dioClient: dioClient);
});

final dashboardProvider =
    AsyncNotifierProvider<DashboardNotifier, Map<String, dynamic>>(
      DashboardNotifier.new,
    );

class DashboardNotifier extends AsyncNotifier<Map<String, dynamic>> {
  DashboardRepository get _repository => ref.read(dashboardRepositoryProvider);

  @override
  Future<Map<String, dynamic>> build() async {
    return _repository.getDashboardStats();
  }

  Future<void> refresh() async {
    state = const AsyncValue.loading();
    final data = await _repository.getDashboardStats();
    state = AsyncValue.data(data);
  }
}
