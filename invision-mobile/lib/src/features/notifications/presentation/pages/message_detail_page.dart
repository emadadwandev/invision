import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../providers/notifications_providers.dart';

class MessageDetailPage extends ConsumerWidget {
  const MessageDetailPage({super.key, required this.messageId});

  final int messageId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final messageAsync = ref.watch(messageDetailProvider(messageId));

    return Scaffold(
      appBar: AppBar(title: const Text('Message')),
      body: messageAsync.when(
        data: (message) {
          // Mark as read on view
          Future.microtask(() {
            ref.read(notificationsRepositoryProvider).markMessageRead(messageId);
          });

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(message.subject,
                    style: Theme.of(context).textTheme.headlineSmall),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Icon(Icons.person, size: 16,
                        color: Theme.of(context).colorScheme.primary),
                    const SizedBox(width: 4),
                    Text('From: ${message.senderName ?? 'Unknown'}',
                        style: Theme.of(context).textTheme.bodyMedium),
                    const Spacer(),
                    Text(message.createdAt?.substring(0, 10) ?? '',
                        style: Theme.of(context).textTheme.bodySmall),
                  ],
                ),
                if (message.isGroup) ...[
                  const SizedBox(height: 4),
                  Chip(
                    label: const Text('Group Message',
                        style: TextStyle(fontSize: 11)),
                    avatar: const Icon(Icons.group, size: 16),
                    padding: EdgeInsets.zero,
                    materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                    visualDensity: VisualDensity.compact,
                  ),
                ],
                const Divider(height: 24),
                if (message.recipients != null &&
                    message.recipients!.isNotEmpty) ...[
                  Text('Recipients',
                      style: Theme.of(context).textTheme.titleSmall),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8,
                    runSpacing: 4,
                    children: message.recipients!.map((r) {
                      return Chip(
                        label: Text(r.name, style: const TextStyle(fontSize: 12)),
                        avatar: Icon(
                          r.readAt != null ? Icons.check_circle : Icons.circle_outlined,
                          size: 16,
                          color: r.readAt != null ? Colors.green : Colors.grey,
                        ),
                        padding: EdgeInsets.zero,
                        materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        visualDensity: VisualDensity.compact,
                      );
                    }).toList(),
                  ),
                  const Divider(height: 24),
                ],
                Text(message.body,
                    style: Theme.of(context).textTheme.bodyLarge),
              ],
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}
