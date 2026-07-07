import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/buttons.dart';
import '../providers/semester_provider.dart';

class SemesterListPage extends ConsumerWidget {
  const SemesterListPage({super.key});

  Future<void> _showSemesterForm(
    BuildContext context,
    WidgetRef ref, {
    Map<String, dynamic>? semester,
  }) async {
    final namaController = TextEditingController(
      text: semester?['nama'] as String? ?? '',
    );
    var statusAktif = semester?['status_aktif'] as bool? ?? true;

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
              return Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Text(
                    semester == null ? 'Tambah Semester' : 'Edit Semester',
                    style: Theme.of(context).textTheme.headlineSmall,
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: namaController,
                    decoration: const InputDecoration(
                      labelText: 'Nama Semester',
                      hintText: 'Contoh: 2025/2026 Genap',
                    ),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      const Text(
                        'Status Aktif',
                        style: TextStyle(fontWeight: FontWeight.w600),
                      ),
                      const Spacer(),
                      Switch(
                        value: statusAktif,
                        onChanged: (value) =>
                            setState(() => statusAktif = value),
                        activeThumbColor: AppColors.brandBlue,
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),
                  GradientButton(
                    label: semester == null ? 'Simpan' : 'Perbarui',
                    onPressed: () async {
                      final nama = namaController.text.trim();
                      if (nama.isEmpty) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Nama semester wajib diisi.'),
                          ),
                        );
                        return;
                      }

                      final notifier = ref.read(semesterProvider.notifier);
                      if (semester == null) {
                        await notifier.create(nama, statusAktif);
                      } else {
                        await notifier.updateSemester(
                          semester['id'] as int,
                          nama,
                          statusAktif,
                        );
                      }

                      if (context.mounted) {
                        Navigator.of(context).pop();
                      }
                    },
                  ),
                  const SizedBox(height: 8),
                ],
              );
            },
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final semesterState = ref.watch(semesterProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Semester'),
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
      body: semesterState.when(
        data: (items) {
          if (items.isEmpty) {
            return const Center(child: Text('Belum ada data semester.'));
          }
          return ListView.separated(
            padding: const EdgeInsets.all(16),
            itemCount: items.length,
            separatorBuilder: (context, index) => const SizedBox(height: 12),
            itemBuilder: (context, index) {
              final semester = items[index];
              final isActive = semester['status_aktif'] as bool? ?? false;
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
                              semester['nama'] as String? ?? '-',
                              style: const TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 6,
                            ),
                            decoration: BoxDecoration(
                              color: isActive
                                  ? AppColors.successGreen.withValues(
                                      alpha: 0.15,
                                    )
                                  : AppColors.textMuted.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(999),
                            ),
                            child: Text(
                              isActive ? 'Aktif' : 'Nonaktif',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                                color: isActive
                                    ? AppColors.successGreen
                                    : AppColors.textMuted,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Row(
                        children: [
                          IconButton(
                            onPressed: () => _showSemesterForm(
                              context,
                              ref,
                              semester: semester,
                            ),
                            icon: const Icon(
                              Icons.edit,
                              color: AppColors.brandBlue,
                            ),
                            tooltip: 'Edit',
                          ),
                          const SizedBox(width: 8),
                          IconButton(
                            onPressed: () async {
                              final confirmed = await showDialog<bool>(
                                context: context,
                                builder: (context) {
                                  return AlertDialog(
                                    title: const Text('Hapus Semester'),
                                    content: const Text(
                                      'Yakin ingin menghapus semester ini?',
                                    ),
                                    actions: [
                                      TextButton(
                                        onPressed: () =>
                                            Navigator.of(context).pop(false),
                                        child: const Text('Batal'),
                                      ),
                                      GradientButton(
                                        onPressed: () =>
                                            Navigator.of(context).pop(true),
                                        width: 100,
                                        child: const Text('Hapus'),
                                      ),
                                    ],
                                  );
                                },
                              );
                              if (confirmed == true) {
                                await ref
                                    .read(semesterProvider.notifier)
                                    .deleteSemester(semester['id'] as int);
                              }
                            },
                            icon: const Icon(
                              Icons.delete,
                              color: AppColors.rose,
                            ),
                            tooltip: 'Hapus',
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
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _showSemesterForm(context, ref),
        icon: const Icon(Icons.add),
        label: const Text('Tambah Semester'),
      ),
    );
  }
}
