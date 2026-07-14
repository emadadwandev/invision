import 'package:flutter_test/flutter_test.dart';
import 'package:invision_mobile/src/features/export/data/models/export_models.dart';

void main() {
  group('ReportTemplateModel', () {
    test('fromJson parses correctly', () {
      final json = {
        'id': 1,
        'name': 'Monthly Sales',
        'type': 'sales',
        'description': 'Monthly sales summary',
        'config': {'entity': 'sales_orders', 'filters': {}},
        'layout': null,
        'is_shared': true,
        'is_favorite': false,
        'schedule': null,
        'last_generated_at': '2026-04-01T10:00:00.000Z',
        'created_at': '2026-03-01T08:00:00.000Z',
      };

      final model = ReportTemplateModel.fromJson(json);

      expect(model.id, 1);
      expect(model.name, 'Monthly Sales');
      expect(model.type, 'sales');
      expect(model.description, 'Monthly sales summary');
      expect(model.isShared, true);
      expect(model.isFavorite, false);
      expect(model.lastGeneratedAt, isNotNull);
      expect(model.config['entity'], 'sales_orders');
    });

    test('toJson produces correct output', () {
      final model = ReportTemplateModel(
        id: 1,
        name: 'Test',
        type: 'custom',
        config: {'entity': 'stores'},
        isShared: false,
        createdAt: DateTime(2026, 1, 1),
      );

      final json = model.toJson();

      expect(json['name'], 'Test');
      expect(json['type'], 'custom');
      expect(json['config']['entity'], 'stores');
      expect(json['is_shared'], false);
    });
  });

  group('SavedExportModel', () {
    test('fromJson parses correctly', () {
      final json = {
        'id': 5,
        'title': 'Sales Report',
        'format': 'pdf',
        'file_path': 'exports/report.pdf',
        'file_size': 2048,
        'template': {'name': 'Monthly'},
        'created_at': '2026-04-01T12:00:00.000Z',
      };

      final model = SavedExportModel.fromJson(json);

      expect(model.id, 5);
      expect(model.title, 'Sales Report');
      expect(model.format, 'pdf');
      expect(model.fileSize, 2048);
      expect(model.templateName, 'Monthly');
    });

    test('formattedSize returns correct units', () {
      expect(
        SavedExportModel(id: 1, title: '', format: '', filePath: '', fileSize: 500, createdAt: DateTime.now()).formattedSize,
        '500 B',
      );
      expect(
        SavedExportModel(id: 1, title: '', format: '', filePath: '', fileSize: 2048, createdAt: DateTime.now()).formattedSize,
        '2.0 KB',
      );
      expect(
        SavedExportModel(id: 1, title: '', format: '', filePath: '', fileSize: 1048576, createdAt: DateTime.now()).formattedSize,
        '1.0 MB',
      );
    });

    test('formatLabel returns correct labels', () {
      expect(SavedExportModel(id: 1, title: '', format: 'csv', filePath: '', createdAt: DateTime.now()).formatLabel, 'CSV');
      expect(SavedExportModel(id: 1, title: '', format: 'excel', filePath: '', createdAt: DateTime.now()).formatLabel, 'Excel');
      expect(SavedExportModel(id: 1, title: '', format: 'pdf', filePath: '', createdAt: DateTime.now()).formatLabel, 'PDF');
      expect(SavedExportModel(id: 1, title: '', format: 'presentation', filePath: '', createdAt: DateTime.now()).formatLabel, 'Presentation');
      expect(SavedExportModel(id: 1, title: '', format: 'html', filePath: '', createdAt: DateTime.now()).formatLabel, 'HTML Report');
    });
  });

  group('PresentationDataModel', () {
    test('fromJson parses slides correctly', () {
      final json = {
        'title': 'Market Review',
        'generated_at': '2026-04-01T10:00:00.000Z',
        'slides': [
          {'title': 'Title Slide', 'layout': 'title', 'content': {'subtitle': 'Q1'}, 'notes': ''},
          {'title': 'KPIs', 'layout': 'kpi_grid', 'content': [{'label': 'Revenue', 'value': '1000'}], 'notes': 'Note'},
        ],
      };

      final model = PresentationDataModel.fromJson(json);

      expect(model.title, 'Market Review');
      expect(model.slides.length, 2);
      expect(model.slides[0].title, 'Title Slide');
      expect(model.slides[0].layout, 'title');
      expect(model.slides[1].notes, 'Note');
    });

    test('handles empty slides list', () {
      final json = {
        'title': 'Empty',
        'generated_at': '',
        'slides': [],
      };

      final model = PresentationDataModel.fromJson(json);
      expect(model.slides, isEmpty);
    });
  });

  group('PresentationTemplateModel', () {
    test('fromJson parses with slide definitions', () {
      final json = {
        'id': 1,
        'name': 'Market Template',
        'category': 'market_review',
        'description': 'Template for market reviews',
        'slide_definitions': [
          {'title': 'Slide 1', 'layout': 'title', 'data_source': null},
          {'title': 'Slide 2', 'layout': 'kpi_grid', 'data_source': 'overview_kpis'},
        ],
        'theme': {'primary_color': '#0E5A8A'},
        'is_default': true,
      };

      final model = PresentationTemplateModel.fromJson(json);

      expect(model.name, 'Market Template');
      expect(model.category, 'market_review');
      expect(model.slideDefinitions.length, 2);
      expect(model.theme?['primary_color'], '#0E5A8A');
      expect(model.isDefault, true);
    });
  });
}
