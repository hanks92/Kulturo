import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

import 'package:flutter_html/flutter_html.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:flutter_html/flutter_html.dart' as html;

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

  Widget buildDeltaView(String content) {
    try {
      final parsed = jsonDecode(content);

      if (parsed is List) {
        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: parsed.map<Widget>((op) {
            final insert = op['insert'];
            if (insert is String) {
              return Padding(
                padding: const EdgeInsets.symmetric(vertical: 4),
                child: Text(insert.trim(), style: const TextStyle(fontSize: 16)),
              );
            } else if (insert is Map && insert.containsKey('image')) {
              String imageUrl = insert['image'];

              if (imageUrl.contains('/public/uploads/')) {
                imageUrl = imageUrl.split('/public').last;
              }

              if (imageUrl.startsWith('/home/') || imageUrl.startsWith('file:/')) {
                return const Text('⚠️ Image non prise en charge (chemin local)');
              }

              final fullUrl = imageUrl.startsWith('/')
                  ? 'http://localhost:8000$imageUrl'
                  : imageUrl;

              return Padding(
                padding: const EdgeInsets.symmetric(vertical: 8),
                child: fullUrl.endsWith('.svg')
                    ? SvgPicture.network(
                        fullUrl,
                        placeholderBuilder: (_) => const CircularProgressIndicator(),
                        height: 100,
                      )
                    : Image.network(
                        fullUrl,
                        errorBuilder: (context, error, stackTrace) =>
                            const Text('❌ Erreur chargement image'),
                      ),
              );
            } else {
              return const SizedBox.shrink();
            }
          }).toList(),
        );
      } else {
        return _buildHtml(content);
      }
    } catch (e) {
      if (content.contains('<img') || content.contains('<br') || content.contains('</')) {
        return _buildHtml(content);
      } else {
        return Text(content.trim(), style: const TextStyle(fontSize: 16));
      }
    }
  }

  Widget _buildHtml(String htmlContent) {
    return Html(
      data: htmlContent,
      style: {
        "img": Style(
          margin: Margins.symmetric(vertical: 10),
        ),
      },
      extensions: [
        TagExtension(
          tagsToExtend: {"img"},
          builder: (context) {
            final src = context.attributes['src'] ?? '';
            return Padding(
              padding: const EdgeInsets.symmetric(vertical: 8),
              child: src.endsWith('.svg')
                  ? SvgPicture.network(
                      src,
                      placeholderBuilder: (_) => const CircularProgressIndicator(),
                      height: 100,
                    )
                  : Image.network(
                      src,
                      errorBuilder: (context, error, stackTrace) =>
                          const Text('❌ Erreur chargement image'),
                    ),
            );
          },
        ),
      ],
    );
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
                      child: Padding(
                        padding: const EdgeInsets.all(12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text("Question :", style: TextStyle(fontWeight: FontWeight.bold)),
                            buildDeltaView(card.question),
                            const SizedBox(height: 12),
                            const Text("Réponse :", style: TextStyle(fontWeight: FontWeight.bold)),
                            buildDeltaView(card.answer),
                          ],
                        ),
                      ),
                    );
                  },
                ),
    );
  }
}
