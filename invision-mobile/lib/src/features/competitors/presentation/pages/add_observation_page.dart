import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/theme/app_theme.dart';
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
              backgroundColor: AppColors.secondary),
        );
        context.pop();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
              content: Text('Error: $e'),
              backgroundColor: AppColors.error),
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
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Record Observation',
            style: Theme.of(context).textTheme.headlineMedium
                ?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
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
                return GestureDetector(
                  onTap: () => setState(() => _observationType = type.$1),
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 10, vertical: 6),
                    decoration: BoxDecoration(
                      color: isSelected
                          ? AppColors.primary
                          : AppColors.surfaceContainerHigh,
                      borderRadius: BorderRadius.circular(100),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(type.$3,
                            size: 15,
                            color: isSelected
                                ? Colors.white
                                : AppColors.onSurfaceVariant),
                        const SizedBox(width: 4),
                        Text(type.$2,
                            style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                                color: isSelected
                                    ? Colors.white
                                    : AppColors.onSurfaceVariant)),
                      ],
                    ),
                  ),
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
            GestureDetector(
              onTap: _isSubmitting ? null : _submit,
              child: Container(
                height: 50,
                decoration: BoxDecoration(
                  gradient: _isSubmitting
                      ? null
                      : const LinearGradient(
                          colors: [
                            AppColors.primary,
                            AppColors.primaryContainer
                          ],
                        ),
                  color: _isSubmitting ? AppColors.outlineVariant : null,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    if (_isSubmitting)
                      const SizedBox(
                        width: 18, height: 18,
                        child: CircularProgressIndicator(
                            strokeWidth: 2, color: Colors.white),
                      )
                    else
                      const Icon(Icons.save_rounded,
                          color: Colors.white, size: 18),
                    const SizedBox(width: 8),
                    Text(
                      _isSubmitting ? 'Saving...' : 'Record Observation',
                      style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w700,
                          fontSize: 15),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
