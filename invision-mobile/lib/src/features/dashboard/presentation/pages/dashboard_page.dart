import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../../../core/theme/app_theme.dart';
import '../../data/models/dashboard_models.dart';
import '../providers/dashboard_providers.dart';

final _currencyFormat = NumberFormat.currency(symbol: '\$', decimalDigits: 0);
final _numberFormat = NumberFormat('#,##0');

class DashboardPage extends ConsumerStatefulWidget {
  const DashboardPage({super.key});

  @override
  ConsumerState<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends ConsumerState<DashboardPage> {
  String _period = 'month';

  final _periods = const [
    ('week', 'Week'),
    ('month', 'Month'),
    ('quarter', 'Quarter'),
    ('year', 'Year'),
  ];

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;
    final overview = ref.watch(overviewKpiProvider);
    final sales = ref.watch(salesKpiProvider(_period));
    final routes = ref.watch(routeKpiProvider(_period));
    final campaigns = ref.watch(campaignKpiProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      body: RefreshIndicator(
        color: AppColors.primary,
        onRefresh: () async {
          ref.invalidate(overviewKpiProvider);
          ref.invalidate(salesKpiProvider(_period));
          ref.invalidate(routeKpiProvider(_period));
          ref.invalidate(campaignKpiProvider);
        },
        child: CustomScrollView(
          slivers: [
            // ── Sticky blur header ─────────────────────────────
            SliverAppBar(
              pinned: true,
              floating: true,
              backgroundColor: AppColors.surface.withOpacity(0.9),
              elevation: 0,
              scrolledUnderElevation: 0,
              title: Row(
                children: [
                  Container(
                    width: 36,
                    height: 36,
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(colors: [
                        AppColors.primary,
                        AppColors.primaryContainer,
                      ]),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.hub_rounded,
                        color: Colors.white, size: 20),
                  ),
                  const SizedBox(width: 10),
                  Text('Dashboard',
                      style: tt.titleLarge
                          ?.copyWith(color: AppColors.onSurface)),
                ],
              ),
              actions: [
                IconButton(
                  onPressed: () => context.go('/login'),
                  icon: const Icon(Icons.logout_rounded, size: 22),
                  color: AppColors.onSurfaceVariant,
                  tooltip: 'Logout',
                ),
              ],
              bottom: PreferredSize(
                preferredSize: const Size.fromHeight(52),
                child: Container(
                  height: 52,
                  padding: const EdgeInsets.only(bottom: 8),
                  child: ListView(
                    scrollDirection: Axis.horizontal,
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    children: _periods.map((p) {
                      final active = p.$1 == _period;
                      return Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: FilterChip(
                          label: Text(p.$2),
                          selected: active,
                          onSelected: (_) =>
                              setState(() => _period = p.$1),
                          backgroundColor: AppColors.surfaceContainerLow,
                          selectedColor: AppColors.primaryContainer,
                          checkmarkColor: Colors.white,
                          labelStyle: tt.labelMedium?.copyWith(
                            color: active
                                ? Colors.white
                                : AppColors.onSurfaceVariant,
                            fontWeight: active
                                ? FontWeight.w700
                                : FontWeight.w500,
                          ),
                          showCheckmark: false,
                        ),
                      );
                    }).toList(),
                  ),
                ),
              ),
            ),

            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
              sliver: SliverList(
                delegate: SliverChildListDelegate([
                  // ── Overview ───────────────────────────────────
                  overview.when(
                    data: (o) => _OverviewSection(data: o),
                    loading: () => const _ShimmerCard(),
                    error: (e, _) => _ErrorCard(message: e.toString()),
                  ),
                  const SizedBox(height: 16),

                  // ── Sales ──────────────────────────────────────
                  sales.when(
                    data: (s) => _SalesSection(data: s),
                    loading: () => const _ShimmerCard(),
                    error: (e, _) => _ErrorCard(message: e.toString()),
                  ),
                  const SizedBox(height: 16),

                  // ── Routes ─────────────────────────────────────
                  routes.when(
                    data: (r) => _RoutesSection(data: r),
                    loading: () => const _ShimmerCard(),
                    error: (e, _) => _ErrorCard(message: e.toString()),
                  ),
                  const SizedBox(height: 16),

                  // ── Campaigns ──────────────────────────────────
                  campaigns.when(
                    data: (c) => _CampaignsSection(data: c),
                    loading: () => const _ShimmerCard(),
                    error: (e, _) => _ErrorCard(message: e.toString()),
                  ),
                  const SizedBox(height: 24),

                  // ── Inquiry tiles ──────────────────────────────
                  Text('Inquiries',
                      style: tt.titleLarge
                          ?.copyWith(color: AppColors.onSurface)),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      _InquiryTile(
                        icon: Icons.store_rounded,
                        label: 'Stores',
                        color: AppColors.secondary,
                        bgColor: AppColors.secondaryContainer,
                        onTap: () => context.push('/inquiry/stores'),
                      ),
                      const SizedBox(width: 10),
                      _InquiryTile(
                        icon: Icons.attach_money_rounded,
                        label: 'Sales',
                        color: AppColors.tertiary,
                        bgColor: AppColors.tertiaryContainer,
                        onTap: () => context.push('/inquiry/sales'),
                      ),
                      const SizedBox(width: 10),
                      _InquiryTile(
                        icon: Icons.map_rounded,
                        label: 'Routes',
                        color: AppColors.primary,
                        bgColor: AppColors.primaryContainer,
                        onTap: () => context.push('/inquiry/routes'),
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),

                  // ── Quick access ───────────────────────────────
                  Text('Quick Access',
                      style: tt.titleLarge
                          ?.copyWith(color: AppColors.onSurface)),
                  const SizedBox(height: 12),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: const [
                      _QuickChip(icon: Icons.store_rounded, label: 'Stores', path: '/stores'),
                      _QuickChip(icon: Icons.inventory_2_rounded, label: 'Products', path: '/products'),
                      _QuickChip(icon: Icons.route_rounded, label: 'My Route', path: '/my-route'),
                      _QuickChip(icon: Icons.map_rounded, label: 'Routes', path: '/routes'),
                      _QuickChip(icon: Icons.campaign_rounded, label: 'Campaigns', path: '/campaigns'),
                      _QuickChip(icon: Icons.task_alt_rounded, label: 'My Tasks', path: '/my-tasks'),
                      _QuickChip(icon: Icons.receipt_long_rounded, label: 'Sales', path: '/sales'),
                      _QuickChip(icon: Icons.point_of_sale_rounded, label: 'POS', path: '/pos'),
                      _QuickChip(icon: Icons.notifications_rounded, label: 'Alerts', path: '/notifications'),
                      _QuickChip(icon: Icons.radar_rounded, label: 'Command', path: '/command-center'),
                      _QuickChip(icon: Icons.assessment_rounded, label: 'Reports', path: '/reports'),
                    ],
                  ),
                ]),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────
// Overview
// ─────────────────────────────────────────────────────────────
class _OverviewSection extends StatelessWidget {
  const _OverviewSection({required this.data});
  final OverviewKpi data;

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;
    return Column(
      children: [
        // Hero banner
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [AppColors.primary, AppColors.primaryContainer],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text("Today's Activity",
                  style: tt.labelMedium
                      ?.copyWith(color: Colors.white70, letterSpacing: 0.5)),
              const SizedBox(height: 12),
              Row(
                children: [
                  _HeroStat(label: 'Visits', value: _numberFormat.format(data.todayVisits)),
                  _HeroStat(label: 'Orders', value: _numberFormat.format(data.todayOrders)),
                  _HeroStat(label: 'Sales', value: _currencyFormat.format(data.todaySales)),
                  _HeroStat(label: 'Collected', value: _currencyFormat.format(data.todayCollections)),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 10),
        // KPI grid
        GridView.count(
          crossAxisCount: 3,
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          crossAxisSpacing: 8,
          mainAxisSpacing: 8,
          childAspectRatio: 1.15,
          children: [
            _KpiCard(label: 'Users', value: '', icon: Icons.people_rounded, accent: AppColors.primary),
            _KpiCard(label: 'Field Force', value: '', icon: Icons.groups_rounded, accent: AppColors.secondary),
            _KpiCard(label: 'Online', value: '', icon: Icons.wifi_rounded, accent: const Color(0xFF1B6D24)),
            _KpiCard(label: 'Stores', value: '', icon: Icons.store_rounded, accent: AppColors.tertiary),
            _KpiCard(label: 'Campaigns', value: '', icon: Icons.campaign_rounded, accent: const Color(0xFF7B5800)),
            _KpiCard(label: 'Routes', value: '', icon: Icons.route_rounded, accent: AppColors.primaryContainer),
          ],
        ),
      ],
    );
  }
}

class _HeroStat extends StatelessWidget {
  const _HeroStat({required this.label, required this.value});
  final String label, value;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Column(
        children: [
          Text(value,
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  color: Colors.white, fontWeight: FontWeight.w800)),
          const SizedBox(height: 2),
          Text(label,
              style: const TextStyle(
                  color: Colors.white70, fontSize: 10, fontFamily: 'Inter')),
        ],
      ),
    );
  }
}

class _KpiCard extends StatelessWidget {
  const _KpiCard({required this.label, required this.value, required this.icon, required this.accent});
  final String label, value;
  final IconData icon;
  final Color accent;

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLowest,
        borderRadius: BorderRadius.circular(12),
        border: Border(left: BorderSide(color: accent, width: 3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, color: accent, size: 18),
          const SizedBox(height: 6),
          Text(value,
              style: tt.headlineSmall
                  ?.copyWith(color: AppColors.onSurface, fontSize: 20)),
          Text(label,
              style: tt.bodySmall
                  ?.copyWith(color: AppColors.onSurfaceVariant),
              maxLines: 1,
              overflow: TextOverflow.ellipsis),
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────
// Sales
// ─────────────────────────────────────────────────────────────
class _SalesSection extends StatelessWidget {
  const _SalesSection({required this.data});
  final SalesKpi data;

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLowest,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Sales Performance',
                  style: tt.titleMedium?.copyWith(color: AppColors.onSurface)),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: AppColors.secondaryContainer,
                  borderRadius: BorderRadius.circular(100),
                ),
                child: Text(' orders',
                    style: tt.labelSmall?.copyWith(
                        color: AppColors.onSecondaryContainer,
                        letterSpacing: 0)),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Text(_currencyFormat.format(data.totalRevenue),
              style: tt.displayMedium?.copyWith(color: AppColors.primary)),
          const SizedBox(height: 4),
          Text('Total Revenue',
              style:
                  tt.bodySmall?.copyWith(color: AppColors.onSurfaceVariant)),
          const SizedBox(height: 16),
          Row(
            children: [
              _StatPill(
                  label: 'Avg Order',
                  value: _currencyFormat.format(data.avgOrderValue),
                  color: AppColors.primary),
              const SizedBox(width: 8),
              _StatPill(
                  label: 'Delivered',
                  value: '',
                  color: AppColors.secondary),
              const SizedBox(width: 8),
              _StatPill(
                  label: 'Cancelled',
                  value: '',
                  color: AppColors.error),
            ],
          ),
          if (data.topStores.isNotEmpty) ...[
            const SizedBox(height: 16),
            Divider(
                color: AppColors.outlineVariant.withOpacity(0.5), height: 1),
            const SizedBox(height: 12),
            Text('Top Stores',
                style: tt.labelMedium
                    ?.copyWith(color: AppColors.onSurfaceVariant)),
            const SizedBox(height: 8),
            ...data.topStores.take(5).map((s) => Padding(
                  padding: const EdgeInsets.symmetric(vertical: 3),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Flexible(
                          child: Text(s.name,
                              overflow: TextOverflow.ellipsis,
                              style: tt.bodyMedium
                                  ?.copyWith(color: AppColors.onSurface))),
                      Text(_currencyFormat.format(s.totalSales),
                          style: tt.labelMedium?.copyWith(
                              color: AppColors.secondary,
                              fontWeight: FontWeight.w700)),
                    ],
                  ),
                )),
          ],
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────
// Routes
// ─────────────────────────────────────────────────────────────
class _RoutesSection extends StatelessWidget {
  const _RoutesSection({required this.data});
  final RouteKpi data;

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLowest,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Route Performance',
              style: tt.titleMedium?.copyWith(color: AppColors.onSurface)),
          const SizedBox(height: 16),
          _ProgressBar(
            label: 'Route Completion',
            pct: data.completionRate,
            detail:
                '/ routes',
            color: AppColors.primary,
          ),
          const SizedBox(height: 12),
          _ProgressBar(
            label: 'Visit Completion',
            pct: data.visitCompletionRate,
            detail: '/ visits',
            color: AppColors.secondary,
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              _StatPill(
                  label: 'Avg Duration',
                  value: ' min',
                  color: AppColors.tertiary),
              const SizedBox(width: 8),
              _StatPill(
                  label: 'Skipped',
                  value: '',
                  color: AppColors.error),
            ],
          ),
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────
// Campaigns
// ─────────────────────────────────────────────────────────────
class _CampaignsSection extends StatelessWidget {
  const _CampaignsSection({required this.data});
  final CampaignKpi data;

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLowest,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Campaigns',
              style: tt.titleMedium?.copyWith(color: AppColors.onSurface)),
          const SizedBox(height: 16),
          _ProgressBar(
            label: 'Budget Utilization',
            pct: data.budgetUtilization,
            detail:
                ' / ',
            color: const Color(0xFF7B5800),
          ),
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────
// Shared widgets
// ─────────────────────────────────────────────────────────────
class _StatPill extends StatelessWidget {
  const _StatPill({required this.label, required this.value, required this.color});
  final String label, value;
  final Color color;

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(100),
      ),
      child: Column(
        children: [
          Text(value,
              style: tt.labelMedium
                  ?.copyWith(color: color, fontWeight: FontWeight.w700)),
          Text(label,
              style: tt.labelSmall?.copyWith(
                  color: color.withOpacity(0.75), letterSpacing: 0)),
        ],
      ),
    );
  }
}

class _ProgressBar extends StatelessWidget {
  const _ProgressBar({required this.label, required this.pct, required this.detail, required this.color});
  final String label, detail;
  final double pct;
  final Color color;

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(label,
                style: tt.bodySmall?.copyWith(color: AppColors.onSurface)),
            Text('%',
                style: tt.labelSmall?.copyWith(
                    color: color,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 0)),
          ],
        ),
        const SizedBox(height: 6),
        ClipRRect(
          borderRadius: BorderRadius.circular(100),
          child: LinearProgressIndicator(
            value: (pct / 100).clamp(0.0, 1.0),
            backgroundColor: AppColors.surfaceContainerHigh,
            valueColor: AlwaysStoppedAnimation<Color>(color),
            minHeight: 7,
          ),
        ),
        const SizedBox(height: 4),
        Text(detail,
            style: tt.labelSmall?.copyWith(
                color: AppColors.onSurfaceVariant, letterSpacing: 0)),
      ],
    );
  }
}

class _InquiryTile extends StatelessWidget {
  const _InquiryTile({
    required this.icon,
    required this.label,
    required this.color,
    required this.bgColor,
    required this.onTap,
  });
  final IconData icon;
  final String label;
  final Color color, bgColor;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 12),
          decoration: BoxDecoration(
            color: AppColors.surfaceContainerLowest,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
          ),
          child: Column(
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: bgColor.withOpacity(0.25),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, color: color, size: 22),
              ),
              const SizedBox(height: 8),
              Text(label,
                  style: tt.labelMedium
                      ?.copyWith(color: AppColors.onSurface),
                  textAlign: TextAlign.center),
            ],
          ),
        ),
      ),
    );
  }
}

class _QuickChip extends StatelessWidget {
  const _QuickChip({required this.icon, required this.label, required this.path});
  final IconData icon;
  final String label;
  final String path;

  @override
  Widget build(BuildContext context) {
    return ActionChip(
      avatar: Icon(icon, size: 16, color: AppColors.primary),
      label: Text(label),
      onPressed: () => context.push(path),
    );
  }
}

class _ShimmerCard extends StatelessWidget {
  const _ShimmerCard();

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 120,
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLow,
        borderRadius: BorderRadius.circular(16),
      ),
      child: const Center(
          child: CircularProgressIndicator(color: AppColors.primary)),
    );
  }
}

class _ErrorCard extends StatelessWidget {
  const _ErrorCard({required this.message});
  final String message;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.errorContainer,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text('Error: ',
          style: Theme.of(context)
              .textTheme
              .bodySmall
              ?.copyWith(color: AppColors.onErrorContainer)),
    );
  }
}
