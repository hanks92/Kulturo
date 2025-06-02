import 'dart:convert';
import 'package:http/http.dart' as http;
import '../screens/game_screen.dart';


class GameService {
  final String baseUrl;

  GameService({required this.baseUrl});

  Future<Map<String, int>> fetchInventory() async {
    final response = await http.get(Uri.parse('$baseUrl/api/game/inventory'));

    if (response.statusCode == 200) {
      final data = json.decode(response.body) as Map<String, dynamic>;
      return data.map((key, value) => MapEntry(key, value as int));
    } else {
      throw Exception('Erreur chargement inventaire');
    }
  }

  Future<List<Map<String, dynamic>>> loadGarden() async {
    final response = await http.get(Uri.parse('$baseUrl/api/game/load-garden'));

    if (response.statusCode == 200) {
      return List<Map<String, dynamic>>.from(json.decode(response.body));
    } else {
      throw Exception('Erreur chargement jardin');
    }
  }

  Future<void> updateWater(int water) async {
    await http.post(
      Uri.parse('$baseUrl/api/game/update-water'),
      headers: {'Content-Type': 'application/json'},
      body: json.encode({'water': water}),
    );
  }

  Future<void> updatePlants(List<Map<String, dynamic>> plants) async {
    await http.post(
      Uri.parse('$baseUrl/api/game/update-plants'),
      headers: {'Content-Type': 'application/json'},
      body: json.encode({'plants': plants}),
    );
  }
}
