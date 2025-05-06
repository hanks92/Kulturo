import 'package:flutter/material.dart';
import '../models/user.dart';
import '../utils/profile_image.dart';
import '../screens/deck_list_screen.dart'; // <-- Ajoute cette importation

class Sidebar extends StatefulWidget {
  final User user;

  const Sidebar({super.key, required this.user});

  @override
  State<Sidebar> createState() => _SidebarState();
}

class _SidebarState extends State<Sidebar> {
  int _selectedIndex = 0;

  late final List<BottomNavigationBarItem> _navBarItems;
  late final List<Widget> _pages;

  @override
  void initState() {
    super.initState();

    final imagePath =
        'assets/profile_pictures/${getProfileImage(widget.user.profileImage)}';

    _navBarItems = [
      const BottomNavigationBarItem(
        icon: Icon(Icons.home_outlined),
        activeIcon: Icon(Icons.home_rounded),
        label: 'Home',
      ),
      const BottomNavigationBarItem(
        icon: Icon(Icons.layers_outlined),
        activeIcon: Icon(Icons.layers_rounded),
        label: 'Decks',
      ),
      const BottomNavigationBarItem(
        icon: Icon(Icons.smart_toy_outlined),
        activeIcon: Icon(Icons.smart_toy_rounded),
        label: 'AI',
      ),
      const BottomNavigationBarItem(
        icon: Icon(Icons.emoji_events_outlined),
        activeIcon: Icon(Icons.emoji_events),
        label: 'Achievements',
      ),
      BottomNavigationBarItem(
        icon: CircleAvatar(
          radius: 12,
          backgroundImage: AssetImage(imagePath),
        ),
        activeIcon: CircleAvatar(
          radius: 14,
          backgroundImage: AssetImage(imagePath),
        ),
        label: 'Profile',
      ),
    ];

    _pages = [
      const Center(child: Text('Home Page')),
      const DeckListScreen(), // ✅ Ici tu remplaces par la vraie page
      const Center(child: Text('AI Page')),
      const Center(child: Text('Achievements Page')),
      const Center(child: Text('Profile Page')),
    ];
  }

  @override
  Widget build(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    final isSmallScreen = width < 600;
    final isLargeScreen = width > 800;

    return Scaffold(
      bottomNavigationBar: isSmallScreen
          ? BottomNavigationBar(
              items: _navBarItems,
              currentIndex: _selectedIndex,
              onTap: (index) => setState(() => _selectedIndex = index),
              type: BottomNavigationBarType.fixed,
            )
          : null,
      body: Row(
        children: [
          if (!isSmallScreen)
            NavigationRail(
              selectedIndex: _selectedIndex,
              onDestinationSelected: (index) =>
                  setState(() => _selectedIndex = index),
              extended: isLargeScreen,
              destinations: _navBarItems
                  .map((item) => NavigationRailDestination(
                        icon: item.icon,
                        selectedIcon: item.activeIcon,
                        label: Text(item.label ?? ''),
                      ))
                  .toList(),
            ),
          const VerticalDivider(thickness: 1, width: 1),
          Expanded(child: _pages[_selectedIndex]),
        ],
      ),
    );
  }
}
