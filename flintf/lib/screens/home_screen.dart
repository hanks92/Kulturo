import 'package:flutter/material.dart';
import '../layouts/authenticated_layout.dart';

class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return const AuthenticatedLayout();
  }
}
