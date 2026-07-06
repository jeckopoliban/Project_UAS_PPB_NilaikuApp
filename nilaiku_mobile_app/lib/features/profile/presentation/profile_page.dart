import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:dio/dio.dart';
import 'package:image_picker/image_picker.dart';

import '../../../core/network/api_endpoints.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/buttons.dart';
import '../../auth/providers/auth_provider.dart';
import '../providers/profile_provider.dart';

class ProfilePage extends ConsumerStatefulWidget {
  const ProfilePage({super.key});

  @override
  ConsumerState<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends ConsumerState<ProfilePage> {
  final ImagePicker _imagePicker = ImagePicker();
  bool _isPhotoBusy = false;
  String? _cachedPhotoUrl;
  Future<Uint8List?>? _cachedPhotoBytesFuture;

  String _displayText(dynamic value) {
    final text = value?.toString().trim() ?? '';
    return text.isEmpty ? '-' : text;
  }

  String _photoUrl(Map<String, dynamic> profil) {
    final photoPath = profil['foto_profil']?.toString().trim() ?? '';
    if (photoPath.isEmpty) return '';
    final version = profil['updated_at']?.toString().trim() ?? photoPath;
    return '${ApiEndpoints.profilePhoto}?v=$version';
  }

  Future<Uint8List?> _loadPhotoBytes(String photoUrl) async {
    try {
      final response = await ref
          .read(dioClientProvider)
          .instance
          .get<List<int>>(
            photoUrl,
            options: Options(responseType: ResponseType.bytes),
          );
      final data = response.data;
      if (data == null || data.isEmpty) {
        return null;
      }
      return Uint8List.fromList(data);
    } catch (_) {
      return null;
    }
  }

  Widget _buildPhotoAvatar(String photoUrl, String initials) {
    if (_cachedPhotoUrl != photoUrl) {
      _cachedPhotoUrl = photoUrl;
      _cachedPhotoBytesFuture = photoUrl.isEmpty
          ? Future.value(null)
          : _loadPhotoBytes(photoUrl);
    }

    return ClipOval(
      child: FutureBuilder<Uint8List?>(
        future: _cachedPhotoBytesFuture,
        builder: (context, snapshot) {
          final bytes = snapshot.data;
          if (photoUrl.isNotEmpty && bytes != null && bytes.isNotEmpty) {
            return Image.memory(
              bytes,
              fit: BoxFit.cover,
              gaplessPlayback: true,
            );
          }

          if (photoUrl.isNotEmpty &&
              snapshot.connectionState == ConnectionState.waiting) {
            return const Center(
              child: SizedBox(
                width: 22,
                height: 22,
                child: CircularProgressIndicator(strokeWidth: 2),
              ),
            );
          }

          return Center(
            child: Text(
              initials,
              style: const TextStyle(
                fontSize: 32,
                fontWeight: FontWeight.w700,
                color: AppColors.brandBlue,
              ),
            ),
          );
        },
      ),
    );
  }

  Future<void> _showMessage(String message) async {
    if (!mounted) return;
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(message)));
  }

  Future<void> _pickProfilePhoto() async {
    final file = await _imagePicker.pickImage(
      source: ImageSource.gallery,
      imageQuality: 85,
      maxWidth: 1200,
    );

    if (file == null || !mounted) return;

    setState(() => _isPhotoBusy = true);
    final success = await ref
        .read(profileProvider.notifier)
        .uploadProfilePhoto(file);
    if (!mounted) return;
    setState(() => _isPhotoBusy = false);

    await _showMessage(
      success
          ? 'Foto profil berhasil diperbarui.'
          : 'Gagal mengunggah foto profil.',
    );
  }

  Future<void> _deleteProfilePhoto() async {
    if (!mounted) return;

    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('Hapus Foto Profil'),
          content: const Text('Anda yakin ingin menghapus foto profil ini?'),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(false),
              child: const Text('Batal'),
            ),
            GradientButton(
              onPressed: () => Navigator.of(context).pop(true),
              width: 110,
              child: const Text('Hapus'),
            ),
          ],
        );
      },
    );

    if (confirmed != true || !mounted) return;

    setState(() => _isPhotoBusy = true);
    final success = await ref
        .read(profileProvider.notifier)
        .deleteProfilePhoto();
    if (!mounted) return;
    setState(() => _isPhotoBusy = false);

    await _showMessage(
      success
          ? 'Foto profil berhasil dihapus.'
          : 'Gagal menghapus foto profil.',
    );
  }

  Future<bool> _saveProfileField({
    required Map<String, dynamic> user,
    required Map<String, dynamic> profil,
    String? name,
    String? email,
    String? nimNis,
    String? noHp,
    String? namaInstitusi,
    String? jenisInstitusi,
    String? programStudi,
    int? targetSks,
    required String successMessage,
  }) async {
    final success = await ref
        .read(profileProvider.notifier)
        .updateProfile(
          name: name ?? user['name']?.toString() ?? '',
          email: email ?? user['email']?.toString() ?? '',
          nimNis: nimNis ?? profil['nim_nis']?.toString(),
          noHp: noHp ?? profil['no_hp']?.toString(),
          namaInstitusi:
              namaInstitusi ?? profil['nama_institusi']?.toString() ?? '',
          jenisInstitusi:
              jenisInstitusi ??
              profil['jenis_institusi']?.toString() ??
              'perguruan_tinggi',
          programStudi: programStudi ?? profil['program_studi']?.toString(),
          targetIpk: double.tryParse(profil['target_ipk']?.toString() ?? ''),
          targetSks:
              targetSks ?? int.tryParse(profil['target_sks']?.toString() ?? ''),
        );

    await _showMessage(success ? successMessage : 'Gagal memperbarui data.');
    return success;
  }

  Future<void> _editWithTextDialog({
    required String title,
    required String label,
    required String initialValue,
    required Future<void> Function(String value) onSave,
    TextInputType keyboardType = TextInputType.text,
  }) async {
    final controller = TextEditingController(text: initialValue);
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: Text(title),
          content: TextField(
            controller: controller,
            decoration: InputDecoration(labelText: label),
            keyboardType: keyboardType,
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(false),
              child: const Text('Batal'),
            ),
            GradientButton(
              onPressed: () => Navigator.of(context).pop(true),
              width: 110,
              child: const Text('Simpan'),
            ),
          ],
        );
      },
    );

    if (confirmed != true || !mounted) return;
    await onSave(controller.text.trim());
  }

  Future<void> _editNamaEmail(
    Map<String, dynamic> user,
    Map<String, dynamic> profil,
  ) async {
    final nameController = TextEditingController(
      text: user['name']?.toString() ?? '',
    );
    final emailController = TextEditingController(
      text: user['email']?.toString() ?? '',
    );

    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('Ubah Nama & Email'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: nameController,
                decoration: const InputDecoration(labelText: 'Nama'),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: emailController,
                decoration: const InputDecoration(labelText: 'Email'),
                keyboardType: TextInputType.emailAddress,
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(false),
              child: const Text('Batal'),
            ),
            GradientButton(
              onPressed: () => Navigator.of(context).pop(true),
              width: 110,
              child: const Text('Simpan'),
            ),
          ],
        );
      },
    );

    if (confirmed != true || !mounted) return;

    await _saveProfileField(
      user: user,
      profil: profil,
      name: nameController.text.trim(),
      email: emailController.text.trim().toLowerCase(),
      successMessage: 'Nama dan email berhasil diperbarui.',
    );
  }

  Future<void> _editJenisInstitusi(
    Map<String, dynamic> user,
    Map<String, dynamic> profil,
  ) async {
    var selected = profil['jenis_institusi']?.toString() ?? 'perguruan_tinggi';

    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setStateDialog) {
            return AlertDialog(
              title: const Text('Ubah Jenis Institusi'),
              content: DropdownButtonFormField<String>(
                initialValue: selected,
                items: const [
                  DropdownMenuItem(
                    value: 'perguruan_tinggi',
                    child: Text('Perguruan Tinggi'),
                  ),
                  DropdownMenuItem(value: 'sekolah', child: Text('Sekolah')),
                ],
                onChanged: (value) {
                  if (value != null) {
                    setStateDialog(() => selected = value);
                  }
                },
                decoration: const InputDecoration(labelText: 'Jenis Institusi'),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.of(context).pop(false),
                  child: const Text('Batal'),
                ),
                GradientButton(
                  onPressed: () => Navigator.of(context).pop(true),
                  width: 110,
                  child: const Text('Simpan'),
                ),
              ],
            );
          },
        );
      },
    );

    if (confirmed != true || !mounted) return;

    final label = selected == 'perguruan_tinggi'
        ? 'Perguruan Tinggi'
        : 'Sekolah';
    await _saveProfileField(
      user: user,
      profil: profil,
      jenisInstitusi: selected,
      successMessage: 'Jenis institusi berhasil diubah menjadi $label.',
    );
  }

  Future<void> _openPhotoMenu(Map<String, dynamic> profil) async {
    if (_isPhotoBusy) return;

    final hasPhoto = _photoUrl(profil).isNotEmpty;
    if (!hasPhoto) {
      await _pickProfilePhoto();
      return;
    }

    final action = await showModalBottomSheet<_PhotoAction>(
      context: context,
      showDragHandle: true,
      builder: (context) {
        return SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              ListTile(
                leading: const Icon(Icons.photo_camera_outlined),
                title: const Text('Upload foto baru'),
                onTap: () => Navigator.of(context).pop(_PhotoAction.upload),
              ),
              ListTile(
                leading: const Icon(
                  Icons.delete_outline,
                  color: AppColors.rose,
                ),
                title: const Text(
                  'Hapus foto',
                  style: TextStyle(color: AppColors.rose),
                ),
                onTap: () => Navigator.of(context).pop(_PhotoAction.delete),
              ),
            ],
          ),
        );
      },
    );

    if (!mounted) return;

    switch (action) {
      case _PhotoAction.upload:
        await _pickProfilePhoto();
        break;
      case _PhotoAction.delete:
        await _deleteProfilePhoto();
        break;
      case null:
        break;
    }
  }

  @override
  Widget build(BuildContext context) {
    final profileState = ref.watch(profileProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Profil Saya'), centerTitle: true),
      body: profileState.when(
        data: (user) {
          final profil = user['profil'] is Map<String, dynamic>
              ? Map<String, dynamic>.from(user['profil'] as Map)
              : <String, dynamic>{};
          final name = user['name']?.toString() ?? '-';
          final email = user['email']?.toString() ?? '-';
          final photoUrl = _photoUrl(profil);
          final hasPhoto = photoUrl.isNotEmpty;
          final initials = name.isNotEmpty
              ? name.trim().split(' ').first[0].toUpperCase()
              : '-';

          return RefreshIndicator(
            onRefresh: () => ref.read(profileProvider.notifier).refresh(),
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                Center(
                  child: ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: 720),
                    child: Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(
                        horizontal: 22,
                        vertical: 20,
                      ),
                      decoration: BoxDecoration(
                        gradient: AppColors.gradientButton,
                        borderRadius: BorderRadius.circular(24),
                        boxShadow: [
                          BoxShadow(
                            color: AppColors.brandBlue.withAlpha(
                              (0.22 * 255).round(),
                            ),
                            blurRadius: 28,
                            offset: const Offset(0, 14),
                          ),
                        ],
                      ),
                      child: Stack(
                        children: [
                          Positioned(
                            right: -30,
                            top: -36,
                            child: Container(
                              width: 150,
                              height: 150,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                color: Colors.white.withAlpha(
                                  (0.06 * 255).round(),
                                ),
                              ),
                            ),
                          ),
                          Positioned(
                            left: 180,
                            bottom: -20,
                            child: Container(
                              width: 110,
                              height: 60,
                              decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(40),
                                color: Colors.white.withAlpha(
                                  (0.05 * 255).round(),
                                ),
                              ),
                            ),
                          ),
                          Row(
                            crossAxisAlignment: CrossAxisAlignment.center,
                            children: [
                              Stack(
                                clipBehavior: Clip.none,
                                children: [
                                  Container(
                                    width: 108,
                                    height: 108,
                                    decoration: BoxDecoration(
                                      shape: BoxShape.circle,
                                      color: Colors.white,
                                      border: Border.all(
                                        color: Colors.white.withAlpha(
                                          (0.5 * 255).round(),
                                        ),
                                        width: 3,
                                      ),
                                    ),
                                    child: hasPhoto
                                        ? _buildPhotoAvatar(photoUrl, initials)
                                        : Center(
                                            child: Icon(
                                              Icons.person,
                                              size: 64,
                                              color: AppColors.brandBlue
                                                  .withAlpha(
                                                    (0.78 * 255).round(),
                                                  ),
                                            ),
                                          ),
                                  ),
                                  Positioned(
                                    right: -1,
                                    bottom: -1,
                                    child: _PhotoCameraButton(
                                      isBusy: _isPhotoBusy,
                                      onPressed: () => _openPhotoMenu(profil),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(width: 18),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Text(
                                      name,
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                      style: const TextStyle(
                                        fontSize: 24,
                                        fontWeight: FontWeight.w800,
                                        color: Colors.white,
                                        letterSpacing: -0.2,
                                      ),
                                    ),
                                    const SizedBox(height: 10),
                                    Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 12,
                                        vertical: 8,
                                      ),
                                      decoration: BoxDecoration(
                                        color: Colors.white.withAlpha(
                                          (0.12 * 255).round(),
                                        ),
                                        borderRadius: BorderRadius.circular(
                                          999,
                                        ),
                                        border: Border.all(
                                          color: Colors.white.withAlpha(
                                            (0.14 * 255).round(),
                                          ),
                                        ),
                                      ),
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          const Icon(
                                            Icons.email_outlined,
                                            size: 15,
                                            color: Colors.white,
                                          ),
                                          const SizedBox(width: 8),
                                          Flexible(
                                            child: Text(
                                              email,
                                              overflow: TextOverflow.ellipsis,
                                              style: const TextStyle(
                                                fontSize: 13,
                                                color: Colors.white,
                                                fontWeight: FontWeight.w500,
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(width: 14),
                              _ProfileEditButton(
                                onPressed: () => _editNamaEmail(user, profil),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 20),
                const Text(
                  'Informasi Akademik & Kontak',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                ),
                const SizedBox(height: 12),
                _ProfileInfoTile(
                  icon: Icons.badge_outlined,
                  label: 'NIM / NIS',
                  value: _displayText(profil['nim_nis']),
                  onEdit: () => _editWithTextDialog(
                    title: 'Ubah NIM / NIS',
                    label: 'NIM / NIS',
                    initialValue: _displayText(profil['nim_nis']) == '-'
                        ? ''
                        : _displayText(profil['nim_nis']),
                    onSave: (value) => _saveProfileField(
                      user: user,
                      profil: profil,
                      nimNis: value,
                      successMessage: 'NIM / NIS berhasil diperbarui.',
                    ),
                  ),
                ),
                _ProfileInfoTile(
                  icon: Icons.school_outlined,
                  label: 'Program Studi',
                  value: _displayText(profil['program_studi']),
                  onEdit: () => _editWithTextDialog(
                    title: 'Ubah Program Studi',
                    label: 'Program Studi',
                    initialValue: _displayText(profil['program_studi']) == '-'
                        ? ''
                        : _displayText(profil['program_studi']),
                    onSave: (value) => _saveProfileField(
                      user: user,
                      profil: profil,
                      programStudi: value,
                      successMessage: 'Program studi berhasil diperbarui.',
                    ),
                  ),
                ),
                _ProfileInfoTile(
                  icon: Icons.apartment_outlined,
                  label: 'Nama Institusi',
                  value: _displayText(profil['nama_institusi']),
                  onEdit: () => _editWithTextDialog(
                    title: 'Ubah Nama Institusi',
                    label: 'Nama Institusi',
                    initialValue: _displayText(profil['nama_institusi']) == '-'
                        ? ''
                        : _displayText(profil['nama_institusi']),
                    onSave: (value) => _saveProfileField(
                      user: user,
                      profil: profil,
                      namaInstitusi: value,
                      successMessage: 'Nama institusi berhasil diperbarui.',
                    ),
                  ),
                ),
                _ProfileInfoTile(
                  icon: Icons.category_outlined,
                  label: 'Jenis Institusi',
                  value:
                      (profil['jenis_institusi']?.toString() ?? '-') ==
                          'perguruan_tinggi'
                      ? 'Perguruan Tinggi'
                      : (profil['jenis_institusi']?.toString() ?? '-') ==
                            'sekolah'
                      ? 'Sekolah'
                      : '-',
                  onEdit: () => _editJenisInstitusi(user, profil),
                ),
                _ProfileInfoTile(
                  icon: Icons.phone_android_outlined,
                  label: 'Nomor HP',
                  value: _displayText(profil['no_hp']),
                  onEdit: () => _editWithTextDialog(
                    title: 'Ubah Nomor HP',
                    label: 'Nomor HP',
                    initialValue: _displayText(profil['no_hp']) == '-'
                        ? ''
                        : _displayText(profil['no_hp']),
                    keyboardType: TextInputType.phone,
                    onSave: (value) => _saveProfileField(
                      user: user,
                      profil: profil,
                      noHp: value,
                      successMessage: 'Nomor HP berhasil diperbarui.',
                    ),
                  ),
                ),
                _ProfileInfoTile(
                  icon: Icons.workspace_premium_outlined,
                  label: 'Target IPK',
                  value: _displayText(profil['target_ipk']),
                  editable: false,
                ),
                _ProfileInfoTile(
                  icon: Icons.rule_folder_outlined,
                  label: 'Target SKS',
                  value: _displayText(profil['target_sks']),
                  onEdit: () => _editWithTextDialog(
                    title: 'Ubah Target SKS',
                    label: 'Target SKS',
                    initialValue: _displayText(profil['target_sks']) == '-'
                        ? ''
                        : _displayText(profil['target_sks']),
                    keyboardType: TextInputType.number,
                    onSave: (value) => _saveProfileField(
                      user: user,
                      profil: profil,
                      targetSks: int.tryParse(value),
                      successMessage: 'Target SKS berhasil diperbarui.',
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                OutlinedButton.icon(
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppColors.rose,
                    side: const BorderSide(color: AppColors.rose),
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(999),
                    ),
                  ),
                  onPressed: () async {
                    final confirmed = await showDialog<bool>(
                      context: context,
                      builder: (context) {
                        return AlertDialog(
                          title: const Text('Logout'),
                          content: const Text(
                            'Anda yakin ingin keluar dari aplikasi?',
                          ),
                          actions: [
                            TextButton(
                              onPressed: () => Navigator.of(context).pop(false),
                              child: const Text('Batal'),
                            ),
                            GradientButton(
                              onPressed: () => Navigator.of(context).pop(true),
                              width: 100,
                              child: const Text('Logout'),
                            ),
                          ],
                        );
                      },
                    );
                    if (confirmed == true) {
                      await ref
                          .read(authProvider.notifier)
                          .logout(confirmed: true, force: true);
                    }
                  },
                  icon: const Icon(Icons.logout, color: AppColors.rose),
                  label: const Text(
                    'Logout',
                    style: TextStyle(color: AppColors.rose),
                  ),
                ),
              ],
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) =>
            Center(child: Text('Gagal memuat profil: $error')),
      ),
    );
  }
}

class _ProfileEditButton extends StatelessWidget {
  final VoidCallback onPressed;

  const _ProfileEditButton({required this.onPressed});

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Material(
          color: Colors.white,
          shape: const CircleBorder(),
          elevation: 4,
          child: InkWell(
            onTap: onPressed,
            customBorder: const CircleBorder(),
            child: const SizedBox(
              width: 56,
              height: 56,
              child: Icon(Icons.edit, color: AppColors.brandBlue, size: 24),
            ),
          ),
        ),
        const SizedBox(height: 8),
        const Text(
          'Edit Profil',
          style: TextStyle(
            fontSize: 12,
            color: Colors.white,
            fontWeight: FontWeight.w500,
          ),
        ),
      ],
    );
  }
}

class _ProfileInfoTile extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  final VoidCallback? onEdit;
  final bool editable;

  const _ProfileInfoTile({
    required this.icon,
    required this.label,
    required this.value,
    this.onEdit,
    this.editable = true,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.borderSubtle),
      ),
      child: Row(
        children: [
          Container(
            width: 42,
            height: 42,
            decoration: BoxDecoration(
              color: AppColors.brandBlue.withAlpha((0.08 * 255).round()),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: AppColors.brandBlue),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: const TextStyle(
                    fontSize: 12,
                    color: AppColors.textMuted,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: AppColors.textHeading,
                  ),
                ),
              ],
            ),
          ),
          if (editable && onEdit != null)
            IconButton(
              onPressed: onEdit,
              icon: const Icon(Icons.edit_outlined, color: AppColors.brandBlue),
              tooltip: 'Ubah $label',
            ),
        ],
      ),
    );
  }
}

class _PhotoCameraButton extends StatelessWidget {
  final VoidCallback? onPressed;
  final bool isBusy;

  const _PhotoCameraButton({required this.onPressed, required this.isBusy});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      shape: const CircleBorder(),
      elevation: 4,
      child: InkWell(
        customBorder: const CircleBorder(),
        onTap: onPressed,
        child: SizedBox(
          width: 38,
          height: 38,
          child: Center(
            child: isBusy
                ? const SizedBox(
                    width: 16,
                    height: 16,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : Icon(
                    Icons.photo_camera_outlined,
                    size: 18,
                    color: AppColors.brandBlue,
                  ),
          ),
        ),
      ),
    );
  }
}

enum _PhotoAction { upload, delete }
