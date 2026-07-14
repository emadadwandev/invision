import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/theme/app_theme.dart';
import '../../data/models/store_model.dart';
import '../../presentation/providers/store_providers.dart';

class StoreDetailPage extends ConsumerWidget {
  const StoreDetailPage({required this.storeId, super.key});

  final int storeId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final storeAsync = ref.watch(storeDetailProvider(storeId));

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Store Details',
            style: Theme.of(context)
                .textTheme
                .headlineMedium
                ?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0,
        scrolledUnderElevation: 0,
      ),
      body: storeAsync.when(
        data: (store) => _StoreDetailBody(store: store),
        loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}

class _StoreDetailBody extends StatelessWidget {
  const _StoreDetailBody({required this.store});

  final Store store;

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Header card
        Container(
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
              Row(
                children: [
                  Expanded(
                    child: Text(store.name,
                        style: tt.headlineSmall?.copyWith(color: Colors.white)),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                    decoration: BoxDecoration(
                      color: store.isActive
                          ? Colors.white.withOpacity(0.25)
                          : Colors.red.withOpacity(0.3),
                      borderRadius: BorderRadius.circular(100),
                    ),
                    child: Text(
                      store.isActive ? 'Active' : 'Inactive',
                      style: const TextStyle(
                          color: Colors.white,
                          fontSize: 11,
                          fontWeight: FontWeight.w700),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              Text('Code: ${store.code}',
                  style: tt.bodyMedium?.copyWith(color: Colors.white70)),
            ],
          ),
        ),
        const SizedBox(height: 14),

        // Info card
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: AppColors.surfaceContainerLowest,
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Information', style: tt.titleMedium?.copyWith(color: AppColors.onSurface)),
              const SizedBox(height: 12),
              Divider(color: AppColors.outlineVariant.withOpacity(0.5), height: 1),
              const SizedBox(height: 12),
              _InfoRow('Category', store.category.label),
              _InfoRow('Rank', store.rank.label),
              if (store.areaName != null) _InfoRow('Area', store.areaName!),
              if (store.address != null) _InfoRow('Address', store.address!),
              if (store.gpsLatitude != null && store.gpsLongitude != null)
                _InfoRow('GPS',
                    '${store.gpsLatitude!.toStringAsFixed(6)}, ${store.gpsLongitude!.toStringAsFixed(6)}'),
            ],
          ),
        ),

        // Contacts
        if (store.contacts.isNotEmpty) ...[
          const SizedBox(height: 14),
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: AppColors.surfaceContainerLowest,
              borderRadius: BorderRadius.circular(16),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Contacts', style: tt.titleMedium?.copyWith(color: AppColors.onSurface)),
                const SizedBox(height: 12),
                Divider(color: AppColors.outlineVariant.withOpacity(0.5), height: 1),
                const SizedBox(height: 8),
                ...store.contacts.map(
                  (c) => Padding(
                    padding: const EdgeInsets.symmetric(vertical: 6),
                    child: Row(
                      children: [
                        Container(
                          width: 36, height: 36,
                          decoration: BoxDecoration(
                            color: c.isPrimary
                                ? AppColors.secondaryContainer
                                : AppColors.surfaceContainerLow,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Icon(
                            c.isPrimary ? Icons.star_rounded : Icons.person_rounded,
                            color: c.isPrimary ? AppColors.secondary : AppColors.outline,
                            size: 18,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(c.name,
                                  style: tt.titleSmall?.copyWith(color: AppColors.onSurface)),
                              if (c.position != null || c.phone != null)
                                Text(
                                  [c.position, c.phone].where((e) => e != null).join(' · '),
                                  style: tt.bodySmall?.copyWith(color: AppColors.onSurfaceVariant),
                                ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ],
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow(this.label, this.value);

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(label,
                style: tt.bodySmall?.copyWith(color: AppColors.onSurfaceVariant)),
          ),
          Expanded(
              child: Text(value,
                  style: tt.bodyMedium?.copyWith(color: AppColors.onSurface))),
        ],
      ),
    );
  }
}
