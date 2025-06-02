class PlantInventoryItem {
  final String plantType;
  final int quantity;

  PlantInventoryItem({required this.plantType, required this.quantity});

  factory PlantInventoryItem.fromJson(Map<String, dynamic> json) {
    return PlantInventoryItem(
      plantType: json['type'] ?? '',
      quantity: json['quantity'] ?? 0,
    );
  }
}
