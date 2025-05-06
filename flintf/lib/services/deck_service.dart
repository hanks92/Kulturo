import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/deck.dart';
import 'storage_service.dart';

class DeckService {
  final _storage = getTokenStorage();
  final String _apiUrl = 'http://localhost:8000/api/decks'; // ⬅️ ta route API Symfony

  Future<List<Deck>> fetchDecks() async {
    final token = await _storage.read();
    final response = await http.get(
      Uri.parse(_apiUrl),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final List<dynamic> jsonData = jsonDecode(response.body);
      return jsonData.map((d) => Deck.fromJson(d)).toList();
    } else {
      throw Exception("Erreur lors du chargement des decks");
    }
  }
}
