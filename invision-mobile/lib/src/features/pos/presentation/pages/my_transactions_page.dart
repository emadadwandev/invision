import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/theme/app_theme.dart';
import '../../data/models/pos_transaction_model.dart';
import '../providers/pos_providers.dart';

class MyTransactionsPage extends ConsumerStatefulWidget {
  const MyTransactionsPage({super.key});

  @override
  ConsumerState<MyTransactionsPage> createState() =>
      _MyTransactionsPageState();
}

class _MyTransactionsPageState extends ConsumerState<MyTransactionsPage> {
  String? _typeFilter;

  @override
  Widget build(BuildContext context) {
    final txnAsync = ref.watch(myTransactionsProvider(_typeFilter));

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('My POS Transactions',
            style: Theme.of(context).textTheme.headlineMedium
                ?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
        actions: [
          PopupMenuButton<String?>(
            icon: const Icon(Icons.filter_list_rounded,
                color: AppColors.onSurface),
            onSelected: (value) => setState(() => _typeFilter = value),
            itemBuilder: (context) => [
              const PopupMenuItem(value: null, child: Text('All Types')),
              const PopupMenuItem(value: 'sell_out', child: Text('Sell Out')),
              const PopupMenuItem(value: 'sell_through', child: Text('Sell Through')),
              const PopupMenuItem(value: 'return', child: Text('Return')),
            ],
          ),
        ],
      ),
      body: txnAsync.when(
        data: (transactions) => transactions.isEmpty
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
                      child: const Icon(Icons.receipt_long_rounded,
                          size: 28, color: AppColors.onSurfaceVariant),
                    ),
                    const SizedBox(height: 12),
                    const Text('No transactions yet.',
                        style: TextStyle(color: AppColors.onSurfaceVariant)),
                  ],
                ),
              )
            : RefreshIndicator(
                onRefresh: () async =>
                    ref.invalidate(myTransactionsProvider(_typeFilter)),
                child: ListView.builder(
                  itemCount: transactions.length,
                  padding: const EdgeInsets.all(12),
                  itemBuilder: (context, index) =>
                      _MyTxnCard(transaction: transactions[index]),
                ),
              ),
        loading: () => const Center(
            child: CircularProgressIndicator(color: AppColors.primary)),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}

class _MyTxnCard extends StatelessWidget {
  const _MyTxnCard({required this.transaction});

  final PosTransaction transaction;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLowest,
        borderRadius: BorderRadius.circular(14),
        border: Border(left: BorderSide(
          color: transaction.status.color,
          width: 3,
        )),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(transaction.transactionNumber,
                    style: const TextStyle(
                        fontWeight: FontWeight.w700,
                        color: AppColors.onSurface, fontSize: 13,
                        fontFamily: 'monospace')),
                if (transaction.storeName != null) ...[
                  const SizedBox(height: 2),
                  Text(transaction.storeName!,
                      style: const TextStyle(
                          fontSize: 12, color: AppColors.onSurfaceVariant)),
                ],
                const SizedBox(height: 6),
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 7, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.surfaceContainerHigh,
                        borderRadius: BorderRadius.circular(100),
                      ),
                      child: Text(transaction.type.label,
                          style: const TextStyle(
                              fontSize: 10, color: AppColors.onSurfaceVariant,
                              fontWeight: FontWeight.w600)),
                    ),
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 7, vertical: 2),
                      decoration: BoxDecoration(
                        color: transaction.status.color.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(100),
                        border: Border.all(
                            color: transaction.status.color.withOpacity(0.4)),
                      ),
                      child: Text(transaction.status.label,
                          style: TextStyle(
                              fontSize: 10,
                              color: transaction.status.color,
                              fontWeight: FontWeight.w700)),
                    ),
                  ],
                ),
              ],
            ),
          ),
          Text(
            '\$${transaction.totalAmount.toStringAsFixed(2)}',
            style: const TextStyle(
                fontWeight: FontWeight.w800,
                fontSize: 15, color: AppColors.onSurface),
          ),
        ],
      ),
    );
  }
}
