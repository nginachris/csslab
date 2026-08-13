<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

header('Content-Type: text/plain; charset=UTF-8');

$db = db_connection();
if ($db === null) {
    echo "DB_CONNECT_FAIL\n";
    $error = db_last_error();
    if ($error) {
        echo $error . "\n";
    }
    exit(1);
}

echo "DB_CONNECT_OK\n";

$db->query(
    "CREATE TABLE IF NOT EXISTS registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(120) NOT NULL,
        email VARCHAR(150) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        dob DATE NOT NULL,
        gender VARCHAR(20) NOT NULL,
        course VARCHAR(100) NOT NULL,
        interests VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
);

$tableResult = $db->query("SHOW TABLES LIKE 'registrations'");
if ($tableResult && $tableResult->num_rows === 1) {
    echo "TABLE_REGISTRATIONS_OK\n";
} else {
    echo "TABLE_REGISTRATIONS_MISSING\n";
    exit(1);
}

$countResult = $db->query("SELECT COUNT(*) AS total FROM registrations");
$row = $countResult ? $countResult->fetch_assoc() : ['total' => '0'];
echo "REGISTRATIONS_COUNT=" . ($row['total'] ?? '0') . "\n";
