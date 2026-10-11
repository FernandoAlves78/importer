<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

use App\JsonResponse;
use App\SampleCsvGenerator;

try {
    $path = SampleCsvGenerator::ensureExists();
    $filename = 'users_50k.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . (string) filesize($path));
    header('Cache-Control: no-store');

    readfile($path);
    exit;
} catch (Throwable $e) {
    JsonResponse::error($e->getMessage(), 500);
}
