import 'dart:convert';
import 'package:http/http.dart' as http;
import 'storage_service.dart';
import '../models/user.dart'; // Vérifie que ce chemin est correct

class AuthService {
  final _storage = getTokenStorage();
  final String _apiLoginUrl = 'http://localhost:8000/api/login_check';
  final String _apiMeUrl = 'http://localhost:8000/api/me';
  final String _apiRegisterUrl = 'http://localhost:8000/api/register';

  /// Authentifie l'utilisateur, sauvegarde le token JWT, puis vérifie qu'il est utilisable.
  Future<User?> loginAndFetchUser(String email, String password) async {
    final loginSuccess = await login(email, password);

    if (!loginSuccess) {
      print('⛔ Échec de la connexion');
      return null;
    }

    final token = await _storage.read();
    print('✅ Token récupéré: $token');

    final user = await getUser();
    if (user == null) {
      print('⛔ Erreur lors de la récupération du profil utilisateur.');
    }

    return user;
  }

  /// Authentifie l'utilisateur et sauvegarde le token JWT
  Future<bool> login(String email, String password) async {
    final response = await http.post(
      Uri.parse(_apiLoginUrl),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({'username': email, 'password': password}),
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      await _storage.save(data['token']);
      return true;
    }

    print('⛔ Requête de login échouée: ${response.statusCode}');
    return false;
  }

  /// Inscrit un nouvel utilisateur via l’API
  Future<bool> register({
    required String email,
    required String username,
    required String password,
    String? profileImage,
  }) async {
    final response = await http.post(
      Uri.parse(_apiRegisterUrl),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'email': email,
        'username': username,
        'password': password,
        'profileImage': profileImage,
      }),
    );

    if (response.statusCode == 201) {
      print('✅ Utilisateur inscrit avec succès');
      return true;
    }

    print('⛔ Échec de l’inscription: ${response.statusCode}');
    print(response.body);
    return false;
  }

  /// Supprime le token JWT sauvegardé (logout).
  Future<void> logout() async {
    await _storage.delete();
    print('ℹ️ Token supprimé');
  }

  /// Lit le token JWT depuis le stockage sécurisé.
  Future<String?> getToken() async {
    return await _storage.read();
  }

  /// Récupère l'utilisateur actuellement connecté via l'endpoint /api/me.
  Future<User?> getUser() async {
    final token = await getToken();
    if (token == null) {
      print('⛔ Aucun token trouvé');
      return null;
    }

    final response = await http.get(
      Uri.parse(_apiMeUrl),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return User.fromJson(data);
    }

    print('⛔ Erreur ${response.statusCode} lors de l’appel à /api/me');
    return null;
  }
}
