import 'package:flutter/material.dart';
import 'package:flame/game.dart';
import '../game/garden_game.dart';
import '../widgets/inventory_panel.dart'; // ✅ Import correct

class GameScreen extends StatefulWidget {
  const GameScreen({Key? key}) : super(key: key);

  @override
  State<GameScreen> createState() => _GameScreenState();
}

class _GameScreenState extends State<GameScreen> {
  bool _showInventory = false;

  void _toggleInventory() {
    setState(() {
      _showInventory = !_showInventory;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          GameWidget(game: GardenGame()),
          Positioned(
            top: 40,
            right: 20,
            child: ElevatedButton(
              onPressed: _toggleInventory,
              child: const Text("Inventaire"),
            ),
          ),
          if (_showInventory)
            InventoryPanel(
              onClose: _toggleInventory,
            ),
        ],
      ),
    );
  }
}
