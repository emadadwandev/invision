import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

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
      appBar: AppBar(title: const Text('Two-Factor Authentication')),
      body: statusAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, _) => Center(child: Text('Error: $err')),
        data: (status) => SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Status card
              _StatusCard(status: status),
              const SizedBox(height: 16),

              if (_error != null) ...[
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.red.shade50,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(_error!,
                      style: TextStyle(color: Colors.red.shade700)),
                ),
                const SizedBox(height: 16),
              ],

              // Enable flow
              if (!status.enabled && _enableResult == null) ...[
                Text('Protect your account',
                    style: theme.textTheme.titleMedium),
                const SizedBox(height: 8),
                const Text(
                  'Add an extra layer of security by enabling two-factor '
                  'authentication. You\'ll need an authenticator app like '
                  'Google Authenticator or Authy.',
                ),
                const SizedBox(height: 16),
                FilledButton.icon(
                  onPressed: _loading ? null : _enableMfa,
                  icon: _loading
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Icon(Icons.security),
                  label: const Text('Enable MFA'),
                ),
              ],

              // Setup confirmation step
              if (_enableResult != null) ...[
                Text('Scan this secret', style: theme.textTheme.titleMedium),
                const SizedBox(height: 8),
                const Text(
                  'Add this secret key to your authenticator app, then '
                  'enter the 6-digit code below to confirm.',
                ),
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade100,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.grey.shade300),
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
                FilledButton(
                  onPressed: _loading ? null : _confirmMfa,
                  child: _loading
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(
                              strokeWidth: 2, color: Colors.white),
                        )
                      : const Text('Confirm & Enable'),
                ),
              ],

              // Recovery codes
              if (_recoveryCodes != null) ...[
                const SizedBox(height: 24),
                Text('Recovery Codes', style: theme.textTheme.titleMedium),
                const SizedBox(height: 8),
                const Text(
                  'Save these codes in a secure place. Each code can only '
                  'be used once to sign in if you lose access to your '
                  'authenticator app.',
                ),
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.amber.shade50,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.amber.shade300),
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
              if (status.enabled && status.confirmed) ...[
                const SizedBox(height: 16),
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        Text('Manage MFA',
                            style: theme.textTheme.titleMedium),
                        const SizedBox(height: 8),
                        Text(
                          'Recovery codes remaining: '
                          '${status.recoveryCodesRemaining}',
                          style: theme.textTheme.bodyMedium,
                        ),
                        const SizedBox(height: 12),
                        OutlinedButton.icon(
                          onPressed: _loading ? null : _regenerateCodes,
                          icon: const Icon(Icons.refresh),
                          label: const Text('Regenerate Recovery Codes'),
                        ),
                        const Divider(height: 24),
                        Text('Disable MFA',
                            style: theme.textTheme.titleSmall
                                ?.copyWith(color: Colors.red)),
                        const SizedBox(height: 8),
                        TextField(
                          controller: _passwordController,
                          obscureText: true,
                          decoration: const InputDecoration(
                            labelText: 'Current Password',
                            border: OutlineInputBorder(),
                            prefixIcon: Icon(Icons.lock),
                          ),
                        ),
                        const SizedBox(height: 8),
                        OutlinedButton.icon(
                          onPressed: _loading ? null : _disableMfa,
                          icon: const Icon(Icons.shield_outlined,
                              color: Colors.red),
                          label: const Text('Disable MFA',
                              style: TextStyle(color: Colors.red)),
                          style: OutlinedButton.styleFrom(
                            side: const BorderSide(color: Colors.red),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
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
    return Card(
      color: enabled ? Colors.green.shade50 : Colors.grey.shade100,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Icon(
              enabled ? Icons.verified_user : Icons.shield_outlined,
              color: enabled ? Colors.green : Colors.grey,
              size: 40,
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    enabled ? 'MFA Enabled' : 'MFA Disabled',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          color: enabled ? Colors.green.shade700 : Colors.grey,
                          fontWeight: FontWeight.bold,
                        ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    enabled
                        ? 'Your account is protected with two-factor authentication.'
                        : 'Enable MFA to add an extra layer of security.',
                    style: Theme.of(context).textTheme.bodySmall,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
