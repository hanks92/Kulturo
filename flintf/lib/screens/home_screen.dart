import 'package:flutter/material.dart';
import '../models/user.dart';
import '../widgets/sidebar.dart';

class HomeScreen extends StatelessWidget {
  final User user;

  const HomeScreen({super.key, required this.user});

  @override
  Widget build(BuildContext context) {
    return Sidebar(user: user);
  }
}
