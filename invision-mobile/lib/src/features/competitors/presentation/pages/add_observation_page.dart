import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../providers/competitor_providers.dart';

class AddObservationPage extends ConsumerStatefulWidget {
  const AddObservationPage({
    super.key,
    required this.storeId,
    this.storeVisitId,
  });

  final int storeId;
  final int? storeVisitId;

  @override
  ConsumerState<AddObservationPage> createState() =>
      _AddObservationPageState();
}

class _AddObservationPageState extends ConsumerState<AddObservationPage> {
  final _formKey = GlobalKey<FormState>();
  final _notesController = TextEditingController();
  final _quantityController = TextEditingController();
  final _priceController = TextEditingController();

  String _observationType = 'sales';
  int? _selectedCompetitorId;
  int? _selectedProductId;
  bool _isSubmitting = false;

  final _observationTypes = [
    ('sales', 'Sales', Icons.point_of_sale),
    ('posm', 'POSM', Icons.campaign_outlined),
    ('pricing', 'Pricing', Icons.attach_money),
    ('display', 'Display', Icons.storefront),
    ('promotion', 'Promotion', Icons.local_offer),
    ('stock_level', 'Stock Level', Icons.inventory),
    ('other', 'Other', Icons.more_horiz),
  ];

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSubmitting = true);

    try {
      final repo = ref.read(competitorRepositoryProvider);
      await repo.createObservation(
        storeId: widget.storeId,
        observationType: _observationType,
        storeVisitId: widget.storeVisitId,
        competitorId: _selectedCompetitorId,
        competitorProductId: _selectedProductId,
        quantity: _quantityController.text.isNotEmpty
            ? int.tryParse(_quantityController.text)
            : null,
        price: _priceController.text.isNotEmpty
            ? double.tryParse(_priceController.text)
            : null,
        notes:
            _notesController.text.isNotEmpty ? _notesController.text : null,
      );

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
              content: Text('Observation recorded successfully'),
              backgroundColor: Colors.green),
        );
        context.pop();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
              content: Text('Error: $e'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  void dispose() {
    _notesController.dispose();
    _quantityController.dispose();
    _priceController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final competitorsAsync = ref.watch(competitorsProvider(null));

    return Scaffold(
      appBar: AppBar(title: const Text('Record Observation')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Observation Type Selection
            Text('Observation Type',
                style: Theme.of(context).textTheme.titleSmall),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: _observationTypes.map((type) {
                final isSelected = _observationType == type.$1;
                return ChoiceChip(
                  selected: isSelected,
                  label: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(type.$3,
                          size: 16,
                          color:
                              isSelected ? Colors.white : Colors.grey),
                      const SizedBox(width: 4),
                      Text(type.$2),
                    ],
                  ),
                  onSelected: (selected) {
                    if (selected) {
                      setState(() => _observationType = type.$1);
                    }
                  },
                );
              }).toList(),
            ),

            const SizedBox(height: 16),

            // Competitor Dropdown
            Text('Competitor (optional)',
                style: Theme.of(context).textTheme.titleSmall),
            const SizedBox(height: 8),
            competitorsAsync.when(
              data: (competitors) => DropdownButtonFormField<int?>(
                initialValue: _selectedCompetitorId,
                decoration: const InputDecoration(
                  border: OutlineInputBorder(),
                  contentPadding:
                      EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                ),
                hint: const Text('Select competitor'),
                items: [
                  const DropdownMenuItem<int?>(
                      value: null, child: Text('None')),
                  ...competitors.map((c) => DropdownMenuItem(
                      value: c.id, child: Text(c.name))),
                ],
                onChanged: (value) {
                  setState(() {
                    _selectedCompetitorId = value;
                    _selectedProductId = null;
                  });
                },
              ),
              loading: () => const LinearProgressIndicator(),
              error: (_, __) => const Text('Could not load competitors'),
            ),

            // Competitor Product Dropdown (if competitor selected)
            if (_selectedCompetitorId != null) ...[
              const SizedBox(height: 16),
              Text('Product (optional)',
                  style: Theme.of(context).textTheme.titleSmall),
              const SizedBox(height: 8),
              Consumer(
                builder: (context, ref, _) {
                  final productsAsync = ref.watch(
                      competitorProductsProvider(_selectedCompetitorId));
                  return productsAsync.when(
                    data: (products) => DropdownButtonFormField<int?>(
                      initialValue: _selectedProductId,
                      decoration: const InputDecoration(
                        border: OutlineInputBorder(),
                        contentPadding: EdgeInsets.symmetric(
                            horizontal: 12, vertical: 8),
                      ),
                      hint: const Text('Select product'),
                      items: [
                        const DropdownMenuItem<int?>(
                            value: null, child: Text('None')),
                        ...products.map((p) => DropdownMenuItem(
                            value: p.id, child: Text(p.name))),
                      ],
                      onChanged: (value) =>
                          setState(() => _selectedProductId = value),
                    ),
                    loading: () => const LinearProgressIndicator(),
                    error: (_, __) =>
                        const Text('Could not load products'),
                  );
                },
              ),
            ],

            const SizedBox(height: 16),

            // Quantity & Price
            Row(
              children: [
                Expanded(
                  child: TextFormField(
                    controller: _quantityController,
                    decoration: const InputDecoration(
                      labelText: 'Quantity',
                      border: OutlineInputBorder(),
                    ),
                    keyboardType: TextInputType.number,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: TextFormField(
                    controller: _priceController,
                    decoration: const InputDecoration(
                      labelText: 'Price',
                      border: OutlineInputBorder(),
                      prefixText: '\$ ',
                    ),
                    keyboardType:
                        const TextInputType.numberWithOptions(decimal: true),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 16),

            // Notes
            TextFormField(
              controller: _notesController,
              decoration: const InputDecoration(
                labelText: 'Notes',
                border: OutlineInputBorder(),
                alignLabelWithHint: true,
              ),
              maxLines: 3,
            ),

            const SizedBox(height: 24),

            // Submit
            FilledButton.icon(
              onPressed: _isSubmitting ? null : _submit,
              icon: _isSubmitting
                  ? const SizedBox(
                      width: 16,
                      height: 16,
                      child: CircularProgressIndicator(
                          strokeWidth: 2, color: Colors.white),
                    )
                  : const Icon(Icons.save),
              label: Text(_isSubmitting ? 'Saving...' : 'Record Observation'),
              style: FilledButton.styleFrom(
                minimumSize: const Size.fromHeight(48),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
