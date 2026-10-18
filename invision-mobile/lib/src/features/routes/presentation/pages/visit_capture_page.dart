import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/theme/app_theme.dart';
import '../../../competitors/data/models/competitor_models.dart';
import '../../../products/data/models/product_model.dart';
import '../../data/models/visit_capture_model.dart';
import '../providers/visit_capture_providers.dart';

class VisitCapturePage extends ConsumerStatefulWidget {
  const VisitCapturePage({super.key, required this.visitId});

  final int visitId;

  @override
  ConsumerState<VisitCapturePage> createState() => _VisitCapturePageState();
}

class _VisitCapturePageState extends ConsumerState<VisitCapturePage> {
  final _remarksController = TextEditingController();
  final Map<int, _MetricControllers> _metrics = {};
  int? _competitorId;
  bool _initialized = false;
  bool _saving = false;

  @override
  void dispose() {
    _remarksController.dispose();
    for (final controllers in _metrics.values) {
      controllers.dispose();
    }
    super.dispose();
  }

  void _initialize(List<Product> products, VisitCapture capture) {
    if (_initialized) return;
    _remarksController.text = capture.remarks ?? '';
    _competitorId = capture.items
        .map((item) => item.competitorId)
        .whereType<int>()
        .firstOrNull;
    for (final saved in capture.items) {
      if (!products.any((product) => product.id == saved.productId)) continue;
      _metrics[saved.productId] = _MetricControllers.fromValues(
        ourSellOut: saved.ourSellOutQuantity,
        competitorSellOut: saved.competitorSellOutQuantity,
        ourShelf: saved.ourShelfStockQuantity,
        competitorShelf: saved.competitorShelfStockQuantity,
        ourWarehouse: saved.ourWarehouseStockQuantity,
        competitorWarehouse: saved.competitorWarehouseStockQuantity,
        ourSellThrough: saved.ourSellThroughQuantity,
        competitorSellThrough: saved.competitorSellThroughQuantity,
        plannedSellThrough: saved.plannedSellThroughQuantity,
        plannedSellThroughDate: saved.plannedSellThroughDate,
        plannedSellThroughBasis: saved.plannedSellThroughBasis,
        plannedSellThroughNotes: saved.plannedSellThroughNotes ?? '',
        ourPosmPresent: saved.ourPosmPresent,
        competitorPosmPresent: saved.competitorPosmPresent,
        ourFrontShopPresent: saved.ourFrontShopPresent,
        competitorFrontShopPresent: saved.competitorFrontShopPresent,
      );
    }
    _initialized = true;
  }

  Future<void> _save(List<Product> products) async {
    setState(() => _saving = true);
    try {
      final items = products.map((product) {
        final values = _metrics[product.id]!;
        int read(TextEditingController controller) =>
            int.tryParse(controller.text.trim()) ?? 0;
        return <String, dynamic>{
          'product_id': product.id,
          'competitor_id': _competitorId,
          'our_sell_out_quantity': read(values.ourSellOut),
          'competitor_sell_out_quantity': read(values.competitorSellOut),
          'our_shelf_stock_quantity': read(values.ourShelf),
          'competitor_shelf_stock_quantity': read(values.competitorShelf),
          'our_warehouse_stock_quantity': read(values.ourWarehouse),
          'competitor_warehouse_stock_quantity': read(
            values.competitorWarehouse,
          ),
          'our_sell_through_quantity': read(values.ourSellThrough),
          'competitor_sell_through_quantity': read(
            values.competitorSellThrough,
          ),
          'planned_sell_through_quantity': read(values.plannedSellThrough),
          'planned_sell_through_date': values.plannedSellThroughDate,
          'planned_sell_through_basis': values.plannedSellThroughBasis,
          'planned_sell_through_notes': values.plannedSellThroughNotes.text.trim(),
          'our_posm_present': values.ourPosmPresent,
          'competitor_posm_present': values.competitorPosmPresent,
          'our_front_shop_present': values.ourFrontShopPresent,
          'competitor_front_shop_present': values.competitorFrontShopPresent,
        };
      }).toList();

      await ref
          .read(visitCaptureRepositoryProvider)
          .saveCapture(
            widget.visitId,
            remarks: _remarksController.text.trim(),
            items: items,
          );
      ref.invalidate(visitCaptureProvider(widget.visitId));
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(const SnackBar(content: Text('Visit data saved.')));
        Navigator.of(context).pop(true);
      }
    } catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Could not save visit data: $error')),
        );
      }
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  Future<void> _changeCompetitor(int? competitorId) async {
    if (_competitorId == competitorId) return;
    final hasCompetitorData = _metrics.values.any((values) =>
        (int.tryParse(values.competitorSellOut.text) ?? 0) > 0 ||
        (int.tryParse(values.competitorShelf.text) ?? 0) > 0 ||
        (int.tryParse(values.competitorWarehouse.text) ?? 0) > 0 ||
        (int.tryParse(values.competitorSellThrough.text) ?? 0) > 0 ||
        values.competitorPosmPresent ||
        values.competitorFrontShopPresent);
    if (hasCompetitorData) {
      final clearData = await showDialog<bool>(
        context: context,
        builder: (context) => AlertDialog(
          title: const Text('Change competitor?'),
          content: const Text(
            'Changing the competitor will clear the competitor quantities and visibility recorded for this visit.',
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: const Text('Keep current'),
            ),
            FilledButton(
              onPressed: () => Navigator.pop(context, true),
              child: const Text('Clear and change'),
            ),
          ],
        ),
      );
      if (clearData != true || !mounted) return;
    }
    setState(() {
      _competitorId = competitorId;
      for (final controllers in _metrics.values) {
        controllers.clearCompetitorValues();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final captureAsync = ref.watch(visitCaptureProvider(widget.visitId));
    final productsAsync = ref.watch(visitProductsProvider);
    final competitorsAsync = ref.watch(visitCompetitorsProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Store Visit'),
        backgroundColor: AppColors.surface,
      ),
      body: captureAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => _LoadError(
          message: 'Could not load visit: $error',
          onRetry: () => ref.invalidate(visitCaptureProvider(widget.visitId)),
        ),
        data: (capture) => productsAsync.when(
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (error, _) => _LoadError(
            message: 'Could not load products: $error',
            onRetry: () => ref.invalidate(visitProductsProvider),
          ),
          data: (products) {
            _initialize(products, capture);
            return competitorsAsync.when(
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (error, _) => _LoadError(
                message: 'Could not load competitors: $error',
                onRetry: () => ref.invalidate(visitCompetitorsProvider),
              ),
              data: (competitors) =>
                  _buildForm(capture.storeName, products, competitors),
            );
          },
        ),
      ),
    );
  }

  Widget _buildForm(
    String storeName,
    List<Product> products,
    List<Competitor> competitors,
  ) {
    if (products.isEmpty) {
      return const Center(
        child: Text('No products are available for visit capture.'),
      );
    }
    final selectedProducts =
        products.where((product) => _metrics.containsKey(product.id)).toList();

    return Column(
      children: [
        Expanded(
          child: ListView(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
            children: [
              Text(storeName, style: Theme.of(context).textTheme.headlineSmall),
              const SizedBox(height: 6),
              Text(
                'Record quantities observed during this visit.',
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  color: AppColors.onSurfaceVariant,
                ),
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<int?>(
                initialValue:
                    competitors.any((item) => item.id == _competitorId)
                    ? _competitorId
                    : null,
                decoration: const InputDecoration(
                  labelText: 'Competitor to compare',
                  border: OutlineInputBorder(),
                ),
                items: [
                  const DropdownMenuItem<int?>(
                    value: null,
                    child: Text('None'),
                  ),
                  ...competitors.map(
                    (competitor) => DropdownMenuItem<int?>(
                      value: competitor.id,
                      child: Text(
                        competitor.name,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ),
                ],
                onChanged: _saving ? null : _changeCompetitor,
              ),
              const SizedBox(height: 14),
              Autocomplete<Product>(
                displayStringForOption: (product) => product.name,
                optionsBuilder: (value) {
                  final query = value.text.trim().toLowerCase();
                  if (query.isEmpty) return const Iterable<Product>.empty();
                  return products.where((product) =>
                      !_metrics.containsKey(product.id) &&
                      (product.name.toLowerCase().contains(query) ||
                          (product.sku?.toLowerCase().contains(query) ?? false)));
                },
                onSelected: (product) {
                  setState(() {
                    _metrics[product.id] = _MetricControllers.fromValues(
                      ourSellOut: 0,
                      competitorSellOut: 0,
                      ourShelf: 0,
                      competitorShelf: 0,
                      ourWarehouse: 0,
                      competitorWarehouse: 0,
                      ourSellThrough: 0,
                      competitorSellThrough: 0,
                      plannedSellThrough: 0,
                      ourPosmPresent: false,
                      competitorPosmPresent: false,
                      ourFrontShopPresent: false,
                      competitorFrontShopPresent: false,
                    );
                  });
                },
                fieldViewBuilder: (context, controller, focusNode, onSubmit) =>
                    TextField(
                  controller: controller,
                  focusNode: focusNode,
                  decoration: const InputDecoration(
                    labelText: 'Add a product',
                    prefixIcon: Icon(Icons.search),
                    border: OutlineInputBorder(),
                  ),
                  onSubmitted: (_) => onSubmit(),
                ),
              ),
              const SizedBox(height: 12),
              if (selectedProducts.isNotEmpty)
                Padding(
                  padding: const EdgeInsets.only(bottom: 8),
                  child: Text(
                    '${selectedProducts.length} ${selectedProducts.length == 1 ? 'product' : 'products'} in this visit',
                    style: Theme.of(context).textTheme.labelLarge?.copyWith(
                      color: AppColors.onSurfaceVariant,
                    ),
                  ),
                ),
              if (selectedProducts.isEmpty)
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 16),
                  child: Text(
                    'Search and add at least one product observed in this store to save visit data.',
                  ),
                ),
              ...selectedProducts.map((product) => _productCard(product)),
              const SizedBox(height: 16),
              TextField(
                controller: _remarksController,
                minLines: 3,
                maxLines: 6,
                decoration: const InputDecoration(
                  labelText: 'Visit remarks',
                  alignLabelWithHint: true,
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
                    : () => _save(selectedProducts),
                icon: _saving
                    ? const SizedBox.square(
                        dimension: 18,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Icon(Icons.save_outlined),
                label: const Text('Save visit data'),
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _productCard(Product product) {
    final values = _metrics[product.id]!;
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
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
                            _metrics.remove(product.id)?.dispose();
                          }),
                  icon: const Icon(Icons.close_rounded),
                ),
              ],
            ),
            if (product.sku != null)
              Text(product.sku!, style: Theme.of(context).textTheme.bodySmall),
            const SizedBox(height: 12),
            _quantityGroup('Our products', [
              ('Sell-out', values.ourSellOut),
              ('Shelf stock', values.ourShelf),
              ('Warehouse', values.ourWarehouse),
            ]),
            const SizedBox(height: 12),
            _quantityGroup('Competitor', [
              ('Sell-out', values.competitorSellOut),
              ('Shelf stock', values.competitorShelf),
              ('Warehouse', values.competitorWarehouse),
            ], enabled: _competitorId != null),
            const SizedBox(height: 12),
            _quantityGroup('Sell-through observed', [
              ('Our products', values.ourSellThrough),
              ('Competitor', values.competitorSellThrough),
            ], enabled: _competitorId != null),
            const SizedBox(height: 12),
            Text('Planned sell-through',
                style: Theme.of(context).textTheme.labelLarge),
            const SizedBox(height: 6),
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: values.plannedSellThrough,
                    enabled: !_saving,
                    keyboardType: TextInputType.number,
                    inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                    decoration: const InputDecoration(
                      labelText: 'Quantity',
                      isDense: true,
                      border: OutlineInputBorder(),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: DropdownButtonFormField<String>(
                    initialValue: values.plannedSellThroughBasis,
                    decoration: const InputDecoration(
                      labelText: 'Plan basis',
                      isDense: true,
                      border: OutlineInputBorder(),
                    ),
                    items: const [
                      DropdownMenuItem(value: 'next_week', child: Text('Next week')),
                      DropdownMenuItem(value: 'offer', child: Text('Offer')),
                      DropdownMenuItem(value: 'quarter_agreement', child: Text('Quarter agreement')),
                      DropdownMenuItem(value: 'other', child: Text('Other')),
                    ],
                    onChanged: _saving
                        ? null
                        : (value) => setState(() => values.plannedSellThroughBasis = value),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: _saving ? null : () => _choosePlannedDate(values),
                    icon: const Icon(Icons.calendar_month_outlined),
                    label: Text(values.plannedSellThroughDate == null
                        ? 'Choose planned date'
                        : 'Planned: ${values.plannedSellThroughDate}'),
                  ),
                ),
                if (values.plannedSellThroughDate != null)
                  IconButton(
                    tooltip: 'Clear planned date',
                    onPressed: _saving
                        ? null
                        : () => setState(
                            () => values.plannedSellThroughDate = null,
                          ),
                    icon: const Icon(Icons.close),
                  ),
              ],
            ),
            TextField(
              controller: values.plannedSellThroughNotes,
              enabled: !_saving,
              decoration: const InputDecoration(
                labelText: 'Offer or agreement details (optional)',
                isDense: true,
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 12),
            Text('In-store visibility',
                style: Theme.of(context).textTheme.labelLarge),
            SwitchListTile.adaptive(
              contentPadding: EdgeInsets.zero,
              dense: true,
              title: const Text('Our POS marketing material'),
              value: values.ourPosmPresent,
              onChanged: _saving
                  ? null
                  : (value) => setState(() => values.ourPosmPresent = value),
            ),
            SwitchListTile.adaptive(
              contentPadding: EdgeInsets.zero,
              dense: true,
              title: const Text('Our front shop display'),
              value: values.ourFrontShopPresent,
              onChanged: _saving
                  ? null
                  : (value) => setState(() => values.ourFrontShopPresent = value),
            ),
            SwitchListTile.adaptive(
              contentPadding: EdgeInsets.zero,
              dense: true,
              title: const Text('Competitor POS marketing material'),
              value: values.competitorPosmPresent,
              onChanged: _saving || _competitorId == null
                  ? null
                  : (value) => setState(() => values.competitorPosmPresent = value),
            ),
            SwitchListTile.adaptive(
              contentPadding: EdgeInsets.zero,
              dense: true,
              title: const Text('Competitor front shop display'),
              value: values.competitorFrontShopPresent,
              onChanged: _saving || _competitorId == null
                  ? null
                  : (value) => setState(() => values.competitorFrontShopPresent = value),
            ),
          ],
        ),
      ),
    );
  }

  Widget _quantityGroup(
    String title,
    List<(String, TextEditingController)> fields, {
    bool enabled = true,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(title, style: Theme.of(context).textTheme.labelLarge),
        const SizedBox(height: 6),
        Row(
          children: fields
              .map(
                (field) => Expanded(
                  child: Padding(
                    padding: const EdgeInsets.only(right: 6),
                    child: TextField(
                      controller: field.$2,
                      enabled: enabled && !_saving,
                      keyboardType: TextInputType.number,
                      inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                      decoration: InputDecoration(
                        labelText: field.$1,
                        isDense: true,
                        border: const OutlineInputBorder(),
                      ),
                    ),
                  ),
                ),
              )
              .toList(),
        ),
      ],
    );
  }

  Future<void> _choosePlannedDate(_MetricControllers values) async {
    final today = DateTime.now();
    final initial = values.plannedSellThroughDate == null
        ? today.add(const Duration(days: 7))
        : DateTime.tryParse(values.plannedSellThroughDate!) ?? today;
    final date = await showDatePicker(
      context: context,
      initialDate: initial,
      firstDate: DateTime(today.year, today.month, today.day),
      lastDate: DateTime(today.year + 2),
    );
    if (date != null && mounted) {
      setState(() => values.plannedSellThroughDate =
          '${date.year.toString().padLeft(4, '0')}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}');
    }
  }
}

class _MetricControllers {
  _MetricControllers.fromValues({
    required int ourSellOut,
    required int competitorSellOut,
    required int ourShelf,
    required int competitorShelf,
    required int ourWarehouse,
    required int competitorWarehouse,
    required int ourSellThrough,
    required int competitorSellThrough,
    required int plannedSellThrough,
    this.plannedSellThroughDate,
    this.plannedSellThroughBasis,
    String plannedSellThroughNotes = '',
    required this.ourPosmPresent,
    required this.competitorPosmPresent,
    required this.ourFrontShopPresent,
    required this.competitorFrontShopPresent,
  }) : ourSellOut = TextEditingController(text: '$ourSellOut'),
       competitorSellOut = TextEditingController(text: '$competitorSellOut'),
       ourShelf = TextEditingController(text: '$ourShelf'),
       competitorShelf = TextEditingController(text: '$competitorShelf'),
       ourWarehouse = TextEditingController(text: '$ourWarehouse'),
       competitorWarehouse = TextEditingController(
         text: '$competitorWarehouse',
       ),
       ourSellThrough = TextEditingController(text: '$ourSellThrough'),
       competitorSellThrough = TextEditingController(text: '$competitorSellThrough'),
       plannedSellThrough = TextEditingController(text: '$plannedSellThrough'),
       plannedSellThroughNotes = TextEditingController(text: plannedSellThroughNotes);

  final TextEditingController ourSellOut;
  final TextEditingController competitorSellOut;
  final TextEditingController ourShelf;
  final TextEditingController competitorShelf;
  final TextEditingController ourWarehouse;
  final TextEditingController competitorWarehouse;
  final TextEditingController ourSellThrough;
  final TextEditingController competitorSellThrough;
  final TextEditingController plannedSellThrough;
  final TextEditingController plannedSellThroughNotes;
  String? plannedSellThroughDate;
  String? plannedSellThroughBasis;
  bool ourPosmPresent;
  bool competitorPosmPresent;
  bool ourFrontShopPresent;
  bool competitorFrontShopPresent;

  void clearCompetitorValues() {
    competitorSellOut.text = '0';
    competitorShelf.text = '0';
    competitorWarehouse.text = '0';
    competitorSellThrough.text = '0';
    competitorPosmPresent = false;
    competitorFrontShopPresent = false;
  }

  void dispose() {
    ourSellOut.dispose();
    competitorSellOut.dispose();
    ourShelf.dispose();
    competitorShelf.dispose();
    ourWarehouse.dispose();
    competitorWarehouse.dispose();
    ourSellThrough.dispose();
    competitorSellThrough.dispose();
    plannedSellThrough.dispose();
    plannedSellThroughNotes.dispose();
  }
}

class _LoadError extends StatelessWidget {
  const _LoadError({required this.message, required this.onRetry});

  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) => Center(
    child: Padding(
      padding: const EdgeInsets.all(24),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(message, textAlign: TextAlign.center),
          const SizedBox(height: 12),
          OutlinedButton(onPressed: onRetry, child: const Text('Retry')),
        ],
      ),
    ),
  );
}
