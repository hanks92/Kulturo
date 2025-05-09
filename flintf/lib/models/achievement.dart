class Achievement {
  final String code;
  final String name;
  final String description;
  final Map<String, dynamic> rewards;
  final bool isPremium;
  final bool isUnlocked;

  Achievement({
    required this.code,
    required this.name,
    required this.description,
    required this.rewards,
    required this.isPremium,
    this.isUnlocked = false,
  });

  factory Achievement.fromJson(Map<String, dynamic> json, {Set<String> unlocked = const {}}) {
    return Achievement(
      code: json['code'],
      name: json['name'],
      description: json['description'],
      rewards: Map<String, dynamic>.from(json['rewards']),
      isPremium: json['isPremium'],
      isUnlocked: unlocked.contains(json['code']),
    );
  }
}
