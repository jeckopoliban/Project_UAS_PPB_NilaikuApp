import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../../core/theme/app_theme.dart';
import '../../auth/providers/auth_provider.dart';
import '../providers/dashboard_provider.dart';

class DashboardPage extends ConsumerWidget {
  const DashboardPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final dashboardState = ref.watch(dashboardProvider);
    final authState = ref.watch(authProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Dashboard'), centerTitle: true),
      body: dashboardState.when(
        data: (data) {
          final name = authState.when(
            data: (auth) => auth.userName?.toString() ?? 'Mahasiswa',
            loading: () => 'Mahasiswa',
            error: (_, _) => 'Mahasiswa',
          );
          final firstName = name.trim().isEmpty
              ? 'Mahasiswa'
              : name.trim().split(' ').where((part) => part.isNotEmpty).first;
          final currentDate = DateFormat(
            'EEEE, d MMMM y',
            'id_ID',
          ).format(DateTime.now());

          double parseNumber(Object? value, [double fallback = 0]) {
            if (value is num) return value.toDouble();
            if (value is String) return double.tryParse(value) ?? fallback;
            return fallback;
          }

          String formatNumber(
            Object? value, {
            int fractionDigits = 2,
            String fallback = '-',
          }) {
            if (value is num) return value.toStringAsFixed(fractionDigits);
            if (value is String) {
              final parsed = double.tryParse(value);
              return parsed != null
                  ? parsed.toStringAsFixed(fractionDigits)
                  : fallback;
            }
            return fallback;
          }

          final ipk = data['ipk_kumulatif'] ?? data['ip_semester_terakhir'];
          final ipkLabel = ipk != null ? formatNumber(ipk) : '-';
          final activeSemester = data['total_semester']?.toString() ?? '-';
          final targetIpk = data['target_ipk'] != null
              ? formatNumber(data['target_ipk'])
              : '-';

          final progressSks = data['progress_sks'];
          final currentSks =
              progressSks is Map<String, dynamic> &&
                  progressSks['current'] is num
              ? (progressSks['current'] as num).toDouble()
              : parseNumber(data['sks_lulus']);
          final targetSks =
              progressSks is Map<String, dynamic> &&
                  progressSks['target'] is num
              ? (progressSks['target'] as num).toDouble()
              : parseNumber(data['target_sks']);
          final progressRatio = targetSks > 0
              ? (currentSks / targetSks).clamp(0.0, 1.0)
              : 0.0;
          final progressBadge = targetSks > 0
              ? '${(progressRatio * 100).round()}%'
              : '-';

          final ipTrendRaw = data['ip_trend'] as List<dynamic>? ?? [];
          final ipTrend = ipTrendRaw.whereType<Map<String, dynamic>>().toList();

          final reminders = data['reminders'] is List
              ? List<String>.from(
                  (data['reminders'] as List).map(
                    (item) => item?.toString() ?? '',
                  ),
                )
              : <String>[];

          return RefreshIndicator(
            onRefresh: () => ref.read(dashboardProvider.notifier).refresh(),
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                Card(
                  margin: EdgeInsets.zero,
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Halo, $firstName! 👋',
                          style: const TextStyle(
                            fontSize: 26,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'Berikut ringkasan akademik Anda hari ini.',
                          style: TextStyle(
                            fontSize: 14,
                            color: AppColors.textBody,
                          ),
                        ),
                        const SizedBox(height: 16),
                        Text(
                          currentDate,
                          style: const TextStyle(
                            fontSize: 13,
                            color: AppColors.textMuted,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      child: _DashboardStatCard(
                        label: 'IPK',
                        value: ipkLabel,
                        subtitle: 'IPK kumulatif Anda',
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: _DashboardStatCard(
                        label: 'Semester Aktif',
                        value: activeSemester,
                        subtitle: 'Semester berjalan',
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: _DashboardStatCard(
                        label: 'Target IPK',
                        value: targetIpk,
                        subtitle: 'Target akademik Anda',
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                _DashboardSectionCard(
                  title: 'Progress SKS',
                  badge: progressBadge,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        '${currentSks.toStringAsFixed(0)} / ${targetSks.toStringAsFixed(0)} SKS',
                        style: const TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      const SizedBox(height: 16),
                      ClipRRect(
                        borderRadius: BorderRadius.circular(999),
                        child: Container(
                          height: 10,
                          color: AppColors.bgPage,
                          child: Stack(
                            children: [
                              FractionallySizedBox(
                                widthFactor: progressRatio.clamp(0.0, 1.0),
                                alignment: Alignment.centerLeft,
                                child: Container(
                                  decoration: BoxDecoration(
                                    gradient: AppColors.gradientButton,
                                    borderRadius: BorderRadius.circular(999),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
                Card(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 18,
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: _DashboardIconAction(
                            label: 'Semester',
                            icon: Icons.calendar_month,
                            onTap: () => context.go('/semester'),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: _DashboardIconAction(
                            label: 'Mata Kuliah',
                            icon: Icons.book,
                            onTap: () => context.go('/mata-kuliah'),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: _DashboardIconAction(
                            label: 'Grading',
                            icon: Icons.rule,
                            onTap: () => context.go('/grading'),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: _DashboardIconAction(
                            label: 'IP/IPK',
                            icon: Icons.show_chart,
                            onTap: () => context.go('/ip-ipk'),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                _DashboardSectionCard(
                  title: 'Reminder',
                  badge: 'Penting',
                  child: Column(
                    children: reminders.isNotEmpty
                        ? reminders.map((reminder) {
                            final isSuccess = reminder.toLowerCase().contains(
                              'seluruh data akademik sudah lengkap',
                            );
                            return Padding(
                              padding: const EdgeInsets.only(bottom: 12),
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Container(
                                    width: 28,
                                    height: 28,
                                    decoration: BoxDecoration(
                                      color: isSuccess
                                          ? AppColors.successGreen.withAlpha(
                                              (0.15 * 255).round(),
                                            )
                                          : AppColors.warningAmber.withAlpha(
                                              (0.15 * 255).round(),
                                            ),
                                      shape: BoxShape.circle,
                                    ),
                                    child: Icon(
                                      isSuccess
                                          ? Icons.check
                                          : Icons.warning_amber,
                                      size: 18,
                                      color: isSuccess
                                          ? AppColors.successGreen
                                          : AppColors.warningAmber,
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Text(
                                      reminder,
                                      style: const TextStyle(fontSize: 14),
                                    ),
                                  ),
                                ],
                              ),
                            );
                          }).toList()
                        : [
                            const Text(
                              'Seluruh data akademik sudah lengkap.',
                              style: TextStyle(fontSize: 14),
                            ),
                          ],
                  ),
                ),
                const SizedBox(height: 16),
                _DashboardSectionCard(
                  title: 'Grafik Perkembangan IP',
                  subtitle: 'Lihat tren IP per semester dan progress akademik.',
                  badge: 'Terbaru',
                  child: ipTrend.isEmpty
                      ? SizedBox(
                          height: 220,
                          child: Center(
                            child: Text(
                              'Belum ada data IP semester.',
                              style: const TextStyle(
                                color: AppColors.textMuted,
                              ),
                            ),
                          ),
                        )
                      : SizedBox(
                          height: 220,
                          child: LineChart(
                            LineChartData(
                              gridData: FlGridData(
                                horizontalInterval: 0.5,
                                drawVerticalLine: false,
                                getDrawingHorizontalLine: (value) => FlLine(
                                  color: AppColors.borderSubtle,
                                  strokeWidth: 1,
                                ),
                              ),
                              titlesData: FlTitlesData(
                                bottomTitles: AxisTitles(
                                  sideTitles: SideTitles(
                                    showTitles: true,
                                    interval: 1,
                                    reservedSize: 32,
                                    getTitlesWidget: (value, meta) {
                                      final index = value.toInt();
                                      if (index < 0 ||
                                          index >= ipTrend.length) {
                                        return const SizedBox.shrink();
                                      }
                                      final label =
                                          ipTrend[index]['nama_semester']
                                              ?.toString() ??
                                          '';
                                      return SideTitleWidget(
                                        meta: meta,
                                        child: Text(
                                          label,
                                          style: const TextStyle(
                                            fontSize: 10,
                                            color: AppColors.textMuted,
                                          ),
                                          textAlign: TextAlign.center,
                                        ),
                                      );
                                    },
                                  ),
                                ),
                                leftTitles: AxisTitles(
                                  sideTitles: SideTitles(
                                    showTitles: true,
                                    interval: 0.5,
                                    getTitlesWidget: (value, meta) {
                                      return SideTitleWidget(
                                        meta: meta,
                                        child: Text(
                                          value.toStringAsFixed(1),
                                          style: const TextStyle(
                                            fontSize: 10,
                                            color: AppColors.textMuted,
                                          ),
                                        ),
                                      );
                                    },
                                  ),
                                ),
                                topTitles: AxisTitles(
                                  sideTitles: SideTitles(showTitles: false),
                                ),
                                rightTitles: AxisTitles(
                                  sideTitles: SideTitles(showTitles: false),
                                ),
                              ),
                              minY: 0,
                              maxY: 4,
                              lineTouchData: LineTouchData(
                                touchTooltipData: LineTouchTooltipData(
                                  getTooltipColor: (_) => Colors.white,
                                  tooltipBorderRadius: BorderRadius.circular(
                                    12,
                                  ),
                                  tooltipPadding: const EdgeInsets.symmetric(
                                    horizontal: 12,
                                    vertical: 10,
                                  ),
                                  getTooltipItems: (items) {
                                    return items.map((item) {
                                      return LineTooltipItem(
                                        item.y.toStringAsFixed(2),
                                        const TextStyle(
                                          color: AppColors.textHeading,
                                          fontWeight: FontWeight.w700,
                                        ),
                                      );
                                    }).toList();
                                  },
                                ),
                              ),
                              minX: 0,
                              maxX: ipTrend.length > 1
                                  ? (ipTrend.length - 1).toDouble()
                                  : 1,
                              lineBarsData: [
                                LineChartBarData(
                                  spots: List.generate(ipTrend.length, (index) {
                                    final value = ipTrend[index]['nilai_ip'];
                                    final chartValue = value is num
                                        ? value.toDouble()
                                        : double.tryParse(
                                                value?.toString() ?? '0',
                                              ) ??
                                              0.0;
                                    return FlSpot(index.toDouble(), chartValue);
                                  }),
                                  isCurved: true,
                                  color: AppColors.brandBlue,
                                  dotData: FlDotData(show: true),
                                  belowBarData: BarAreaData(
                                    show: true,
                                    gradient: LinearGradient(
                                      colors: [
                                        AppColors.brandBlue.withAlpha(
                                          (0.36 * 255).round(),
                                        ),
                                        AppColors.brandBlue.withAlpha(
                                          (0.04 * 255).round(),
                                        ),
                                      ],
                                    ),
                                  ),
                                  barWidth: 3,
                                ),
                              ],
                            ),
                          ),
                        ),
                ),
              ],
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) =>
            Center(child: Text('Gagal memuat dashboard: $error')),
      ),
    );
  }
}

class _DashboardStatCard extends StatelessWidget {
  final String label;
  final String value;
  final String subtitle;

  const _DashboardStatCard({
    required this.label,
    required this.value,
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
            Text(label, style: const TextStyle(color: AppColors.textMuted)),
            const SizedBox(height: 12),
            Text(
              value,
              style: const TextStyle(
                fontSize: 26,
                fontWeight: FontWeight.w700,
                color: AppColors.textHeading,
              ),
            ),
            const SizedBox(height: 8),
            Text(subtitle, style: const TextStyle(color: AppColors.textMuted)),
          ],
        ),
      ),
    );
  }
}

class _DashboardSectionCard extends StatelessWidget {
  final String title;
  final String? subtitle;
  final String? badge;
  final Widget child;

  const _DashboardSectionCard({
    required this.title,
    required this.child,
    this.subtitle,
    this.badge,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      if (subtitle != null && subtitle!.isNotEmpty) ...[
                        const SizedBox(height: 6),
                        Text(
                          subtitle!,
                          style: const TextStyle(color: AppColors.textMuted),
                        ),
                      ],
                    ],
                  ),
                ),
                if (badge != null && badge!.isNotEmpty)
                  Builder(
                    builder: (context) {
                      final key = badge!.toLowerCase();
                      final bgColor = key == 'penting'
                          ? AppColors.warningAmber.withAlpha(
                              (0.12 * 255).round(),
                            )
                          : key == 'terbaru'
                          ? AppColors.successGreen.withAlpha(
                              (0.12 * 255).round(),
                            )
                          : AppColors.brandBlueLight.withAlpha(
                              (0.12 * 255).round(),
                            );
                      final textColor = key == 'penting'
                          ? AppColors.warningAmber
                          : key == 'terbaru'
                          ? AppColors.successGreen
                          : AppColors.brandBlue;
                      return Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 6,
                        ),
                        decoration: BoxDecoration(
                          color: bgColor,
                          borderRadius: BorderRadius.circular(999),
                        ),
                        child: Text(
                          badge!,
                          style: TextStyle(
                            fontSize: 12,
                            color: textColor,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      );
                    },
                  ),
              ],
            ),
            const SizedBox(height: 16),
            child,
          ],
        ),
      ),
    );
  }
}

class _DashboardIconAction extends StatelessWidget {
  final String label;
  final IconData icon;
  final VoidCallback onTap;

  const _DashboardIconAction({
    required this.label,
    required this.icon,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.center,
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 64,
            height: 64,
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [AppColors.brandBlueLight, AppColors.brandBlue],
              ),
              borderRadius: BorderRadius.circular(18),
              boxShadow: [
                BoxShadow(
                  color: AppColors.brandBlue.withAlpha((0.18 * 255).round()),
                  blurRadius: 16,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: Icon(icon, color: Colors.white, size: 28),
          ),
          const SizedBox(height: 10),
          Text(
            label,
            textAlign: TextAlign.center,
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
          ),
        ],
      ),
    );
  }
}
