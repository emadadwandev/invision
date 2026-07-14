import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/theme/app_theme.dart';
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
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Create Order',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            TextFormField(
              controller: _storeIdController,
              decoration: const InputDecoration(
                labelText: 'Store ID',
                prefixIcon: Icon(Icons.store_rounded, size: 18, color: AppColors.outline),
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
                prefixIcon: Icon(Icons.note_rounded, size: 18, color: AppColors.outline),
              ),
              maxLines: 2,
            ),
            const SizedBox(height: 20),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('Items',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(color: AppColors.onSurface)),
                GestureDetector(
                  onTap: _addItem,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
                    decoration: BoxDecoration(
                      color: AppColors.surfaceContainerLow,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.add_rounded, size: 16, color: AppColors.primary),
                        SizedBox(width: 4),
                        Text('Add Item',
                            style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.w600, fontSize: 13)),
                      ],
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            for (int i = 0; i < _items.length; i++)
              _OrderItemWidget(
                entry: _items[i],
                index: i,
                canRemove: _items.length > 1,
                onRemove: () => _removeItem(i),
              ),
            const SizedBox(height: 24),
            GestureDetector(
              onTap: _isSubmitting ? null : _submit,
              child: Container(
                width: double.infinity,
                height: 52,
                decoration: BoxDecoration(
                  gradient: _isSubmitting
                      ? null
                      : const LinearGradient(
                          colors: [AppColors.primary, AppColors.primaryContainer],
                        ),
                  color: _isSubmitting ? AppColors.surfaceContainerHigh : null,
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: _isSubmitting
                      ? null
                      : [BoxShadow(color: AppColors.primary.withOpacity(0.25), blurRadius: 12, offset: const Offset(0, 4))],
                ),
                alignment: Alignment.center,
                child: _isSubmitting
                    ? const SizedBox(
                        height: 22, width: 22,
                        child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.primary),
                      )
                    : const Text('Create Order',
                        style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 16)),
              ),
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
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLowest,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Item ${index + 1}',
                  style: Theme.of(context).textTheme.titleSmall
                      ?.copyWith(color: AppColors.onSurface, fontWeight: FontWeight.w700)),
              if (canRemove)
                GestureDetector(
                  onTap: onRemove,
                  child: Container(
                    padding: const EdgeInsets.all(4),
                    decoration: BoxDecoration(
                      color: AppColors.errorContainer,
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: const Icon(Icons.delete_rounded, color: AppColors.error, size: 16),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              Expanded(
                child: TextFormField(
                  controller: entry.productIdController,
                  decoration: const InputDecoration(
                    labelText: 'Product ID',
                  ),
                  keyboardType: TextInputType.number,
                  validator: (v) =>
                      v == null || v.isEmpty ? 'Required' : null,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: TextFormField(
                  controller: entry.barcodeController,
                  decoration: const InputDecoration(
                    labelText: 'Barcode',
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              Expanded(
                child: TextFormField(
                  controller: entry.quantityController,
                  decoration: const InputDecoration(
                    labelText: 'Qty',
                  ),
                  keyboardType: TextInputType.number,
                  validator: (v) =>
                      v == null || v.isEmpty ? 'Required' : null,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: TextFormField(
                  controller: entry.unitPriceController,
                  decoration: const InputDecoration(
                    labelText: 'Unit Price',
                  ),
                  keyboardType:
                      const TextInputType.numberWithOptions(decimal: true),
                  validator: (v) =>
                      v == null || v.isEmpty ? 'Required' : null,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: TextFormField(
                  controller: entry.discountController,
                  decoration: const InputDecoration(
                    labelText: 'Disc %',
                  ),
                  keyboardType:
                      const TextInputType.numberWithOptions(decimal: true),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
