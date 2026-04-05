import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/theme/app_theme.dart';
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
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Competitors',
            style: Theme.of(context).textTheme.headlineMedium
                ?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.analytics_outlined,
                color: AppColors.onSurface),
            tooltip: 'Analysis',
            onPressed: () => context.push('/competitors/analysis'),
          ),
        ],
      ),
      body: Column(
        children: [
          Container(
            color: AppColors.surfaceContainerLow,
            padding: const EdgeInsets.all(12),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _searchController,
                    decoration: InputDecoration(
                      hintText: 'Search competitors...',
                      prefixIcon: const Icon(Icons.search_rounded, size: 20,
                          color: AppColors.outline),
                      isDense: true,
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10)),
                      contentPadding: const EdgeInsets.symmetric(
                          horizontal: 12, vertical: 10),
                    ),
                    onSubmitted: (_) => _onSearch(),
                  ),
                ),
                const SizedBox(width: 8),
                GestureDetector(
                  onTap: _onSearch,
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 16, vertical: 11),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [AppColors.primary, AppColors.primaryContainer],
                      ),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Text('Search',
                        style: TextStyle(
                            color: Colors.white, fontWeight: FontWeight.w700)),
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: competitorsAsync.when(
              data: (competitors) => competitors.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Container(
                            width: 64, height: 64,
                            decoration: BoxDecoration(
                              color: AppColors.surfaceContainerHigh,
                              borderRadius: BorderRadius.circular(16),
                            ),
                            child: const Icon(Icons.business_rounded,
                                size: 28, color: AppColors.onSurfaceVariant),
                          ),
                          const SizedBox(height: 12),
                          const Text('No competitors found.',
                              style: TextStyle(
                                  color: AppColors.onSurfaceVariant)),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: () async =>
                          ref.invalidate(competitorsProvider(_search)),
                      child: ListView.builder(
                        padding: const EdgeInsets.all(12),
                        itemCount: competitors.length,
                        itemBuilder: (context, index) =>
                            _CompetitorCard(competitor: competitors[index]),
                      ),
                    ),
              loading: () => const Center(
                  child: CircularProgressIndicator(color: AppColors.primary)),
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
    return GestureDetector(
      onTap: () => context.push('/competitors/${competitor.id}'),
      child: Container(
        margin: const EdgeInsets.only(bottom: 8),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surfaceContainerLowest,
          borderRadius: BorderRadius.circular(14),
          border: Border(left: BorderSide(
            color: competitor.isActive ? AppColors.secondary : AppColors.error,
            width: 3,
          )),
        ),
        child: Row(
          children: [
            Container(
              width: 40, height: 40,
              decoration: BoxDecoration(
                color: competitor.isActive
                    ? AppColors.secondary.withOpacity(0.12)
                    : AppColors.errorContainer,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Center(
                child: Text(
                  competitor.name.substring(0, 1).toUpperCase(),
                  style: TextStyle(
                    fontSize: 18, fontWeight: FontWeight.w900,
                    color: competitor.isActive
                        ? AppColors.secondary
                        : AppColors.error,
                  ),
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(competitor.name,
                      style: const TextStyle(
                          fontWeight: FontWeight.w700,
                          color: AppColors.onSurface)),
                  if (competitor.description != null)
                    Text(
                      competitor.description!,
                      maxLines: 1, overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                          fontSize: 12,
                          color: AppColors.onSurfaceVariant),
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
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(
                color: competitor.isActive
                    ? AppColors.secondaryContainer
                    : AppColors.errorContainer,
                borderRadius: BorderRadius.circular(100),
              ),
              child: Text(
                competitor.isActive ? 'Active' : 'Inactive',
                style: TextStyle(
                    fontSize: 11, fontWeight: FontWeight.w700,
                    color: competitor.isActive
                        ? AppColors.secondary
                        : AppColors.error),
              ),
            ),
          ],
        ),
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
        Icon(icon, size: 13, color: AppColors.outline),
        const SizedBox(width: 4),
        Text(label,
            style: const TextStyle(
                fontSize: 11, color: AppColors.onSurfaceVariant)),
      ],
    );
  }
}
