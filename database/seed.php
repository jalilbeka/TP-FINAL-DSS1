<?php
/**
 * Database Seed Script
 *
 * Creates the database, tables, and inserts all seed data with proper
 * password hashing. Run from CLI:
 *
 *   php database/seed.php
 *
 * Prerequisites:
 *   - MySQL/MariaDB running on localhost
 *   - Root user with no password (default XAMPP/WAMP)
 *
 * @package DSS University
 */

declare(strict_types=1);

// ---------------------------------------------------------------------------
// Configuration
// ---------------------------------------------------------------------------
$host   = '127.0.0.1';
$user   = 'root';
$pass   = '';
$dbName = 'dss_university';

echo "=== DSS University - Database Seeder ===" . PHP_EOL . PHP_EOL;

// ---------------------------------------------------------------------------
// 1. Connect WITHOUT database (to create it)
// ---------------------------------------------------------------------------
try {
    $pdo = new PDO(
        "mysql:host={$host};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
    echo "[OK] Connected to MySQL server." . PHP_EOL;
} catch (PDOException $e) {
    die("[ERROR] Connection failed: " . $e->getMessage() . PHP_EOL);
}

// ---------------------------------------------------------------------------
// 2. Create database
// ---------------------------------------------------------------------------
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `{$dbName}`");
echo "[OK] Database '{$dbName}' ready." . PHP_EOL;

// ---------------------------------------------------------------------------
// 3. Load and execute schema
// ---------------------------------------------------------------------------
$schemaPath = __DIR__ . '/schema.sql';

if (!file_exists($schemaPath)) {
    die("[ERROR] Schema file not found: {$schemaPath}" . PHP_EOL);
}

$schema = file_get_contents($schemaPath);

// Remove the CREATE DATABASE and USE lines (we already handled those)
$schema = preg_replace('/^CREATE DATABASE.*$/m', '', $schema);
$schema = preg_replace('/^USE .*$/m', '', $schema);

// Execute each statement individually
$statements = array_filter(
    array_map('trim', explode(';', $schema)),
    fn($s) => !empty($s) && !str_starts_with($s, '--')
);

foreach ($statements as $stmt) {
    if (!empty(trim($stmt))) {
        try {
            $pdo->exec($stmt);
        } catch (PDOException $e) {
            // Ignore drop-table errors if tables don't exist yet
            if (stripos($stmt, 'DROP TABLE') === false) {
                echo "[WARN] Statement error: " . $e->getMessage() . PHP_EOL;
                echo "       Statement: " . substr($stmt, 0, 80) . "..." . PHP_EOL;
            }
        }
    }
}
echo "[OK] Schema tables created." . PHP_EOL;

// ---------------------------------------------------------------------------
// 4. Seed Users
// ---------------------------------------------------------------------------
$adminPassword   = password_hash('admin123', PASSWORD_BCRYPT);
$studentPassword = password_hash('student123', PASSWORD_BCRYPT);

$stmt = $pdo->prepare(
    "INSERT INTO `users` (`username`, `email`, `password`, `role`)
     VALUES (:username, :email, :password, :role)"
);

$stmt->execute([
    ':username' => 'admin',
    ':email'    => 'admin@dss-university.com',
    ':password' => $adminPassword,
    ':role'     => 'admin',
]);
$adminUserId = (int) $pdo->lastInsertId();
echo "[OK] Admin user created (id={$adminUserId})." . PHP_EOL;

$stmt->execute([
    ':username' => 'student',
    ':email'    => 'ahmed.benali@student.dss-university.com',
    ':password' => $studentPassword,
    ':role'     => 'student',
]);
$studentUserId = (int) $pdo->lastInsertId();
echo "[OK] Student user created (id={$studentUserId})." . PHP_EOL;

// ---------------------------------------------------------------------------
// 5. Seed Student Profile
// ---------------------------------------------------------------------------
$stmt = $pdo->prepare(
    "INSERT INTO `students` (`user_id`, `first_name`, `last_name`, `email`)
     VALUES (:user_id, :first_name, :last_name, :email)"
);
$stmt->execute([
    ':user_id'    => $studentUserId,
    ':first_name' => 'Ahmed',
    ':last_name'  => 'Benali',
    ':email'      => 'ahmed.benali@student.dss-university.com',
]);
$studentId = (int) $pdo->lastInsertId();
echo "[OK] Student profile 'Ahmed Benali' created (id={$studentId})." . PHP_EOL;

// ---------------------------------------------------------------------------
// 6. Seed Specializations
// ---------------------------------------------------------------------------
$specializations = [
    [
        'code'           => 'P1',
        'name_en'        => 'Artificial Intelligence',
        'name_fr'        => 'Intelligence Artificielle',
        'description_en' => 'Study of intelligent systems, machine learning, deep learning, natural language processing, and computer vision.',
        'description_fr' => 'Étude des systèmes intelligents, apprentissage automatique, apprentissage profond, traitement du langage naturel et vision par ordinateur.',
    ],
    [
        'code'           => 'P2',
        'name_en'        => 'Cybersecurity',
        'name_fr'        => 'Cybersécurité',
        'description_en' => 'Protection of information systems, network security, cryptography, ethical hacking, and digital forensics.',
        'description_fr' => 'Protection des systèmes d\'information, sécurité des réseaux, cryptographie, piratage éthique et criminalistique numérique.',
    ],
    [
        'code'           => 'P3',
        'name_en'        => 'Data Science',
        'name_fr'        => 'Science des Données',
        'description_en' => 'Data analysis, statistical modeling, big data technologies, data visualization, and predictive analytics.',
        'description_fr' => 'Analyse de données, modélisation statistique, technologies big data, visualisation de données et analyse prédictive.',
    ],
    [
        'code'           => 'P4',
        'name_en'        => 'Information Systems and Software Engineering',
        'name_fr'        => 'Ingénierie des Systèmes d\'Information et du Logiciel',
        'description_en' => 'Software development methodologies, database design, enterprise systems, web technologies, and project management.',
        'description_fr' => 'Méthodologies de développement logiciel, conception de bases de données, systèmes d\'entreprise, technologies web et gestion de projet.',
    ],
];

$stmt = $pdo->prepare(
    "INSERT INTO `specializations` (`code`, `name_en`, `name_fr`, `description_en`, `description_fr`)
     VALUES (:code, :name_en, :name_fr, :description_en, :description_fr)"
);

$specIds = [];
foreach ($specializations as $spec) {
    $stmt->execute($spec);
    $specIds[$spec['code']] = (int) $pdo->lastInsertId();
}
echo "[OK] 4 specializations created." . PHP_EOL;

// ---------------------------------------------------------------------------
// 7. Seed Criteria
// ---------------------------------------------------------------------------
$criteria = [
    ['name_en' => 'Programming Skills',    'name_fr' => 'Compétences en Programmation',  'type' => 'maximize', 'weight' => 0.20],
    ['name_en' => 'Mathematics',           'name_fr' => 'Mathématiques',                 'type' => 'maximize', 'weight' => 0.15],
    ['name_en' => 'Competency Alignment',  'name_fr' => 'Alignement des Compétences',    'type' => 'maximize', 'weight' => 0.20],
    ['name_en' => 'Career Opportunities',  'name_fr' => 'Opportunités de Carrière',      'type' => 'maximize', 'weight' => 0.20],
    ['name_en' => 'Difficulty',            'name_fr' => 'Difficulté',                    'type' => 'minimize', 'weight' => 0.10],
    ['name_en' => 'Personal Interest',     'name_fr' => 'Intérêt Personnel',             'type' => 'maximize', 'weight' => 0.15],
];

$stmt = $pdo->prepare(
    "INSERT INTO `criteria` (`name_en`, `name_fr`, `type`, `weight`)
     VALUES (:name_en, :name_fr, :type, :weight)"
);

$criteriaIds = [];
foreach ($criteria as $c) {
    $stmt->execute($c);
    $criteriaIds[$c['name_en']] = (int) $pdo->lastInsertId();
}
echo "[OK] 6 criteria created." . PHP_EOL;

// ---------------------------------------------------------------------------
// 8. Seed Evaluations (Demo student: Ahmed Benali)
// ---------------------------------------------------------------------------
// Evaluation matrix: [specialization_code][criterion_name] => score
// Scores are realistic for a student with strong programming/math background
$evaluationMatrix = [
    'P1' => [ // AI
        'Programming Skills'   => 8.5,
        'Mathematics'          => 9.0,
        'Competency Alignment' => 8.0,
        'Career Opportunities' => 9.5,
        'Difficulty'           => 8.0, // High difficulty (minimize → penalty)
        'Personal Interest'    => 9.0,
    ],
    'P2' => [ // Cybersecurity
        'Programming Skills'   => 7.0,
        'Mathematics'          => 5.5,
        'Competency Alignment' => 6.5,
        'Career Opportunities' => 8.5,
        'Difficulty'           => 6.0,
        'Personal Interest'    => 7.0,
    ],
    'P3' => [ // Data Science
        'Programming Skills'   => 8.0,
        'Mathematics'          => 8.5,
        'Competency Alignment' => 7.5,
        'Career Opportunities' => 9.0,
        'Difficulty'           => 7.0,
        'Personal Interest'    => 8.5,
    ],
    'P4' => [ // ISSE
        'Programming Skills'   => 9.0,
        'Mathematics'          => 6.0,
        'Competency Alignment' => 8.5,
        'Career Opportunities' => 7.5,
        'Difficulty'           => 5.0, // Lower difficulty (minimize → bonus)
        'Personal Interest'    => 7.0,
    ],
];

$stmt = $pdo->prepare(
    "INSERT INTO `evaluations` (`student_id`, `specialization_id`, `criterion_id`, `score`)
     VALUES (:student_id, :specialization_id, :criterion_id, :score)"
);

$evalCount = 0;
foreach ($evaluationMatrix as $specCode => $scores) {
    foreach ($scores as $criterionName => $score) {
        $stmt->execute([
            ':student_id'        => $studentId,
            ':specialization_id' => $specIds[$specCode],
            ':criterion_id'      => $criteriaIds[$criterionName],
            ':score'             => $score,
        ]);
        $evalCount++;
    }
}
echo "[OK] {$evalCount} evaluations created (4 specs × 6 criteria)." . PHP_EOL;

// ---------------------------------------------------------------------------
// Done
// ---------------------------------------------------------------------------
echo PHP_EOL . "=== Seeding complete! ===" . PHP_EOL;
echo "Login credentials:" . PHP_EOL;
echo "  Admin:   username=admin,   password=admin123" . PHP_EOL;
echo "  Student: username=student, password=student123" . PHP_EOL;
