import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/theme/app_theme.dart';
import '../../../products/data/models/product_model.dart';
import '../../data/models/pos_transaction_model.dart';
import '../providers/pos_providers.dart';

class CreatePosTransactionPage extends ConsumerStatefulWidget {
  const CreatePosTransactionPage({
    super.key,
    required this.storeId,
    required this.storeName,
  });

  final int storeId;
  final String storeName;

  @override
  ConsumerState<CreatePosTransactionPage> createState() =>
      _CreatePosTransactionPageState();
}

class _CreatePosTransactionPageState
    extends ConsumerState<CreatePosTransactionPage> {
  final _notesController = TextEditingController();
  final Map<int, _PosItemControllers> _items = {};
  String? _paymentMethod;
  bool _saving = false;
  PosTransaction? _transaction;

  @override
  void dispose() {
    _notesController.dispose();
    for (final item in _items.values) {
      item.dispose();
    }
    super.dispose();
  }

  void _addProduct(Product product) {
    final price = product.currentPrices.firstOrNull?.price ??
        product.priceLevels.firstOrNull?.price ??
        0;
    setState(() {
      _items[product.id] = _PosItemControllers(price: price);
    });
  }

  Future<void> _save(List<Product> products) async {
    if (_items.isEmpty) {
      _message('Add at least one product to the sale.');
      return;
    }

    final items = <Map<String, dynamic>>[];
    for (final product in products.where((p) => _items.containsKey(p.id))) {
      final controllers = _items[product.id]!;
      final quantity = int.tryParse(controllers.quantity.text.trim());
      final unitPrice = double.tryParse(controllers.unitPrice.text.trim());
      if (quantity == null || quantity < 1 || unitPrice == null || unitPrice < 0) {
        _message('Enter a valid quantity and unit price for ${product.name}.');
        return;
      }
      items.add({
        'product_id': product.id,
        'quantity': quantity,
        'unit_price': unitPrice,
      });
    }

    setState(() => _saving = true);
    try {
      final created = await ref.read(posRepositoryProvider).createTransaction(
            storeId: widget.storeId,
            type: 'sell_out',
            paymentMethod: _paymentMethod,
            notes: _notesController.text.trim(),
            items: items,
          );
      if (!mounted) return;
      setState(() => _transaction = created);

      final completed =
          await ref.read(posRepositoryProvider).completeTransaction(created.id);
      if (mounted) setState(() => _transaction = completed);
    } catch (error) {
      if (mounted) {
        final pending = _transaction;
        _message(pending == null
            ? 'Could not create the POS sale: $error'
            : 'Transaction ${pending.transactionNumber} was saved but could not be completed: $error');
      }
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  void _message(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }

  @override
  Widget build(BuildContext context) {
    final productsAsync = ref.watch(posProductCatalogProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text(_transaction == null ? 'POS Sale' : 'Sale Receipt'),
        backgroundColor: AppColors.surface,
        actions: [
          if (_transaction != null)
            TextButton(onPressed: () => context.pop(), child: const Text('Done')),
        ],
      ),
      body: _transaction != null
          ? _receipt(_transaction!)
          : productsAsync.when(
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (error, _) => Center(
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Text('Could not load products: $error'),
                ),
              ),
              data: (products) => _saleForm(products),
            ),
    );
  }

  Widget _saleForm(List<Product> products) {
    if (products.isEmpty) {
      return const Center(child: Text('No products are available for POS sales.'));
    }
    final selectedProducts = products.where((p) => _items.containsKey(p.id)).toList();

    return Column(
      children: [
        Expanded(
          child: ListView(
            padding: const EdgeInsets.all(16),
            children: [
              Text(widget.storeName,
                  style: Theme.of(context).textTheme.headlineSmall),
              const SizedBox(height: 6),
              Text('Sell-out transaction',
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        color: AppColors.onSurfaceVariant,
                      )),
              const SizedBox(height: 16),
              Autocomplete<Product>(
                displayStringForOption: (product) => product.name,
                optionsBuilder: (value) {
                  final query = value.text.trim().toLowerCase();
                  if (query.isEmpty) return const Iterable<Product>.empty();
                  return products.where((product) =>
                      !_items.containsKey(product.id) &&
                      (product.name.toLowerCase().contains(query) ||
                          (product.sku?.toLowerCase().contains(query) ?? false) ||
                          (product.barcode?.contains(query) ?? false)));
                },
                onSelected: _addProduct,
                fieldViewBuilder: (context, controller, focusNode, onSubmit) =>
                    TextField(
                  controller: controller,
                  focusNode: focusNode,
                  decoration: const InputDecoration(
                    labelText: 'Search and add product',
                    prefixIcon: Icon(Icons.search),
                    border: OutlineInputBorder(),
                  ),
                  onSubmitted: (_) => onSubmit(),
                ),
              ),
              const SizedBox(height: 12),
              if (selectedProducts.isEmpty)
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 16),
                  child: Text('Add the products included in this sale.'),
                ),
              ...selectedProducts.map(_productCard),
              const SizedBox(height: 8),
              DropdownButtonFormField<String?>(
                initialValue: _paymentMethod,
                decoration: const InputDecoration(
                  labelText: 'Payment method (optional)',
                  border: OutlineInputBorder(),
                ),
                items: const [
                  DropdownMenuItem<String?>(value: null, child: Text('Not specified')),
                  DropdownMenuItem<String?>(value: 'cash', child: Text('Cash')),
                  DropdownMenuItem<String?>(value: 'card', child: Text('Card')),
                  DropdownMenuItem<String?>(value: 'bank_transfer', child: Text('Bank transfer')),
                  DropdownMenuItem<String?>(value: 'credit', child: Text('Credit')),
                ],
                onChanged: _saving ? null : (value) => setState(() => _paymentMethod = value),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _notesController,
                minLines: 2,
                maxLines: 4,
                decoration: const InputDecoration(
                  labelText: 'Notes (optional)',
                  border: OutlineInputBorder(),
                ),
              ),
            ],
          ),
        ),
        SafeArea(
          top: false,
          child: Padding(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 12),
            child: SizedBox(
              width: double.infinity,
              child: FilledButton.icon(
                onPressed: _saving || selectedProducts.isEmpty
                    ? null
                    : () => _save(products),
                icon: _saving
                    ? const SizedBox.square(
                        dimension: 18,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Icon(Icons.point_of_sale_rounded),
                label: const Text('Complete sale'),
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _productCard(Product product) {
    final controllers = _items[product.id]!;
    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(product.name,
                      style: Theme.of(context).textTheme.titleSmall),
                ),
                IconButton(
                  tooltip: 'Remove product',
                  onPressed: _saving
                      ? null
                      : () => setState(() {
                            _items.remove(product.id)?.dispose();
                          }),
                  icon: const Icon(Icons.close_rounded),
                ),
              ],
            ),
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: controllers.quantity,
                    enabled: !_saving,
                    keyboardType: TextInputType.number,
                    inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                    decoration: const InputDecoration(
                      labelText: 'Quantity',
                      border: OutlineInputBorder(),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: TextField(
                    controller: controllers.unitPrice,
                    enabled: !_saving,
                    keyboardType: const TextInputType.numberWithOptions(decimal: true),
                    inputFormatters: [
                      FilteringTextInputFormatter.allow(RegExp(r'^\d*\.?\d{0,2}')),
                    ],
                    decoration: const InputDecoration(
                      labelText: 'Unit price',
                      border: OutlineInputBorder(),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _receipt(PosTransaction transaction) {
    return Center(
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Container(
          width: double.infinity,
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: AppColors.surfaceContainerLowest,
            borderRadius: BorderRadius.circular(20),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.check_circle_rounded,
                  size: 58, color: AppColors.secondary),
              const SizedBox(height: 12),
              Text('Sale recorded',
                  style: Theme.of(context).textTheme.headlineSmall),
              const SizedBox(height: 6),
              Text(widget.storeName,
                  style: Theme.of(context).textTheme.bodyMedium),
              const Divider(height: 28),
              const Text('Transaction reference'),
              const SizedBox(height: 4),
              SelectableText(
                transaction.transactionNumber,
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w700,
                      fontFamily: 'monospace',
                    ),
              ),
              const SizedBox(height: 18),
              Text(
                transaction.totalAmount.toStringAsFixed(2),
                style: Theme.of(context).textTheme.displaySmall?.copyWith(
                      color: AppColors.primary,
                      fontWeight: FontWeight.w800,
                    ),
              ),
              const SizedBox(height: 4),
              Text('Total · ${transaction.status.label}',
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: AppColors.onSurfaceVariant,
                      )),
              if (transaction.paymentMethod != null) ...[
                const SizedBox(height: 12),
                Text('Payment: ${transaction.paymentMethod}'),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

class _PosItemControllers {
  _PosItemControllers({required double price})
      : quantity = TextEditingController(text: '1'),
        unitPrice = TextEditingController(text: price.toStringAsFixed(2));

  final TextEditingController quantity;
  final TextEditingController unitPrice;

  void dispose() {
    quantity.dispose();
    unitPrice.dispose();
  }
}
