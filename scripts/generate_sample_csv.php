<?php

declare(strict_types=1);

/**
 * CLI helper: php scripts/generate_sample_csv.php
 * Prefer /api/sample.php which generates on first download.
 */

require_once dirname(__DIR__) . '/src/bootstrap.php';

use App\Config;
use App\SampleCsvGenerator;

$path = SampleCsvGenerator::path();
SampleCsvGenerator::generate($path, Config::sampleRowCount());

echo "Generated: {$path}" . PHP_EOL;
echo 'Rows: ' . Config::sampleRowCount() . PHP_EOL;
