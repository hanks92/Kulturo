import 'dart:convert';
import 'package:http/http.dart' as http;
import 'storage_service.dart';

class AuthService {
  final _storage = getTokenStorage();
  final String _apiUrl = 'http://localhost:8000/api/login_check';

  Future<bool> login(String email, String password) async {
    final response = await http.post(
      Uri.parse(_apiUrl),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({'username': email, 'password': password}),
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      await _storage.save(data['token']);
      return true;
    }
    return false;
  }

  Future<void> logout() async {
    await _storage.delete();
  }

  Future<String?> getToken() async {
    return await _storage.read();
  }
}
