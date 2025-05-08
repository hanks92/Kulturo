import 'package:flutter/material.dart';

class ReviewFinishedScreen extends StatelessWidget {
  final int cardsReviewed;
  final bool flameLit;

  const ReviewFinishedScreen({
    super.key,
    required this.cardsReviewed,
    this.flameLit = false,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Révision terminée")),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Text(
                "🎉 Bravo !",
                style: TextStyle(fontSize: 32, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 16),
              const Text(
                "Toutes les cartes du jour sont révisées !",
                style: TextStyle(fontSize: 18),
              ),
              const SizedBox(height: 32),

              // 🔧 Sécurité sur les valeurs
              Text("⭐ XP gagnés : +${cardsReviewed.toString()}"),
              Text("💧 Eau gagnée : +${cardsReviewed.toString()}"),

              const SizedBox(height: 32),
              if (flameLit)
                Image.asset("assets/flame.gif", height: 80),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: () => Navigator.pop(context),
                child: const Text("Retour aux decks"),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
