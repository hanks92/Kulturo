import 'package:flutter/material.dart';
import '../services/auth_service.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _authService = AuthService();

  String _error = '';

  Future<void> _login() async {
    final email = _emailController.text;
    final password = _passwordController.text;

    final success = await _authService.login(email, password);
    if (success) {
      Navigator.pushReplacementNamed(context, '/home');
    } else {
      setState(() => _error = 'Email ou mot de passe incorrect');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Connexion')),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            TextField(
              controller: _emailController,
              decoration: const InputDecoration(labelText: 'Email'),
            ),
            TextField(
              controller: _passwordController,
              obscureText: true,
              decoration: const InputDecoration(labelText: 'Mot de passe'),
            ),
            const SizedBox(height: 20),
            ElevatedButton(
              onPressed: _login,
              child: const Text('Se connecter'),
            ),
            if (_error.isNotEmpty)
              Text(_error, style: const TextStyle(color: Colors.red)),
          ],
        ),
      ),
    );
  }
}
