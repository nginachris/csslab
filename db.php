<?php
declare(strict_types=1);

$GLOBALS['DB_LAST_ERROR'] = null;

function db_last_error(): ?string
{
    return $GLOBALS['DB_LAST_ERROR'] ?? null;
}

function db_local_overrides(): array
{
    $file = __DIR__ . '/db.local.php';
    if (!is_file($file)) {
        return [];
    }

    $overrides = require $file;
    return is_array($overrides) ? $overrides : [];
}

function db_settings(): array
{
    $settings = [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => (int)(getenv('DB_PORT') ?: '3306'),
        'user' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'database' => getenv('DB_NAME') ?: 'student_study_hub',
        'socket' => getenv('DB_SOCKET') ?: null,
    ];

    $local = db_local_overrides();
    if ($local !== []) {
        $settings = array_merge($settings, $local);
    }

    $settings['port'] = (int)($settings['port'] ?? 3306);
    $settings['host'] = (string)($settings['host'] ?? '127.0.0.1');
    $settings['user'] = (string)($settings['user'] ?? 'root');
    $settings['password'] = (string)($settings['password'] ?? '');
    $settings['database'] = (string)($settings['database'] ?? 'student_study_hub');
    $settings['socket'] = $settings['socket'] ?? null;

    return $settings;
}

function db_connection(?string $database = null): ?mysqli
{
    $GLOBALS['DB_LAST_ERROR'] = null;
    $settings = db_settings();
    $dbName = $database ?? $settings['database'];

    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $connection = new mysqli(
            $settings['host'],
            $settings['user'],
            $settings['password'],
            $dbName,
            $settings['port'],
            $settings['socket'] ?: null
        );
        $connection->set_charset('utf8mb4');
        return $connection;
    } catch (Throwable $exception) {
        $GLOBALS['DB_LAST_ERROR'] = $exception->getMessage();
        return null;
    }
}

function db_server_connection(): ?mysqli
{
    return db_connection('');
}
