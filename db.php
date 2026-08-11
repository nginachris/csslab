<?php
declare(strict_types=1);

function db_connection(): ?mysqli
{
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $database = 'student_study_hub';

    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $connection = new mysqli($host, $user, $password, $database);
        $connection->set_charset('utf8mb4');
        return $connection;
    } catch (Throwable $exception) {
        return null;
    }
}
