import 'dart:convert';
import 'package:http/http.dart' as http;
import '../services/auth_service.dart';

const String baseUrl = 'http://localhost:8000'; // à adapter si besoin

class AIService {
  Future<Map<String, dynamic>> generateDeckViaAI({
    required String title,
    required String prompt,
    String? resources,
  }) async {
    final authService = AuthService();
    final token = await authService.getToken();

    if (token == null) {
      throw Exception("Utilisateur non authentifié");
    }

    final response = await http.post(
      Uri.parse('$baseUrl/api/ai/generate'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'title': title,
        'prompt': prompt,
        'resources': resources ?? '',
      }),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception("Erreur IA (${response.statusCode}) : ${response.body}");
    }
  }
}
