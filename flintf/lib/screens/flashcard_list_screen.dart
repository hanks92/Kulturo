import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

import '../models/flashcard.dart';
import '../services/auth_service.dart';

class FlashcardListScreen extends StatefulWidget {
  final int deckId;

  const FlashcardListScreen({Key? key, required this.deckId}) : super(key: key);

  @override
  State<FlashcardListScreen> createState() => _FlashcardListScreenState();
}

class _FlashcardListScreenState extends State<FlashcardListScreen> {
  List<Flashcard> flashcards = [];
  bool isLoading = true;

  @override
  void initState() {
    super.initState();
    fetchFlashcards();
  }

  Future<void> fetchFlashcards() async {
    final token = await AuthService().getToken();
    final url = Uri.parse('http://localhost:8000/api/deck/${widget.deckId}/flashcards');

    final response = await http.get(
      url,
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final List<dynamic> data = json.decode(response.body);
      setState(() {
        flashcards = data.map((item) => Flashcard.fromJson(item)).toList();
        isLoading = false;
      });
    } else {
      setState(() => isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Erreur: ${response.statusCode}')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Flashcards')),
      body: isLoading
          ? const Center(child: CircularProgressIndicator())
          : flashcards.isEmpty
              ? const Center(child: Text('Aucune flashcard trouvée.'))
              : ListView.builder(
                  itemCount: flashcards.length,
                  itemBuilder: (context, index) {
                    final card = flashcards[index];
                    return Card(
                      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      child: ListTile(
                        title: Text(card.question),
                        subtitle: Text(card.answer),
                      ),
                    );
                  },
                ),
    );
  }
}
