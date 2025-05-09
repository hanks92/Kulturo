import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/achievement.dart';
import 'auth_service.dart';

const String baseUrl = 'http://localhost:8000'; // ✅ adapte si besoin

class AchievementService {
  Future<List<Achievement>> fetchAchievements() async {
    final authService = AuthService();
    final token = await authService.getToken();

    if (token == null) {
      throw Exception("Utilisateur non authentifié.");
    }

    final response = await http.get(
      Uri.parse('$baseUrl/api/achievements'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final List<dynamic> data = json.decode(response.body);
      return data.map((item) => Achievement.fromJson(item)).toList();
    } else {
      throw Exception('Erreur lors de la récupération des achievements (${response.statusCode})');
    }
  }
}
