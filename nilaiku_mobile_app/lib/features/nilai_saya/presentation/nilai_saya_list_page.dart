import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../semester/providers/semester_provider.dart';
import '../providers/nilai_saya_provider.dart';

class NilaiSayaListPage extends ConsumerStatefulWidget {
  const NilaiSayaListPage({super.key});

  @override
  ConsumerState<NilaiSayaListPage> createState() => _NilaiSayaListPageState();
}

class _NilaiSayaListPageState extends ConsumerState<NilaiSayaListPage> {
  int? _selectedSemesterId;

  @override
  Widget build(BuildContext context) {
    final semesterState = ref.watch(semesterProvider);
    final nilaiSayaState = ref.watch(nilaiSayaProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Nilai Saya'), centerTitle: true),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: semesterState.when(
              data: (items) {
                final defaultSemester = items.firstWhere(
                  (item) => item['status_aktif'] == true,
                  orElse: () =>
                      items.isNotEmpty ? items.first : <String, dynamic>{},
                );
                final initialSemesterId =
                    _selectedSemesterId ?? (defaultSemester['id'] as int?);

                if (initialSemesterId != null && _selectedSemesterId == null) {
                  WidgetsBinding.instance.addPostFrameCallback((_) {
                    if (!mounted) return;
                    setState(() => _selectedSemesterId = initialSemesterId);
                    ref
                        .read(nilaiSayaProvider.notifier)
                        .refresh(tahunAkademikId: initialSemesterId);
                  });
                }

                return DropdownButtonFormField<int?>(
                  initialValue: initialSemesterId,
                  items: items
                      .map(
                        (item) => DropdownMenuItem<int?>(
                          value: item['id'] as int,
                          child: Text(item['nama'] as String? ?? '-'),
                        ),
                      )
                      .toList(),
                  onChanged: (value) {
                    setState(() => _selectedSemesterId = value);
                    ref
                        .read(nilaiSayaProvider.notifier)
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
            child: nilaiSayaState.when(
              data: (items) {
                if (items.isEmpty) {
                  return const Center(child: Text('Belum ada data nilai.'));
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
                    final status =
                        (item['status'] as String? ?? 'Belum Ada Komponen')
                            .toLowerCase();
                    final isComplete =
                        status == 'selesai' || status == 'lengkap';
                    final hasAnyComponent =
                        (item['nama_komponen_penilaian'] as String?)
                            ?.isNotEmpty ==
                        true;
                    final hasExistingGrade =
                        item['nilai_akhir'] != null ||
                        item['grade'] != null ||
                        item['nilai'] != null ||
                        item['huruf_mutu'] != null;
                    return Card(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Expanded(
                                  child: Text(
                                    item['nama_mk'] as String? ?? '-',
                                    style: const TextStyle(
                                      fontSize: 16,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ),
                                if (isComplete)
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 10,
                                      vertical: 6,
                                    ),
                                    decoration: BoxDecoration(
                                      color: AppColors.successGreen.withValues(
                                        alpha: 0.15,
                                      ),
                                      borderRadius: BorderRadius.circular(999),
                                    ),
                                    child: Text(
                                      'Lengkap',
                                      style: TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.w600,
                                        color: AppColors.successGreen,
                                      ),
                                    ),
                                  )
                                else
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 10,
                                      vertical: 6,
                                    ),
                                    decoration: BoxDecoration(
                                      color: AppColors.warningAmber.withValues(
                                        alpha: 0.15,
                                      ),
                                      borderRadius: BorderRadius.circular(999),
                                    ),
                                    child: Text(
                                      'Belum Lengkap',
                                      style: TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.w600,
                                        color: AppColors.warningAmber,
                                      ),
                                    ),
                                  ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Text('SKS: ${item['sks'] ?? '-'}'),
                            const SizedBox(height: 4),
                            Text('Semester: ${item['semester'] ?? '-'}'),
                            const SizedBox(height: 4),
                            Text(
                              'Komponen Penilaian: ${item['komponen_penilaian'] ?? '-'}',
                            ),
                            const SizedBox(height: 12),
                            if (isComplete)
                              Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      'Nilai Akhir: ${item['nilai_akhir'] ?? item['nilai'] ?? '-'}',
                                      style: const TextStyle(
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Text(
                                    'Huruf: ${item['huruf_mutu'] ?? item['grade'] ?? '-'}',
                                  ),
                                ],
                              )
                            else
                              Text(
                                'Status: ${item['status'] ?? 'Belum Lengkap'}',
                              ),
                            const SizedBox(height: 12),
                            Row(
                              children: [
                                ElevatedButton.icon(
                                  onPressed: () async {
                                    final result = await context.push<bool>(
                                      '/mata-kuliah/${item['id']}/nilai',
                                      extra: item['nama_mk'] as String? ?? '',
                                    );

                                    if (result == true && mounted) {
                                      await ref
                                          .read(nilaiSayaProvider.notifier)
                                          .refresh(
                                            tahunAkademikId:
                                                _selectedSemesterId,
                                          );
                                    }
                                  },
                                  icon: Icon(
                                    hasExistingGrade
                                        ? Icons.edit
                                        : hasAnyComponent
                                        ? Icons.play_arrow
                                        : Icons.edit_document,
                                  ),
                                  label: Text(
                                    hasExistingGrade
                                        ? 'Edit Nilai'
                                        : hasAnyComponent
                                        ? 'Lanjutkan Input'
                                        : 'Input Nilai',
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
    );
  }
}
