import 'package:flutter/material.dart';
import '../models/achievement.dart';
import '../services/achievement_service.dart';

class AchievementListScreen extends StatefulWidget {
  const AchievementListScreen({Key? key}) : super(key: key);

  @override
  State<AchievementListScreen> createState() => _AchievementListScreenState();
}

class _AchievementListScreenState extends State<AchievementListScreen> {
  late Future<List<Achievement>> _achievementsFuture;

  @override
  void initState() {
    super.initState();
    _achievementsFuture = AchievementService().fetchAchievements();
  }

  void _showModal(BuildContext context, String description) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text("Description"),
        content: Text(description),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text("Fermer"),
          ),
        ],
      ),
    );
  }

  Widget _buildAchievementCard(Achievement ach) {
    final unlocked = ach.isUnlocked;
    return GestureDetector(
      onTap: () => _showModal(context, ach.description),
      child: Container(
        padding: const EdgeInsets.all(16),
        margin: const EdgeInsets.symmetric(vertical: 8, horizontal: 16),
        decoration: BoxDecoration(
          color: unlocked ? Colors.green[100] : Colors.grey[300],
          border: Border.all(
            color: unlocked ? Colors.green : Colors.grey,
            width: 2,
          ),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              ach.name,
              style: TextStyle(
                fontWeight: FontWeight.bold,
                color: unlocked ? Colors.black : Colors.grey[600],
              ),
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: ach.rewards.entries.map((e) {
                return Text(
                  "${e.key}: ${e.value}",
                  style: TextStyle(
                    fontSize: 12,
                    color: unlocked ? Colors.black87 : Colors.grey[600],
                  ),
                );
              }).toList(),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Mes Succès")),
      body: FutureBuilder<List<Achievement>>(
        future: _achievementsFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          } else if (snapshot.hasError) {
            return Center(child: Text('Erreur: ${snapshot.error}'));
          } else if (!snapshot.hasData || snapshot.data!.isEmpty) {
            return const Center(child: Text("Aucun succès trouvé."));
          }

          final achievements = snapshot.data!;
          return ListView.builder(
            itemCount: achievements.length,
            itemBuilder: (context, index) => _buildAchievementCard(achievements[index]),
          );
        },
      ),
    );
  }
}
