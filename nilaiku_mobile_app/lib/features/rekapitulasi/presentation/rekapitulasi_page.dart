import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_theme.dart';
import '../../semester/providers/semester_provider.dart';
import '../providers/rekapitulasi_provider.dart';

class RekapitulasiPage extends ConsumerStatefulWidget {
  const RekapitulasiPage({super.key});

  @override
  ConsumerState<RekapitulasiPage> createState() => _RekapitulasiPageState();
}

class _RekapitulasiPageState extends ConsumerState<RekapitulasiPage> {
  int? _selectedSemesterId;
  bool _didLoadInitialData = false;
  bool _isExporting = false;

  void _maybeLoadInitialData(List<Map<String, dynamic>> semesters) {
    if (_didLoadInitialData) return;

    final activeSemester = semesters.firstWhere(
      (item) => item['status_aktif'] == true,
      orElse: () => semesters.first,
    );

    _selectedSemesterId = activeSemester['id'] as int;
    _didLoadInitialData = true;

    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      ref
          .read(rekapitulasiProvider.notifier)
          .refresh(semesterId: _selectedSemesterId!);
      setState(() {});
    });
  }

  Future<void> _exportPdf(int semesterId) async {
    if (_isExporting) return;

    setState(() {
      _isExporting = true;
    });

    try {
      final repository = ref.read(rekapitulasiRepositoryProvider);
      final signedUrl = await repository.getSignedPdfUrl(
        tahunAkademikId: semesterId,
      );

      final launched = await launchUrl(
        Uri.parse(signedUrl),
        mode: LaunchMode.externalApplication,
      );

      if (!launched && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Tidak dapat membuka tautan ekspor PDF.'),
          ),
        );
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Tidak dapat membuka tautan ekspor PDF.'),
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isExporting = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final semesterState = ref.watch(semesterProvider);
    final rekapState = ref.watch(rekapitulasiProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Rekapitulasi Nilai'),
        centerTitle: true,
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: semesterState.when(
              data: (semesters) {
                if (semesters.isEmpty) {
                  return const Text('Belum ada data semester.');
                }

                final defaultSemester = semesters.firstWhere(
                  (item) => item['status_aktif'] == true,
                  orElse: () => semesters.first,
                );
                final defaultId = defaultSemester['id'] as int;

                if (_selectedSemesterId == null) {
                  _maybeLoadInitialData(semesters);
                }

                final selectedSemesterId = _selectedSemesterId ?? defaultId;

                return Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    DropdownButtonFormField<int>(
                      initialValue: selectedSemesterId,
                      items: semesters
                          .map(
                            (item) => DropdownMenuItem<int>(
                              value: item['id'] as int,
                              child: Text(item['nama'] as String? ?? '-'),
                            ),
                          )
                          .toList(),
                      decoration: const InputDecoration(
                        labelText: 'Filter Semester',
                      ),
                      onChanged: (value) {
                        if (value == null || value == _selectedSemesterId) {
                          return;
                        }
                        setState(() {
                          _selectedSemesterId = value;
                        });
                        ref
                            .read(rekapitulasiProvider.notifier)
                            .refresh(semesterId: value);
                      },
                    ),
                    const SizedBox(height: 12),
                    Align(
                      alignment: Alignment.centerRight,
                      child: ElevatedButton.icon(
                        onPressed: _isExporting
                            ? null
                            : () => _exportPdf(selectedSemesterId),
                        icon: _isExporting
                            ? const SizedBox(
                                width: 18,
                                height: 18,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                ),
                              )
                            : const Icon(Icons.picture_as_pdf),
                        label: Text(
                          _isExporting ? 'Membuka PDF...' : 'Cetak/Export',
                        ),
                      ),
                    ),
                  ],
                );
              },
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (error, stack) =>
                  Center(child: Text('Gagal memuat semester: $error')),
            ),
          ),
          Expanded(
            child: rekapState.when(
              data: (result) {
                final totalSks = result.fold<int>(
                  0,
                  (sum, row) => sum + (row['sks'] as int? ?? 0),
                );
                final completeCount = result
                    .where((row) => row['status']?.toString() == 'Lengkap')
                    .length;
                final incompleteCount = result.length - completeCount;
                final weightedSum = result.fold<double>(0, (sum, row) {
                  final indeks = row['indeks'];
                  final sks = row['sks'] as int? ?? 0;
                  if (indeks is num) {
                    return sum + sks * indeks.toDouble();
                  }
                  return sum;
                });
                final sksForIp = result.fold<double>(
                  0,
                  (sum, row) =>
                      sum +
                      (row['indeks'] != null
                          ? (row['sks'] as int? ?? 0).toDouble()
                          : 0.0),
                );
                final ipSemester = sksForIp > 0 ? weightedSum / sksForIp : null;
                final ipSemesterValue = ipSemester != null
                    ? ipSemester.toStringAsFixed(2)
                    : '-';

                if (result.isEmpty) {
                  return Center(
                    child: Card(
                      margin: const EdgeInsets.all(16),
                      child: const Padding(
                        padding: EdgeInsets.all(24),
                        child: Text('Belum ada data rekapitulasi.'),
                      ),
                    ),
                  );
                }

                return ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    Wrap(
                      spacing: 12,
                      runSpacing: 12,
                      children: [
                        _SummaryCard(
                          label: 'IP Semester',
                          value: ipSemesterValue,
                        ),
                        _SummaryCard(
                          label: 'Total SKS Semester',
                          value: totalSks.toString(),
                        ),
                        _SummaryCard(
                          label: 'Status Mata Kuliah',
                          value: '$completeCount Lengkap',
                          subtitle: '$incompleteCount Belum Lengkap',
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: SingleChildScrollView(
                          scrollDirection: Axis.horizontal,
                          child: DataTable(
                            headingRowColor: WidgetStateProperty.all(
                              AppColors.bgPage,
                            ),
                            columns: const [
                              DataColumn(label: Text('Nama Mata Kuliah')),
                              DataColumn(label: Text('SKS')),
                              DataColumn(label: Text('Nilai Akhir')),
                              DataColumn(label: Text('Huruf Mutu')),
                              DataColumn(label: Text('Indeks')),
                              DataColumn(label: Text('Status')),
                            ],
                            rows: result.map((row) {
                              return DataRow(
                                cells: [
                                  DataCell(
                                    Text(row['nama_mk']?.toString() ?? '-'),
                                  ),
                                  DataCell(Text(row['sks']?.toString() ?? '-')),
                                  DataCell(
                                    Text(row['nilai_akhir']?.toString() ?? '-'),
                                  ),
                                  DataCell(
                                    Text(row['huruf_mutu']?.toString() ?? '-'),
                                  ),
                                  DataCell(
                                    Text(row['indeks']?.toString() ?? '-'),
                                  ),
                                  DataCell(
                                    Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 12,
                                        vertical: 6,
                                      ),
                                      decoration: BoxDecoration(
                                        color: row['status'] == 'Lengkap'
                                            ? AppColors.successGreen.withValues(
                                                alpha: 0.15,
                                              )
                                            : AppColors.warningAmber.withValues(
                                                alpha: 0.15,
                                              ),
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: Text(
                                        row['status']?.toString() ?? '-',
                                        style: TextStyle(
                                          color: row['status'] == 'Lengkap'
                                              ? AppColors.successGreen
                                              : AppColors.warningAmber,
                                          fontWeight: FontWeight.w600,
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              );
                            }).toList(),
                          ),
                        ),
                      ),
                    ),
                  ],
                );
              },
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (error, stack) =>
                  Center(child: Text('Gagal memuat rekapitulasi: $error')),
            ),
          ),
        ],
      ),
    );
  }
}

class _SummaryCard extends StatelessWidget {
  final String label;
  final String value;
  final String? subtitle;

  const _SummaryCard({required this.label, required this.value, this.subtitle});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: MediaQuery.of(context).size.width / 2 - 26,
      child: Card(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(label, style: const TextStyle(color: AppColors.textMuted)),
              const SizedBox(height: 10),
              Text(
                value,
                style: const TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.w700,
                ),
              ),
              if (subtitle != null) ...[
                const SizedBox(height: 8),
                Text(
                  subtitle!,
                  style: const TextStyle(color: AppColors.textMuted),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
