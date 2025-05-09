import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

import '../services/auth_service.dart';
import 'deck_list_screen.dart';

class AIScreen extends StatefulWidget {
  const AIScreen({Key? key}) : super(key: key);

  @override
  State<AIScreen> createState() => _AIScreenState();
}

class _AIScreenState extends State<AIScreen> {
  final _formKey = GlobalKey<FormState>();
  String _title = '';
  String _prompt = '';
  String _resources = '';
  bool _isSubmitting = false;

  Future<void> _submitForm() async {
    if (!_formKey.currentState!.validate()) return;

    _formKey.currentState!.save();
    setState(() => _isSubmitting = true);

    final authService = AuthService();
    final token = await authService.getToken();

    if (token == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("Utilisateur non authentifié.")),
      );
      setState(() => _isSubmitting = false);
      return;
    }

    final response = await http.post(
      Uri.parse('http://localhost:8000/api/ai/generate'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'title': _title,
        'prompt': _prompt,
        'resources': _resources,
      }),
    );

    setState(() => _isSubmitting = false);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      final success = data['success'];
      if (success == true) {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (_) => const DeckListScreen()),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text("Erreur: ${data['error'] ?? 'Échec inconnu.'}")),
        );
      }
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text("Erreur serveur (${response.statusCode})")),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Générer un deck via IA")),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: _isSubmitting
            ? const Center(child: CircularProgressIndicator())
            : Form(
                key: _formKey,
                child: ListView(
                  children: [
                    TextFormField(
                      decoration: const InputDecoration(labelText: 'Titre du Deck'),
                      validator: (value) =>
                          value == null || value.isEmpty ? 'Veuillez entrer un titre.' : null,
                      onSaved: (value) => _title = value!,
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      decoration: const InputDecoration(labelText: 'Prompt pour l’IA'),
                      maxLines: 4,
                      validator: (value) =>
                          value == null || value.isEmpty ? 'Veuillez entrer un prompt.' : null,
                      onSaved: (value) => _prompt = value!,
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      decoration:
                          const InputDecoration(labelText: 'Ressources supplémentaires (optionnel)'),
                      maxLines: 3,
                      onSaved: (value) => _resources = value ?? '',
                    ),
                    const SizedBox(height: 24),
                    ElevatedButton(
                      onPressed: _submitForm,
                      child: const Text('Générer le Deck'),
                    ),
                  ],
                ),
              ),
      ),
    );
  }
}
