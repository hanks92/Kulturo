class Flashcard {
  final String question;
  final String answer;

  Flashcard({required this.question, required this.answer});

  factory Flashcard.fromJson(Map<String, dynamic> json) => Flashcard(
    question: json['question'],
    answer: json['answer'],
  );
}

class RevisionSession {
  final Flashcard flashcard;
  final Map<int, String> predictedDueDates;

  RevisionSession({required this.flashcard, required this.predictedDueDates});

  factory RevisionSession.fromJson(Map<String, dynamic> json) {
    return RevisionSession(
      flashcard: Flashcard.fromJson(json['flashcard']),
      predictedDueDates: Map<int, String>.from(json['predictedDueDates']),
    );
  }
}
