import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/theme/app_theme.dart';
import '../../data/repositories/mfa_repository.dart';
import '../providers/mfa_providers.dart';

class MfaSetupPage extends ConsumerStatefulWidget {
  const MfaSetupPage({super.key});

  @override
  ConsumerState<MfaSetupPage> createState() => _MfaSetupPageState();
}

class _MfaSetupPageState extends ConsumerState<MfaSetupPage> {
  final _codeController = TextEditingController();
  final _passwordController = TextEditingController();

  MfaEnableResult? _enableResult;
  List<String>? _recoveryCodes;
  bool _loading = false;
  String? _error;

  @override
  void dispose() {
    _codeController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _enableMfa() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final repo = ref.read(mfaRepositoryProvider);
      final result = await repo.enable();
      setState(() {
        _enableResult = result;
        _recoveryCodes = result.recoveryCodes;
      });
    } catch (e) {
      setState(() => _error = 'Failed to enable MFA: $e');
    } finally {
      setState(() => _loading = false);
    }
  }

  Future<void> _confirmMfa() async {
    final code = _codeController.text.trim();
    if (code.length != 6) {
      setState(() => _error = 'Enter a 6-digit code');
      return;
    }
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final repo = ref.read(mfaRepositoryProvider);
      await repo.confirm(code);
      ref.invalidate(mfaStatusProvider);
      setState(() {
        _enableResult = null;
        _codeController.clear();
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('MFA enabled successfully'),
            backgroundColor: Colors.green,
          ),
        );
      }
    } catch (e) {
      setState(() => _error = 'Invalid confirmation code');
    } finally {
      setState(() => _loading = false);
    }
  }

  Future<void> _disableMfa() async {
    final password = _passwordController.text.trim();
    if (password.isEmpty) {
      setState(() => _error = 'Enter your password to disable MFA');
      return;
    }
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final repo = ref.read(mfaRepositoryProvider);
      await repo.disable(password);
      ref.invalidate(mfaStatusProvider);
      _passwordController.clear();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('MFA has been disabled'),
            backgroundColor: Colors.orange,
          ),
        );
      }
    } catch (e) {
      setState(() => _error = 'Failed to disable MFA. Check your password.');
    } finally {
      setState(() => _loading = false);
    }
  }

  Future<void> _regenerateCodes() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final repo = ref.read(mfaRepositoryProvider);
      final codes = await repo.regenerateRecoveryCodes();
      setState(() => _recoveryCodes = codes);
    } catch (e) {
      setState(() => _error = 'Failed to regenerate recovery codes');
    } finally {
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final statusAsync = ref.watch(mfaStatusProvider);
    final theme = Theme.of(context);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Two-Factor Authentication',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: statusAsync.when(
        loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
        error: (err, _) => Center(child: Text('Error: $err')),
        data: (status) => SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Status card
              _StatusCard(status: status),
              const SizedBox(height: 16),

              if (_error != null) ...[const SizedBox(height: 2),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppColors.errorContainer,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(_error!,
                      style: const TextStyle(color: AppColors.error)),
                ),
                const SizedBox(height: 14)],

              // Enable flow
              if (!status.enabled && _enableResult == null) ...[const SizedBox(height: 8),
                Text('Protect your account',
                    style: Theme.of(context).textTheme.titleMedium
                        ?.copyWith(color: AppColors.onSurface, fontWeight: FontWeight.w700)),
                const SizedBox(height: 8),
                const Text(
                  'Add an extra layer of security by enabling two-factor '
                  'authentication. You\'ll need an authenticator app like '
                  'Google Authenticator or Authy.',
                  style: TextStyle(color: AppColors.onSurfaceVariant),
                ),
                const SizedBox(height: 16),
                GestureDetector(
                  onTap: _loading ? null : _enableMfa,
                  child: Container(
                    width: double.infinity, height: 50,
                    decoration: BoxDecoration(
                      gradient: _loading
                          ? null
                          : const LinearGradient(
                              colors: [AppColors.primary, AppColors.primaryContainer],
                            ),
                      color: _loading ? AppColors.surfaceContainerHigh : null,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    alignment: Alignment.center,
                    child: _loading
                        ? const SizedBox(width: 18, height: 18,
                            child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.primary))
                        : const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.security_rounded, color: Colors.white, size: 18),
                              SizedBox(width: 6),
                              Text('Enable MFA',
                                  style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 15)),
                            ],
                          ),
                  ),
                )],

              // Setup confirmation step
              if (_enableResult != null) ...[const SizedBox(height: 4),
                Text('Scan this secret',
                    style: Theme.of(context).textTheme.titleMedium
                        ?.copyWith(color: AppColors.onSurface, fontWeight: FontWeight.w700)),
                const SizedBox(height: 8),
                const Text(
                  'Add this secret key to your authenticator app, then '
                  'enter the 6-digit code below to confirm.',
                  style: TextStyle(color: AppColors.onSurfaceVariant),
                ),
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: AppColors.surfaceContainerLow,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: AppColors.outlineVariant),
                  ),
                  child: Row(
                    children: [
                      Expanded(
                        child: SelectableText(
                          _enableResult!.secret,
                          style: const TextStyle(
                            fontFamily: 'monospace',
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.copy),
                        onPressed: () {
                          Clipboard.setData(
                              ClipboardData(text: _enableResult!.secret));
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                                content: Text('Secret copied to clipboard')),
                          );
                        },
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: _codeController,
                  keyboardType: TextInputType.number,
                  maxLength: 6,
                  decoration: const InputDecoration(
                    labelText: 'Verification Code',
                    hintText: '000000',
                    border: OutlineInputBorder(),
                    counterText: '',
                    prefixIcon: Icon(Icons.pin),
                  ),
                ),
                const SizedBox(height: 12),
                GestureDetector(
                  onTap: _loading ? null : _confirmMfa,
                  child: Container(
                    width: double.infinity, height: 50,
                    decoration: BoxDecoration(
                      gradient: _loading
                          ? null
                          : const LinearGradient(
                              colors: [AppColors.primary, AppColors.primaryContainer],
                            ),
                      color: _loading ? AppColors.surfaceContainerHigh : null,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    alignment: Alignment.center,
                    child: _loading
                        ? const SizedBox(width: 18, height: 18,
                            child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : const Text('Confirm & Enable',
                            style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 15)),
                  ),
                ),
              ],

              // Recovery codes
              if (_recoveryCodes != null) ...[const SizedBox(height: 20),
                Text('Recovery Codes',
                    style: Theme.of(context).textTheme.titleMedium
                        ?.copyWith(color: AppColors.onSurface, fontWeight: FontWeight.w700)),
                const SizedBox(height: 8),
                const Text(
                  'Save these codes in a secure place. Each code can only '
                  'be used once to sign in if you lose access to your '
                  'authenticator app.',
                  style: TextStyle(color: AppColors.onSurfaceVariant),
                ),
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFF9C4),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: const Color(0xFFFFE082)),
                  ),
                  child: Column(
                    children: [
                      Wrap(
                        spacing: 8,
                        runSpacing: 4,
                        children: _recoveryCodes!
                            .map((code) => Chip(
                                  label: Text(code,
                                      style: const TextStyle(
                                          fontFamily: 'monospace')),
                                ))
                            .toList(),
                      ),
                      const SizedBox(height: 8),
                      TextButton.icon(
                        onPressed: () {
                          Clipboard.setData(ClipboardData(
                              text: _recoveryCodes!.join('\n')));
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                                content: Text('Codes copied to clipboard')),
                          );
                        },
                        icon: const Icon(Icons.copy, size: 16),
                        label: const Text('Copy All'),
                      ),
                    ],
                  ),
                ),
              ],

              // MFA is enabled — show management options
              if (status.enabled && status.confirmed) ...[const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(18),
                  decoration: BoxDecoration(
                    color: AppColors.surfaceContainerLowest,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Text('Manage MFA',
                          style: Theme.of(context).textTheme.titleMedium
                              ?.copyWith(color: AppColors.onSurface, fontWeight: FontWeight.w700)),
                      const SizedBox(height: 8),
                      Text(
                        'Recovery codes remaining: '
                        '${status.recoveryCodesRemaining}',
                        style: Theme.of(context).textTheme.bodyMedium
                            ?.copyWith(color: AppColors.onSurface),
                      ),
                      const SizedBox(height: 12),
                      GestureDetector(
                        onTap: _loading ? null : _regenerateCodes,
                        child: Container(
                          padding: const EdgeInsets.symmetric(vertical: 10),
                          decoration: BoxDecoration(
                            color: AppColors.surfaceContainerLow,
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: AppColors.outlineVariant),
                          ),
                          alignment: Alignment.center,
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.refresh_rounded, size: 16, color: AppColors.primary),
                              SizedBox(width: 6),
                              Text('Regenerate Recovery Codes',
                                  style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.w600)),
                            ],
                          ),
                        ),
                      ),
                      const Padding(padding: EdgeInsets.symmetric(vertical: 14),
                          child: Divider(color: AppColors.outlineVariant, height: 1)),
                      Text('Disable MFA',
                          style: Theme.of(context).textTheme.titleSmall
                              ?.copyWith(color: AppColors.error, fontWeight: FontWeight.w700)),
                      const SizedBox(height: 8),
                      TextField(
                        controller: _passwordController,
                        obscureText: true,
                        decoration: const InputDecoration(
                          labelText: 'Current Password',
                          prefixIcon: Icon(Icons.lock_rounded, size: 18, color: AppColors.outline),
                        ),
                      ),
                      const SizedBox(height: 10),
                      GestureDetector(
                        onTap: _loading ? null : _disableMfa,
                        child: Container(
                          padding: const EdgeInsets.symmetric(vertical: 10),
                          decoration: BoxDecoration(
                            color: AppColors.errorContainer,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          alignment: Alignment.center,
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.shield_outlined, color: AppColors.error, size: 16),
                              SizedBox(width: 6),
                              Text('Disable MFA',
                                  style: TextStyle(color: AppColors.error, fontWeight: FontWeight.w700)),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                )],
            ],
          ),
        ),
      ),
    );
  }
}

class _StatusCard extends StatelessWidget {
  const _StatusCard({required this.status});
  final MfaStatus status;

  @override
  Widget build(BuildContext context) {
    final enabled = status.enabled && status.confirmed;
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: enabled ? AppColors.secondaryContainer : AppColors.surfaceContainerHigh,
        borderRadius: BorderRadius.circular(14),
      ),
      child: Row(
        children: [
          Container(
            width: 48, height: 48,
            decoration: BoxDecoration(
              color: enabled ? AppColors.secondary.withOpacity(0.15) : AppColors.surfaceContainerLowest,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(
              enabled ? Icons.verified_user_rounded : Icons.shield_outlined,
              color: enabled ? AppColors.secondary : AppColors.outline,
              size: 26,
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  enabled ? 'MFA Enabled' : 'MFA Disabled',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        color: enabled ? AppColors.onSecondaryContainer : AppColors.onSurface,
                        fontWeight: FontWeight.w700,
                      ),
                ),
                const SizedBox(height: 4),
                Text(
                  enabled
                      ? 'Your account is protected with two-factor authentication.'
                      : 'Enable MFA to add an extra layer of security.',
                  style: Theme.of(context).textTheme.bodySmall
                      ?.copyWith(color: AppColors.onSurfaceVariant),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
