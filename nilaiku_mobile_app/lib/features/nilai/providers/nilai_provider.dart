import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../auth/providers/auth_provider.dart';
import '../data/nilai_repository.dart';

final nilaiRepositoryProvider = Provider<NilaiRepository>((ref) {
  final dioClient = ref.watch(dioClientProvider);
  return NilaiRepository(dioClient: dioClient);
});

final nilaiProvider =
    AsyncNotifierProvider<NilaiNotifier, List<Map<String, dynamic>>>(
      NilaiNotifier.new,
    );

class NilaiNotifier extends AsyncNotifier<List<Map<String, dynamic>>> {
  NilaiRepository get _repository => ref.read(nilaiRepositoryProvider);
  bool _hasLoaded = false;
  int? _loadedMataKuliahId;
  bool get hasLoaded => _hasLoaded;
  int? get loadedMataKuliahId => _loadedMataKuliahId;

  @override
  Future<List<Map<String, dynamic>>> build() async {
    return const [];
  }

  Future<void> load(int mataKuliahId) async {
    _hasLoaded = false;
    _loadedMataKuliahId = null;
    state = const AsyncValue.loading();
    try {
      final items = await _repository.getKomponenNilai(mataKuliahId);
      _hasLoaded = true;
      _loadedMataKuliahId = mataKuliahId;
      state = AsyncValue.data(items);
    } catch (error, stack) {
      _hasLoaded = false;
      _loadedMataKuliahId = null;
      state = AsyncValue.error(error, stack);
    }
  }

  Future<void> updateNilai(
    int mataKuliahId,
    String namaKomponenPenilaian,
    List<Map<String, dynamic>> komponen,
  ) async {
    await _repository.updateNilai(
      mataKuliahId,
      namaKomponenPenilaian,
      komponen,
    );
    await load(mataKuliahId);
  }
}
