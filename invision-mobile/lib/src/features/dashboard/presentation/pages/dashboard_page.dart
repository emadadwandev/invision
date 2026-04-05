import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../data/models/dashboard_models.dart';
import '../providers/dashboard_providers.dart';

final _currencyFormat = NumberFormat.currency(symbol: '\$', decimalDigits: 2);
final _numberFormat = NumberFormat('#,##0');

class DashboardPage extends ConsumerStatefulWidget {
  const DashboardPage({super.key});

  @override
  ConsumerState<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends ConsumerState<DashboardPage> {
  String _period = 'month';

  @override
  Widget build(BuildContext context) {
    final overview = ref.watch(overviewKpiProvider);
    final sales = ref.watch(salesKpiProvider(_period));
    final routes = ref.watch(routeKpiProvider(_period));
    final campaigns = ref.watch(campaignKpiProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Dashboard'),
        actions: [
          PopupMenuButton<String>(
            initialValue: _period,
            onSelected: (v) => setState(() => _period = v),
            icon: const Icon(Icons.calendar_today, size: 20),
            itemBuilder: (_) => const [
              PopupMenuItem(value: 'week', child: Text('This Week')),
              PopupMenuItem(value: 'month', child: Text('This Month')),
              PopupMenuItem(value: 'quarter', child: Text('This Quarter')),
              PopupMenuItem(value: 'year', child: Text('This Year')),
            ],
          ),
          IconButton(
            onPressed: () => context.go('/login'),
            icon: const Icon(Icons.logout),
            tooltip: 'Logout',
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(overviewKpiProvider);
          ref.invalidate(salesKpiProvider(_period));
          ref.invalidate(routeKpiProvider(_period));
          ref.invalidate(campaignKpiProvider);
        },
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Overview KPIs
            overview.when(
              data: (o) => _OverviewSection(data: o),
              loading: () => const _LoadingCard(),
              error: (e, _) => _ErrorCard(message: e.toString()),
            ),
            const SizedBox(height: 16),

            // Sales KPIs
            sales.when(
              data: (s) => _SalesSection(data: s),
              loading: () => const _LoadingCard(),
              error: (e, _) => _ErrorCard(message: e.toString()),
            ),
            const SizedBox(height: 16),

            // Route KPIs
            routes.when(
              data: (r) => _RoutesSection(data: r),
              loading: () => const _LoadingCard(),
              error: (e, _) => _ErrorCard(message: e.toString()),
            ),
            const SizedBox(height: 16),

            // Campaign KPIs
            campaigns.when(
              data: (c) => _CampaignsSection(data: c),
              loading: () => const _LoadingCard(),
              error: (e, _) => _ErrorCard(message: e.toString()),
            ),
            const SizedBox(height: 24),

            // Inquiry links
            Text('Inquiry Screens',
                style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            _NavTile(
              icon: Icons.store,
              label: 'Store Inquiry',
              color: Colors.purple,
              onTap: () => context.push('/inquiry/stores'),
            ),
            const SizedBox(height: 8),
            _NavTile(
              icon: Icons.attach_money,
              label: 'Sales Inquiry',
              color: Colors.green,
              onTap: () => context.push('/inquiry/sales'),
            ),
            const SizedBox(height: 8),
            _NavTile(
              icon: Icons.map,
              label: 'Route Inquiry',
              color: Colors.blue,
              onTap: () => context.push('/inquiry/routes'),
            ),
            const SizedBox(height: 24),

            // Quick Access nav
            Text('Quick Access',
                style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                _QuickChip(icon: Icons.store, label: 'Stores', path: '/stores'),
                _QuickChip(icon: Icons.inventory_2, label: 'Products', path: '/products'),
                _QuickChip(icon: Icons.route, label: 'My Route', path: '/my-route'),
                _QuickChip(icon: Icons.map, label: 'Routes', path: '/routes'),
                _QuickChip(icon: Icons.campaign, label: 'Campaigns', path: '/campaigns'),
                _QuickChip(icon: Icons.task_alt, label: 'My Tasks', path: '/my-tasks'),
                _QuickChip(icon: Icons.point_of_sale, label: 'Sales', path: '/sales'),
                _QuickChip(icon: Icons.receipt_long, label: 'My Orders', path: '/my-orders'),
                _QuickChip(icon: Icons.point_of_sale, label: 'POS', path: '/pos'),
                _QuickChip(icon: Icons.inventory, label: 'Inventory', path: '/inventory'),
                _QuickChip(icon: Icons.notifications, label: 'Notifications', path: '/notifications'),
                _QuickChip(icon: Icons.inbox, label: 'Inbox', path: '/inbox'),
                _QuickChip(icon: Icons.assignment, label: 'Tasks', path: '/assigned-tasks'),
                _QuickChip(icon: Icons.radar, label: 'Command Center', path: '/command-center'),
                _QuickChip(icon: Icons.assessment, label: 'Reports', path: '/reports'),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Overview section
// ---------------------------------------------------------------------------
class _OverviewSection extends StatelessWidget {
  const _OverviewSection({required this.data});
  final OverviewKpi data;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Top KPI row
        Row(
          children: [
            _KpiCard(label: 'Users', value: '${data.totalUsers}', icon: Icons.people, color: Colors.indigo),
            const SizedBox(width: 8),
            _KpiCard(label: 'Field Force', value: '${data.fieldForceCount}', icon: Icons.groups, color: Colors.blue),
            const SizedBox(width: 8),
            _KpiCard(label: 'Online', value: '${data.onlineNow}', icon: Icons.wifi, color: Colors.green),
          ],
        ),
        const SizedBox(height: 8),
        Row(
          children: [
            _KpiCard(label: 'Stores', value: '${data.totalStores}', icon: Icons.store, color: Colors.purple),
            const SizedBox(width: 8),
            _KpiCard(label: 'Campaigns', value: '${data.activeCampaigns}', icon: Icons.star, color: Colors.amber),
            const SizedBox(width: 8),
            _KpiCard(label: 'Routes', value: '${data.activeRoutes}', icon: Icons.route, color: Colors.teal),
          ],
        ),
        const SizedBox(height: 12),
        // Today stats
        Card(
          color: Colors.indigo.shade700,
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _TodayStat(label: 'Visits', value: _numberFormat.format(data.todayVisits)),
                _TodayStat(label: 'Orders', value: _numberFormat.format(data.todayOrders)),
                _TodayStat(label: 'Sales', value: _currencyFormat.format(data.todaySales)),
                _TodayStat(label: 'Collected', value: _currencyFormat.format(data.todayCollections)),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

class _TodayStat extends StatelessWidget {
  const _TodayStat({required this.label, required this.value});
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(label, style: const TextStyle(color: Colors.white70, fontSize: 11)),
        const SizedBox(height: 4),
        Text(value, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14)),
      ],
    );
  }
}

// ---------------------------------------------------------------------------
// Sales section
// ---------------------------------------------------------------------------
class _SalesSection extends StatelessWidget {
  const _SalesSection({required this.data});
  final SalesKpi data;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Sales Performance', style: Theme.of(context).textTheme.titleSmall),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(child: _MetricTile(label: 'Revenue', value: _currencyFormat.format(data.totalRevenue), color: Colors.green)),
                const SizedBox(width: 8),
                Expanded(child: _MetricTile(label: 'Avg Order', value: _currencyFormat.format(data.avgOrderValue), color: Colors.blue)),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(child: _MetricTile(label: 'Total Orders', value: '${data.totalOrders}', color: Colors.grey)),
                const SizedBox(width: 8),
                Expanded(child: _MetricTile(label: 'Delivered', value: '${data.deliveredCount}', color: Colors.green)),
                const SizedBox(width: 8),
                Expanded(child: _MetricTile(label: 'Cancelled', value: '${data.cancelledCount}', color: Colors.red)),
              ],
            ),
            if (data.topStores.isNotEmpty) ...[
              const Divider(height: 24),
              Text('Top Stores', style: Theme.of(context).textTheme.labelLarge),
              const SizedBox(height: 8),
              ...data.topStores.take(5).map((s) => Padding(
                    padding: const EdgeInsets.symmetric(vertical: 2),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Flexible(child: Text(s.name, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 13))),
                        Text(_currencyFormat.format(s.totalSales), style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Colors.green)),
                      ],
                    ),
                  )),
            ],
          ],
        ),
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Routes section
// ---------------------------------------------------------------------------
class _RoutesSection extends StatelessWidget {
  const _RoutesSection({required this.data});
  final RouteKpi data;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Route Performance', style: Theme.of(context).textTheme.titleSmall),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: _ProgressMetric(
                    label: 'Route Completion',
                    pct: data.completionRate,
                    detail: '${data.completedInstances}/${data.totalRouteInstances}',
                    color: Colors.indigo,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _ProgressMetric(
                    label: 'Visit Completion',
                    pct: data.visitCompletionRate,
                    detail: '${data.completedVisits}/${data.totalVisits}',
                    color: Colors.blue,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(child: _MetricTile(label: 'Avg Duration', value: '${data.avgVisitDuration} min', color: Colors.orange)),
                const SizedBox(width: 8),
                Expanded(child: _MetricTile(label: 'Skipped', value: '${data.skippedVisits}', color: Colors.red)),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Campaigns section
// ---------------------------------------------------------------------------
class _CampaignsSection extends StatelessWidget {
  const _CampaignsSection({required this.data});
  final CampaignKpi data;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Campaigns', style: Theme.of(context).textTheme.titleSmall),
            const SizedBox(height: 12),
            _ProgressMetric(
              label: 'Budget Utilization',
              pct: data.budgetUtilization,
              detail: '${_currencyFormat.format(data.totalSpent)} / ${_currencyFormat.format(data.totalBudget)}',
              color: Colors.amber,
            ),
          ],
        ),
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Shared widgets
// ---------------------------------------------------------------------------
class _KpiCard extends StatelessWidget {
  const _KpiCard({required this.label, required this.value, required this.icon, required this.color});
  final String label;
  final String value;
  final IconData icon;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Card(
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Column(
            children: [
              Icon(icon, color: color, size: 22),
              const SizedBox(height: 4),
              Text(value, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              Text(label, style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
            ],
          ),
        ),
      ),
    );
  }
}

class _MetricTile extends StatelessWidget {
  const _MetricTile({required this.label, required this.value, required this.color});
  final String label;
  final String value;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 12),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: TextStyle(fontSize: 11, color: color)),
          const SizedBox(height: 2),
          Text(value, style: TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: color)),
        ],
      ),
    );
  }
}

class _ProgressMetric extends StatelessWidget {
  const _ProgressMetric({required this.label, required this.pct, required this.detail, required this.color});
  final String label;
  final double pct;
  final String detail;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(label, style: const TextStyle(fontSize: 12)),
            Text('${pct.toStringAsFixed(1)}%', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: color)),
          ],
        ),
        const SizedBox(height: 4),
        ClipRRect(
          borderRadius: BorderRadius.circular(4),
          child: LinearProgressIndicator(
            value: (pct / 100).clamp(0.0, 1.0),
            backgroundColor: Colors.grey.shade200,
            color: color,
            minHeight: 6,
          ),
        ),
        const SizedBox(height: 2),
        Text(detail, style: TextStyle(fontSize: 10, color: Colors.grey.shade500)),
      ],
    );
  }
}

class _NavTile extends StatelessWidget {
  const _NavTile({required this.icon, required this.label, required this.onTap, this.color});
  final IconData icon;
  final String label;
  final VoidCallback onTap;
  final Color? color;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: ListTile(
        leading: Icon(icon, color: color ?? Theme.of(context).colorScheme.primary),
        title: Text(label),
        trailing: const Icon(Icons.chevron_right),
        onTap: onTap,
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
      avatar: Icon(icon, size: 16),
      label: Text(label, style: const TextStyle(fontSize: 12)),
      onPressed: () => context.push(path),
    );
  }
}

class _LoadingCard extends StatelessWidget {
  const _LoadingCard();

  @override
  Widget build(BuildContext context) {
    return const Card(child: Padding(padding: EdgeInsets.all(32), child: Center(child: CircularProgressIndicator())));
  }
}

class _ErrorCard extends StatelessWidget {
  const _ErrorCard({required this.message});
  final String message;

  @override
  Widget build(BuildContext context) {
    return Card(
      color: Colors.red.shade50,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Text('Error: $message', style: TextStyle(color: Colors.red.shade700, fontSize: 13)),
      ),
    );
  }
}
