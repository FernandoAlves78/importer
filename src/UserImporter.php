<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;
use RuntimeException;

final class UserImporter
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array{processed:int,inserted:int,errorCount:int,errors:list<array{row:int,message:string}>,done:bool}
     */
    public function processBatch(string $csvPath, int $offset, int $limit, int $totalRows): array
    {
        $handle = fopen($csvPath, 'rb');
        if ($handle === false) {
            throw new RuntimeException('CSV file not found.');
        }

        try {
            $header = fgetcsv($handle, null, ',', '"', '\\');
            CsvValidator::assertHeader($header);

            // Skip already-processed data rows (offset = count of data rows done).
            for ($i = 0; $i < $offset; $i++) {
                if (fgetcsv($handle, null, ',', '"', '\\') === false) {
                    return [
                        'processed' => $offset,
                        'inserted' => 0,
                        'errorCount' => 0,
                        'errors' => [],
                        'done' => true,
                    ];
                }
            }

            $stmt = $this->pdo->prepare(
                'INSERT INTO users (name, email, phone, city, country) VALUES (?, ?, ?, ?, ?)'
            );

            $inserted = 0;
            $errorCount = 0;
            $errors = [];
            $processed = 0;
            $rowNumber = $offset + 1;

            $this->pdo->beginTransaction();

            try {
                while ($processed < $limit && ($row = fgetcsv($handle, null, ',', '"', '\\')) !== false) {
                    if ($this->isEmptyRow($row)) {
                        continue;
                    }

                    $processed++;
                    $mapped = $this->mapRow($row);

                    if ($mapped === null) {
                        $errorCount++;
                        $this->pushError($errors, $rowNumber, 'Invalid or incomplete row.');
                        $rowNumber++;
                        continue;
                    }

                    if (!filter_var($mapped['email'], FILTER_VALIDATE_EMAIL)) {
                        $errorCount++;
                        $this->pushError($errors, $rowNumber, 'Invalid email: ' . $mapped['email']);
                        $rowNumber++;
                        continue;
                    }

                    try {
                        $stmt->execute([
                            $mapped['name'],
                            $mapped['email'],
                            $mapped['phone'],
                            $mapped['city'],
                            $mapped['country'],
                        ]);
                        $inserted++;
                    } catch (PDOException $e) {
                        $errorCount++;
                        // Duplicate email (UNIQUE) is expected during re-imports.
                        if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                            $this->pushError($errors, $rowNumber, 'Duplicate email: ' . $mapped['email']);
                        } else {
                            $this->pushError($errors, $rowNumber, 'Insert failed: ' . $e->getMessage());
                        }
                    }

                    $rowNumber++;
                }

                $this->pdo->commit();
            } catch (\Throwable $e) {
                $this->pdo->rollBack();
                throw $e;
            }
        } finally {
            fclose($handle);
        }

        $newOffset = $offset + $processed;
        $done = $newOffset >= $totalRows || $processed === 0;

        return [
            'processed' => $newOffset,
            'inserted' => $inserted,
            'errorCount' => $errorCount,
            'errors' => $errors,
            'done' => $done,
        ];
    }

    /** @return list<array<string, mixed>> */
    public function latestUsers(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, email, phone, city, country, created_at
             FROM users
             ORDER BY id DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @param list<string|null> $row
     * @return array{name:string,email:string,phone:?string,city:?string,country:?string}|null
     */
    private function mapRow(array $row): ?array
    {
        if (count($row) < 5) {
            return null;
        }

        $name = trim((string) $row[0]);
        $email = strtolower(trim((string) $row[1]));

        if ($name === '' || $email === '') {
            return null;
        }

        return [
            'name' => $name,
            'email' => $email,
            'phone' => $this->nullable($row[2] ?? null),
            'city' => $this->nullable($row[3] ?? null),
            'country' => $this->nullable($row[4] ?? null),
        ];
    }

    private function nullable(mixed $value): ?string
    {
        $v = trim((string) $value);
        return $v === '' ? null : $v;
    }

    /** @param list<string|null> $row */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Cap payload size so chunk responses stay small under high error rates.
     *
     * @param list<array{row:int,message:string}> $errors
     */
    private function pushError(array &$errors, int $row, string $message): void
    {
        if (count($errors) >= Config::maxErrorMessages()) {
            return;
        }
        $errors[] = ['row' => $row, 'message' => $message];
    }
}
