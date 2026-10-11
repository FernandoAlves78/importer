<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

use App\Database;
use App\JsonResponse;
use App\UserImporter;

JsonResponse::allowGet();

try {
    $importer = new UserImporter(Database::connection());
    JsonResponse::ok([
        'users' => $importer->latestUsers(5),
    ]);
} catch (Throwable $e) {
    JsonResponse::error($e->getMessage(), 500);
}
