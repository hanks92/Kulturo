import 'dart:convert';
import 'package:http/http.dart' as http;
import '../services/auth_service.dart';

class GameService {
  final String baseUrl;
  final AuthService _authService = AuthService();

  GameService({required this.baseUrl});

  Future<Map<String, int>> fetchInventory() async {
    final token = await _authService.getToken();
    if (token == null) throw Exception('❌ Token manquant');

    final response = await http.get(
      Uri.parse('$baseUrl/api/game/inventory'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );

    // 🪵 Debug pour comprendre ce que retourne l’API
    print('🔎 Status: ${response.statusCode}');
    print('📦 Body: ${response.body}');

    if (response.statusCode == 200) {
      final data = json.decode(response.body) as Map<String, dynamic>;
      return data.map((key, value) => MapEntry(key, value as int));
    } else {
      throw Exception('Erreur chargement inventaire');
    }
  }

  Future<List<Map<String, dynamic>>> loadGarden() async {
    final token = await _authService.getToken();
    if (token == null) throw Exception('❌ Token manquant');

    final response = await http.get(
      Uri.parse('$baseUrl/api/game/load-garden'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      return List<Map<String, dynamic>>.from(json.decode(response.body));
    } else {
      throw Exception('Erreur chargement jardin');
    }
  }

  Future<void> updateWater(int water) async {
    final token = await _authService.getToken();
    if (token == null) throw Exception('❌ Token manquant');

    await http.post(
      Uri.parse('$baseUrl/api/game/update-water'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: json.encode({'water': water}),
    );
  }

  Future<void> updatePlants(List<Map<String, dynamic>> plants) async {
    final token = await _authService.getToken();
    if (token == null) throw Exception('❌ Token manquant');

    await http.post(
      Uri.parse('$baseUrl/api/game/update-plants'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: json.encode({'plants': plants}),
    );
  }
}
