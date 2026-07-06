import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../auth/providers/auth_provider.dart';
import '../data/semester_repository.dart';

final semesterRepositoryProvider = Provider<SemesterRepository>((ref) {
  final dioClient = ref.watch(dioClientProvider);
  return SemesterRepository(dioClient: dioClient);
});

final semesterProvider =
    AsyncNotifierProvider<SemesterNotifier, List<Map<String, dynamic>>>(
      SemesterNotifier.new,
    );

class SemesterNotifier extends AsyncNotifier<List<Map<String, dynamic>>> {
  SemesterRepository get _semesterRepository =>
      ref.read(semesterRepositoryProvider);

  @override
  Future<List<Map<String, dynamic>>> build() async {
    return _semesterRepository.getSemesters();
  }

  Future<void> refresh() async {
    state = const AsyncValue.loading();
    final semesters = await _semesterRepository.getSemesters();
    state = AsyncValue.data(semesters);
  }

  Future<void> create(String nama, bool statusAktif) async {
    await _semesterRepository.createSemester(
      nama: nama,
      statusAktif: statusAktif,
    );
    await refresh();
  }

  Future<void> updateSemester(int id, String nama, bool statusAktif) async {
    await _semesterRepository.updateSemester(
      id: id,
      nama: nama,
      statusAktif: statusAktif,
    );
    await refresh();
  }

  Future<void> deleteSemester(int id) async {
    await _semesterRepository.deleteSemester(id);
    await refresh();
  }
}
