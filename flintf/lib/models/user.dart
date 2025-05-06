class User {
  final int id;
  final String username;
  final String email;
  final String profileImage;

  User({
    required this.id,
    required this.username,
    required this.email,
    required this.profileImage,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] ?? 0,
      username: json['username'] ?? '',
      email: json['email'] ?? '',
      profileImage: json['profile_image'] ?? 'default.png',
    );
  }
}
