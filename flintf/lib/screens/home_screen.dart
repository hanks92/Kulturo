import 'package:flutter/material.dart';
import '../layouts/authenticated_layout.dart';

class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return AuthenticatedLayout( // 👈 const retiré ici
      child: Scaffold(
        appBar: AppBar(title: const Text('Accueil')),
        body: const Center(
          child: Text(
            'Bienvenue sur Kulturo !',
            style: TextStyle(fontSize: 20),
          ),
        ),
      ),
    );
  }
}
