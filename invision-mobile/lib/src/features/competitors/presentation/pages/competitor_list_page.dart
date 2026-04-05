import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../data/models/competitor_models.dart';
import '../providers/competitor_providers.dart';

class CompetitorListPage extends ConsumerStatefulWidget {
  const CompetitorListPage({super.key});

  @override
  ConsumerState<CompetitorListPage> createState() => _CompetitorListPageState();
}

class _CompetitorListPageState extends ConsumerState<CompetitorListPage> {
  final _searchController = TextEditingController();
  String? _search;

  void _onSearch() {
    setState(() {
      _search =
          _searchController.text.isEmpty ? null : _searchController.text;
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final competitorsAsync = ref.watch(competitorsProvider(_search));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Competitors'),
        actions: [
          IconButton(
            icon: const Icon(Icons.analytics_outlined),
            tooltip: 'Analysis',
            onPressed: () => context.push('/competitors/analysis'),
          ),
        ],
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _searchController,
                    decoration: InputDecoration(
                      hintText: 'Search competitors...',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                          horizontal: 12, vertical: 8),
                    ),
                    onSubmitted: (_) => _onSearch(),
                  ),
                ),
                const SizedBox(width: 8),
                FilledButton(
                  onPressed: _onSearch,
                  child: const Text('Search'),
                ),
              ],
            ),
          ),
          Expanded(
            child: competitorsAsync.when(
              data: (competitors) => competitors.isEmpty
                  ? const Center(child: Text('No competitors found.'))
                  : RefreshIndicator(
                      onRefresh: () async =>
                          ref.invalidate(competitorsProvider(_search)),
                      child: ListView.builder(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        itemCount: competitors.length,
                        itemBuilder: (context, index) =>
                            _CompetitorCard(competitor: competitors[index]),
                      ),
                    ),
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (e, _) => Center(child: Text('Error: $e')),
            ),
          ),
        ],
      ),
    );
  }
}

class _CompetitorCard extends StatelessWidget {
  const _CompetitorCard({required this.competitor});
  final Competitor competitor;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor:
              competitor.isActive ? Colors.green.shade100 : Colors.red.shade100,
          child: Text(
            competitor.name.substring(0, 1).toUpperCase(),
            style: TextStyle(
              color: competitor.isActive ? Colors.green : Colors.red,
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
        title: Text(competitor.name,
            style: const TextStyle(fontWeight: FontWeight.w600)),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (competitor.description != null)
              Text(
                competitor.description!,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(fontSize: 12),
              ),
            const SizedBox(height: 4),
            Row(
              children: [
                _InfoChip(
                    label: '${competitor.productsCount ?? 0} products',
                    icon: Icons.inventory_2_outlined),
                const SizedBox(width: 8),
                _InfoChip(
                    label: '${competitor.observationsCount ?? 0} obs',
                    icon: Icons.visibility_outlined),
              ],
            ),
          ],
        ),
        trailing: Chip(
          label: Text(
            competitor.isActive ? 'Active' : 'Inactive',
            style: TextStyle(
              fontSize: 11,
              color: competitor.isActive ? Colors.green : Colors.red,
            ),
          ),
          backgroundColor: competitor.isActive
              ? Colors.green.shade50
              : Colors.red.shade50,
          side: BorderSide.none,
          padding: EdgeInsets.zero,
          visualDensity: VisualDensity.compact,
        ),
        onTap: () => context.push('/competitors/${competitor.id}'),
      ),
    );
  }
}

class _InfoChip extends StatelessWidget {
  const _InfoChip({required this.label, required this.icon});
  final String label;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 14, color: Colors.grey),
        const SizedBox(width: 4),
        Text(label, style: const TextStyle(fontSize: 11, color: Colors.grey)),
      ],
    );
  }
}
