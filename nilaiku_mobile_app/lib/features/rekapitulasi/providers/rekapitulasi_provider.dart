import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../auth/providers/auth_provider.dart';
import '../data/rekapitulasi_repository.dart';

final rekapitulasiRepositoryProvider = Provider<RekapitulasiRepository>((ref) {
  final dioClient = ref.watch(dioClientProvider);
  return RekapitulasiRepository(dioClient: dioClient);
});

final rekapitulasiProvider =
    AsyncNotifierProvider<RekapitulasiNotifier, List<Map<String, dynamic>>>(
      RekapitulasiNotifier.new,
    );

final ipIpkProvider =
    AsyncNotifierProvider<IpIpkNotifier, Map<String, dynamic>>(
      IpIpkNotifier.new,
    );

class RekapitulasiNotifier extends AsyncNotifier<List<Map<String, dynamic>>> {
  RekapitulasiRepository get _repository =>
      ref.read(rekapitulasiRepositoryProvider);

  @override
  Future<List<Map<String, dynamic>>> build() async {
    return const [];
  }

  Future<void> refresh({required int semesterId}) async {
    state = const AsyncValue.loading();
    final items = await _repository.getRekapitulasi(
      tahunAkademikId: semesterId,
    );
    state = AsyncValue.data(items);
  }
}

class IpIpkNotifier extends AsyncNotifier<Map<String, dynamic>> {
  RekapitulasiRepository get _repository =>
      ref.read(rekapitulasiRepositoryProvider);

  @override
  Future<Map<String, dynamic>> build() async {
    return _repository.getIpIpk();
  }

  Future<void> refresh({int? semesterId}) async {
    state = const AsyncValue.loading();
    final data = await _repository.getIpIpk(tahunAkademikId: semesterId);
    state = AsyncValue.data(data);
  }
}
