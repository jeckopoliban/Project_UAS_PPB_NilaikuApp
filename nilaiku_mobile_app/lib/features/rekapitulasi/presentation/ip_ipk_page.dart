import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/buttons.dart';
import '../../profile/providers/profile_provider.dart';
import '../../rekapitulasi/providers/rekapitulasi_provider.dart';

class IpIpkPage extends ConsumerStatefulWidget {
  const IpIpkPage({super.key});

  @override
  ConsumerState<IpIpkPage> createState() => _IpIpkPageState();
}

class _IpIpkPageState extends ConsumerState<IpIpkPage> {
  final TextEditingController _targetIpkController = TextEditingController();
  bool _initializedTarget = false;
  bool _isSaving = false;

  @override
  void dispose() {
    _targetIpkController.dispose();
    super.dispose();
  }

  void _initializeTarget(Map<String, dynamic> profil) {
    if (!_initializedTarget) {
      final targetIpk = profil['target_ipk'];
      if (targetIpk != null) {
        _targetIpkController.text = targetIpk.toString();
      }
      _initializedTarget = true;
    }
  }

  Future<void> _saveTargetIpk(Map<String, dynamic> profil) async {
    final namaInstitusi = profil['nama_institusi']?.toString() ?? '';
    final jenisInstitusi = profil['jenis_institusi']?.toString() ?? '';
    final targetText = _targetIpkController.text.trim().replaceAll(',', '.');
    final targetIpk = double.tryParse(targetText);

    if (targetText.isEmpty ||
        targetIpk == null ||
        targetIpk < 0 ||
        targetIpk > 4) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Masukkan target IPK antara 0.00 dan 4.00.'),
        ),
      );
      return;
    }

    if (namaInstitusi.isEmpty || jenisInstitusi.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Profil institusi belum lengkap.')),
      );
      return;
    }

    setState(() {
      _isSaving = true;
    });

    final success = await ref
        .read(profileProvider.notifier)
        .updateProfile(
          namaInstitusi: namaInstitusi,
          jenisInstitusi: jenisInstitusi,
          targetIpk: targetIpk,
        );

    setState(() {
      _isSaving = false;
    });

    if (success) {
      await ref.read(profileProvider.notifier).refresh();
      await ref.read(ipIpkProvider.notifier).refresh();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Target IPK berhasil disimpan.')),
        );
      }
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Gagal menyimpan target IPK.')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final profileState = ref.watch(profileProvider);
    final ipIpkState = ref.watch(ipIpkProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('IP / IPK'), centerTitle: true),
      body: ipIpkState.when(
        data: (data) {
          if (data.isEmpty) {
            return const Center(child: Text('Belum ada data IP/IPK.'));
          }

          return profileState.when(
            data: (user) {
              final profil = user['profil'] is Map<String, dynamic>
                  ? Map<String, dynamic>.from(user['profil'] as Map)
                  : <String, dynamic>{};
              _initializeTarget(profil);

              return _buildContent(data, profil);
            },
            loading: () => _buildContent(data, null),
            error: (_, _) => _buildContent(data, null),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) =>
            Center(child: Text('Gagal memuat IP/IPK: $error')),
      ),
    );
  }

  Widget _buildContent(
    Map<String, dynamic> data,
    Map<String, dynamic>? profil,
  ) {
    final ipkString = data['ipk']?.toString() ?? '-';
    final ipkValue = double.tryParse(data['ipk']?.toString() ?? '');
    final targetIpkValue = profil != null
        ? double.tryParse(profil['target_ipk']?.toString() ?? '')
        : null;
    final targetIpkString = targetIpkValue != null
        ? targetIpkValue.toStringAsFixed(2)
        : '-';
    final difference = ipkValue != null && targetIpkValue != null
        ? ipkValue - targetIpkValue
        : null;
    final differenceLabel = difference != null
        ? (difference >= 0
              ? '+${difference.toStringAsFixed(2)}'
              : difference.toStringAsFixed(2))
        : '-';

    final breakdown = data['breakdown'] as List<dynamic>? ?? [];

    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: SizedBox(
                  height: 320,
                  child: _InfoCard(
                    title: 'IP Sementara',
                    content: Text(
                      ipkString,
                      style: const TextStyle(
                        fontSize: 32,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    subtitle:
                        'Indeks nilai sementara berdasarkan entri saat ini',
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: SizedBox(
                  height: 320,
                  child: _InfoCard(
                    title: 'Target IPK',
                    content: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        TextField(
                          controller: _targetIpkController,
                          keyboardType: const TextInputType.numberWithOptions(
                            decimal: true,
                          ),
                          decoration: const InputDecoration(
                            hintText: '3.70',
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.all(
                                Radius.circular(16),
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: 12),
                        SizedBox(
                          width: double.infinity,
                          child: GradientButton(
                            onPressed: _isSaving
                                ? null
                                : () => _saveTargetIpk(profil ?? {}),
                            child: _isSaving
                                ? const SizedBox(
                                    height: 18,
                                    width: 18,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2,
                                      color: Colors.white,
                                    ),
                                  )
                                : const Text('Simpan Target'),
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Target IPK saat ini: $targetIpkString',
                          style: const TextStyle(fontSize: 13),
                        ),
                      ],
                    ),
                    subtitle: 'Tentukan target IPK Anda dan simpan perubahan',
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: SizedBox(
                  height: 320,
                  child: _InfoCard(
                    title: 'Selisih Target',
                    content: Text(
                      differenceLabel,
                      style: TextStyle(
                        fontSize: 32,
                        fontWeight: FontWeight.bold,
                        color: difference == null
                            ? AppColors.textHeading
                            : difference >= 0
                            ? AppColors.successGreen
                            : AppColors.rose,
                      ),
                    ),
                    subtitle: 'Selisih dari target IPK',
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          const Text(
            'Breakdown per Semester',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
          ),
          const SizedBox(height: 12),
          Expanded(
            child: ListView.separated(
              itemCount: breakdown.length,
              separatorBuilder: (context, index) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                final semester = breakdown[index] as Map<String, dynamic>;
                return Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          semester['nama_tahun']?.toString() ?? '-',
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Row(
                          children: [
                            _InfoChip(
                              'SKS',
                              semester['total_sks']?.toString() ?? '-',
                            ),
                            _InfoChip('IP', semester['ip']?.toString() ?? '-'),
                          ],
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _InfoCard extends StatelessWidget {
  final String title;
  final Widget content;
  final String subtitle;

  const _InfoCard({
    required this.title,
    required this.content,
    required this.subtitle,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              title,
              style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: AppColors.textMuted,
              ),
            ),
            const SizedBox(height: 12),
            content,
            const SizedBox(height: 12),
            Text(
              subtitle,
              style: const TextStyle(fontSize: 12, color: AppColors.textMuted),
            ),
          ],
        ),
      ),
    );
  }
}

class _InfoChip extends StatelessWidget {
  final String label;
  final String value;

  const _InfoChip(this.label, this.value);

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      margin: const EdgeInsets.only(right: 8),
      decoration: BoxDecoration(
        color: AppColors.bgPage,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text('$label: $value', style: const TextStyle(fontSize: 13)),
    );
  }
}
