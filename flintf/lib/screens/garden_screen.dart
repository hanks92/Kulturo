import 'package:flutter/material.dart';
import 'package:flame/game.dart';
import '../game/garden_game.dart';
import '../models/user.dart';

class GardenScreen extends StatelessWidget {
  final User user;

  const GardenScreen({super.key, required this.user});

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Colors.white, // pour éviter un fond noir
      child: GameWidget(game: GardenGame()),
    );
  }
}
