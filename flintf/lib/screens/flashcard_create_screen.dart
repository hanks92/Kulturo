import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_quill/flutter_quill.dart';
import 'package:flutter_quill_extensions/flutter_quill_extensions.dart';
import 'package:http/http.dart' as http;

import '../models/deck.dart';
import '../services/auth_service.dart';

class FlashcardCreateScreen extends StatefulWidget {
  final Deck deck;

  const FlashcardCreateScreen({Key? key, required this.deck}) : super(key: key);

  @override
  _FlashcardCreateScreenState createState() => _FlashcardCreateScreenState();
}

class _FlashcardCreateScreenState extends State<FlashcardCreateScreen> {
  final _formKey = GlobalKey<FormState>();
  late QuillController _questionController;
  late QuillController _answerController;
  final FocusNode _questionFocusNode = FocusNode();
  final FocusNode _answerFocusNode = FocusNode();
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    _questionController = QuillController.basic();
    _answerController = QuillController.basic();
  }

  @override
  void dispose() {
    _questionController.dispose();
    _answerController.dispose();
    _questionFocusNode.dispose();
    _answerFocusNode.dispose();
    super.dispose();
  }

  Future<void> _submitForm() async {
    setState(() => _isSubmitting = true);

    final authService = AuthService();
    final token = await authService.getToken();

    if (token == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("Utilisateur non authentifié.")),
      );
      setState(() => _isSubmitting = false);
      return;
    }

    final question = jsonEncode(_questionController.document.toDelta().toJson());
    final answer = jsonEncode(_answerController.document.toDelta().toJson());

    final response = await http.post(
      Uri.parse('http://localhost:8000/api/deck/${widget.deck.id}/flashcard'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({'question': question, 'answer': answer}),
    );

    setState(() => _isSubmitting = false);

    if (response.statusCode == 201) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("Flashcard créée avec succès.")),
      );
      setState(() {
        _questionController = QuillController.basic();
        _answerController = QuillController.basic();
      });
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text("Erreur: ${response.statusCode}")),
      );
    }
  }

  Widget _buildEditor(String label, QuillController controller, FocusNode focusNode) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontWeight: FontWeight.bold)),
        const SizedBox(height: 8),
        QuillSimpleToolbar(
          controller: controller,
          config: QuillSimpleToolbarConfig(
            embedButtons: FlutterQuillEmbeds.toolbarButtons(),
            showClipboardPaste: true,
            buttonOptions: QuillSimpleToolbarButtonOptions(
              base: QuillToolbarBaseButtonOptions(
                afterButtonPressed: () => focusNode.requestFocus(),
              ),
            ),
          ),
        ),
        const SizedBox(height: 8),
        Container(
          height: 150,
          decoration: BoxDecoration(border: Border.all(color: Colors.grey)),
          child: QuillEditor(
            controller: controller,
            focusNode: focusNode,
            scrollController: ScrollController(),
            config: QuillEditorConfig(
              placeholder: label,
              padding: const EdgeInsets.all(8),
              autoFocus: false,
              expands: false,
              embedBuilders: FlutterQuillEmbeds.editorBuilders(),
            ),
          ),
        ),
        const SizedBox(height: 24),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Créer une flashcard')),
      body: _isSubmitting
          ? const Center(child: CircularProgressIndicator())
          : Padding(
              padding: const EdgeInsets.all(16),
              child: Form(
                key: _formKey,
                child: SingleChildScrollView(
                  child: Column(
                    children: [
                      _buildEditor("Question", _questionController, _questionFocusNode),
                      _buildEditor("Réponse", _answerController, _answerFocusNode),
                      ElevatedButton(
                        onPressed: _submitForm,
                        child: const Text("Créer la flashcard"),
                      ),
                    ],
                  ),
                ),
              ),
            ),
    );
  }
}
