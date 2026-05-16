<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploadService
{
    private string $uploadDir = 'uploads/products';

    public function upload(UploadedFile $file): string
    {
        $fileName = uniqid() . '.' . $file->guessExtension();

        $file->move(
            $this->uploadDir,
            $fileName
        );

        // 👉 on retourne URL (ce qui va en DB)
        return '/' . $this->uploadDir . '/' . $fileName;
    }
}