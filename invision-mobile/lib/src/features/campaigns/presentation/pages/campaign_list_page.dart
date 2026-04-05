import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/enums/campaign_status.dart';
import '../../data/models/campaign_model.dart';
import '../providers/campaign_providers.dart';

class CampaignListPage extends ConsumerStatefulWidget {
  const CampaignListPage({super.key});

  @override
  ConsumerState<CampaignListPage> createState() => _CampaignListPageState();
}

class _CampaignListPageState extends ConsumerState<CampaignListPage> {
  final _searchController = TextEditingController();
  CampaignFilter _filter = const CampaignFilter();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _onSearch() {
    setState(() {
      _filter = _filter.copyWith(search: _searchController.text);
    });
  }

  @override
  Widget build(BuildContext context) {
    final campaignsAsync = ref.watch(campaignsProvider(_filter));

    return Scaffold(
      appBar: AppBar(title: const Text('Campaigns')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(12),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _searchController,
                    decoration: const InputDecoration(
                      hintText: 'Search campaigns...',
                      prefixIcon: Icon(Icons.search),
                      border: OutlineInputBorder(),
                      isDense: true,
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
            child: campaignsAsync.when(
              data: (campaigns) => campaigns.isEmpty
                  ? const Center(child: Text('No campaigns found.'))
                  : RefreshIndicator(
                      onRefresh: () async =>
                          ref.invalidate(campaignsProvider(_filter)),
                      child: ListView.builder(
                        itemCount: campaigns.length,
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        itemBuilder: (context, index) =>
                            _CampaignCard(campaign: campaigns[index]),
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

class _CampaignCard extends StatelessWidget {
  const _CampaignCard({required this.campaign});

  final Campaign campaign;

  Color _statusColor(CampaignStatus status) {
    return switch (status) {
      CampaignStatus.draft => Colors.grey,
      CampaignStatus.scheduled => Colors.blue,
      CampaignStatus.active => Colors.green,
      CampaignStatus.paused => Colors.orange,
      CampaignStatus.completed => Colors.teal,
      CampaignStatus.cancelled => Colors.red,
    };
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        title: Text(campaign.name, style: theme.textTheme.titleSmall),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              '${campaign.type.label} · ${campaign.startDate} — ${campaign.endDate}',
              style: theme.textTheme.bodySmall,
            ),
            if (campaign.budget != null)
              Text(
                'Budget: \$${campaign.budget!.toStringAsFixed(2)}',
                style: theme.textTheme.bodySmall,
              ),
          ],
        ),
        trailing: Chip(
          label: Text(
            campaign.status.label,
            style: TextStyle(
              color: _statusColor(campaign.status),
              fontSize: 10,
              fontWeight: FontWeight.bold,
            ),
          ),
          backgroundColor:
              _statusColor(campaign.status).withValues(alpha: 0.1),
          side: BorderSide.none,
          padding: EdgeInsets.zero,
          visualDensity: VisualDensity.compact,
        ),
        onTap: () => context.push('/campaigns/${campaign.id}'),
      ),
    );
  }
}
