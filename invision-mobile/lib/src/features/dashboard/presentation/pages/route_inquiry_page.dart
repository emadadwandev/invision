import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../data/models/inquiry_models.dart';
import '../providers/dashboard_providers.dart';

class RouteInquiryPage extends ConsumerStatefulWidget {
  const RouteInquiryPage({super.key});

  @override
  ConsumerState<RouteInquiryPage> createState() => _RouteInquiryPageState();
}

class _RouteInquiryPageState extends ConsumerState<RouteInquiryPage> {
  String? _status;

  RouteInquiryFilter get _filter => RouteInquiryFilter(status: _status);

  @override
  Widget build(BuildContext context) {
    final routes = ref.watch(routeInquiryProvider(_filter));

    return Scaffold(
      appBar: AppBar(title: const Text('Route Inquiry')),
      body: Column(
        children: [
          Container(
            color: Colors.grey.shade50,
            padding: const EdgeInsets.all(12),
            child: DropdownButtonFormField<String>(
              key: ValueKey(_status),
              initialValue: _status,
              decoration: const InputDecoration(labelText: 'Status', isDense: true, border: OutlineInputBorder()),
              items: const [
                DropdownMenuItem(value: null, child: Text('All')),
                DropdownMenuItem(value: 'pending', child: Text('Pending')),
                DropdownMenuItem(value: 'in_progress', child: Text('In Progress')),
                DropdownMenuItem(value: 'completed', child: Text('Completed')),
                DropdownMenuItem(value: 'cancelled', child: Text('Cancelled')),
              ],
              onChanged: (v) => setState(() => _status = v),
            ),
          ),
          Expanded(
            child: routes.when(
              data: (list) => _RouteList(routes: list),
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (e, _) => Center(child: Text('Error: $e')),
            ),
          ),
        ],
      ),
    );
  }
}

class _RouteList extends StatelessWidget {
  const _RouteList({required this.routes});
  final List<RouteInquiryItem> routes;

  @override
  Widget build(BuildContext context) {
    if (routes.isEmpty) {
      return const Center(child: Text('No route instances found.'));
    }
    return ListView.separated(
      padding: const EdgeInsets.all(12),
      itemCount: routes.length,
      separatorBuilder: (_, _) => const SizedBox(height: 8),
      itemBuilder: (_, i) {
        final r = routes[i];
        final statusColor = switch (r.status) {
          'completed' => Colors.green,
          'in_progress' => Colors.blue,
          'cancelled' => Colors.red,
          _ => Colors.grey,
        };
        return Card(
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(child: Text(r.routeName ?? '-', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14))),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: statusColor.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(r.status.replaceAll('_', ' ').toUpperCase(),
                          style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: statusColor)),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Text('${r.user ?? '-'}  ·  ${r.routeDate}', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                const SizedBox(height: 8),
                // Progress bar
                Row(
                  children: [
                    Expanded(
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(4),
                        child: LinearProgressIndicator(
                          value: (r.completionPct / 100).clamp(0.0, 1.0),
                          backgroundColor: Colors.grey.shade200,
                          color: Colors.indigo,
                          minHeight: 8,
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text('${r.completionPct.toStringAsFixed(0)}%',
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                  ],
                ),
                const SizedBox(height: 4),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text('${r.completedVisits}/${r.totalVisits} visits',
                        style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
                    if (r.distanceKm != null)
                      Text('${r.distanceKm!.toStringAsFixed(1)} km',
                          style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
