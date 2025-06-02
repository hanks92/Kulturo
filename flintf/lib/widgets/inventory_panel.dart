import 'package:flutter/material.dart';

class InventoryPanel extends StatelessWidget {
  final VoidCallback onClose;

  const InventoryPanel({super.key, required this.onClose});

  @override
  Widget build(BuildContext context) {
    return Positioned.fill(
      child: Container(
        color: Colors.black54,
        child: Column(
          children: [
            Align(
              alignment: Alignment.topRight,
              child: IconButton(
                icon: const Icon(Icons.close, color: Colors.white),
                onPressed: onClose,
              ),
            ),
            const Text(
              'Inventaire',
              style: TextStyle(fontSize: 24, color: Colors.white),
            ),
            // Ici tu pourras afficher les items
            const Expanded(
              child: Center(
                child: Text(
                  'Liste des plantes à afficher ici',
                  style: TextStyle(color: Colors.white),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
