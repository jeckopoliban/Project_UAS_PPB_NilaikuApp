import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/buttons.dart';
import '../../semester/providers/semester_provider.dart';
import '../providers/grading_provider.dart';

class GradingListPage extends ConsumerStatefulWidget {
  const GradingListPage({super.key});

  @override
  ConsumerState<GradingListPage> createState() => _GradingListPageState();
}

class _GradingListPageState extends ConsumerState<GradingListPage> {
  final _templateNameController = TextEditingController();
  final _items = <Map<String, dynamic>>[];
  String? _createErrorMessage;

  void _addItem() {
    setState(() {
      _items.add({
        'batas_bawah': '',
        'batas_atas': '',
        'huruf_mutu': '',
        'indeks': '',
      });
    });
  }

  Future<void> _showTemplateDialog({Map<String, dynamic>? template}) async {
    _templateNameController.text = template?['nama_template'] as String? ?? '';
    _items.clear();
    _createErrorMessage = null;

    if (template != null) {
      final existingItems = template['items'] as List<dynamic>? ?? [];
      if (existingItems.isEmpty) {
        _addItem();
      } else {
        for (final existing in existingItems) {
          _items.add({
            'batas_bawah': existing['batas_bawah']?.toString() ?? '',
            'batas_atas': existing['batas_atas']?.toString() ?? '',
            'huruf_mutu': existing['huruf_mutu']?.toString() ?? '',
            'indeks': existing['indeks']?.toString() ?? '',
          });
        }
      }
    } else {
      _addItem();
    }

    await showDialog<void>(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: Text(
            template != null ? 'Edit Template Grading' : 'Buat Skala Sendiri',
          ),
          content: StatefulBuilder(
            builder: (context, setState) {
              return SizedBox(
                width: double.maxFinite,
                child: SingleChildScrollView(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      TextField(
                        controller: _templateNameController,
                        decoration: const InputDecoration(
                          labelText: 'Nama Template',
                        ),
                      ),
                      if (_createErrorMessage != null) ...[
                        const SizedBox(height: 12),
                        Text(
                          _createErrorMessage!,
                          style: const TextStyle(color: AppColors.rose),
                        ),
                      ],
                      const SizedBox(height: 16),
                      ...List.generate(_items.length, (index) {
                        final item = _items[index];
                        return Card(
                          margin: const EdgeInsets.only(bottom: 12),
                          child: Padding(
                            padding: const EdgeInsets.all(12),
                            child: Column(
                              children: [
                                Row(
                                  children: [
                                    Expanded(
                                      child: TextField(
                                        decoration: const InputDecoration(
                                          labelText: 'Batas Bawah',
                                        ),
                                        keyboardType: TextInputType.number,
                                        controller:
                                            TextEditingController(
                                                text:
                                                    item['batas_bawah']
                                                        ?.toString() ??
                                                    '',
                                              )
                                              ..selection =
                                                  TextSelection.collapsed(
                                                    offset:
                                                        item['batas_bawah']
                                                            ?.toString()
                                                            .length ??
                                                        0,
                                                  ),
                                        onChanged: (value) {
                                          setState(
                                            () => item['batas_bawah'] = value,
                                          );
                                        },
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: TextField(
                                        decoration: const InputDecoration(
                                          labelText: 'Batas Atas',
                                        ),
                                        keyboardType: TextInputType.number,
                                        controller:
                                            TextEditingController(
                                                text:
                                                    item['batas_atas']
                                                        ?.toString() ??
                                                    '',
                                              )
                                              ..selection =
                                                  TextSelection.collapsed(
                                                    offset:
                                                        item['batas_atas']
                                                            ?.toString()
                                                            .length ??
                                                        0,
                                                  ),
                                        onChanged: (value) {
                                          setState(
                                            () => item['batas_atas'] = value,
                                          );
                                        },
                                      ),
                                    ),
                                  ],
                                ),
                                if (item['batas_bawah'] != '' &&
                                    item['batas_atas'] != '')
                                  Builder(
                                    builder: (context) {
                                      final bawah =
                                          double.tryParse(
                                            item['batas_bawah'].toString(),
                                          ) ??
                                          0;
                                      final atas =
                                          double.tryParse(
                                            item['batas_atas'].toString(),
                                          ) ??
                                          0;
                                      if (bawah < atas) {
                                        return const SizedBox.shrink();
                                      }
                                      return Padding(
                                        padding: const EdgeInsets.only(top: 6),
                                        child: Text(
                                          'Batas bawah harus lebih kecil dari batas atas.',
                                          style: const TextStyle(
                                            color: AppColors.rose,
                                            fontSize: 12,
                                          ),
                                        ),
                                      );
                                    },
                                  ),
                                const SizedBox(height: 8),
                                Row(
                                  children: [
                                    Expanded(
                                      child: TextField(
                                        decoration: const InputDecoration(
                                          labelText: 'Huruf Mutu',
                                        ),
                                        controller:
                                            TextEditingController(
                                                text:
                                                    item['huruf_mutu']
                                                        ?.toString() ??
                                                    '',
                                              )
                                              ..selection =
                                                  TextSelection.collapsed(
                                                    offset:
                                                        item['huruf_mutu']
                                                            ?.toString()
                                                            .length ??
                                                        0,
                                                  ),
                                        onChanged: (value) {
                                          setState(
                                            () => item['huruf_mutu'] = value,
                                          );
                                        },
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: TextField(
                                        decoration: const InputDecoration(
                                          labelText: 'Indeks',
                                        ),
                                        keyboardType: TextInputType.number,
                                        controller:
                                            TextEditingController(
                                                text:
                                                    item['indeks']
                                                        ?.toString() ??
                                                    '',
                                              )
                                              ..selection =
                                                  TextSelection.collapsed(
                                                    offset:
                                                        item['indeks']
                                                            ?.toString()
                                                            .length ??
                                                        0,
                                                  ),
                                        onChanged: (value) {
                                          setState(
                                            () => item['indeks'] = value,
                                          );
                                        },
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 8),
                                Align(
                                  alignment: Alignment.centerRight,
                                  child: IconButton(
                                    onPressed: () {
                                      setState(() {
                                        _items.removeAt(index);
                                      });
                                    },
                                    icon: const Icon(
                                      Icons.delete,
                                      color: AppColors.rose,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      }),
                      const SizedBox(height: 12),
                      GradientButton(
                        onPressed: () {
                          setState(() {
                            _items.add({
                              'batas_bawah': '',
                              'batas_atas': '',
                              'huruf_mutu': '',
                              'indeks': '',
                            });
                          });
                        },
                        child: const Text('Tambah Baris'),
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Batal'),
            ),
            GradientButton(
              onPressed: () async {
                final namaTemplate = _templateNameController.text.trim();
                if (namaTemplate.isEmpty || _items.isEmpty) {
                  setState(
                    () => _createErrorMessage =
                        'Nama template dan setidaknya satu baris wajib diisi.',
                  );
                  return;
                }

                final hasInvalidRange = _items.any((item) {
                  final bawah =
                      double.tryParse(item['batas_bawah']?.toString() ?? '') ??
                      0;
                  final atas =
                      double.tryParse(item['batas_atas']?.toString() ?? '') ??
                      0;
                  return bawah >= atas;
                });

                if (hasInvalidRange) {
                  setState(
                    () => _createErrorMessage =
                        'Setiap baris harus memiliki batas bawah yang lebih kecil dari batas atas.',
                  );
                  return;
                }

                final parsedItems = _items
                    .map(
                      (item) => {
                        'batas_bawah':
                            double.tryParse(
                              item['batas_bawah']?.toString() ?? '',
                            ) ??
                            0,
                        'batas_atas':
                            double.tryParse(
                              item['batas_atas']?.toString() ?? '',
                            ) ??
                            0,
                        'huruf_mutu': item['huruf_mutu']?.toString() ?? '',
                        'indeks':
                            double.tryParse(item['indeks']?.toString() ?? '') ??
                            0,
                      },
                    )
                    .toList();

                try {
                  if (template != null) {
                    await ref
                        .read(gradingProvider.notifier)
                        .updateTemplate(
                          template['id'] as int,
                          namaTemplate,
                          parsedItems,
                        );
                  } else {
                    await ref
                        .read(gradingProvider.notifier)
                        .create(namaTemplate, parsedItems);
                  }
                  if (context.mounted) Navigator.of(context).pop();
                } catch (error) {
                  if (!context.mounted) return;
                  final message = error is Exception
                      ? error.toString()
                      : 'Gagal menyimpan template grading.';
                  setState(() => _createErrorMessage = message);
                }
              },
              child: const Text('Simpan'),
            ),
          ],
        );
      },
    );
  }

  Future<void> _showApplyDialog(int templateId) async {
    final semesterState = ref.read(semesterProvider).asData?.value ?? [];
    final selectedIds = <int>{};
    var applyToAll = false;

    await showDialog<void>(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setState) {
            return AlertDialog(
              title: const Text('Terapkan ke Semester'),
              content: SizedBox(
                width: double.maxFinite,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    CheckboxListTile(
                      value: applyToAll,
                      onChanged: (value) =>
                          setState(() => applyToAll = value ?? false),
                      title: const Text('Terapkan ke semua semester'),
                    ),
                    if (!applyToAll)
                      ...semesterState.map(
                        (semester) => CheckboxListTile(
                          value: selectedIds.contains(semester['id'] as int),
                          onChanged: (value) {
                            setState(() {
                              final id = semester['id'] as int;
                              if (value == true) {
                                selectedIds.add(id);
                              } else {
                                selectedIds.remove(id);
                              }
                            });
                          },
                          title: Text(semester['nama'] as String? ?? '-'),
                        ),
                      ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.of(context).pop(),
                  child: const Text('Batal'),
                ),
                GradientButton(
                  onPressed: () async {
                    await ref
                        .read(gradingProvider.notifier)
                        .apply(
                          templateId,
                          tahunAkademikIds: applyToAll
                              ? null
                              : selectedIds.toList(),
                          applyToAll: applyToAll,
                        );
                    if (context.mounted) Navigator.of(context).pop();
                  },
                  width: 120,
                  child: const Text('Terapkan'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  Future<void> _confirmDeleteTemplate(int templateId) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('Hapus Template'),
          content: const Text(
            'Apakah Anda yakin ingin menghapus template grading ini?',
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(false),
              child: const Text('Batal'),
            ),
            GradientButton(
              onPressed: () => Navigator.of(context).pop(true),
              width: 100,
              child: const Text('Hapus'),
            ),
          ],
        );
      },
    );

    if (confirmed == true) {
      await ref.read(gradingProvider.notifier).delete(templateId);
    }
  }

  @override
  Widget build(BuildContext context) {
    final gradingState = ref.watch(gradingProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Grading'), centerTitle: true),
      body: gradingState.when(
        data: (items) {
          if (items.isEmpty) {
            return const Center(child: Text('Belum ada template grading.'));
          }
          final systemTemplates = items
              .where((item) => item['mahasiswa_id'] == null)
              .toList();
          final myTemplates = items
              .where((item) => item['mahasiswa_id'] != null)
              .toList();
          Widget buildSection(
            String title,
            List<Map<String, dynamic>> templates,
          ) {
            return Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 12,
                  ),
                  child: Text(
                    title,
                    style: Theme.of(context).textTheme.headlineSmall,
                  ),
                ),
                ...templates.map((template) {
                  final items = template['items'] as List<dynamic>? ?? [];
                  final isMyTemplate = template['mahasiswa_id'] != null;
                  return Card(
                    margin: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 8,
                    ),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          ExpansionTile(
                            initiallyExpanded: false,
                            title: Row(
                              children: [
                                Expanded(
                                  child: Text(
                                    template['nama_template'] as String? ?? '-',
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
                                    color: AppColors.brandBlue.withValues(
                                      alpha: 0.12,
                                    ),
                                    borderRadius: BorderRadius.circular(999),
                                  ),
                                  child: Text(
                                    'Items: ${items.length}',
                                    style: const TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            children: [
                              if (items.isEmpty)
                                const Padding(
                                  padding: EdgeInsets.symmetric(
                                    vertical: 8,
                                    horizontal: 16,
                                  ),
                                  child: Text('Tidak ada detail item.'),
                                )
                              else
                                ...items.map((item) {
                                  final batasBawah = item['batas_bawah'];
                                  final batasAtas = item['batas_atas'];
                                  final hurufMutu = item['huruf_mutu'];
                                  final indeks = item['indeks'];
                                  return Padding(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 16,
                                      vertical: 8,
                                    ),
                                    child: Text(
                                      '$batasBawah - $batasAtas = $hurufMutu ($indeks)',
                                      style: const TextStyle(fontSize: 14),
                                    ),
                                  );
                                }),
                              const Divider(),
                              Row(
                                children: [
                                  Expanded(
                                    child: GradientButton(
                                      onPressed: () => _showApplyDialog(
                                        template['id'] as int,
                                      ),
                                      child: Row(
                                        mainAxisAlignment:
                                            MainAxisAlignment.center,
                                        mainAxisSize: MainAxisSize.min,
                                        children: const [
                                          Icon(Icons.send, color: Colors.white),
                                          SizedBox(width: 8),
                                          Text('Terapkan ke Semester'),
                                        ],
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              if (isMyTemplate) ...[
                                const SizedBox(height: 12),
                                Row(
                                  children: [
                                    Expanded(
                                      child: OutlinedButton.icon(
                                        onPressed: () => _showTemplateDialog(
                                          template: template,
                                        ),
                                        icon: const Icon(
                                          Icons.edit,
                                          color: AppColors.brandBlue,
                                        ),
                                        label: const Text('Edit'),
                                        style: OutlinedButton.styleFrom(
                                          foregroundColor: AppColors.brandBlue,
                                          side: BorderSide(
                                            color: AppColors.brandBlue
                                                .withValues(alpha: 0.32),
                                          ),
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: OutlinedButton.icon(
                                        onPressed: () => _confirmDeleteTemplate(
                                          template['id'] as int,
                                        ),
                                        icon: const Icon(
                                          Icons.delete,
                                          color: AppColors.rose,
                                        ),
                                        label: const Text('Hapus'),
                                        style: OutlinedButton.styleFrom(
                                          foregroundColor: AppColors.rose,
                                          side: BorderSide(
                                            color: AppColors.rose.withValues(
                                              alpha: 0.32,
                                            ),
                                          ),
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ],
                          ),
                        ],
                      ),
                    ),
                  );
                }),
              ],
            );
          }

          return SingleChildScrollView(
            child: Column(
              children: [
                buildSection('Template Sistem', systemTemplates),
                buildSection('Template Saya', myTemplates),
              ],
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) =>
            Center(child: Text('Terjadi kesalahan: $error')),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _showTemplateDialog(),
        icon: const Icon(Icons.add),
        label: const Text('Buat Skala Sendiri'),
      ),
    );
  }
}
