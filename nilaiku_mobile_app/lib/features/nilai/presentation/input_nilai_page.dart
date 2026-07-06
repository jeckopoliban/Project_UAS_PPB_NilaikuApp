import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/buttons.dart';
import '../providers/nilai_provider.dart';

class InputNilaiPage extends ConsumerStatefulWidget {
  final int mataKuliahId;
  final String namaMataKuliah;

  const InputNilaiPage({
    super.key,
    required this.mataKuliahId,
    required this.namaMataKuliah,
  });

  @override
  ConsumerState<InputNilaiPage> createState() => _InputNilaiPageState();
}

class _InputNilaiPageState extends ConsumerState<InputNilaiPage> {
  final TextEditingController namaKomponenPenilaianController =
      TextEditingController();
  final List<Map<String, TextEditingController>> komponenControllers = [];
  bool _isDataLoaded = false;
  bool _isEditing = false;
  bool _hasRequestedLoad = false;

  @override
  void initState() {
    super.initState();
    _hasRequestedLoad = false;
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted || _hasRequestedLoad) return;
      _hasRequestedLoad = true;
      ref.read(nilaiProvider.notifier).load(widget.mataKuliahId);
    });
  }

  @override
  void didUpdateWidget(covariant InputNilaiPage oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.mataKuliahId != widget.mataKuliahId) {
      _isDataLoaded = false;
      _isEditing = false;
      _hasRequestedLoad = false;
      namaKomponenPenilaianController.clear();
      _clearControllers();
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (!mounted || _hasRequestedLoad) return;
        _hasRequestedLoad = true;
        ref.read(nilaiProvider.notifier).load(widget.mataKuliahId);
      });
    }
  }

  @override
  void dispose() {
    namaKomponenPenilaianController.dispose();
    for (final controllerRow in komponenControllers) {
      controllerRow['nama']?.dispose();
      controllerRow['bobot']?.dispose();
      controllerRow['nilai']?.dispose();
    }
    super.dispose();
  }

  void _clearControllers() {
    for (final controllerRow in komponenControllers) {
      controllerRow['nama']?.dispose();
      controllerRow['bobot']?.dispose();
      controllerRow['nilai']?.dispose();
    }
    komponenControllers.clear();
  }

  void _populateFromData(List<Map<String, dynamic>> komponenList) {
    if (_isDataLoaded) return;

    namaKomponenPenilaianController.text = komponenList.isNotEmpty
        ? komponenList.first['nama_komponen_penilaian']?.toString() ?? ''
        : '';

    _clearControllers();
    komponenControllers.addAll(
      komponenList.map((item) {
        return {
          'nama': TextEditingController(
            text: item['nama_komponen']?.toString() ?? '',
          ),
          'bobot': TextEditingController(
            text: item['bobot_persen']?.toString() ?? '',
          ),
          'nilai': TextEditingController(
            text: item['nilai_angka']?.toString() ?? '',
          ),
        };
      }),
    );

    if (komponenControllers.isEmpty) {
      komponenControllers.addAll([
        {
          'nama': TextEditingController(text: ''),
          'bobot': TextEditingController(text: ''),
          'nilai': TextEditingController(text: ''),
        },
      ]);
    }

    _isEditing = komponenList.isNotEmpty;
    _isDataLoaded = true;
    if (mounted) {
      setState(() {});
    }
  }

  double get _totalBobot {
    return komponenControllers.fold<double>(0, (prev, item) {
      final bobotText = item['bobot']?.text ?? '';
      return prev + (double.tryParse(bobotText.replaceAll(',', '.')) ?? 0);
    });
  }

  bool get _isSaveEnabled {
    return (_totalBobot - 100).abs() < 0.001 &&
        namaKomponenPenilaianController.text.trim().isNotEmpty;
  }

  Color get _progressColor {
    if (_isSaveEnabled) return AppColors.successGreen;
    if (_totalBobot > 100) return AppColors.rose;
    return AppColors.warningAmber;
  }

  String get _progressLabel =>
      'Total Bobot: ${_totalBobot.toStringAsFixed(_totalBobot.truncateToDouble() == _totalBobot ? 0 : 2)}% dari 100%';

  InputDecoration _fieldDecoration(String hintText) {
    return InputDecoration(
      hintText: hintText,
      filled: true,
      fillColor: Colors.white,
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(18),
        borderSide: const BorderSide(color: AppColors.borderSubtle),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(18),
        borderSide: const BorderSide(color: AppColors.borderSubtle),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(18),
        borderSide: const BorderSide(color: AppColors.brandBlue, width: 1.5),
      ),
      contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
    );
  }

  Widget _buildCourseSummaryCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: AppColors.borderSubtle),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withAlpha((0.04 * 255).round()),
            blurRadius: 24,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Mata Kuliah',
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w500,
              color: AppColors.textBody,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            widget.namaMataKuliah,
            style: const TextStyle(
              fontSize: 26,
              fontWeight: FontWeight.w700,
              color: AppColors.textHeading,
            ),
          ),
          const SizedBox(height: 12),
          Text(
            'Total Bobot Terisi: ${_totalBobot.toStringAsFixed(_totalBobot.truncateToDouble() == _totalBobot ? 0 : 2)}%',
            style: const TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w600,
              color: AppColors.textBody,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildKomponenHeader() {
    return Row(
      children: const [
        Expanded(
          flex: 4,
          child: Text(
            'Komponen',
            style: TextStyle(fontWeight: FontWeight.w700),
          ),
        ),
        Expanded(
          flex: 2,
          child: Text(
            'Bobot (%)',
            style: TextStyle(fontWeight: FontWeight.w700),
          ),
        ),
        Expanded(
          flex: 2,
          child: Text(
            'Nilai Angka',
            style: TextStyle(fontWeight: FontWeight.w700),
          ),
        ),
        SizedBox(
          width: 52,
          child: Text(
            'Aksi',
            textAlign: TextAlign.center,
            style: TextStyle(fontWeight: FontWeight.w700),
          ),
        ),
      ],
    );
  }

  Widget _buildKomponenRow(int index) {
    final controllerRow = komponenControllers[index];
    final namaController = controllerRow['nama']!;
    final bobotController = controllerRow['bobot']!;
    final nilaiController = controllerRow['nilai']!;

    return Padding(
      padding: const EdgeInsets.only(top: 14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            flex: 4,
            child: TextField(
              decoration: _fieldDecoration('Nama Komponen'),
              controller: namaController,
              onChanged: (_) => setState(() {}),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            flex: 2,
            child: TextField(
              keyboardType: const TextInputType.numberWithOptions(
                decimal: true,
              ),
              decoration: _fieldDecoration('30.00'),
              controller: bobotController,
              onChanged: (_) => setState(() {}),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            flex: 2,
            child: TextField(
              keyboardType: const TextInputType.numberWithOptions(
                decimal: true,
              ),
              decoration: _fieldDecoration('100.00'),
              controller: nilaiController,
              onChanged: (_) => setState(() {}),
            ),
          ),
          const SizedBox(width: 8),
          SizedBox(
            width: 44,
            child: IconButton(
              onPressed: () {
                setState(() {
                  controllerRow['nama']?.dispose();
                  controllerRow['bobot']?.dispose();
                  controllerRow['nilai']?.dispose();
                  komponenControllers.removeAt(index);
                });
              },
              icon: const Icon(Icons.delete, color: AppColors.rose),
            ),
          ),
        ],
      ),
    );
  }

  void _addRow() {
    setState(() {
      komponenControllers.addAll([
        {
          'nama': TextEditingController(text: ''),
          'bobot': TextEditingController(text: ''),
          'nilai': TextEditingController(text: ''),
        },
      ]);
    });
  }

  Future<void> _save() async {
    if (!_isSaveEnabled ||
        namaKomponenPenilaianController.text.trim().isEmpty) {
      return;
    }

    final items = komponenControllers.map((controllerRow) {
      return {
        'nama_komponen': controllerRow['nama']?.text.trim() ?? '',
        'bobot_persen':
            double.tryParse(controllerRow['bobot']?.text.trim() ?? '') ?? 0,
        'nilai_angka': controllerRow['nilai']?.text.trim().isEmpty == true
            ? null
            : double.tryParse(controllerRow['nilai']?.text.trim() ?? ''),
      };
    }).toList();

    await ref
        .read(nilaiProvider.notifier)
        .updateNilai(
          widget.mataKuliahId,
          namaKomponenPenilaianController.text.trim(),
          items,
        );

    if (!mounted) return;
    Navigator.of(context).pop(true);
  }

  Widget _buildForm() {
    final progressValue = (_totalBobot / 100).clamp(0.0, 1.0);

    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildCourseSummaryCard(),
          const SizedBox(height: 20),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(24),
              border: Border.all(color: AppColors.borderSubtle),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withAlpha((0.04 * 255).round()),
                  blurRadius: 24,
                  offset: const Offset(0, 10),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Nama Komponen Penilaian',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                    color: AppColors.textHeading,
                  ),
                ),
                const SizedBox(height: 12),
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      child: TextField(
                        controller: namaKomponenPenilaianController,
                        decoration: _fieldDecoration('Tugas + UTS + UAS'),
                        onChanged: (_) => setState(() {}),
                      ),
                    ),
                    const SizedBox(width: 14),
                    SizedBox(
                      width: 210,
                      child: GradientButton(
                        onPressed: _addRow,
                        child: const Text('Tambah Baris Komponen'),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 24),
                _buildKomponenHeader(),
                const SizedBox(height: 8),
                const Divider(height: 1),
                ...List.generate(komponenControllers.length, _buildKomponenRow),
                const SizedBox(height: 24),
                Row(
                  children: [
                    Expanded(
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(999),
                        child: LinearProgressIndicator(
                          value: progressValue,
                          minHeight: 12,
                          backgroundColor: AppColors.borderSubtle,
                          valueColor: AlwaysStoppedAnimation<Color>(
                            _progressColor,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Text(
                      _progressLabel,
                      style: TextStyle(
                        color: _progressColor,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 22),
                Row(
                  children: [
                    Expanded(
                      child: GradientButton(
                        onPressed: _isSaveEnabled ? _save : null,
                        child: const Text('Simpan Nilai'),
                      ),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: OutlineBlueButton(
                        label: 'Batal',
                        onPressed: () => Navigator.of(context).pop(),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final nilaiState = ref.watch(nilaiProvider);

    return Scaffold(
      appBar: AppBar(
        title: Text(
          '${_isEditing ? 'Edit' : 'Input'} Nilai ${widget.namaMataKuliah}',
        ),
        centerTitle: true,
      ),
      body: nilaiState.when(
        data: (result) {
          if (!_isDataLoaded) {
            final notifier = ref.read(nilaiProvider.notifier);
            if (!notifier.hasLoaded ||
                notifier.loadedMataKuliahId != widget.mataKuliahId) {
              return const Center(child: CircularProgressIndicator());
            }

            WidgetsBinding.instance.addPostFrameCallback((_) {
              if (!mounted || _isDataLoaded) return;
              _populateFromData(result);
            });
            return const Center(child: CircularProgressIndicator());
          }
          return _buildForm();
        },
        loading: () {
          if (_isDataLoaded) {
            return _buildForm();
          }
          return const Center(child: CircularProgressIndicator());
        },
        error: (error, stack) {
          if (_isDataLoaded) {
            return _buildForm();
          }
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text('Gagal memuat: $error'),
                const SizedBox(height: 12),
                GradientButton(
                  onPressed: () => ref
                      .read(nilaiProvider.notifier)
                      .load(widget.mataKuliahId),
                  child: const Text('Coba lagi'),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
