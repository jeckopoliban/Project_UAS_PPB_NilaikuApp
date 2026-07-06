import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../auth/providers/auth_provider.dart';
import '../data/grading_repository.dart';

final gradingRepositoryProvider = Provider<GradingRepository>((ref) {
  final dioClient = ref.watch(dioClientProvider);
  return GradingRepository(dioClient: dioClient);
});

final gradingProvider =
    AsyncNotifierProvider<GradingNotifier, List<Map<String, dynamic>>>(
      GradingNotifier.new,
    );

class GradingNotifier extends AsyncNotifier<List<Map<String, dynamic>>> {
  GradingRepository get _repository => ref.read(gradingRepositoryProvider);

  @override
  Future<List<Map<String, dynamic>>> build() async {
    return _repository.getGradingTemplates();
  }

  Future<void> refresh() async {
    state = const AsyncValue.loading();
    final items = await _repository.getGradingTemplates();
    state = AsyncValue.data(items);
  }

  Future<void> create(
    String namaTemplate,
    List<Map<String, dynamic>> items,
  ) async {
    await _repository.createGradingTemplate(
      namaTemplate: namaTemplate,
      items: items,
    );
    await refresh();
  }

  Future<void> apply(
    int templateId, {
    List<int>? tahunAkademikIds,
    bool applyToAll = false,
  }) async {
    await _repository.applyGradingTemplate(
      templateId,
      tahunAkademikIds: tahunAkademikIds,
      applyToAll: applyToAll,
    );
    await refresh();
  }

  Future<void> updateTemplate(
    int templateId,
    String namaTemplate,
    List<Map<String, dynamic>> items,
  ) async {
    await _repository.updateGradingTemplate(
      templateId: templateId,
      namaTemplate: namaTemplate,
      items: items,
    );
    await refresh();
  }

  Future<void> delete(int templateId) async {
    await _repository.deleteGradingTemplate(templateId);
    await refresh();
  }
}
