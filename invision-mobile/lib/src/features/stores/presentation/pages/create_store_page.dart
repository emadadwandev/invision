import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/enums/store_category.dart';
import '../../../../core/theme/app_theme.dart';
import '../../../gps_tracking/presentation/providers/gps_tracking_providers.dart';
import '../providers/store_providers.dart';

class CreateStorePage extends ConsumerStatefulWidget {
  const CreateStorePage({super.key});

  @override
  ConsumerState<CreateStorePage> createState() => _CreateStorePageState();
}

class _CreateStorePageState extends ConsumerState<CreateStorePage> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _codeController = TextEditingController();
  final _addressController = TextEditingController();
  StoreCategory _category = StoreCategory.supermarket;
  String? _qrCode;
  double? _latitude;
  double? _longitude;
  bool _loadingLocation = false;
  bool _saving = false;

  @override
  void dispose() {
    _nameController.dispose();
    _codeController.dispose();
    _addressController.dispose();
    super.dispose();
  }

  Future<void> _scanQr() async {
    final code = await context.push<String>('/scanner');
    if (!mounted || code == null || code.trim().isEmpty) return;
    setState(() => _qrCode = code.trim());
  }

  Future<void> _captureLocation() async {
    setState(() => _loadingLocation = true);
    try {
      final position = await ref
          .read(locationServiceProvider)
          .getCurrentPosition();
      if (position == null) {
        throw Exception('Location unavailable. Enable GPS and grant location permission.');
      }
      if (mounted) {
        setState(() {
          _latitude = position.latitude;
          _longitude = position.longitude;
        });
      }
    } catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Could not capture location: $error')),
        );
      }
    } finally {
      if (mounted) setState(() => _loadingLocation = false);
    }
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    if (_qrCode == null) {
      _showMessage('Scan a QR code for this store before saving.');
      return;
    }
    if (_latitude == null || _longitude == null) {
      _showMessage('Capture the store’s current GPS location before saving.');
      return;
    }

    setState(() => _saving = true);
    try {
      final store = await ref.read(storeRepositoryProvider).createStore(
            name: _nameController.text.trim(),
            code: _codeController.text.trim(),
            qrCode: _qrCode!,
            category: _category.apiValue,
            gpsLatitude: _latitude!,
            gpsLongitude: _longitude!,
            address: _addressController.text,
          );
      ref.invalidate(storesProvider);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('${store.name} was added.')),
        );
        context.pop(store);
      }
    } catch (error) {
      if (mounted) _showMessage('Could not add store: $error');
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  void _showMessage(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Add Store'),
        backgroundColor: AppColors.surface,
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            Text(
              'Store details',
              style: Theme.of(context).textTheme.titleLarge,
            ),
            const SizedBox(height: 14),
            TextFormField(
              controller: _nameController,
              textCapitalization: TextCapitalization.words,
              decoration: const InputDecoration(
                labelText: 'Store name *',
                border: OutlineInputBorder(),
              ),
              validator: (value) => value == null || value.trim().isEmpty
                  ? 'Enter the store name.'
                  : null,
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _codeController,
              textCapitalization: TextCapitalization.characters,
              decoration: const InputDecoration(
                labelText: 'Store code *',
                border: OutlineInputBorder(),
              ),
              validator: (value) => value == null || value.trim().isEmpty
                  ? 'Enter a unique store code.'
                  : null,
            ),
            const SizedBox(height: 12),
            DropdownButtonFormField<StoreCategory>(
              initialValue: _category,
              decoration: const InputDecoration(
                labelText: 'Category *',
                border: OutlineInputBorder(),
              ),
              items: StoreCategory.values
                  .map((category) => DropdownMenuItem(
                        value: category,
                        child: Text(category.label),
                      ))
                  .toList(),
              onChanged: _saving
                  ? null
                  : (category) {
                      if (category != null) setState(() => _category = category);
                    },
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _addressController,
              minLines: 2,
              maxLines: 3,
              textCapitalization: TextCapitalization.sentences,
              decoration: const InputDecoration(
                labelText: 'Address (optional)',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 20),
            Text('Store verification',
                style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 10),
            OutlinedButton.icon(
              onPressed: _saving ? null : _scanQr,
              icon: const Icon(Icons.qr_code_scanner_rounded),
              label: Text(_qrCode == null ? 'Scan store QR code *' : 'QR scanned'),
            ),
            if (_qrCode != null) ...[
              const SizedBox(height: 4),
              Text(
                _qrCode!,
                style: Theme.of(context)
                    .textTheme
                    .bodySmall
                    ?.copyWith(color: AppColors.onSurfaceVariant),
              ),
            ],
            const SizedBox(height: 8),
            OutlinedButton.icon(
              onPressed: _saving || _loadingLocation ? null : _captureLocation,
              icon: _loadingLocation
                  ? const SizedBox.square(
                      dimension: 18,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Icon(Icons.my_location_rounded),
              label: Text(_latitude == null
                  ? 'Capture current location *'
                  : 'Location captured'),
            ),
            if (_latitude != null && _longitude != null) ...[
              const SizedBox(height: 4),
              Text(
                '${_latitude!.toStringAsFixed(6)}, ${_longitude!.toStringAsFixed(6)}',
                style: Theme.of(context)
                    .textTheme
                    .bodySmall
                    ?.copyWith(color: AppColors.onSurfaceVariant),
              ),
            ],
            const SizedBox(height: 22),
            FilledButton.icon(
              onPressed: _saving ? null : _save,
              icon: _saving
                  ? const SizedBox.square(
                      dimension: 18,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Icon(Icons.add_business_rounded),
              label: const Text('Save store'),
            ),
          ],
        ),
      ),
    );
  }
}
