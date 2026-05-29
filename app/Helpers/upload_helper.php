<?php

/**
 * Reusable upload helpers.
 *
 * Always uses CodeIgniter UploadedFile::getClientExtension() — NEVER getExtension()
 * to avoid finfo_file() crash on macOS temp folder cleanup.
 */

use CodeIgniter\HTTP\Files\UploadedFile;

if (! function_exists('handleDocumentUpload')) {
    /**
     * Save uploaded document to writable/uploads/{subfolder}/ and return metadata.
     *
     * @return array{path:string,size:int,mime:string,original:string}|null
     */
    function handleDocumentUpload(?UploadedFile $file, string $subfolder, array $allowedExt = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'webp',
    ], int $maxBytes = 10485760): ?array
    {
        if ($file === null || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        if ($file->getSize() > $maxBytes) {
            throw new RuntimeException('Ukuran file melebihi batas ' . ($maxBytes / 1024 / 1024) . ' MB.');
        }

        $ext = strtolower($file->getClientExtension());
        if ($ext === '' || ! in_array($ext, $allowedExt, true)) {
            throw new RuntimeException('Tipe file tidak diizinkan.');
        }

        $targetDir = WRITEPATH . 'uploads/' . trim($subfolder, '/');
        if (! is_dir($targetDir)) {
            @mkdir($targetDir, 0775, true);
        }

        $newName  = 'doc_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $file->move($targetDir, $newName, true);

        return [
            'path'     => 'uploads/' . trim($subfolder, '/') . '/' . $newName,
            'size'     => (int) filesize($targetDir . '/' . $newName),
            'mime'     => (string) $file->getClientMimeType(),
            'original' => (string) $file->getClientName(),
        ];
    }
}
