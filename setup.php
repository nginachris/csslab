<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$settings = db_settings();
$database = $settings['database'];
$table = 'registrations';
$messages = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $connection = db_server_connection();
        if ($connection === null) {
            $reason = db_last_error();
            $suffix = $reason ? ' ' . $reason : '';
            throw new RuntimeException('Could not connect to MariaDB server.' . $suffix);
        }

        $connection->query("CREATE DATABASE IF NOT EXISTS `$database`");
        $connection->select_db($database);

        $connection->query(
            "CREATE TABLE IF NOT EXISTS `$table` (
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

        $success = true;
        $messages[] = 'MariaDB database `' . $database . '` is ready.';
        $messages[] = 'Table `registrations` is ready.';
        $messages[] = 'You can now open `register.php` and submit the form.';
    } catch (Throwable $exception) {
        $messages[] = 'Setup failed: ' . $exception->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup | Student Study Hub</title>
    <link rel="stylesheet" href="csslab.css">
</head>
<body>
    <div class="page">
        <header class="site-header">
            <div class="brand-row">
                <div>
                    <h1 class="site-title">Database Setup</h1>
                    <p class="site-tagline">Create the MariaDB database and registrations table for the PHP demo.</p>
                </div>
            </div>
            <nav class="site-nav" aria-label="Primary">
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="courses.html">Courses</a></li>
                    <li><a href="resources.html">Resources</a></li>
                    <li><a href="gallery.html">Gallery</a></li>
                    <li><a href="register.php">Register</a></li>
                </ul>
            </nav>
        </header>

        <section class="form-panel">
            <h2>Run database setup</h2>
            <p class="muted">Click the button below while MariaDB is running. It creates the database and table if they do not already exist.</p>

            <form method="post" action="">
                <div class="actions">
                    <input type="submit" value="Create Database and Table">
                    <a class="button" href="register.php">Go to Registration</a>
                </div>
            </form>

            <?php if ($messages !== []): ?>
                <div class="content-card stack">
                    <h2><?= $success ? 'Setup Complete' : 'Setup Status' ?></h2>
                    <?php foreach ($messages as $message): ?>
                        <p class="<?= $success ? 'muted' : 'form-status' ?>"><?= h($message) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <footer class="footer">
            <p>&copy; 2026 Student Study Hub</p>
        </footer>
    </div>
</body>
</html>
