import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'package:image_picker/image_picker.dart';

import '../../auth/providers/auth_provider.dart';
import '../data/profile_repository.dart';

final profileRepositoryProvider = Provider<ProfileRepository>((ref) {
  final dioClient = ref.watch(dioClientProvider);
  return ProfileRepository(dioClient: dioClient);
});

final profileProvider =
    AsyncNotifierProvider<ProfileNotifier, Map<String, dynamic>>(
      ProfileNotifier.new,
    );

class ProfileNotifier extends AsyncNotifier<Map<String, dynamic>> {
  ProfileRepository get _repository => ref.read(profileRepositoryProvider);

  @override
  Future<Map<String, dynamic>> build() async {
    return _repository.getProfile();
  }

  Future<void> refresh() async {
    state = const AsyncValue.loading();
    final profile = await _repository.getProfile();
    state = AsyncValue.data(profile);
  }

  Future<bool> updateProfile({
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
    state = const AsyncValue.loading();
    try {
      await _repository.updateProfile(
        name: name,
        email: email,
        nimNis: nimNis,
        noHp: noHp,
        namaInstitusi: namaInstitusi,
        jenisInstitusi: jenisInstitusi,
        programStudi: programStudi,
        targetIpk: targetIpk,
        targetSks: targetSks,
      );
      final updatedProfile = await _repository.getProfile();
      state = AsyncValue.data(updatedProfile);
      return true;
    } catch (error, stack) {
      state = AsyncValue.error(error, stack);
      return false;
    }
  }

  Future<bool> uploadProfilePhoto(XFile file) async {
    final previousState = state;
    try {
      await _repository.uploadProfilePhoto(file);
      final updatedProfile = await _repository.getProfile();
      state = AsyncValue.data(updatedProfile);
      return true;
    } catch (error, stack) {
      state = previousState;
      state = AsyncValue.error(error, stack);
      return false;
    }
  }

  Future<bool> deleteProfilePhoto() async {
    final previousState = state;
    try {
      await _repository.deleteProfilePhoto();
      final updatedProfile = await _repository.getProfile();
      state = AsyncValue.data(updatedProfile);
      return true;
    } catch (error, stack) {
      state = previousState;
      state = AsyncValue.error(error, stack);
      return false;
    }
  }
}
