class ReportTemplateModel {
  final int id;
  final String name;
  final String type;
  final String? description;
  final Map<String, dynamic> config;
  final Map<String, dynamic>? layout;
  final bool isShared;
  final bool isFavorite;
  final String? schedule;
  final DateTime? lastGeneratedAt;
  final DateTime createdAt;

  ReportTemplateModel({
    required this.id,
    required this.name,
    required this.type,
    this.description,
    required this.config,
    this.layout,
    this.isShared = false,
    this.isFavorite = false,
    this.schedule,
    this.lastGeneratedAt,
    required this.createdAt,
  });

  factory ReportTemplateModel.fromJson(Map<String, dynamic> json) {
    return ReportTemplateModel(
      id: json['id'] as int,
      name: json['name'] as String,
      type: json['type'] as String? ?? 'custom',
      description: json['description'] as String?,
      config: json['config'] is Map ? Map<String, dynamic>.from(json['config']) : {},
      layout: json['layout'] is Map ? Map<String, dynamic>.from(json['layout']) : null,
      isShared: json['is_shared'] == true,
      isFavorite: json['is_favorite'] == true,
      schedule: json['schedule'] as String?,
      lastGeneratedAt: json['last_generated_at'] != null
          ? DateTime.tryParse(json['last_generated_at'])
          : null,
      createdAt: DateTime.parse(json['created_at']),
    );
  }

  Map<String, dynamic> toJson() => {
        'name': name,
        'type': type,
        'description': description,
        'config': config,
        'layout': layout,
        'is_shared': isShared,
      };
}

class PresentationTemplateModel {
  final int id;
  final String name;
  final String category;
  final String? description;
  final List<Map<String, dynamic>> slideDefinitions;
  final Map<String, dynamic>? theme;
  final bool isDefault;

  PresentationTemplateModel({
    required this.id,
    required this.name,
    required this.category,
    this.description,
    required this.slideDefinitions,
    this.theme,
    this.isDefault = false,
  });

  factory PresentationTemplateModel.fromJson(Map<String, dynamic> json) {
    return PresentationTemplateModel(
      id: json['id'] as int,
      name: json['name'] as String,
      category: json['category'] as String? ?? 'general',
      description: json['description'] as String?,
      slideDefinitions: (json['slide_definitions'] as List?)
              ?.map((e) => Map<String, dynamic>.from(e))
              .toList() ??
          [],
      theme: json['theme'] is Map ? Map<String, dynamic>.from(json['theme']) : null,
      isDefault: json['is_default'] == true,
    );
  }
}

class SavedExportModel {
  final int id;
  final String title;
  final String format;
  final String filePath;
  final int fileSize;
  final String? templateName;
  final DateTime createdAt;

  SavedExportModel({
    required this.id,
    required this.title,
    required this.format,
    required this.filePath,
    this.fileSize = 0,
    this.templateName,
    required this.createdAt,
  });

  factory SavedExportModel.fromJson(Map<String, dynamic> json) {
    return SavedExportModel(
      id: json['id'] as int,
      title: json['title'] as String,
      format: json['format'] as String,
      filePath: json['file_path'] as String,
      fileSize: json['file_size'] as int? ?? 0,
      templateName: json['template']?['name'] as String?,
      createdAt: DateTime.parse(json['created_at']),
    );
  }

  String get formattedSize {
    if (fileSize < 1024) return '$fileSize B';
    if (fileSize < 1024 * 1024) return '${(fileSize / 1024).toStringAsFixed(1)} KB';
    return '${(fileSize / (1024 * 1024)).toStringAsFixed(1)} MB';
  }

  String get formatLabel {
    return switch (format) {
      'csv' => 'CSV',
      'excel' => 'Excel',
      'pdf' => 'PDF',
      'presentation' => 'Presentation',
      'html' => 'HTML Report',
      _ => format.toUpperCase(),
    };
  }
}

class PresentationDataModel {
  final String title;
  final String generatedAt;
  final List<SlideModel> slides;

  PresentationDataModel({
    required this.title,
    required this.generatedAt,
    required this.slides,
  });

  factory PresentationDataModel.fromJson(Map<String, dynamic> json) {
    return PresentationDataModel(
      title: json['title'] as String? ?? '',
      generatedAt: json['generated_at'] as String? ?? '',
      slides: (json['slides'] as List?)
              ?.map((e) => SlideModel.fromJson(Map<String, dynamic>.from(e)))
              .toList() ??
          [],
    );
  }
}

class SlideModel {
  final String title;
  final String layout;
  final dynamic content;
  final String notes;

  SlideModel({
    required this.title,
    required this.layout,
    this.content,
    this.notes = '',
  });

  factory SlideModel.fromJson(Map<String, dynamic> json) {
    return SlideModel(
      title: json['title'] as String? ?? '',
      layout: json['layout'] as String? ?? 'content',
      content: json['content'],
      notes: json['notes'] as String? ?? '',
    );
  }
}
