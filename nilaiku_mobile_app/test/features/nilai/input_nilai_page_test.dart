import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:nilaiku_mobile_app/core/network/dio_client.dart';
import 'package:nilaiku_mobile_app/core/storage/secure_storage.dart';
import 'package:nilaiku_mobile_app/features/nilai/presentation/input_nilai_page.dart';
import 'package:nilaiku_mobile_app/features/nilai/providers/nilai_provider.dart';
import 'package:nilaiku_mobile_app/features/nilai/data/nilai_repository.dart';

class FakeNilaiRepository extends NilaiRepository {
  FakeNilaiRepository(this.items)
    : super(dioClient: DioClient(secureStorage: SecureStorage()));

  final List<Map<String, dynamic>> items;

  @override
  Future<List<Map<String, dynamic>>> getKomponenNilai(int mataKuliahId) async {
    return items;
  }

  @override
  Future<void> updateNilai(
    int mataKuliahId,
    String namaKomponenPenilaian,
    List<Map<String, dynamic>> komponen,
  ) async {}
}

void main() {
  testWidgets('populates existing component data into the form once', (
    tester,
  ) async {
    final fakeRepository = FakeNilaiRepository([
      {
        'nama_komponen_penilaian': 'Proyek Akhir',
        'nama_komponen': 'Tugas',
        'bobot_persen': 40,
        'nilai_angka': 90,
      },
    ]);

    await tester.pumpWidget(
      ProviderScope(
        overrides: [nilaiRepositoryProvider.overrideWithValue(fakeRepository)],
        child: const MaterialApp(
          home: InputNilaiPage(
            mataKuliahId: 1159,
            namaMataKuliah: 'Metode Penelitian dan Bisnis',
          ),
        ),
      ),
    );

    await tester.pump();
    await tester.pump(const Duration(milliseconds: 100));
    await tester.pumpAndSettle();

    expect(
      find.text('Edit Nilai Metode Penelitian dan Bisnis'),
      findsOneWidget,
    );

    final textFields = tester
        .widgetList<TextField>(find.byType(TextField))
        .toList();
    expect(
      textFields.any((field) => field.controller?.text == 'Tugas'),
      isTrue,
    );
    expect(textFields.any((field) => field.controller?.text == '40'), isTrue);
  });
}
