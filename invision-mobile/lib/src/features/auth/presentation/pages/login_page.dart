import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/theme/app_theme.dart';
import '../providers/auth_provider.dart';

class LoginPage extends ConsumerStatefulWidget {
  const LoginPage({super.key});

  @override
  ConsumerState<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends ConsumerState<LoginPage> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _loading = false;
  bool _obscurePassword = true;
  String? _error;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    final email = _emailController.text.trim();
    final password = _passwordController.text;

    if (email.isEmpty || password.isEmpty) {
      setState(() => _error = 'Please enter email and password');
      return;
    }

    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      await ref.read(authProvider.notifier).login(
            email: email,
            password: password,
          );
      if (mounted) context.go('/dashboard');
    } on DioException catch (e) {
      setState(() {
        if (e.response?.statusCode == 401 || e.response?.statusCode == 422) {
          _error = 'Invalid email or password';
        } else {
          _error = 'Connection error. Please try again.';
        }
      });
    } catch (e) {
      debugPrint('Login error: $e');
      setState(() => _error = 'Something went wrong. Please try again.');
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 440),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // ── Logo ──────────────────────────────────────
                  Row(
                    children: [
                      Container(
                        width: 44,
                        height: 44,
                        decoration: BoxDecoration(
                          color: AppColors.primary,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Icon(Icons.hub_rounded,
                            color: Colors.white, size: 26),
                      ),
                      const SizedBox(width: 12),
                      Text('Invision',
                          style: tt.headlineMedium?.copyWith(
                            color: AppColors.onSurface,
                            letterSpacing: -0.5,
                          )),
                    ],
                  ),
                  const SizedBox(height: 40),

                  // ── Headline ──────────────────────────────────
                  Text('Welcome Back',
                      style: tt.headlineLarge
                          ?.copyWith(color: AppColors.onSurface)),
                  const SizedBox(height: 6),
                  Text(
                    'Access your secure dashboard to manage tasks.',
                    style: tt.bodyMedium
                        ?.copyWith(color: AppColors.onSurfaceVariant),
                  ),
                  const SizedBox(height: 36),

                  // ── Error banner ──────────────────────────────
                  if (_error != null) ...[
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: AppColors.errorContainer,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.error_outline,
                              color: AppColors.onErrorContainer, size: 18),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(_error!,
                                style: tt.bodySmall?.copyWith(
                                    color: AppColors.onErrorContainer,
                                    fontWeight: FontWeight.w600)),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),
                  ],

                  // ── Email ─────────────────────────────────────
                  Text('Email Address',
                      style: tt.labelMedium
                          ?.copyWith(color: AppColors.onSurfaceVariant)),
                  const SizedBox(height: 8),
                  TextField(
                    controller: _emailController,
                    keyboardType: TextInputType.emailAddress,
                    textInputAction: TextInputAction.next,
                    style: tt.bodyMedium?.copyWith(color: AppColors.onSurface),
                    decoration: InputDecoration(
                      hintText: 'name@company.com',
                      prefixIcon: const Icon(Icons.email_outlined,
                          color: AppColors.outline, size: 20),
                    ),
                  ),
                  const SizedBox(height: 20),

                  // ── Password ──────────────────────────────────
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('Password',
                          style: tt.labelMedium
                              ?.copyWith(color: AppColors.onSurfaceVariant)),
                      TextButton(
                        onPressed: () {},
                        style: TextButton.styleFrom(
                          padding: EdgeInsets.zero,
                          minimumSize: Size.zero,
                          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        ),
                        child: Text('Forgot Password?',
                            style: tt.labelSmall?.copyWith(
                                color: AppColors.primary,
                                letterSpacing: 0)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  TextField(
                    controller: _passwordController,
                    obscureText: _obscurePassword,
                    textInputAction: TextInputAction.done,
                    onSubmitted: (_) => _login(),
                    style: tt.bodyMedium?.copyWith(color: AppColors.onSurface),
                    decoration: InputDecoration(
                      hintText: '••••••••',
                      prefixIcon: const Icon(Icons.lock_outline,
                          color: AppColors.outline, size: 20),
                      suffixIcon: IconButton(
                        icon: Icon(
                          _obscurePassword
                              ? Icons.visibility_outlined
                              : Icons.visibility_off_outlined,
                          color: AppColors.outline,
                          size: 20,
                        ),
                        onPressed: () =>
                            setState(() => _obscurePassword = !_obscurePassword),
                      ),
                    ),
                  ),
                  const SizedBox(height: 32),

                  // ── CTA ───────────────────────────────────────
                  _GradientButton(
                    onTap: _loading ? null : _login,
                    child: _loading
                        ? const SizedBox(
                            height: 22,
                            width: 22,
                            child: CircularProgressIndicator(
                              strokeWidth: 2.5,
                              color: Colors.white,
                            ))
                        : Text('Sign In',
                            style: tt.labelLarge
                                ?.copyWith(color: Colors.white, fontSize: 16)),
                  ),
                  const SizedBox(height: 24),

                  // ── Footer ────────────────────────────────────
                  Center(
                    child: Text(
                      'By continuing, you agree to the Invision Terms of Service.',
                      textAlign: TextAlign.center,
                      style: tt.bodySmall
                          ?.copyWith(color: AppColors.onSurfaceVariant),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Gradient CTA button
// ─────────────────────────────────────────────────────────────────────────────
class _GradientButton extends StatefulWidget {
  const _GradientButton({required this.child, this.onTap});
  final Widget child;
  final VoidCallback? onTap;

  @override
  State<_GradientButton> createState() => _GradientButtonState();
}

class _GradientButtonState extends State<_GradientButton> {
  bool _pressed = false;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTapDown: (_) => setState(() => _pressed = true),
      onTapUp: (_) {
        setState(() => _pressed = false);
        widget.onTap?.call();
      },
      onTapCancel: () => setState(() => _pressed = false),
      child: AnimatedScale(
        scale: _pressed ? 0.97 : 1.0,
        duration: const Duration(milliseconds: 120),
        child: Container(
          height: 54,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            gradient: widget.onTap == null
                ? null
                : const LinearGradient(
                    colors: [AppColors.primary, AppColors.primaryContainer],
                  ),
            color: widget.onTap == null ? AppColors.outlineVariant : null,
            borderRadius: BorderRadius.circular(12),
            boxShadow: widget.onTap == null
                ? null
                : [
                    BoxShadow(
                      color: AppColors.primary.withOpacity(0.25),
                      blurRadius: 12,
                      offset: const Offset(0, 4),
                    )
                  ],
          ),
          child: widget.child,
        ),
      ),
    );
  }
}
