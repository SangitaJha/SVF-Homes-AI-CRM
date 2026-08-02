<?php

declare(strict_types=1);

namespace App\Storage;

final class StorageManager
{
    public function putUploadedFile(array $file, string $directory = 'uploads'): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        $extension = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
        if ($extension === '') {
            return null;
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $driver = getenv('STORAGE_DRIVER') ?: 'local';
        $targetDirectory = __DIR__ . '/../uploads/' . trim($directory, '/');
        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0775, true);
        }

        $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file((string)$file['tmp_name'], $targetPath)) {
            return null;
        }

        if ($driver === 'cloud') {
            return 'uploads/' . trim($directory, '/') . '/' . $filename;
        }

        return 'uploads/' . trim($directory, '/') . '/' . $filename;
    }
}