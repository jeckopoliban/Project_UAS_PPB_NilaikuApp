import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../auth/providers/auth_provider.dart';
import '../data/nilai_saya_repository.dart';

final nilaiSayaRepositoryProvider = Provider<NilaiSayaRepository>((ref) {
  final dioClient = ref.watch(dioClientProvider);
  return NilaiSayaRepository(dioClient: dioClient);
});

final nilaiSayaProvider =
    AsyncNotifierProvider<NilaiSayaNotifier, List<Map<String, dynamic>>>(
      NilaiSayaNotifier.new,
    );

class NilaiSayaNotifier extends AsyncNotifier<List<Map<String, dynamic>>> {
  NilaiSayaRepository get _repository => ref.read(nilaiSayaRepositoryProvider);
  int? _tahunAkademikId;

  @override
  Future<List<Map<String, dynamic>>> build() async {
    return _repository.getNilaiSaya();
  }

  Future<void> refresh({int? tahunAkademikId}) async {
    _tahunAkademikId = tahunAkademikId;
    state = const AsyncValue.loading();
    final items = await _repository.getNilaiSaya(
      tahunAkademikId: _tahunAkademikId,
    );
    state = AsyncValue.data(items);
  }
}
