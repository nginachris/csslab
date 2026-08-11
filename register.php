<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function clean(string $value): string
{
    return trim($value);
}

$message = '';
$messageState = '';
$submittedRecord = null;
$dbConnection = db_connection();
$dbNotice = '';
$recentRegistrations = [];

$fullName = '';
$email = '';
$dob = '';
$course = '';
$gender = '';
$interests = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = clean((string)($_POST['fullname'] ?? ''));
    $email = clean((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $dob = clean((string)($_POST['dob'] ?? ''));
    $course = clean((string)($_POST['course'] ?? ''));
    $gender = clean((string)($_POST['gender'] ?? ''));
    $interests = array_values(array_filter(array_map('trim', (array)($_POST['interests'] ?? []))));

    $missing = [];
    if ($fullName === '') {
        $missing[] = 'Full Name';
    }
    if ($email === '') {
        $missing[] = 'Email';
    }
    if ($password === '') {
        $missing[] = 'Password';
    }
    if ($dob === '') {
        $missing[] = 'Date of Birth';
    }
    if ($course === '') {
        $missing[] = 'Course';
    }
    if ($gender === '') {
        $missing[] = 'Gender';
    }
    if ($interests === []) {
        $missing[] = 'Interests';
    }

    if ($missing !== []) {
        $message = 'Please complete the required fields: ' . implode(', ', $missing) . '.';
        $messageState = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageState = 'error';
    } elseif ($dbConnection === null) {
        $message = 'Form received by POST, but the database connection is not available. Check your MySQL settings.';
        $messageState = 'error';
    } else {
        try {
            $interestsText = implode(', ', $interests);
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $statement = $dbConnection->prepare(
                'INSERT INTO registrations (full_name, email, password_hash, dob, gender, course, interests) VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $statement->bind_param('sssssss', $fullName, $email, $passwordHash, $dob, $gender, $course, $interestsText);
            $statement->execute();

            $submittedRecord = [
                'full_name' => $fullName,
                'email' => $email,
                'dob' => $dob,
                'gender' => $gender,
                'course' => $course,
                'interests' => $interestsText,
                'request_method' => $_SERVER['REQUEST_METHOD'],
            ];

            $message = 'Registration received via POST and saved successfully.';
            $messageState = 'success';
        } catch (Throwable $exception) {
            $message = 'The record could not be saved. Please confirm that the table exists and try again.';
            $messageState = 'error';
        }
    }
}

if ($dbConnection !== null) {
    try {
        $result = $dbConnection->query(
            'SELECT id, full_name, email, dob, gender, course, interests, created_at FROM registrations ORDER BY id DESC LIMIT 5'
        );
        $recentRegistrations = $result->fetch_all(MYSQLI_ASSOC);
    } catch (Throwable $exception) {
        $dbNotice = 'The database is connected, but no records could be retrieved yet.';
    }
} else {
    $dbNotice = 'Database connection unavailable. Open setup.php in XAMPP to create the database, then confirm your MySQL settings.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Student Study Hub</title>
    <link rel="stylesheet" href="csslab.css">
</head>
<body>
    <div class="page">
        <header class="site-header">
            <div class="brand-row">
                <div>
                    <h1 class="site-title">Student Registration Form</h1>
                    <p class="site-tagline">PHP form processing and MySQLi integration.</p>
                </div>
            </div>
            <nav class="site-nav" aria-label="Primary">
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="courses.html">Courses</a></li>
                    <li><a href="resources.html">Resources</a></li>
                    <li><a href="gallery.html">Gallery</a></li>
                    <li><a class="active" href="register.php">Register</a></li>
                </ul>
            </nav>
        </header>

        <section class="form-panel">
            <h2>Register your details</h2>
            <form data-register-form method="post" action="" novalidate>
                <div class="form-grid">
                    <div class="field">
                        <label for="fullname">Full Name</label>
                        <input id="fullname" type="text" name="fullname" placeholder="Enter your full name" required value="<?= h($fullName) ?>">
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" placeholder="name@example.com" required value="<?= h($email) ?>">
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" placeholder="Create a password" minlength="6" required>
                    </div>
                    <div class="field">
                        <label for="dob">Date of Birth</label>
                        <input id="dob" type="date" name="dob" required value="<?= h($dob) ?>">
                    </div>
                </div>

                <div class="field" data-required-group="gender" data-group-label="Gender">
                    <label>Gender</label>
                    <div class="inline-group">
                        <label class="inline-option"><input type="radio" name="gender" value="male" required <?= $gender === 'male' ? 'checked' : '' ?>> Male</label>
                        <label class="inline-option"><input type="radio" name="gender" value="female" <?= $gender === 'female' ? 'checked' : '' ?>> Female</label>
                    </div>
                </div>

                <div class="field">
                    <label for="course">Select Course</label>
                    <select id="course" name="course" required>
                        <option value="" disabled <?= $course === '' ? 'selected' : '' ?>>Select a course</option>
                        <option value="HTML Basics" <?= $course === 'HTML Basics' ? 'selected' : '' ?>>HTML Basics</option>
                        <option value="CSS Fundamentals" <?= $course === 'CSS Fundamentals' ? 'selected' : '' ?>>CSS Fundamentals</option>
                        <option value="JavaScript Essentials" <?= $course === 'JavaScript Essentials' ? 'selected' : '' ?>>JavaScript Essentials</option>
                    </select>
                </div>

                <div class="field" data-required-group="interests" data-group-label="Interests">
                    <label>Interests</label>
                    <div class="inline-group">
                        <label class="inline-option"><input type="checkbox" name="interests[]" value="programming" <?= in_array('programming', $interests, true) ? 'checked' : '' ?>> Programming</label>
                        <label class="inline-option"><input type="checkbox" name="interests[]" value="design" <?= in_array('design', $interests, true) ? 'checked' : '' ?>> Design</label>
                        <label class="inline-option"><input type="checkbox" name="interests[]" value="networking" <?= in_array('networking', $interests, true) ? 'checked' : '' ?>> Networking</label>
                    </div>
                </div>

                <p class="muted">All fields are required before you submit the form.</p>

                <div class="actions">
                    <input type="submit" value="Register">
                    <a class="button" href="gallery.html">View Gallery</a>
                </div>
            </form>
            <p id="form-status" class="form-status" data-form-status data-state="<?= h($messageState) ?>" aria-live="polite" <?= $message === '' ? 'hidden' : '' ?>><?= h($message) ?></p>
        </section>

        <section class="content-card stack">
            <h2>Server Response</h2>
            <p class="muted">Method used: <strong><?= h($_SERVER['REQUEST_METHOD']) ?></strong></p>
            <?php if ($submittedRecord !== null): ?>
                <div class="stack">
                    <p><strong>Name:</strong> <?= h($submittedRecord['full_name']) ?></p>
                    <p><strong>Email:</strong> <?= h($submittedRecord['email']) ?></p>
                    <p><strong>Date of Birth:</strong> <?= h($submittedRecord['dob']) ?></p>
                    <p><strong>Gender:</strong> <?= h($submittedRecord['gender']) ?></p>
                    <p><strong>Course:</strong> <?= h($submittedRecord['course']) ?></p>
                    <p><strong>Interests:</strong> <?= h($submittedRecord['interests']) ?></p>
                </div>
            <?php else: ?>
                <p class="muted">Submit the form with POST to see the received data here.</p>
            <?php endif; ?>
        </section>

        <section class="table-panel">
            <div class="panel-heading">
                <h2>Recent Registrations</h2>
                <p class="muted">Retrieved from MySQLi</p>
            </div>
            <?php if ($dbNotice !== ''): ?>
                <p class="form-status" data-state="error"><?= h($dbNotice) ?></p>
            <?php endif; ?>
            <?php if ($recentRegistrations === []): ?>
                <p class="muted">No records found yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Gender</th>
                            <th>Interests</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentRegistrations as $row): ?>
                            <tr>
                                <td><?= h((string)$row['id']) ?></td>
                                <td><?= h((string)$row['full_name']) ?></td>
                                <td><?= h((string)$row['email']) ?></td>
                                <td><?= h((string)$row['course']) ?></td>
                                <td><?= h((string)$row['gender']) ?></td>
                                <td><?= h((string)$row['interests']) ?></td>
                                <td><?= h((string)$row['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <footer class="footer">
            <p>&copy; 2026 Student Study Hub</p>
        </footer>
    </div>
    <script src="student.js" defer></script>
</body>
</html>
