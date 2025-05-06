class Deck {
  final int id;
  final String title;

  Deck({required this.id, required this.title});

  factory Deck.fromJson(Map<String, dynamic> json) {
    return Deck(
      id: json['id'],
      title: json['title'],
    );
  }
}
