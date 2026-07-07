import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/buttons.dart';
import '../../semester/providers/semester_provider.dart';
import '../providers/mata_kuliah_provider.dart';

class MataKuliahListPage extends ConsumerStatefulWidget {
  const MataKuliahListPage({super.key});

  @override
  ConsumerState<MataKuliahListPage> createState() => _MataKuliahListPageState();
}

class _MataKuliahListPageState extends ConsumerState<MataKuliahListPage> {
  int? _selectedSemesterId;

  int? _getDefaultSemesterId(List<Map<String, dynamic>> semesters) {
    if (semesters.isEmpty) return null;
    final active = semesters
        .where((item) => item['status_aktif'] == true)
        .toList();
    if (active.isNotEmpty) return active.first['id'] as int?;
    return semesters.first['id'] as int?;
  }

  Future<void> _openForm({Map<String, dynamic>? mataKuliah}) async {
    final namaController = TextEditingController(
      text: mataKuliah?['nama_mk'] as String? ?? '',
    );
    final sksController = TextEditingController(
      text: mataKuliah != null ? '${mataKuliah['sks']}' : '3',
    );
    final semesterItems = ref.read(semesterProvider).asData?.value ?? [];
    var selectedSemesterId =
        mataKuliah?['tahun_akademik_id'] as int? ??
        _selectedSemesterId ??
        _getDefaultSemesterId(semesterItems);

    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return Padding(
          padding: EdgeInsets.only(
            left: 24,
            right: 24,
            top: 24,
            bottom: MediaQuery.of(context).viewInsets.bottom + 24,
          ),
          child: StatefulBuilder(
            builder: (context, setState) {
              final semesterItems =
                  ref.read(semesterProvider).asData?.value ?? [];
              return Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Text(
                    mataKuliah == null
                        ? 'Tambah Mata Kuliah'
                        : 'Edit Mata Kuliah',
                    style: Theme.of(context).textTheme.headlineSmall,
                  ),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<int?>(
                    initialValue: selectedSemesterId,
                    items: semesterItems
                        .map(
                          (item) => DropdownMenuItem<int?>(
                            value: item['id'] as int?,
                            child: Text(item['nama'] as String? ?? '-'),
                          ),
                        )
                        .toList(),
                    onChanged: (value) =>
                        setState(() => selectedSemesterId = value),
                    decoration: const InputDecoration(labelText: 'Semester'),
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: namaController,
                    decoration: const InputDecoration(
                      labelText: 'Nama Mata Kuliah',
                    ),
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: sksController,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(labelText: 'SKS'),
                  ),
                  const SizedBox(height: 24),
                  GradientButton(
                    label: mataKuliah == null ? 'Simpan' : 'Perbarui',
                    onPressed: () async {
                      final namaMk = namaController.text.trim();
                      final sks = int.tryParse(sksController.text.trim()) ?? 0;
                      if (selectedSemesterId == null ||
                          namaMk.isEmpty ||
                          sks <= 0) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text(
                              'Semua field wajib diisi dengan benar.',
                            ),
                          ),
                        );
                        return;
                      }
                      final notifier = ref.read(mataKuliahProvider.notifier);
                      if (mataKuliah == null) {
                        await notifier.create(
                          tahunAkademikId: selectedSemesterId!,
                          namaMk: namaMk,
                          sks: sks,
                        );
                      } else {
                        await notifier.updateMataKuliah(
                          id: mataKuliah['id'] as int,
                          tahunAkademikId: selectedSemesterId!,
                          namaMk: namaMk,
                          sks: sks,
                        );
                      }
                      if (context.mounted) {
                        Navigator.of(context).pop();
                      }
                    },
                  ),
                ],
              );
            },
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final semesterState = ref.watch(semesterProvider);
    final mataKuliahState = ref.watch(mataKuliahProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Mata Kuliah'),
        centerTitle: true,
        leading: IconButton(
          tooltip: 'Kembali',
          icon: const Icon(Icons.arrow_back),
          onPressed: () async {
            if (!await Navigator.of(context).maybePop() && context.mounted) {
              context.go('/dashboard');
            }
          },
        ),
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: semesterState.when(
              data: (items) {
                return DropdownButtonFormField<int?>(
                  initialValue: _selectedSemesterId,
                  items: [
                    const DropdownMenuItem<int?>(
                      value: null,
                      child: Text('Semua Semester'),
                    ),
                    ...items.map((item) {
                      return DropdownMenuItem<int?>(
                        value: item['id'] as int,
                        child: Text(item['nama'] as String? ?? '-'),
                      );
                    }),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedSemesterId = value;
                    });
                    ref
                        .read(mataKuliahProvider.notifier)
                        .refresh(tahunAkademikId: value);
                  },
                  decoration: const InputDecoration(
                    labelText: 'Filter Semester',
                  ),
                );
              },
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (error, stack) =>
                  Center(child: Text('Gagal memuat semester: $error')),
            ),
          ),
          Expanded(
            child: mataKuliahState.when(
              data: (items) {
                if (items.isEmpty) {
                  return const Center(child: Text('Belum ada mata kuliah.'));
                }
                return ListView.separated(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 8,
                  ),
                  itemCount: items.length,
                  separatorBuilder: (context, index) =>
                      const SizedBox(height: 12),
                  itemBuilder: (context, index) {
                    final item = items[index];
                    final komponen = item['nama_komponen_penilaian'] as String?;
                    return Card(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              item['nama_mk'] as String? ?? '-',
                              style: const TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            const SizedBox(height: 8),
                            Row(
                              children: [
                                Text('SKS: ${item['sks'] ?? '-'}'),
                                const SizedBox(width: 16),
                                Expanded(
                                  child: Text(
                                    'Semester: ${item['tahun_akademik'] is Map ? (item['tahun_akademik']['nama'] ?? item['tahun_akademik_id']) : item['tahun_akademik_id']}',
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Text('Komp. Penilaian: ${komponen ?? '-'}'),
                            const SizedBox(height: 12),
                            Row(
                              children: [
                                const Spacer(),
                                IconButton(
                                  onPressed: () => _openForm(mataKuliah: item),
                                  icon: const Icon(
                                    Icons.edit,
                                    color: AppColors.brandBlue,
                                  ),
                                ),
                                IconButton(
                                  onPressed: () async {
                                    final confirmed = await showDialog<bool>(
                                      context: context,
                                      builder: (context) {
                                        return AlertDialog(
                                          title: const Text(
                                            'Hapus Mata Kuliah',
                                          ),
                                          content: const Text(
                                            'Yakin ingin menghapus mata kuliah ini?',
                                          ),
                                          actions: [
                                            TextButton(
                                              onPressed: () => Navigator.of(
                                                context,
                                              ).pop(false),
                                              child: const Text('Batal'),
                                            ),
                                            GradientButton(
                                              onPressed: () => Navigator.of(
                                                context,
                                              ).pop(true),
                                              width: 100,
                                              child: const Text('Hapus'),
                                            ),
                                          ],
                                        );
                                      },
                                    );
                                    if (confirmed == true) {
                                      await ref
                                          .read(mataKuliahProvider.notifier)
                                          .deleteMataKuliah(item['id'] as int);
                                    }
                                  },
                                  icon: const Icon(
                                    Icons.delete,
                                    color: AppColors.rose,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                );
              },
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (error, stack) =>
                  Center(child: Text('Terjadi kesalahan: $error')),
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _openForm(),
        icon: const Icon(Icons.add),
        label: const Text('Tambah Mata Kuliah'),
      ),
    );
  }
}
