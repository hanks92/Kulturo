import 'package:flutter/material.dart';

class Sidebar extends StatelessWidget {
  const Sidebar({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 240,
      height: double.infinity,
      color: const Color(0xFFf8f9fa), // gris clair
      child: Column(
        children: [
          const SizedBox(height: 20),
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: Image.asset(
              'assets/logos/logo-2.png', // ✅ logo mis à jour
              height: 60,
              fit: BoxFit.contain,
            ),
          ),
          const Divider(),

          _sidebarItem(
            icon: Icons.home,
            label: 'Home',
            route: '/home',
            context: context,
          ),
          _sidebarItem(
            icon: Icons.layers,
            label: 'Decks',
            route: '/decks',
            context: context,
          ),
          _sidebarItem(
            icon: Icons.smart_toy,
            label: 'AI',
            route: '/ai',
            context: context,
          ),
          _sidebarItem(
            icon: Icons.emoji_events,
            label: 'Achievements',
            route: '/achievements',
            context: context,
          ),

          const Divider(),

          // Section profil
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12.0),
            child: ListTile(
              leading: const CircleAvatar(
                backgroundImage: AssetImage('assets/avatars/avataaars.png'), // ✅ avatar mis à jour
              ),
              title: const Text('Profile'),
              onTap: () => Navigator.pushNamed(context, '/profile'),
            ),
          ),

          _sidebarItem(
            icon: Icons.settings,
            label: 'Settings',
            route: '/settings',
            context: context,
          ),

          const Spacer(),

          _sidebarItem(
            icon: Icons.logout,
            label: 'Logout',
            route: '/login',
            context: context,
          ),
        ],
      ),
    );
  }

  Widget _sidebarItem({
    required IconData icon,
    required String label,
    required String route,
    required BuildContext context,
  }) {
    return ListTile(
      leading: Icon(icon, size: 24, color: Colors.grey[700]),
      title: Text(label, style: const TextStyle(fontSize: 16)),
      onTap: () => Navigator.pushNamed(context, route),
      hoverColor: Colors.grey.shade300,
    );
  }
}
