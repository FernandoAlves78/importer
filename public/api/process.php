<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

use App\Config;
use App\Database;
use App\JsonResponse;
use App\UserImporter;

JsonResponse::allowPost();

try {
    $input = JsonResponse::jsonBody();
    $token = preg_replace('/[^a-f0-9]/', '', (string) ($input['token'] ?? '')) ?? '';
    $offset = max(0, (int) ($input['offset'] ?? 0));
    $limit = (int) ($input['limit'] ?? Config::batchSize());
    $totalRows = max(0, (int) ($input['totalRows'] ?? 0));

    if (strlen($token) !== 32) {
        JsonResponse::error('Invalid token.', 400);
    }

    if ($totalRows <= 0) {
        JsonResponse::error('Invalid totalRows.', 400);
    }

    $limit = max(1, min($limit, Config::batchSize()));
    $path = Config::uploadsPath() . '/' . $token . '.csv';

    if (!is_readable($path)) {
        JsonResponse::error('Upload session expired or file missing.', 404);
    }

    $importer = new UserImporter(Database::connection());
    $result = $importer->processBatch($path, $offset, $limit, $totalRows);

    if ($result['done']) {
        @unlink($path);
    }

    JsonResponse::ok([
        'processed' => $result['processed'],
        'total' => $totalRows,
        'inserted' => $result['inserted'],
        'errorCount' => $result['errorCount'],
        'errors' => $result['errors'],
        'done' => $result['done'],
        'percent' => $totalRows > 0
            ? min(100, (int) round(($result['processed'] / $totalRows) * 100))
            : 100,
    ]);
} catch (Throwable $e) {
    JsonResponse::error($e->getMessage(), 500);
}
