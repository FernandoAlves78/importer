<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

use App\Config;
use App\CsvValidator;
use App\JsonResponse;

JsonResponse::allowPost();

try {
    if (!isset($_FILES['file'])) {
        JsonResponse::error('No file uploaded.', 400);
    }

    $validated = CsvValidator::assertValidUpload($_FILES['file']);

    $handle = fopen($validated['path'], 'r');
    if ($handle === false) {
        JsonResponse::error('Unable to read uploaded file.', 400);
    }

    $header = fgetcsv($handle, null, ',', '"', '\\');
    CsvValidator::assertHeader($header);

    $totalRows = 0;
    while (($row = fgetcsv($handle, null, ',', '"', '\\')) !== false) {
        if ($row === [null] || (count($row) === 1 && trim((string) $row[0]) === '')) {
            continue;
        }
        $totalRows++;
    }
    fclose($handle);

    if ($totalRows === 0) {
        JsonResponse::error('CSV contains no data rows.', 400);
    }

    $token = bin2hex(random_bytes(16));
    $dest = Config::uploadsPath() . '/' . $token . '.csv';

    if (!move_uploaded_file($validated['path'], $dest)) {
        JsonResponse::error('Failed to store uploaded file.', 500);
    }

    JsonResponse::ok([
        'token' => $token,
        'totalRows' => $totalRows,
        'batchSize' => Config::batchSize(),
    ]);
} catch (Throwable $e) {
    JsonResponse::error($e->getMessage(), 400);
}
