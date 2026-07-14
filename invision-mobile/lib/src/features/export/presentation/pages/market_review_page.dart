import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/theme/app_theme.dart';
import '../../data/models/export_models.dart';
import '../providers/export_providers.dart';

class MarketReviewPage extends ConsumerStatefulWidget {
  const MarketReviewPage({super.key});

  @override
  ConsumerState<MarketReviewPage> createState() => _MarketReviewPageState();
}

class _MarketReviewPageState extends ConsumerState<MarketReviewPage> {
  String _period = 'month';

  @override
  Widget build(BuildContext context) {
    final presentationAsync = ref.watch(marketReviewProvider(_period));

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Market Review',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
        actions: [
          PopupMenuButton<String>(
            icon: const Icon(Icons.tune_rounded, size: 20, color: AppColors.primary),
            onSelected: (value) => setState(() => _period = value),
            itemBuilder: (_) => [
              const PopupMenuItem(value: 'week', child: Text('This Week')),
              const PopupMenuItem(value: 'month', child: Text('This Month')),
              const PopupMenuItem(value: 'quarter', child: Text('This Quarter')),
              const PopupMenuItem(value: 'year', child: Text('This Year')),
            ],
          ),
        ],
      ),
      body: presentationAsync.when(
        loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
        error: (e, _) => Center(child: Text('Error: $e')),
        data: (presentation) => _PresentationViewer(presentation: presentation),
      ),
    );
  }
}

class _PresentationViewer extends StatefulWidget {
  final PresentationDataModel presentation;

  const _PresentationViewer({required this.presentation});

  @override
  State<_PresentationViewer> createState() => _PresentationViewerState();
}

class _PresentationViewerState extends State<_PresentationViewer> {
  late PageController _pageController;
  int _currentSlide = 0;

  @override
  void initState() {
    super.initState();
    _pageController = PageController();
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final slides = widget.presentation.slides;

    return Column(
      children: [
        // Slide counter
        Padding(
          padding: const EdgeInsets.all(8),
          child: Text(
            'Slide ${_currentSlide + 1} of ${slides.length}',
            style: Theme.of(context).textTheme.bodySmall,
          ),
        ),

        // Slides
        Expanded(
          child: PageView.builder(
            controller: _pageController,
            onPageChanged: (i) => setState(() => _currentSlide = i),
            itemCount: slides.length,
            itemBuilder: (context, index) {
              return _SlideCard(slide: slides[index]);
            },
          ),
        ),

        // Navigation
        Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              TextButton.icon(
                onPressed: _currentSlide > 0
                    ? () => _pageController.previousPage(
                          duration: const Duration(milliseconds: 300),
                          curve: Curves.easeInOut,
                        )
                    : null,
                icon: const Icon(Icons.arrow_back),
                label: const Text('Previous'),
              ),
              // Slide dots
              Row(
                children: List.generate(
                  slides.length,
                  (i) => Container(
                    width: 8,
                    height: 8,
                    margin: const EdgeInsets.symmetric(horizontal: 3),
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: i == _currentSlide
                          ? AppColors.primary
                          : AppColors.outlineVariant,
                    ),
                  ),
                ),
              ),
              TextButton.icon(
                onPressed: _currentSlide < slides.length - 1
                    ? () => _pageController.nextPage(
                          duration: const Duration(milliseconds: 300),
                          curve: Curves.easeInOut,
                        )
                    : null,
                icon: const Icon(Icons.arrow_forward),
                label: const Text('Next'),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _SlideCard extends StatelessWidget {
  final SlideModel slide;

  const _SlideCard({required this.slide});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLowest,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withOpacity(0.06),
            blurRadius: 8, offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              slide.title,
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    fontWeight: FontWeight.bold, color: AppColors.onSurface),
            ),
            const Divider(height: 24, color: AppColors.outlineVariant),
            Expanded(child: _buildContent(context)),
            if (slide.notes.isNotEmpty) ...[
              const Divider(height: 24, color: AppColors.outlineVariant),
              Text(
                slide.notes,
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      fontStyle: FontStyle.italic,
                      color: AppColors.onSurfaceVariant),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildContent(BuildContext context) {
    switch (slide.layout) {
      case 'title':
        return _buildTitleSlide(context);
      case 'kpi_grid':
        return _buildKpiGrid(context);
      case 'table':
        return _buildTable(context);
      case 'two_column':
        return _buildTwoColumn(context);
      default:
        return _buildDefaultContent(context);
    }
  }

  Widget _buildTitleSlide(BuildContext context) {
    final content = slide.content is Map ? slide.content as Map<String, dynamic> : {};
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          if (content['subtitle'] != null)
            Text(
              content['subtitle'],
              style: Theme.of(context).textTheme.titleLarge,
              textAlign: TextAlign.center,
            ),
          if (content['date'] != null) ...[
            const SizedBox(height: 8),
            Text(
              content['date'],
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: AppColors.onSurfaceVariant),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildKpiGrid(BuildContext context) {
    final items = slide.content is List ? (slide.content as List) : [];
    return GridView.builder(
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        childAspectRatio: 1.6,
        crossAxisSpacing: 12,
        mainAxisSpacing: 12,
      ),
      itemCount: items.length,
      itemBuilder: (context, index) {
        final item = items[index] is Map ? items[index] as Map<String, dynamic> : {};
        return Container(
          decoration: BoxDecoration(
            color: Theme.of(context).colorScheme.primaryContainer.withValues(alpha: 0.3),
            borderRadius: BorderRadius.circular(12),
          ),
          padding: const EdgeInsets.all(12),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                '${item['value'] ?? ''}',
                style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                      fontWeight: FontWeight.bold,
                      color: Theme.of(context).colorScheme.primary,
                    ),
              ),
              const SizedBox(height: 4),
              Text(
                item['label'] ?? '',
                style: Theme.of(context).textTheme.bodySmall,
                textAlign: TextAlign.center,
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildTable(BuildContext context) {
    final content = slide.content is Map ? slide.content as Map<String, dynamic> : {};
    final headers = (content['headers'] as List?)?.cast<String>() ?? [];
    final rows = (content['rows'] as List?) ?? [];

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: DataTable(
        columns: headers.map((h) => DataColumn(label: Text(h, style: const TextStyle(fontWeight: FontWeight.bold)))).toList(),
        rows: rows.map<DataRow>((row) {
          final cells = row is List ? row : [];
          return DataRow(
            cells: cells.map<DataCell>((cell) => DataCell(Text('$cell'))).toList(),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildTwoColumn(BuildContext context) {
    final content = slide.content is Map ? slide.content as Map<String, dynamic> : {};
    final left = content['left'] is Map ? Map<String, dynamic>.from(content['left'] as Map) : <String, dynamic>{};
    final right = content['right'] is Map ? Map<String, dynamic>.from(content['right'] as Map) : <String, dynamic>{};

    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(child: _buildColumnContent(context, left)),
        const SizedBox(width: 16),
        Expanded(child: _buildColumnContent(context, right)),
      ],
    );
  }

  Widget _buildColumnContent(BuildContext context, Map<String, dynamic> col) {
    final items = (col['items'] as List?)?.cast<String>() ?? [];
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          col['title'] ?? '',
          style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 8),
        ...items.map((item) => Padding(
              padding: const EdgeInsets.only(bottom: 4),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('• ', style: TextStyle(fontWeight: FontWeight.bold)),
                  Expanded(child: Text(item)),
                ],
              ),
            )),
      ],
    );
  }

  Widget _buildDefaultContent(BuildContext context) {
    if (slide.content is Map) {
      return SingleChildScrollView(
        child: Text(slide.content.toString()),
      );
    }
    if (slide.content is List) {
      return ListView.builder(
        itemCount: (slide.content as List).length,
        itemBuilder: (context, i) => Padding(
          padding: const EdgeInsets.only(bottom: 4),
          child: Text('• ${(slide.content as List)[i]}'),
        ),
      );
    }
    return Text('${slide.content ?? ''}');
  }
}
