import 'package:flutter/material.dart';
import 'package:flame/game.dart';
import '../game/garden_game.dart';
import '../services/game_service.dart';
import '../widgets/inventory_panel.dart';

class GameScreen extends StatefulWidget {
  const GameScreen({Key? key}) : super(key: key);

  @override
  State<GameScreen> createState() => _GameScreenState();
}

class _GameScreenState extends State<GameScreen> {
  final GardenGame _gardenGame = GardenGame();
  final GameService _gameService = GameService(baseUrl: 'http://localhost:8000');

  bool _showInventory = false;
  Map<String, int> _inventory = {};
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadInventory();
  }

  Future<void> _loadInventory() async {
    try {
      final inv = await _gameService.fetchInventory();
      setState(() {
        _inventory = inv;
        _loading = false;
      });
      _gardenGame.loadInventory(inv); // Transfert vers GardenGame
    } catch (e) {
      debugPrint('❌ Erreur chargement inventaire: $e');
      setState(() => _loading = false);
    }
  }

  void _toggleInventory() {
    setState(() {
      _showInventory = !_showInventory;
    });
  }

  void _handlePlantSelect(String plantType) {
    _gardenGame.selectPlant(plantType);
    _toggleInventory();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          if (!_loading)
            GameWidget(game: _gardenGame)
          else
            const Center(child: CircularProgressIndicator()),

          if (!_loading)
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
              inventory: _inventory,
              onClose: _toggleInventory,
              onPlantSelect: _handlePlantSelect,
            ),
        ],
      ),
    );
  }
}
