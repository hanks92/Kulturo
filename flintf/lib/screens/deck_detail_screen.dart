import 'package:flutter/material.dart';
import 'package:getwidget/getwidget.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

import '../models/deck.dart';
import '../screens/review_screen.dart';
import '../services/auth_service.dart';

const String baseUrl = 'http://localhost:8000'; // ✅ Centralisation de l'URL

class DeckDetailScreen extends StatelessWidget {
  final Deck deck;

  const DeckDetailScreen({super.key, required this.deck});

  Future<void> startReview(BuildContext context, int deckId) async {
    try {
      final authService = AuthService();
      final token = await authService.getToken();

      if (token == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text("Utilisateur non authentifié.")),
        );
        return;
      }

      final response = await http.get(
        Uri.parse('$baseUrl/api/review/start/$deckId'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final revisionId = data['firstRevisionId'];

        if (revisionId != null) {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => RevisionPage(revisionId: revisionId),
            ),
          );
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text("Aucune carte à réviser pour aujourd'hui.")),
          );
        }
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text("Erreur serveur (${response.statusCode})")),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text("Erreur: $e")),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(deck.title)),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.start,
            children: [
              Text(
                'Manage your deck: ${deck.title}',
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 26,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF2E5077),
                ),
              ),
              const SizedBox(height: 40),
              Column(
                children: [
                  GFButton(
                    onPressed: () => startReview(context, deck.id),
                    text: "Start Review",
                    color: const Color(0xFF2E5077),
                    shape: GFButtonShape.pills,
                    fullWidthButton: true,
                    size: GFSize.LARGE,
                  ),
                  const SizedBox(height: 16),
                  GFButton(
                    onPressed: () {
                      // Navigue vers création de flashcard
                    },
                    text: "Create Flashcard",
                    color: const Color(0xFF4DA1A9),
                    shape: GFButtonShape.pills,
                    fullWidthButton: true,
                    size: GFSize.LARGE,
                  ),
                  const SizedBox(height: 16),
                  GFButton(
                    onPressed: () {
                      // Navigue vers la liste des flashcards
                    },
                    text: "View All Flashcards",
                    color: const Color(0xFF79D7BE),
                    shape: GFButtonShape.pills,
                    fullWidthButton: true,
                    size: GFSize.LARGE,
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
