import 'package:flutter/material.dart';
import 'package:getwidget/getwidget.dart';
import '../models/deck.dart';
import '../services/deck_service.dart';
import 'deck_detail_screen.dart'; // Assure-toi que ce fichier existe

class DeckListScreen extends StatefulWidget {
  const DeckListScreen({super.key});

  @override
  State<DeckListScreen> createState() => _DeckListScreenState();
}

class _DeckListScreenState extends State<DeckListScreen> {
  final DeckService _deckService = DeckService();
  late Future<List<Deck>> _decksFuture;

  @override
  void initState() {
    super.initState();
    _decksFuture = _deckService.fetchDecks();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Mes Decks")),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: FutureBuilder<List<Deck>>(
          future: _decksFuture,
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            } else if (snapshot.hasError) {
              return Center(child: Text('Erreur: ${snapshot.error}'));
            } else if (!snapshot.hasData || snapshot.data!.isEmpty) {
              return const Center(child: Text('Aucun deck trouvé.'));
            }

            final decks = snapshot.data!;
            return Column(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                const Text(
                  "My decks",
                  style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 20),
                Wrap(
                  spacing: 10,
                  children: [
                    GFButton(
                      onPressed: () {
                        // Navigue vers création classique
                      },
                      text: "Créer un deck",
                      color: const Color(0xFF4DA1A9),
                      shape: GFButtonShape.pills,
                    ),
                    GFButton(
                      onPressed: () {
                        // Navigue vers création IA
                      },
                      text: "Créer avec IA",
                      color: const Color(0xFF79D7BE),
                      shape: GFButtonShape.pills,
                    ),
                  ],
                ),
                const Divider(height: 40, thickness: 2),
                Expanded(
                  child: ListView.builder(
                    itemCount: decks.length,
                    itemBuilder: (context, index) {
                      final deck = decks[index];
                      return Padding(
                        padding: const EdgeInsets.symmetric(vertical: 8.0),
                        child: GFButton(
                          onPressed: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => DeckDetailScreen(deck: deck),
                              ),
                            );
                          },
                          text: deck.title,
                          color: Colors.white,
                          textStyle: const TextStyle(color: Colors.black87),
                          shape: GFButtonShape.pills,
                          blockButton: true,
                          fullWidthButton: true,
                        ),
                      );
                    },
                  ),
                )
              ],
            );
          },
        ),
      ),
    );
  }
}
