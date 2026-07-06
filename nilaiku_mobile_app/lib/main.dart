import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/date_symbol_data_local.dart';

import 'core/router/app_router.dart';
import 'core/theme/app_theme.dart';
import 'core/network/http_overrides_setup.dart'
    if (dart.library.io) 'core/network/http_overrides_setup_io.dart';

void main() async {
  configureDevHttpOverrides();
  await initializeDateFormatting('id_ID', null);
  runApp(const ProviderScope(child: NilaikuApp()));
}

class NilaikuApp extends ConsumerWidget {
  const NilaikuApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(routerProvider);

    return MaterialApp.router(
      title: 'Nilaiku',
      theme: appTheme,
      routerConfig: router,
      debugShowCheckedModeBanner: false,
    );
  }
}
