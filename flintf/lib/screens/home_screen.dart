import 'package:flutter/material.dart';
// Placeholder home page displayed after login.
// The full game remains available through the '/game' route.

class HomeScreen extends StatelessWidget {
  const HomeScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    // Display an empty scaffold for now while the game is hidden.
    return const Scaffold(
      body: SizedBox.expand(),
    );
  }
}