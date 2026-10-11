<?php

declare(strict_types=1);

namespace App;

use RuntimeException;

final class SampleCsvGenerator
{
    private const FIRST_NAMES = [
        'Marco', 'Giulia', 'Luca', 'Sofia', 'Alessandro', 'Chiara', 'Andrea', 'Francesca',
        'Matteo', 'Elena', 'Lorenzo', 'Valentina', 'Davide', 'Martina', 'Simone', 'Sara',
        'James', 'Emma', 'Oliver', 'Olivia', 'Noah', 'Ava', 'Liam', 'Mia',
    ];

    private const LAST_NAMES = [
        'Rossi', 'Russo', 'Ferrari', 'Esposito', 'Bianchi', 'Romano', 'Colombo', 'Ricci',
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
    ];

    private const CITIES = [
        'Roma', 'Milano', 'Napoli', 'Torino', 'Firenze', 'Bologna', 'Venezia', 'Palermo',
        'London', 'Paris', 'Berlin', 'Madrid', 'Lisbon', 'Amsterdam', 'Vienna', 'Zurich',
    ];

    private const COUNTRIES = [
        'Italy', 'Italy', 'Italy', 'United Kingdom', 'France', 'Germany', 'Spain', 'Portugal',
    ];

    public static function path(): string
    {
        return Config::dataPath() . '/users_50k.csv';
    }

    public static function ensureExists(): string
    {
        $path = self::path();
        if (is_readable($path) && filesize($path) > 0) {
            return $path;
        }

        self::generate($path, Config::sampleRowCount());
        return $path;
    }

    public static function generate(string $path, int $rows): void
    {
        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('Unable to create data directory.');
        }

        $tmp = $path . '.tmp';
        $handle = fopen($tmp, 'wb');
        if ($handle === false) {
            throw new RuntimeException('Unable to write sample CSV.');
        }

        try {
            fputcsv($handle, Config::csvColumns(), ',', '"', '\\');

            $firstCount = count(self::FIRST_NAMES);
            $lastCount = count(self::LAST_NAMES);
            $cityCount = count(self::CITIES);
            $countryCount = count(self::COUNTRIES);

            for ($i = 1; $i <= $rows; $i++) {
                $first = self::FIRST_NAMES[$i % $firstCount];
                $last = self::LAST_NAMES[$i % $lastCount];
                $name = $first . ' ' . $last;
                $email = sprintf('user%05d.%s@example.com', $i, strtolower($last));
                $phone = sprintf('+39 3%02d %07d', $i % 100, $i % 10_000_000);
                $city = self::CITIES[$i % $cityCount];
                $country = self::COUNTRIES[$i % $countryCount];

                fputcsv($handle, [$name, $email, $phone, $city, $country], ',', '"', '\\');
            }
        } finally {
            fclose($handle);
        }

        if (!rename($tmp, $path)) {
            @unlink($tmp);
            throw new RuntimeException('Unable to finalize sample CSV.');
        }
    }
}
