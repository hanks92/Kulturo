<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class UploadController extends AbstractController
{
    #[Route('/upload/{type}', name: 'upload_file', methods: ['POST'], requirements: ['type' => 'image|audio'])]
    public function upload(Request $request, SluggerInterface $slugger, string $type): JsonResponse
    {
        $file = $request->files->get('file');
        if (!$file) {
            return $this->json(['error' => 'No file uploaded'], 400);
        }

        $extension = strtolower($file->guessExtension() ?? '');
        $allowed = [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'audio' => ['mp3', 'wav', 'ogg', 'm4a'],
        ];

        if (!in_array($extension, $allowed[$type] ?? [])) {
            return $this->json(['error' => 'Invalid file type: ' . $extension], 400);
        }

        $safeName = $slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $filename = $safeName . '-' . uniqid() . '.' . $extension;

        try {
            $file->move($this->getParameter('uploads_directory'), $filename);
        } catch (FileException $e) {
            return $this->json(['error' => 'Upload failed: ' . $e->getMessage()], 500);
        }

        return $this->json(['location' => '/uploads/' . $filename]);
    }
}
