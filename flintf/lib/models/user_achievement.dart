import 'achievement.dart';

class UserAchievement {
  final Achievement achievement;
  final bool unlocked;
  final String? achievedAt;

  UserAchievement({
    required this.achievement,
    required this.unlocked,
    this.achievedAt,
  });

  factory UserAchievement.fromJson(Map<String, dynamic> json) {
    return UserAchievement(
      achievement: Achievement.fromJson(json),
      unlocked: json['unlocked'] ?? false,
      achievedAt: json['achievedAt'],
    );
  }
}
