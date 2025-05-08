import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

import '../models/deck.dart';
import '../services/auth_service.dart';

class FlashcardCreateScreen extends StatefulWidget {
  final Deck deck;

  const FlashcardCreateScreen({Key? key, required this.deck}) : super(key: key);

  @override
  _FlashcardCreateScreenState createState() => _FlashcardCreateScreenState();
}

class _FlashcardCreateScreenState extends State<FlashcardCreateScreen> {
  final _formKey = GlobalKey<FormState>();
  String _question = '';
  String _answer = '';
  bool _isSubmitting = false;

  Future<void> _submitForm() async {
    final currentState = _formKey.currentState;
    if (currentState == null || !currentState.validate()) return;

    currentState.save();
    setState(() {
      _isSubmitting = true;
    });

    final authService = AuthService();
    final token = await authService.getToken();

    if (token == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("Utilisateur non authentifié.")),
      );
      setState(() {
        _isSubmitting = false;
      });
      return;
    }

    final response = await http.post(
      Uri.parse('http://localhost:8000/api/deck/${widget.deck.id}/flashcard'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'question': _question,
        'answer': _answer,
      }),
    );

    setState(() {
      _isSubmitting = false;
    });

    if (response.statusCode == 201) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("Flashcard créée avec succès.")),
      );
      _formKey.currentState?.reset(); // On vide le formulaire
      setState(() {
        _question = '';
        _answer = '';
      });
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text("Erreur: ${response.statusCode}")),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Créer une flashcard'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: _isSubmitting
            ? const Center(child: CircularProgressIndicator())
            : Form(
                key: _formKey,
                child: Column(
                  children: [
                    TextFormField(
                      decoration: const InputDecoration(labelText: 'Question'),
                      maxLines: 3,
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return 'Veuillez entrer une question.';
                        }
                        return null;
                      },
                      onSaved: (value) => _question = value ?? '',
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      decoration: const InputDecoration(labelText: 'Réponse'),
                      maxLines: 5,
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return 'Veuillez entrer une réponse.';
                        }
                        return null;
                      },
                      onSaved: (value) => _answer = value ?? '',
                    ),
                    const SizedBox(height: 24),
                    ElevatedButton(
                      onPressed: _submitForm,
                      child: const Text('Créer la flashcard'),
                    ),
                  ],
                ),
              ),
      ),
    );
  }
}
