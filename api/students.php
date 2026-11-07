<?php
/**
 * Students API
 *
 * CRUD operations for student profiles.
 * - Admin can manage all students.
 * - Students can view their own profile only.
 *
 * Endpoints:
 *   GET                 - List students (admin: all, student: own)
 *   POST action=create  - Create a new student (admin only)
 *   POST action=update  - Update a student (admin only)
 *   POST action=delete  - Delete a student and their evaluations (admin only)
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
        handleList();
        break;
    case 'POST':
        $action = getInput($input, 'action', '');
        switch ($action) {
            case 'create':
                handleCreate($input);
                break;
            case 'update':
                handleUpdate($input);
                break;
            case 'delete':
                handleDelete($input);
                break;
            default:
                jsonResponse(false, null, 'Invalid action. Use: create, update, or delete.', 400);
        }
        break;
    default:
        jsonResponse(false, null, 'Method not allowed.', 405);
}

// ===========================================================================
// Action Handlers
// ===========================================================================

/**
 * List students. Admin sees all; students see only their own profile.
 *
 * @return never
 */
function handleList(): never
{
    try {
        $pdo  = Database::getConnection();
        $user = getCurrentUser();

        if (isAdmin()) {
            // Admin: list all students
            $stmt = $pdo->query(
                "SELECT s.id, s.user_id, s.first_name, s.last_name, s.email, s.created_at,
                        u.username
                 FROM students s
                 LEFT JOIN users u ON u.id = s.user_id
                 ORDER BY s.last_name, s.first_name"
            );
            $students = $stmt->fetchAll();
        } else {
            // Student: only their own profile
            $stmt = $pdo->prepare(
                "SELECT s.id, s.user_id, s.first_name, s.last_name, s.email, s.created_at,
                        u.username
                 FROM students s
                 LEFT JOIN users u ON u.id = s.user_id
                 WHERE s.user_id = :user_id"
            );
            $stmt->execute([':user_id' => $user['user_id']]);
            $students = $stmt->fetchAll();
        }

        jsonResponse(true, $students, 'Students retrieved successfully.');
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
    }
}

/**
 * Create a new student profile. Admin only.
 *
 * @param array $input Request data with 'first_name', 'last_name', 'email', optional 'user_id'
 * @return never
 */
function handleCreate(array $input): never
{
    requireAdmin();

    $firstName = trim(getInput($input, 'first_name', ''));
    $lastName  = trim(getInput($input, 'last_name', ''));
    $email     = trim(getInput($input, 'email', ''));
    $userId    = getInput($input, 'user_id');

    if (empty($firstName) || empty($lastName) || empty($email)) {
        jsonResponse(false, null, 'First name, last name, and email are required.', 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, null, 'Invalid email address.', 400);
    }

    try {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO students (user_id, first_name, last_name, email)
             VALUES (:user_id, :first_name, :last_name, :email)"
        );
        $stmt->execute([
            ':user_id'    => $userId ? (int) $userId : null,
            ':first_name' => $firstName,
            ':last_name'  => $lastName,
            ':email'      => $email,
        ]);

        $studentId = (int) $pdo->lastInsertId();

        jsonResponse(true, [
            'id'         => $studentId,
            'user_id'    => $userId ? (int) $userId : null,
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
        ], 'Student created successfully.', 201);
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
    }
}

/**
 * Update an existing student profile. Admin only.
 *
 * @param array $input Request data with 'id', 'first_name', 'last_name', 'email'
 * @return never
 */
function handleUpdate(array $input): never
{
    requireAdmin();

    $id        = getInput($input, 'id');
    $firstName = trim(getInput($input, 'first_name', ''));
    $lastName  = trim(getInput($input, 'last_name', ''));
    $email     = trim(getInput($input, 'email', ''));

    if (empty($id)) {
        jsonResponse(false, null, 'Student ID is required.', 400);
    }

    if (empty($firstName) || empty($lastName) || empty($email)) {
        jsonResponse(false, null, 'First name, last name, and email are required.', 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, null, 'Invalid email address.', 400);
    }

    try {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "UPDATE students
             SET first_name = :first_name, last_name = :last_name, email = :email
             WHERE id = :id"
        );
        $stmt->execute([
            ':id'         => (int) $id,
            ':first_name' => $firstName,
            ':last_name'  => $lastName,
            ':email'      => $email,
        ]);

        if ($stmt->rowCount() === 0) {
            jsonResponse(false, null, 'Student not found or no changes made.', 404);
        }

        jsonResponse(true, [
            'id'         => (int) $id,
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
        ], 'Student updated successfully.');
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
    }
}

/**
 * Delete a student and all their evaluations (cascade). Admin only.
 *
 * @param array $input Request data with 'id'
 * @return never
 */
function handleDelete(array $input): never
{
    requireAdmin();

    $id = getInput($input, 'id');

    if (empty($id)) {
        jsonResponse(false, null, 'Student ID is required.', 400);
    }

    try {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = :id");
        $stmt->execute([':id' => (int) $id]);

        if ($stmt->rowCount() === 0) {
            jsonResponse(false, null, 'Student not found.', 404);
        }

        jsonResponse(true, null, 'Student and associated evaluations deleted successfully.');
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
    }
}
