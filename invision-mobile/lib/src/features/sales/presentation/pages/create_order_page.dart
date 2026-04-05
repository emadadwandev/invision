import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../providers/sales_providers.dart';

class CreateOrderPage extends ConsumerStatefulWidget {
  const CreateOrderPage({super.key});

  @override
  ConsumerState<CreateOrderPage> createState() => _CreateOrderPageState();
}

class _CreateOrderPageState extends ConsumerState<CreateOrderPage> {
  final _formKey = GlobalKey<FormState>();
  final _storeIdController = TextEditingController();
  final _notesController = TextEditingController();
  final List<_OrderItemEntry> _items = [_OrderItemEntry()];
  bool _isSubmitting = false;

  @override
  void dispose() {
    _storeIdController.dispose();
    _notesController.dispose();
    for (final item in _items) {
      item.dispose();
    }
    super.dispose();
  }

  void _addItem() {
    setState(() => _items.add(_OrderItemEntry()));
  }

  void _removeItem(int index) {
    if (_items.length > 1) {
      setState(() {
        _items[index].dispose();
        _items.removeAt(index);
      });
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSubmitting = true);

    final items = _items
        .map((item) => {
              'product_id': int.tryParse(item.productIdController.text) ?? 0,
              'quantity': int.tryParse(item.quantityController.text) ?? 1,
              'unit_price':
                  double.tryParse(item.unitPriceController.text) ?? 0,
              if (item.discountController.text.isNotEmpty)
                'discount_percent':
                    double.tryParse(item.discountController.text) ?? 0,
              if (item.barcodeController.text.isNotEmpty)
                'barcode_scanned': item.barcodeController.text,
            })
        .toList();

    try {
      final repo = ref.read(salesRepositoryProvider);
      await repo.createSalesOrder(
        storeId: int.parse(_storeIdController.text),
        items: items,
        notes: _notesController.text.isNotEmpty
            ? _notesController.text
            : null,
      );

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Order created successfully')),
        );
        context.pop();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Create Order')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            TextFormField(
              controller: _storeIdController,
              decoration: const InputDecoration(
                labelText: 'Store ID',
                border: OutlineInputBorder(),
              ),
              keyboardType: TextInputType.number,
              validator: (v) =>
                  v == null || v.isEmpty ? 'Store ID is required' : null,
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _notesController,
              decoration: const InputDecoration(
                labelText: 'Notes (optional)',
                border: OutlineInputBorder(),
              ),
              maxLines: 2,
            ),
            const SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('Items',
                    style: Theme.of(context).textTheme.titleMedium),
                TextButton.icon(
                  onPressed: _addItem,
                  icon: const Icon(Icons.add),
                  label: const Text('Add Item'),
                ),
              ],
            ),
            const SizedBox(height: 8),
            for (int i = 0; i < _items.length; i++)
              _OrderItemWidget(
                entry: _items[i],
                index: i,
                canRemove: _items.length > 1,
                onRemove: () => _removeItem(i),
              ),
            const SizedBox(height: 24),
            FilledButton(
              onPressed: _isSubmitting ? null : _submit,
              child: _isSubmitting
                  ? const SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Text('Create Order'),
            ),
          ],
        ),
      ),
    );
  }
}

class _OrderItemEntry {
  final productIdController = TextEditingController();
  final quantityController = TextEditingController(text: '1');
  final unitPriceController = TextEditingController();
  final discountController = TextEditingController();
  final barcodeController = TextEditingController();

  void dispose() {
    productIdController.dispose();
    quantityController.dispose();
    unitPriceController.dispose();
    discountController.dispose();
    barcodeController.dispose();
  }
}

class _OrderItemWidget extends StatelessWidget {
  const _OrderItemWidget({
    required this.entry,
    required this.index,
    required this.canRemove,
    required this.onRemove,
  });

  final _OrderItemEntry entry;
  final int index;
  final bool canRemove;
  final VoidCallback onRemove;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('Item ${index + 1}',
                    style: Theme.of(context).textTheme.titleSmall),
                if (canRemove)
                  IconButton(
                    onPressed: onRemove,
                    icon: const Icon(Icons.delete, color: Colors.red),
                    iconSize: 20,
                    visualDensity: VisualDensity.compact,
                  ),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(
                  child: TextFormField(
                    controller: entry.productIdController,
                    decoration: const InputDecoration(
                      labelText: 'Product ID',
                      border: OutlineInputBorder(),
                      isDense: true,
                    ),
                    keyboardType: TextInputType.number,
                    validator: (v) =>
                        v == null || v.isEmpty ? 'Required' : null,
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: TextFormField(
                    controller: entry.barcodeController,
                    decoration: const InputDecoration(
                      labelText: 'Barcode',
                      border: OutlineInputBorder(),
                      isDense: true,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(
                  child: TextFormField(
                    controller: entry.quantityController,
                    decoration: const InputDecoration(
                      labelText: 'Qty',
                      border: OutlineInputBorder(),
                      isDense: true,
                    ),
                    keyboardType: TextInputType.number,
                    validator: (v) =>
                        v == null || v.isEmpty ? 'Required' : null,
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: TextFormField(
                    controller: entry.unitPriceController,
                    decoration: const InputDecoration(
                      labelText: 'Unit Price',
                      border: OutlineInputBorder(),
                      isDense: true,
                    ),
                    keyboardType:
                        const TextInputType.numberWithOptions(decimal: true),
                    validator: (v) =>
                        v == null || v.isEmpty ? 'Required' : null,
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: TextFormField(
                    controller: entry.discountController,
                    decoration: const InputDecoration(
                      labelText: 'Disc %',
                      border: OutlineInputBorder(),
                      isDense: true,
                    ),
                    keyboardType:
                        const TextInputType.numberWithOptions(decimal: true),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
