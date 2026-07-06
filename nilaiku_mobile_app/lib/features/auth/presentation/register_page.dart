import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/buttons.dart';
import 'auth_layout.dart';
import '../providers/auth_provider.dart';

class RegisterPage extends ConsumerStatefulWidget {
  const RegisterPage({super.key});

  @override
  ConsumerState<RegisterPage> createState() => _RegisterPageState();
}

class _RegisterPageState extends ConsumerState<RegisterPage> {
  late TextEditingController _nameController;
  late TextEditingController _emailController;
  late TextEditingController _passwordController;
  late TextEditingController _passwordConfirmController;
  bool _passwordVisible = false;
  bool _passwordConfirmVisible = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _nameController = TextEditingController();
    _emailController = TextEditingController();
    _passwordController = TextEditingController();
    _passwordConfirmController = TextEditingController();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _passwordConfirmController.dispose();
    super.dispose();
  }

  Future<void> _handleRegister() async {
    setState(() => _errorMessage = null);

    final name = _nameController.text.trim();
    final email = _emailController.text.trim();
    final password = _passwordController.text.trim();
    final passwordConfirm = _passwordConfirmController.text.trim();

    if (name.isEmpty ||
        email.isEmpty ||
        password.isEmpty ||
        passwordConfirm.isEmpty) {
      setState(() => _errorMessage = 'Semua field harus diisi');
      return;
    }

    if (!email.contains('@')) {
      setState(() => _errorMessage = 'Email tidak valid');
      return;
    }

    if (password.length < 6) {
      setState(() => _errorMessage = 'Password minimal 6 karakter');
      return;
    }

    if (password != passwordConfirm) {
      setState(() => _errorMessage = 'Password tidak cocok');
      return;
    }

    final success = await ref
        .read(authProvider.notifier)
        .register(
          name: name,
          email: email,
          password: password,
          passwordConfirmation: passwordConfirm,
        );

    if (mounted) {
      if (success) {
        context.go('/dashboard');
      } else {
        final asyncState = ref.watch(authProvider);
        final error = asyncState.maybeWhen(
          data: (state) => state.error,
          orElse: () => null,
        );
        setState(() => _errorMessage = error ?? 'Registrasi gagal');
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final authAsyncState = ref.watch(authProvider);
    final isLoading = authAsyncState.isLoading;

    return Scaffold(
      body: AuthScaffold(
        title: 'Nilaiku',
        subtitle: 'Buat akun baru untuk memulai',
        cardTitle: 'Daftar Akun Baru',
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            TextFormField(
              controller: _nameController,
              enabled: !isLoading,
              keyboardType: TextInputType.text,
              decoration: const InputDecoration(
                labelText: 'Nama Lengkap',
                hintText: 'Masukkan nama Anda',
                prefixIcon: Icon(Icons.person_outline),
              ),
            ),
            const SizedBox(height: 16),
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
                hintText: 'Minimal 6 karakter',
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
            const SizedBox(height: 16),
            TextFormField(
              controller: _passwordConfirmController,
              enabled: !isLoading,
              obscureText: !_passwordConfirmVisible,
              decoration: InputDecoration(
                labelText: 'Konfirmasi Password',
                hintText: 'Ulangi password',
                prefixIcon: const Icon(Icons.lock_outline),
                suffixIcon: IconButton(
                  onPressed: !isLoading
                      ? () => setState(
                          () => _passwordConfirmVisible =
                              !_passwordConfirmVisible,
                        )
                      : null,
                  icon: Icon(
                    _passwordConfirmVisible
                        ? Icons.visibility_outlined
                        : Icons.visibility_off_outlined,
                    color: AppColors.textMuted,
                  ),
                ),
              ),
            ),
            const SizedBox(height: 18),
            if (_errorMessage != null)
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
                        _errorMessage!,
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
              label: isLoading ? 'Mendaftar...' : 'Daftar',
              onPressed: _handleRegister,
              isLoading: isLoading,
            ),
            const SizedBox(height: 18),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  'Sudah punya akun? ',
                  style: GoogleFonts.inter(
                    fontSize: 14,
                    fontWeight: FontWeight.w400,
                    color: AppColors.textBody,
                  ),
                ),
                GestureDetector(
                  onTap: !isLoading ? () => context.go('/login') : null,
                  child: Text(
                    'Masuk di sini',
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
