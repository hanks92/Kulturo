import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

import '../services/auth_service.dart'; // ✅ Import pour récupérer le token

const String baseUrl = 'http://localhost:8000';

class RevisionPage extends StatefulWidget {
  final int revisionId;

  const RevisionPage({Key? key, required this.revisionId}) : super(key: key);

  @override
  State<RevisionPage> createState() => _RevisionPageState();
}

class _RevisionPageState extends State<RevisionPage> {
  late Future<RevisionSession> session;
  bool showAnswer = false;

  @override
  void initState() {
    super.initState();
    session = fetchRevisionSession(widget.revisionId);
  }

  Future<RevisionSession> fetchRevisionSession(int id) async {
    final authService = AuthService();
    final token = await authService.getToken(); // ✅ Récupère le token

    final response = await http.get(
      Uri.parse('$baseUrl/api/review/session/$id'),
      headers: {
        'Authorization': 'Bearer $token', // ✅ Token dynamique
        'Accept': 'application/json'
      },
    );

    if (response.statusCode == 200) {
      return RevisionSession.fromJson(jsonDecode(response.body));
    } else {
      throw Exception('Erreur de chargement de la session de révision');
    }
  }

  Future<void> submitResponse(int rating) async {
    final authService = AuthService();
    final token = await authService.getToken(); // ✅ Récupère le token

    final response = await http.post(
      Uri.parse('$baseUrl/api/review/submit/${widget.revisionId}'),
      headers: {
        'Authorization': 'Bearer $token', // ✅ Token dynamique
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: {'response': rating.toString()},
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      final nextId = data['nextRevisionId'];

      if (nextId != null) {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (context) => RevisionPage(revisionId: nextId),
          ),
        );
      } else {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (_) => const FinishedPage()),
        );
      }
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("Erreur lors de l'envoi")),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<RevisionSession>(
      future: session,
      builder: (context, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return const Scaffold(body: Center(child: CircularProgressIndicator()));
        }

        if (snapshot.hasError) {
          return Scaffold(
            body: Center(
              child: Text("Erreur : ${snapshot.error}"),
            ),
          );
        }


        final data = snapshot.data!;
        return Scaffold(
          appBar: AppBar(title: const Text("Révision")),
          body: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              children: [
                const Text("Carte du deck :", style: TextStyle(fontWeight: FontWeight.bold)),
                const SizedBox(height: 10),
                Text(data.flashcard.question),
                const SizedBox(height: 20),
                if (showAnswer)
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text("Réponse :", style: TextStyle(fontWeight: FontWeight.bold)),
                      const SizedBox(height: 10),
                      Text(data.flashcard.answer),
                      const SizedBox(height: 20),
                      ...[1, 2, 3, 4].map((rating) {
                        final labels = ["À revoir", "Difficile", "Correct", "Facile"];
                        final colors = [Colors.red, Colors.orange, Colors.blue, Colors.green];
                        return Container(
                          margin: const EdgeInsets.symmetric(vertical: 5),
                          width: double.infinity,
                          child: ElevatedButton(
                            style: ElevatedButton.styleFrom(backgroundColor: colors[rating - 1]),
                            onPressed: () => submitResponse(rating),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(labels[rating - 1], style: const TextStyle(color: Colors.white)),
                                Text(
                                  "(Prochaine : ${data.predictedDueDates[rating] ?? 'N/A'})",
                                  style: const TextStyle(color: Colors.white70, fontSize: 12),
                                ),
                              ],
                            ),
                          ),
                        );
                      }).toList(),
                    ],
                  )
                else
                  ElevatedButton(
                    onPressed: () => setState(() => showAnswer = true),
                    child: const Text("Afficher la réponse"),
                  ),
              ],
            ),
          ),
        );
      },
    );
  }
}

// Page de fin
class FinishedPage extends StatelessWidget {
  const FinishedPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Révision terminée")),
      body: const Center(
        child: Text("Toutes les cartes ont été révisées !"),
      ),
    );
  }
}

// Modèles
class Flashcard {
  final String question;
  final String answer;

  Flashcard({required this.question, required this.answer});

  factory Flashcard.fromJson(Map<String, dynamic> json) {
    return Flashcard(
      question: json['question'],
      answer: json['answer'],
    );
  }
}

class RevisionSession {
  final Flashcard flashcard;
  final Map<int, String> predictedDueDates;

  RevisionSession({required this.flashcard, required this.predictedDueDates});

  factory RevisionSession.fromJson(Map<String, dynamic> json) {
    return RevisionSession(
      flashcard: Flashcard.fromJson(json['flashcard']),
      predictedDueDates: (json['predictedDueDates'] as Map<String, dynamic>).map(
        (key, value) => MapEntry(int.parse(key), value as String),
      ),

    );
  }
}
