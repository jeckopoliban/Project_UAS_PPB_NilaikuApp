import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/widgets/buttons.dart';
import '../providers/profile_provider.dart';

class EditProfilePage extends ConsumerStatefulWidget {
  const EditProfilePage({super.key});

  @override
  ConsumerState<EditProfilePage> createState() => _EditProfilePageState();
}

class _EditProfilePageState extends ConsumerState<EditProfilePage> {
  final _formKey = GlobalKey<FormState>();
  final _namaInstitusiController = TextEditingController();
  final _nimNisController = TextEditingController();
  final _noHpController = TextEditingController();
  final _targetIpkController = TextEditingController();
  final _targetSksController = TextEditingController();
  String _jenisInstitusi = 'perguruan_tinggi';
  bool _initialized = false;

  @override
  void dispose() {
    _namaInstitusiController.dispose();
    _nimNisController.dispose();
    _noHpController.dispose();
    _targetIpkController.dispose();
    _targetSksController.dispose();
    super.dispose();
  }

  void _initializeFields(Map<String, dynamic> user) {
    if (_initialized) return;
    _initialized = true;

    final profil = user['profil'] is Map<String, dynamic>
        ? Map<String, dynamic>.from(user['profil'] as Map)
        : <String, dynamic>{};

    _namaInstitusiController.text = profil['nama_institusi']?.toString() ?? '';
    _nimNisController.text = profil['nim_nis']?.toString() ?? '';
    _noHpController.text = profil['no_hp']?.toString() ?? '';
    _targetIpkController.text = profil['target_ipk']?.toString() ?? '';
    _targetSksController.text = profil['target_sks']?.toString() ?? '';
    _jenisInstitusi =
        profil['jenis_institusi']?.toString() ?? 'perguruan_tinggi';
  }

  Future<void> _saveProfile() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    final namaInstitusi = _namaInstitusiController.text.trim();
    final nimNis = _nimNisController.text.trim();
    final noHp = _noHpController.text.trim();
    final targetIpk = _targetIpkController.text.trim().isEmpty
        ? null
        : double.tryParse(_targetIpkController.text.trim());
    final targetSks = _targetSksController.text.trim().isEmpty
        ? null
        : int.tryParse(_targetSksController.text.trim());

    final success = await ref
        .read(profileProvider.notifier)
        .updateProfile(
          nimNis: nimNis.isEmpty ? null : nimNis,
          noHp: noHp.isEmpty ? null : noHp,
          namaInstitusi: namaInstitusi,
          jenisInstitusi: _jenisInstitusi,
          targetIpk: targetIpk,
          targetSks: targetSks,
        );

    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Profil berhasil diperbarui')),
      );
      context.go('/profile');
    }
  }

  @override
  Widget build(BuildContext context) {
    final profileState = ref.watch(profileProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Sunting Profil'), centerTitle: true),
      body: profileState.when(
        data: (user) {
          _initializeFields(user);

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  TextFormField(
                    controller: _namaInstitusiController,
                    decoration: const InputDecoration(
                      labelText: 'Nama Institusi',
                    ),
                    validator: (value) {
                      if (value == null || value.trim().isEmpty) {
                        return 'Nama institusi wajib diisi';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<String>(
                    initialValue: _jenisInstitusi,
                    decoration: const InputDecoration(
                      labelText: 'Jenis Institusi',
                    ),
                    items: const [
                      DropdownMenuItem(
                        value: 'perguruan_tinggi',
                        child: Text('Perguruan Tinggi'),
                      ),
                      DropdownMenuItem(
                        value: 'sekolah',
                        child: Text('Sekolah'),
                      ),
                    ],
                    onChanged: (value) {
                      if (value != null) {
                        setState(() {
                          _jenisInstitusi = value;
                        });
                      }
                    },
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _nimNisController,
                    decoration: const InputDecoration(labelText: 'NIM / NIS'),
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _noHpController,
                    decoration: const InputDecoration(labelText: 'No. HP'),
                    keyboardType: TextInputType.phone,
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _targetIpkController,
                    decoration: const InputDecoration(labelText: 'Target IPK'),
                    keyboardType: const TextInputType.numberWithOptions(
                      decimal: true,
                    ),
                    validator: (value) {
                      if (value == null || value.trim().isEmpty) {
                        return null;
                      }
                      final parsed = double.tryParse(value.trim());
                      if (parsed == null) {
                        return 'Masukkan angka yang valid';
                      }
                      if (parsed < 0 || parsed > 4.0) {
                        return 'Target IPK harus antara 0 dan 4.00';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _targetSksController,
                    decoration: const InputDecoration(labelText: 'Target SKS'),
                    keyboardType: TextInputType.number,
                    validator: (value) {
                      if (value == null || value.trim().isEmpty) {
                        return null;
                      }
                      final parsed = int.tryParse(value.trim());
                      if (parsed == null) {
                        return 'Masukkan angka yang valid';
                      }
                      if (parsed < 1 || parsed > 200) {
                        return 'Target SKS harus antara 1 dan 200';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 24),
                  GradientButton(
                    onPressed: _saveProfile,
                    child: const Padding(
                      padding: EdgeInsets.symmetric(vertical: 16),
                      child: Text('Simpan Perubahan'),
                    ),
                  ),
                ],
              ),
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
