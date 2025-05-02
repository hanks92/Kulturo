import 'package:flutter/material.dart';
import '../widgets/sidebar.dart';

class AuthenticatedLayout extends StatelessWidget {
  final Widget child;

  const AuthenticatedLayout({super.key, required this.child});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Row(
        children: [
          const Sidebar(), // notre sidebar à gauche
          Expanded(
            child: Container(
              color: Theme.of(context).scaffoldBackgroundColor,
              child: child,
            ),
          ),
        ],
      ),
    );
  }
}
