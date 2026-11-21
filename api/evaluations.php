<?php
/**
 * Evaluations API
 *
 * CRUD operations for student evaluation scores.
 * Manages the evaluation matrix: student scores per specialization per criterion.
 *
 * Endpoints:
 *   GET                 - Get evaluation matrix for a student (student_id param)
 *   POST action=save    - Bulk save/update evaluations (array of scores)
 *   POST action=delete  - Delete evaluations for a student+specialization combo
 *
 * Score scale: 1.00 – 10.00 (defined by the database CHECK constraint).
 *
 * @package DSS University
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

// ---------------------------------------------------------------------------
// Constants — evaluation score boundaries (match DB CHECK constraint)
// ---------------------------------------------------------------------------
const MIN_SCORE = 1.0;
const MAX_SCORE = 10.0;

setCorsHeaders();
startSession();
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$input  = getJsonInput();

switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'POST':
        $action = getInput($input, 'action', '');
        switch ($action) {
            case 'save':
                handleSave($input);
                break;
            case 'delete':
                handleDelete($input);
                break;
            default:
                jsonResponse(false, null, 'Invalid action. Use: save or delete.', 400);
        }
        break;
    default:
        jsonResponse(false, null, 'Method not allowed.', 405);
}

// ===========================================================================
// Shared Validation Helpers (private to this file)
// ===========================================================================

/**
 * Validate and return a positive integer ID from raw input.
 *
 * Returns the validated integer, or calls jsonResponse() with 400 and exits
 * when the value is missing, non-numeric, zero, or negative.
 *
 * @param mixed  $value     Raw input value
 * @param string $fieldName Human-readable field name for error messages
 * @return int Validated positive integer
 */
function requirePositiveInt(mixed $value, string $fieldName): int
{
    if ($value === null || $value === '') {
        jsonResponse(false, null, "{$fieldName} is required.", 400);
    }

    // Reject non-numeric strings like "abc" or "1.5"
    if (!is_numeric($value) || (int) $value != $value) {
        jsonResponse(false, null, "{$fieldName} must be a valid integer.", 400);
    }

    $intVal = (int) $value;
    if ($intVal <= 0) {
        jsonResponse(false, null, "{$fieldName} must be a positive integer.", 400);
    }

    return $intVal;
}

/**
 * Verify that the authenticated user is allowed to access a given student's data.
 *
 * Students can only access their own evaluations.
 * Admins can access any student's evaluations.
 *
 * @param int   $studentId Target student ID
 * @param array $user      Current user session data from getCurrentUser()
 * @return void Exits with 403 if access is denied
 */
function requireStudentAccess(int $studentId, array $user): void
{
    if (!isAdmin() && $user['student_id'] !== $studentId) {
        jsonResponse(false, null, 'Access denied. You can only manage your own evaluations.', 403);
    }
}

/**
 * Verify that a student record exists in the database.
 *
 * @param PDO $pdo       Database connection
 * @param int $studentId Student ID to check
 * @return void Exits with 404 if student not found
 */
function requireStudentExists(PDO $pdo, int $studentId): void
{
    $stmt = $pdo->prepare("SELECT id FROM students WHERE id = :id");
    $stmt->execute([':id' => $studentId]);
    if (!$stmt->fetch()) {
        jsonResponse(false, null, 'Student not found.', 404);
    }
}

/**
 * Load and return all valid specialization IDs as an associative set.
 *
 * @param PDO $pdo Database connection
 * @return array<int, true> Map of specialization_id => true
 */
function loadValidSpecializationIds(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT id FROM specializations");
    $ids  = [];
    foreach ($stmt->fetchAll() as $row) {
        $ids[(int) $row['id']] = true;
    }
    return $ids;
}

/**
 * Load and return all valid criterion IDs as an associative set.
 *
 * @param PDO $pdo Database connection
 * @return array<int, true> Map of criterion_id => true
 */
function loadValidCriterionIds(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT id FROM criteria");
    $ids  = [];
    foreach ($stmt->fetchAll() as $row) {
        $ids[(int) $row['id']] = true;
    }
    return $ids;
}

// ===========================================================================
// Action Handlers
// ===========================================================================

/**
 * Get evaluation matrix for a student.
 *
 * Returns data in matrix format with specialization rows and criteria columns.
 * Students can only view their own evaluations; admins can view any.
 *
 * @return never
 */
function handleGet(): never
{
    $studentId = requirePositiveInt($_GET['student_id'] ?? null, 'student_id');
    $user      = getCurrentUser();

    requireStudentAccess($studentId, $user);

    try {
        $pdo = Database::getConnection();

        // Fetch student info
        $stmt = $pdo->prepare(
            "SELECT id, first_name, last_name, email FROM students WHERE id = :id"
        );
        $stmt->execute([':id' => $studentId]);
        $student = $stmt->fetch();

        if (!$student) {
            jsonResponse(false, null, 'Student not found.', 404);
        }

        // Fetch all evaluations for this student with related data
        $stmt = $pdo->prepare(
            "SELECT e.id, e.specialization_id, e.criterion_id, e.score,
                    sp.code AS spec_code, sp.name_en AS spec_name_en, sp.name_fr AS spec_name_fr,
                    c.name_en AS criterion_name_en, c.name_fr AS criterion_name_fr,
                    c.type AS criterion_type, c.weight AS criterion_weight
             FROM evaluations e
             JOIN specializations sp ON sp.id = e.specialization_id
             JOIN criteria c ON c.id = e.criterion_id
             WHERE e.student_id = :student_id
             ORDER BY sp.code, c.id"
        );
        $stmt->execute([':student_id' => $studentId]);
        $rawEvaluations = $stmt->fetchAll();

        // Build matrix format: grouped by specialization
        $matrix = [];
        foreach ($rawEvaluations as $eval) {
            $specId = (int) $eval['specialization_id'];

            if (!isset($matrix[$specId])) {
                $matrix[$specId] = [
                    'specialization_id' => $specId,
                    'code'              => $eval['spec_code'],
                    'name_en'           => $eval['spec_name_en'],
                    'name_fr'           => $eval['spec_name_fr'],
                    'scores'            => [],
                ];
            }

            $matrix[$specId]['scores'][] = [
                'criterion_id'   => (int) $eval['criterion_id'],
                'criterion_name' => $eval['criterion_name_en'],
                'criterion_type' => $eval['criterion_type'],
                'weight'         => (float) $eval['criterion_weight'],
                'score'          => (float) $eval['score'],
            ];
        }

        jsonResponse(true, [
            'student'     => $student,
            'evaluations' => array_values($matrix),
        ], 'Evaluations retrieved successfully.');

    } catch (Throwable $e) {
        error_log('Evaluations API [GET] error: ' . $e->getMessage());
        jsonResponse(false, null, 'An internal error occurred while retrieving evaluations.', 500);
    }
}

/**
 * Bulk save/update evaluations.
 *
 * Accepts an array of evaluations with specialization_id, criterion_id, and score.
 * Uses INSERT ... ON DUPLICATE KEY UPDATE for upsert behavior.
 *
 * Validation rules:
 *   - student_id must reference an existing student
 *   - Each entry must have valid specialization_id, criterion_id, and score
 *   - specialization_id and criterion_id must reference existing records
 *   - score must be between MIN_SCORE (1.0) and MAX_SCORE (10.0)
 *   - Duplicate (specialization_id + criterion_id) pairs within the same request are rejected
 *
 * @param array $input Request data with 'student_id' and 'evaluations' array
 * @return never
 */
function handleSave(array $input): never
{
    // --- Extract only the expected fields (prevent mass assignment) ---
    $studentId   = requirePositiveInt(getInput($input, 'student_id'), 'student_id');
    $evaluations = getInput($input, 'evaluations', []);

    if (!is_array($evaluations) || empty($evaluations)) {
        jsonResponse(false, null, 'evaluations array is required and must not be empty.', 400);
    }

    $user = getCurrentUser();
    requireStudentAccess($studentId, $user);

    try {
        $pdo = Database::getConnection();

        // Verify student exists
        requireStudentExists($pdo, $studentId);

        // Pre-load valid IDs for specializations and criteria
        $validSpecIds = loadValidSpecializationIds($pdo);
        $validCritIds = loadValidCriterionIds($pdo);

        // --- First pass: validate ALL entries before touching the database ---
        $errors    = [];
        $seen      = []; // Track duplicates within the same request
        $validated = []; // Clean data for insertion

        foreach ($evaluations as $index => $eval) {
            // Only extract the three expected fields
            $specId      = $eval['specialization_id'] ?? null;
            $criterionId = $eval['criterion_id'] ?? null;
            $score       = $eval['score'] ?? null;

            // Required fields check
            if ($specId === null || $specId === '' ||
                $criterionId === null || $criterionId === '' ||
                $score === null || $score === '') {
                $errors[] = "Entry {$index}: specialization_id, criterion_id, and score are required.";
                continue;
            }

            // Validate specialization_id is a positive integer
            if (!is_numeric($specId) || (int) $specId != $specId || (int) $specId <= 0) {
                $errors[] = "Entry {$index}: specialization_id must be a positive integer.";
                continue;
            }
            $specId = (int) $specId;

            // Validate criterion_id is a positive integer
            if (!is_numeric($criterionId) || (int) $criterionId != $criterionId || (int) $criterionId <= 0) {
                $errors[] = "Entry {$index}: criterion_id must be a positive integer.";
                continue;
            }
            $criterionId = (int) $criterionId;

            // Validate score is numeric and within the allowed range
            if (!is_numeric($score)) {
                $errors[] = "Entry {$index}: score must be a numeric value.";
                continue;
            }
            $score = (float) $score;
            if ($score < MIN_SCORE || $score > MAX_SCORE) {
                $errors[] = "Entry {$index}: score must be between " . MIN_SCORE . " and " . MAX_SCORE . " (got {$score}).";
                continue;
            }

            // Verify specialization exists
            if (!isset($validSpecIds[$specId])) {
                $errors[] = "Entry {$index}: specialization_id {$specId} does not exist.";
                continue;
            }

            // Verify criterion exists
            if (!isset($validCritIds[$criterionId])) {
                $errors[] = "Entry {$index}: criterion_id {$criterionId} does not exist.";
                continue;
            }

            // Detect duplicate entries within the same request
            $key = "{$specId}_{$criterionId}";
            if (isset($seen[$key])) {
                $errors[] = "Entry {$index}: duplicate entry for specialization_id={$specId} and criterion_id={$criterionId} (already provided at entry {$seen[$key]}).";
                continue;
            }
            $seen[$key] = $index;

            $validated[] = [
                'specialization_id' => $specId,
                'criterion_id'      => $criterionId,
                'score'             => $score,
            ];
        }

        // If validation produced errors and no valid entries remain, fail early
        if (!empty($errors) && empty($validated)) {
            jsonResponse(false, ['errors' => $errors], 'No evaluations were saved due to validation errors.', 400);
        }

        // --- Second pass: insert/update all validated entries in a transaction ---
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            "INSERT INTO evaluations (student_id, specialization_id, criterion_id, score)
             VALUES (:student_id, :specialization_id, :criterion_id, :score)
             ON DUPLICATE KEY UPDATE score = VALUES(score)"
        );

        foreach ($validated as $entry) {
            $stmt->execute([
                ':student_id'        => $studentId,
                ':specialization_id' => $entry['specialization_id'],
                ':criterion_id'      => $entry['criterion_id'],
                ':score'             => $entry['score'],
            ]);
        }

        $pdo->commit();

        $savedCount = count($validated);
        $response   = [
            'saved_count' => $savedCount,
            'total_sent'  => count($evaluations),
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        jsonResponse(true, $response, "{$savedCount} evaluations saved successfully.");

    } catch (Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Evaluations API [SAVE] error: ' . $e->getMessage());
        jsonResponse(false, null, 'An internal error occurred while saving evaluations.', 500);
    }
}

/**
 * Delete evaluations for a student + specialization combination.
 *
 * If specialization_id is provided, only that combination is deleted.
 * If omitted, all evaluations for the student are deleted.
 *
 * @param array $input Request data with 'student_id' and optional 'specialization_id'
 * @return never
 */
function handleDelete(array $input): never
{
    $studentId = requirePositiveInt(getInput($input, 'student_id'), 'student_id');
    $user      = getCurrentUser();

    requireStudentAccess($studentId, $user);

    try {
        $pdo = Database::getConnection();

        // Verify student exists
        requireStudentExists($pdo, $studentId);

        $rawSpecId = getInput($input, 'specialization_id');

        if ($rawSpecId !== null && $rawSpecId !== '') {
            // Validate specialization_id if provided
            $specId = requirePositiveInt($rawSpecId, 'specialization_id');

            // Verify specialization exists
            $checkStmt = $pdo->prepare("SELECT id FROM specializations WHERE id = :id");
            $checkStmt->execute([':id' => $specId]);
            if (!$checkStmt->fetch()) {
                jsonResponse(false, null, 'Specialization not found.', 404);
            }

            // Delete evaluations for specific student + specialization
            $stmt = $pdo->prepare(
                "DELETE FROM evaluations
                 WHERE student_id = :student_id AND specialization_id = :specialization_id"
            );
            $stmt->execute([
                ':student_id'        => $studentId,
                ':specialization_id' => $specId,
            ]);
        } else {
            // Delete all evaluations for the student
            $stmt = $pdo->prepare(
                "DELETE FROM evaluations WHERE student_id = :student_id"
            );
            $stmt->execute([':student_id' => $studentId]);
        }

        $deletedCount = $stmt->rowCount();

        if ($deletedCount === 0) {
            jsonResponse(false, null, 'No evaluations found to delete.', 404);
        }

        jsonResponse(true, ['deleted_count' => $deletedCount], "{$deletedCount} evaluations deleted successfully.");

    } catch (Throwable $e) {
        error_log('Evaluations API [DELETE] error: ' . $e->getMessage());
        jsonResponse(false, null, 'An internal error occurred while deleting evaluations.', 500);
    }
}
