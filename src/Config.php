<?php

declare(strict_types=1);

namespace App;

final class Config
{
    public static function dbHost(): string
    {
        return getenv('DB_HOST') ?: 'db';
    }

    public static function dbPort(): string
    {
        return getenv('DB_PORT') ?: '3306';
    }

    public static function dbName(): string
    {
        return getenv('DB_NAME') ?: 'importer';
    }

    public static function dbUser(): string
    {
        return getenv('DB_USER') ?: 'importer';
    }

    public static function dbPassword(): string
    {
        return getenv('DB_PASSWORD') ?: 'importer_secret';
    }

    public static function storagePath(): string
    {
        return dirname(__DIR__) . '/storage';
    }

    public static function uploadsPath(): string
    {
        return self::storagePath() . '/uploads';
    }

    public static function dataPath(): string
    {
        return self::storagePath() . '/data';
    }

    /** Batch size keeps each HTTP request under typical PHP/proxy timeouts. */
    public static function batchSize(): int
    {
        return 1000;
    }

    public static function maxErrorMessages(): int
    {
        return 50;
    }

    public static function sampleRowCount(): int
    {
        return 50_000;
    }

    /** @return list<string> */
    public static function csvColumns(): array
    {
        return ['name', 'email', 'phone', 'city', 'country'];
    }
}
