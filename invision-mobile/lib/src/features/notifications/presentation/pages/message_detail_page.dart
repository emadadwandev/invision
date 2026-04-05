import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/theme/app_theme.dart';
import '../providers/notifications_providers.dart';

class MessageDetailPage extends ConsumerWidget {
  const MessageDetailPage({super.key, required this.messageId});

  final int messageId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final messageAsync = ref.watch(messageDetailProvider(messageId));

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Message',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: messageAsync.when(
        data: (message) {
          Future.microtask(() {
            ref.read(notificationsRepositoryProvider).markMessageRead(messageId);
          });

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(18),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [AppColors.primary, AppColors.primaryContainer],
                      begin: Alignment.topLeft, end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(message.subject,
                          style: Theme.of(context).textTheme.headlineSmall
                              ?.copyWith(color: Colors.white)),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          const Icon(Icons.person_rounded, size: 14, color: Colors.white70),
                          const SizedBox(width: 4),
                          Text('From: ${message.senderName ?? 'Unknown'}',
                              style: const TextStyle(color: Colors.white70, fontSize: 13)),
                          const Spacer(),
                          Text(message.createdAt?.substring(0, 10) ?? '',
                              style: const TextStyle(color: Colors.white54, fontSize: 12)),
                        ],
                      ),
                      if (message.isGroup) ...[const SizedBox(height: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.2),
                            borderRadius: BorderRadius.circular(100),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.group_rounded, size: 12, color: Colors.white),
                              SizedBox(width: 4),
                              Text('Group Message',
                                  style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w700)),
                            ],
                          ),
                        )],
                    ],
                  ),
                ),
                if (message.recipients != null && message.recipients!.isNotEmpty) ...[const SizedBox(height: 14),
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: AppColors.surfaceContainerLowest,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Recipients',
                            style: Theme.of(context).textTheme.titleSmall
                                ?.copyWith(color: AppColors.onSurface, fontWeight: FontWeight.w700)),
                        const SizedBox(height: 10),
                        Wrap(
                          spacing: 8, runSpacing: 6,
                          children: message.recipients!.map((r) {
                            return Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: r.readAt != null ? AppColors.secondaryContainer : AppColors.surfaceContainerHigh,
                                borderRadius: BorderRadius.circular(100),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    r.readAt != null ? Icons.check_circle_rounded : Icons.circle_outlined,
                                    size: 12,
                                    color: r.readAt != null ? AppColors.secondary : AppColors.outline,
                                  ),
                                  const SizedBox(width: 4),
                                  Text(r.name,
                                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600,
                                          color: AppColors.onSurface)),
                                ],
                              ),
                            );
                          }).toList(),
                        ),
                      ],
                    ),
                  )],
                const SizedBox(height: 14),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppColors.surfaceContainerLowest,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
                  ),
                  child: Text(message.body,
                      style: Theme.of(context).textTheme.bodyLarge
                          ?.copyWith(color: AppColors.onSurface)),
                ),
              ],
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}
