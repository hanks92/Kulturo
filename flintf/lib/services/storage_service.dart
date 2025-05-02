import 'dart:io';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';

abstract class TokenStorage {
  Future<void> save(String token);
  Future<String?> read();
  Future<void> delete();
}

class SecureStorage implements TokenStorage {
  final _storage = const FlutterSecureStorage();

  @override
  Future<void> save(String token) async {
    await _storage.write(key: 'jwt', value: token);
  }

  @override
  Future<String?> read() async {
    return await _storage.read(key: 'jwt');
  }

  @override
  Future<void> delete() async {
    await _storage.delete(key: 'jwt');
  }
}

class SharedPrefsStorage implements TokenStorage {
  @override
  Future<void> save(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('jwt', token);
  }

  @override
  Future<String?> read() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('jwt');
  }

  @override
  Future<void> delete() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('jwt');
  }
}

TokenStorage getTokenStorage() {
  // Si tu es en dev sur Linux desktop, utilise SharedPreferences
  if (Platform.isLinux) {
    return SharedPrefsStorage();
  }
  return SecureStorage();
}
