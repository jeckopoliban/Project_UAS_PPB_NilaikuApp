import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../auth/providers/auth_provider.dart';
import '../data/mata_kuliah_repository.dart';

final mataKuliahRepositoryProvider = Provider<MataKuliahRepository>((ref) {
  final dioClient = ref.watch(dioClientProvider);
  return MataKuliahRepository(dioClient: dioClient);
});

final mataKuliahProvider =
    AsyncNotifierProvider<MataKuliahNotifier, List<Map<String, dynamic>>>(
      MataKuliahNotifier.new,
    );

class MataKuliahNotifier extends AsyncNotifier<List<Map<String, dynamic>>> {
  MataKuliahRepository get _repository =>
      ref.read(mataKuliahRepositoryProvider);
  int? _tahunAkademikId;

  @override
  Future<List<Map<String, dynamic>>> build() async {
    return _repository.getMataKuliah();
  }

  Future<void> refresh({int? tahunAkademikId}) async {
    _tahunAkademikId = tahunAkademikId;
    state = const AsyncValue.loading();
    final items = await _repository.getMataKuliah(
      tahunAkademikId: _tahunAkademikId,
    );
    state = AsyncValue.data(items);
  }

  Future<void> create({
    required int tahunAkademikId,
    required String namaMk,
    required int sks,
  }) async {
    await _repository.createMataKuliah(
      tahunAkademikId: tahunAkademikId,
      namaMk: namaMk,
      sks: sks,
    );
    await refresh(tahunAkademikId: _tahunAkademikId);
  }

  Future<void> updateMataKuliah({
    required int id,
    required int tahunAkademikId,
    required String namaMk,
    required int sks,
  }) async {
    await _repository.updateMataKuliah(
      id: id,
      tahunAkademikId: tahunAkademikId,
      namaMk: namaMk,
      sks: sks,
    );
    await refresh(tahunAkademikId: _tahunAkademikId);
  }

  Future<void> deleteMataKuliah(int id) async {
    await _repository.deleteMataKuliah(id);
    await refresh(tahunAkademikId: _tahunAkademikId);
  }
}
