import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

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
      appBar: AppBar(title: const Text('Inbox')),
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
                      hintText: 'Search messages...',
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
            child: inboxAsync.when(
              data: (messages) => messages.isEmpty
                  ? const Center(child: Text('No messages.'))
                  : RefreshIndicator(
                      onRefresh: () async =>
                          ref.invalidate(inboxProvider(_filter)),
                      child: ListView.builder(
                        itemCount: messages.length,
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        itemBuilder: (context, index) =>
                            _MessageCard(message: messages[index]),
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

class _MessageCard extends StatelessWidget {
  const _MessageCard({required this.message});

  final MessageItem message;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        onTap: () => context.push('/messages/${message.id}'),
        leading: Icon(
          message.isGroup ? Icons.group : Icons.person,
          color: theme.colorScheme.primary,
        ),
        title: Text(message.subject, style: theme.textTheme.titleSmall),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('From: ${message.senderName ?? 'Unknown'}',
                style: theme.textTheme.bodySmall),
            if (message.isGroup)
              Chip(
                label: const Text('Group', style: TextStyle(fontSize: 10)),
                padding: EdgeInsets.zero,
                materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                visualDensity: VisualDensity.compact,
              ),
          ],
        ),
        trailing: Text(
          message.createdAt?.substring(0, 10) ?? '',
          style: theme.textTheme.bodySmall,
        ),
      ),
    );
  }
}
