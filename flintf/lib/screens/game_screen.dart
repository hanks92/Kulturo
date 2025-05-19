import 'package:flutter/material.dart';
import 'package:flame/game.dart';
import '../game/garden_game.dart'; // correct si garden_game.dart est dans lib/game/

class GameScreen extends StatelessWidget {
  const GameScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: GameWidget(game: GardenGame()), // sans const
    );
  }
}
