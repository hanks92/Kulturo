// lib/utils/profile_image.dart
String getProfileImage(String? image) {
  final imageName = image ?? '';
  final availableAssets = [
    'avataaars.png',
    ...List.generate(19, (i) => 'avataaars(${i + 1}).png'),
  ];

  if (availableAssets.contains(imageName)) {
    return imageName;
  }

  return 'avataaars.png'; // fallback de sécurité
}
