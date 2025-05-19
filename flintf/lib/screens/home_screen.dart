import 'package:flutter/material.dart';
import 'game_screen.dart'; // ✅ chemin relatif direct

class HomeScreen extends StatelessWidget {
  const HomeScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return const GameScreen(); // ✅ on peut utiliser const car GameScreen est const
  }
}
