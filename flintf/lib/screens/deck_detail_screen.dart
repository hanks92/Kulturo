import 'package:flutter/material.dart';
import 'package:getwidget/getwidget.dart';
import '../models/deck.dart';

class DeckDetailScreen extends StatelessWidget {
  final Deck deck;

  const DeckDetailScreen({super.key, required this.deck});

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
                    onPressed: () {
                      // Navigue vers la revue
                      // Navigator.push(...);
                    },
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
                      // Navigator.push(...);
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
                      // Navigator.push(...);
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
