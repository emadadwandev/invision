import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/theme/app_theme.dart';
import '../../data/models/message_model.dart';
import '../providers/notifications_providers.dart';

class InboxPage extends ConsumerStatefulWidget {
  const InboxPage({super.key});

  @override
  ConsumerState<InboxPage> createState() => _InboxPageState();
}

class _InboxPageState extends ConsumerState<InboxPage> {
  final _searchController = TextEditingController();
  InboxFilter _filter = const InboxFilter();

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
    final inboxAsync = ref.watch(inboxProvider(_filter));

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Inbox',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(12, 12, 12, 0),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _searchController,
                    decoration: const InputDecoration(
                      hintText: 'Search messages...',
                      prefixIcon: Icon(Icons.search_rounded, size: 20, color: AppColors.outline),
                    ),
                    onSubmitted: (_) => _onSearch(),
                  ),
                ),
                const SizedBox(width: 8),
                GestureDetector(
                  onTap: _onSearch,
                  child: Container(
                    width: 46, height: 46,
                    decoration: BoxDecoration(
                      color: AppColors.primary,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.search_rounded, color: Colors.white, size: 22),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),
          Expanded(
            child: inboxAsync.when(
              data: (messages) => messages.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Container(
                            width: 64, height: 64,
                            decoration: BoxDecoration(
                                color: AppColors.surfaceContainerHigh,
                                borderRadius: BorderRadius.circular(16)),
                            child: const Icon(Icons.inbox_rounded, size: 32, color: AppColors.outline),
                          ),
                          const SizedBox(height: 12),
                          Text('No messages.',
                              style: Theme.of(context).textTheme.bodyLarge
                                  ?.copyWith(color: AppColors.onSurfaceVariant)),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: () async => ref.invalidate(inboxProvider(_filter)),
                      child: ListView.builder(
                        itemCount: messages.length,
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        itemBuilder: (context, index) =>
                            _MessageCard(message: messages[index]),
                      ),
                    ),
              loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
              error: (e, _) => Center(child: Text('Error: $e')),
            ),
          ),
        ],
      ),
    );
  }
}

class _MessageCard extends StatelessWidget {
  const _MessageCard({required this.message});

  final MessageItem message;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return GestureDetector(
      onTap: () => context.push('/messages/${message.id}'),
      child: Container(
        margin: const EdgeInsets.only(bottom: 8),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surfaceContainerLowest,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
        ),
        child: Row(
          children: [
            Container(
              width: 36, height: 36,
              decoration: BoxDecoration(
                color: AppColors.surfaceContainerLow,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(
                message.isGroup ? Icons.group_rounded : Icons.person_rounded,
                color: AppColors.primary, size: 18,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(message.subject,
                      style: Theme.of(context).textTheme.titleSmall
                          ?.copyWith(color: AppColors.onSurface, fontWeight: FontWeight.w700)),
                  const SizedBox(height: 2),
                  Text('From: ${message.senderName ?? 'Unknown'}',
                      style: Theme.of(context).textTheme.bodySmall
                          ?.copyWith(color: AppColors.onSurfaceVariant)),
                  if (message.isGroup) ...[const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                      decoration: BoxDecoration(
                          color: AppColors.surfaceContainerHigh,
                          borderRadius: BorderRadius.circular(100)),
                      child: const Text('Group',
                          style: TextStyle(fontSize: 9, color: AppColors.primary, fontWeight: FontWeight.w700)),
                    )],
                ],
              ),
            ),
            Text(
              message.createdAt?.substring(0, 10) ?? '',
              style: Theme.of(context).textTheme.bodySmall?.copyWith(color: AppColors.onSurfaceVariant),
            ),
          ],
        ),
      ),
    );
  }
}
