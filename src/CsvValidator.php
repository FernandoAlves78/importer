<?php

declare(strict_types=1);

namespace App;

use RuntimeException;

final class CsvValidator
{
    private const ALLOWED_MIME = [
        'text/csv',
        'text/plain',
        'application/csv',
        'application/vnd.ms-excel',
        'application/octet-stream',
    ];

    /**
     * @param array{name?:string,type?:string,tmp_name?:string,error?:int,size?:int} $file
     * @return array{path: string, name: string}
     */
    public static function assertValidUpload(array $file): array
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException(self::uploadErrorMessage($error));
        }

        $name = (string) ($file['name'] ?? '');
        $tmp = (string) ($file['tmp_name'] ?? '');

        if ($name === '' || $tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('Invalid upload.');
        }

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            throw new RuntimeException('Only CSV files are accepted.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp) ?: '';
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            throw new RuntimeException('Only CSV files are accepted.');
        }

        return ['path' => $tmp, 'name' => $name];
    }

    private static function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File is too large for server upload limits.',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder on server.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'Upload blocked by a PHP extension.',
            default => 'Upload failed.',
        };
    }

    /**
     * @param list<string|null>|false $header
     */
    public static function assertHeader(array|false $header): void
    {
        if ($header === false) {
            throw new RuntimeException('CSV header is missing.');
        }

        $normalized = array_map(
            static fn ($col) => strtolower(trim((string) $col)),
            $header
        );

        if ($normalized !== Config::csvColumns()) {
            throw new RuntimeException(
                'Invalid CSV header. Expected: ' . implode(',', Config::csvColumns())
            );
        }
    }
}
