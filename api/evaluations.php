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
 * @package DSS University
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

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
    $studentId = $_GET['student_id'] ?? null;

    if (empty($studentId)) {
        jsonResponse(false, null, 'student_id parameter is required.', 400);
    }

    $studentId = (int) $studentId;
    $user = getCurrentUser();

    // Students can only view their own evaluations
    if (!isAdmin() && $user['student_id'] !== $studentId) {
        jsonResponse(false, null, 'Access denied. You can only view your own evaluations.', 403);
    }

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
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
    }
}

/**
 * Bulk save/update evaluations.
 *
 * Accepts an array of evaluations with specialization_id, criterion_id, and score.
 * Uses INSERT ... ON DUPLICATE KEY UPDATE for upsert behavior.
 *
 * @param array $input Request data with 'student_id' and 'evaluations' array
 * @return never
 */
function handleSave(array $input): never
{
    $studentId   = getInput($input, 'student_id');
    $evaluations = getInput($input, 'evaluations', []);

    if (empty($studentId)) {
        jsonResponse(false, null, 'student_id is required.', 400);
    }

    if (empty($evaluations) || !is_array($evaluations)) {
        jsonResponse(false, null, 'evaluations array is required and must not be empty.', 400);
    }

    $studentId = (int) $studentId;
    $user = getCurrentUser();

    // Students can only save their own evaluations
    if (!isAdmin() && $user['student_id'] !== $studentId) {
        jsonResponse(false, null, 'Access denied. You can only edit your own evaluations.', 403);
    }

    try {
        $pdo = Database::getConnection();

        // Verify student exists
        $stmt = $pdo->prepare("SELECT id FROM students WHERE id = :id");
        $stmt->execute([':id' => $studentId]);
        if (!$stmt->fetch()) {
            jsonResponse(false, null, 'Student not found.', 404);
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            "INSERT INTO evaluations (student_id, specialization_id, criterion_id, score)
             VALUES (:student_id, :specialization_id, :criterion_id, :score)
             ON DUPLICATE KEY UPDATE score = VALUES(score)"
        );

        $savedCount = 0;
        $errors     = [];

        foreach ($evaluations as $index => $eval) {
            $specId     = $eval['specialization_id'] ?? null;
            $criterionId = $eval['criterion_id'] ?? null;
            $score      = $eval['score'] ?? null;

            // Validate each evaluation entry
            if (empty($specId) || empty($criterionId) || $score === null) {
                $errors[] = "Entry {$index}: specialization_id, criterion_id, and score are required.";
                continue;
            }

            $score = (float) $score;
            if ($score < 1.0 || $score > 10.0) {
                $errors[] = "Entry {$index}: score must be between 1.0 and 10.0 (got {$score}).";
                continue;
            }

            try {
                $stmt->execute([
                    ':student_id'        => $studentId,
                    ':specialization_id' => (int) $specId,
                    ':criterion_id'      => (int) $criterionId,
                    ':score'             => $score,
                ]);
                $savedCount++;
            } catch (PDOException $e) {
                $errors[] = "Entry {$index}: " . $e->getMessage();
            }
        }

        if (!empty($errors) && $savedCount === 0) {
            $pdo->rollBack();
            jsonResponse(false, ['errors' => $errors], 'No evaluations were saved due to errors.', 400);
        }

        $pdo->commit();

        $response = [
            'saved_count' => $savedCount,
            'total_sent'  => count($evaluations),
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        jsonResponse(true, $response, "{$savedCount} evaluations saved successfully.");
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        jsonResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
    }
}

/**
 * Delete evaluations for a student + specialization combination.
 *
 * @param array $input Request data with 'student_id' and 'specialization_id'
 * @return never
 */
function handleDelete(array $input): never
{
    $studentId = getInput($input, 'student_id');
    $specId    = getInput($input, 'specialization_id');

    if (empty($studentId)) {
        jsonResponse(false, null, 'student_id is required.', 400);
    }

    $studentId = (int) $studentId;
    $user = getCurrentUser();

    // Students can only delete their own evaluations; admins can delete any
    if (!isAdmin() && $user['student_id'] !== $studentId) {
        jsonResponse(false, null, 'Access denied. You can only manage your own evaluations.', 403);
    }

    try {
        $pdo = Database::getConnection();

        if (!empty($specId)) {
            // Delete evaluations for specific student + specialization
            $stmt = $pdo->prepare(
                "DELETE FROM evaluations
                 WHERE student_id = :student_id AND specialization_id = :specialization_id"
            );
            $stmt->execute([
                ':student_id'        => $studentId,
                ':specialization_id' => (int) $specId,
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
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
    }
}
