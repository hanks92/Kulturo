import 'package:flutter/material.dart';

class InventoryPanel extends StatelessWidget {
  final VoidCallback onClose;
  final void Function(String plantType) onPlantSelect;
  final Map<String, int> inventory;

  const InventoryPanel({
    super.key,
    required this.onClose,
    required this.onPlantSelect,
    required this.inventory,
  });

  @override
  Widget build(BuildContext context) {
    return Positioned.fill(
      child: Material(
        color: Colors.black.withOpacity(0.7),
        child: Column(
          children: [
            const SizedBox(height: 40),
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                IconButton(
                  icon: const Icon(Icons.close, color: Colors.white),
                  onPressed: onClose,
                ),
              ],
            ),
            const Text(
              '🌱 Inventaire de plantes',
              style: TextStyle(fontSize: 22, color: Colors.white),
            ),
            const SizedBox(height: 10),
            Expanded(
              child: inventory.isEmpty
                  ? const Center(
                      child: Text(
                        'Aucune plante disponible',
                        style: TextStyle(color: Colors.white),
                      ),
                    )
                  : ListView.builder(
                      padding: const EdgeInsets.all(20),
                      itemCount: inventory.length,
                      itemBuilder: (context, index) {
                        final type = inventory.keys.elementAt(index);
                        final qty = inventory[type]!;
                        return Card(
                          color: Colors.green[800],
                          child: ListTile(
                            title: Text(
                              type,
                              style: const TextStyle(color: Colors.white),
                            ),
                            trailing: Text(
                              'x$qty',
                              style: const TextStyle(color: Colors.white),
                            ),
                            onTap: () => onPlantSelect(type),
                          ),
                        );
                      },
                    ),
            ),
          ],
        ),
      ),
    );
  }
}
