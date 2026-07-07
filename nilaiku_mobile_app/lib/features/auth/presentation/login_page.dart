import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/buttons.dart';
import 'auth_layout.dart';
import '../providers/auth_provider.dart';

class LoginPage extends ConsumerStatefulWidget {
  const LoginPage({super.key});

  @override
  ConsumerState<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends ConsumerState<LoginPage> {
  late TextEditingController _emailController;
  late TextEditingController _passwordController;
  bool _passwordVisible = false;
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    _emailController = TextEditingController();
    _passwordController = TextEditingController();
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    if (_isSubmitting) return;

    final authNotifier = ref.read(authProvider.notifier);
    authNotifier.clearError();

    final email = _emailController.text.trim();
    final password = _passwordController.text.trim();

    if (email.isEmpty || password.isEmpty) {
      authNotifier.setError('Email dan password harus diisi');
      return;
    }

    if (!email.contains('@')) {
      authNotifier.setError('Email tidak valid');
      return;
    }

    if (!mounted) {
      return;
    }

    setState(() => _isSubmitting = true);

    final success = await ref
        .read(authProvider.notifier)
        .login(email: email, password: password);

    if (mounted) {
      setState(() => _isSubmitting = false);
    }

    if (!mounted) {
      return;
    }

    if (success) {
      context.go('/dashboard');
      return;
    }
  }

  @override
  Widget build(BuildContext context) {
    final authAsyncState = ref.watch(authProvider);
    final isLoading = _isSubmitting;
    final authError = authAsyncState.maybeWhen(
      data: (state) => state.error,
      orElse: () => null,
    );

    return Scaffold(
      body: AuthScaffold(
        title: 'Nilaiku',
        subtitle: 'Kelola nilaimu dengan mudah',
        cardTitle: 'Masuk ke Akun',
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            TextFormField(
              controller: _emailController,
              enabled: !isLoading,
              keyboardType: TextInputType.emailAddress,
              decoration: const InputDecoration(
                labelText: 'Email',
                hintText: 'contoh@mail.com',
                prefixIcon: Icon(Icons.email_outlined),
              ),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _passwordController,
              enabled: !isLoading,
              obscureText: !_passwordVisible,
              decoration: InputDecoration(
                labelText: 'Password',
                hintText: 'Masukkan password',
                prefixIcon: const Icon(Icons.lock_outline),
                suffixIcon: IconButton(
                  onPressed: !isLoading
                      ? () =>
                            setState(() => _passwordVisible = !_passwordVisible)
                      : null,
                  icon: Icon(
                    _passwordVisible
                        ? Icons.visibility_outlined
                        : Icons.visibility_off_outlined,
                    color: AppColors.textMuted,
                  ),
                ),
              ),
            ),
            const SizedBox(height: 18),
            if (authError != null)
              Container(
                margin: const EdgeInsets.only(bottom: 16),
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: const Color.fromRGBO(225, 29, 72, 0.08),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                    color: const Color.fromRGBO(225, 29, 72, 0.2),
                  ),
                ),
                child: Row(
                  children: [
                    const Icon(
                      Icons.error_outline,
                      size: 18,
                      color: AppColors.rose,
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        authError,
                        style: GoogleFonts.inter(
                          fontSize: 13,
                          fontWeight: FontWeight.w500,
                          color: AppColors.rose,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            GradientButton(
              label: isLoading ? 'Memproses...' : 'Masuk',
              onPressed: _handleLogin,
              isLoading: isLoading,
            ),
            const SizedBox(height: 18),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  'Belum punya akun? ',
                  style: GoogleFonts.inter(
                    fontSize: 14,
                    fontWeight: FontWeight.w400,
                    color: AppColors.textBody,
                  ),
                ),
                GestureDetector(
                  onTap: !isLoading ? () => context.go('/register') : null,
                  child: Text(
                    'Daftar di sini',
                    style: GoogleFonts.inter(
                      fontSize: 14,
                      fontWeight: FontWeight.w700,
                      color: AppColors.brandBlue,
                      decoration: TextDecoration.underline,
                      decorationThickness: 1.5,
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
