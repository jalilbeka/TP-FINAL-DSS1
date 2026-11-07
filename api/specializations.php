<?php
/**
 * Specializations API
 *
 * CRUD operations for university specialization programs.
 * - All authenticated users can read specializations.
 * - Only admins can create, update, or delete.
 *
 * Endpoints:
 *   GET                 - List all specializations
 *   POST action=create  - Add a new specialization (admin only)
 *   POST action=update  - Update a specialization (admin only)
 *   POST action=delete  - Delete a specialization (admin only)
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
 * List all specializations with bilingual names and descriptions.
 *
 * @return never
 */
function handleList(): never
{
    try {
        $pdo  = Database::getConnection();
        $stmt = $pdo->query(
            "SELECT id, code, name_en, name_fr, description_en, description_fr, created_at
             FROM specializations
             ORDER BY code"
        );
        $specializations = $stmt->fetchAll();

        jsonResponse(true, $specializations, 'Specializations retrieved successfully.');
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
    }
}

/**
 * Create a new specialization. Admin only.
 *
 * @param array $input Request data
 * @return never
 */
function handleCreate(array $input): never
{
    requireAdmin();

    $code          = trim(getInput($input, 'code', ''));
    $nameEn        = trim(getInput($input, 'name_en', ''));
    $nameFr        = trim(getInput($input, 'name_fr', ''));
    $descriptionEn = trim(getInput($input, 'description_en', ''));
    $descriptionFr = trim(getInput($input, 'description_fr', ''));

    if (empty($code) || empty($nameEn) || empty($nameFr)) {
        jsonResponse(false, null, 'Code, English name, and French name are required.', 400);
    }

    try {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO specializations (code, name_en, name_fr, description_en, description_fr)
             VALUES (:code, :name_en, :name_fr, :description_en, :description_fr)"
        );
        $stmt->execute([
            ':code'           => strtoupper($code),
            ':name_en'        => $nameEn,
            ':name_fr'        => $nameFr,
            ':description_en' => $descriptionEn,
            ':description_fr' => $descriptionFr,
        ]);

        $id = (int) $pdo->lastInsertId();

        jsonResponse(true, [
            'id'             => $id,
            'code'           => strtoupper($code),
            'name_en'        => $nameEn,
            'name_fr'        => $nameFr,
            'description_en' => $descriptionEn,
            'description_fr' => $descriptionFr,
        ], 'Specialization created successfully.', 201);
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            jsonResponse(false, null, 'A specialization with this code already exists.', 409);
        }
        jsonResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
    }
}

/**
 * Update an existing specialization. Admin only.
 *
 * @param array $input Request data with 'id' and fields to update
 * @return never
 */
function handleUpdate(array $input): never
{
    requireAdmin();

    $id            = getInput($input, 'id');
    $code          = trim(getInput($input, 'code', ''));
    $nameEn        = trim(getInput($input, 'name_en', ''));
    $nameFr        = trim(getInput($input, 'name_fr', ''));
    $descriptionEn = trim(getInput($input, 'description_en', ''));
    $descriptionFr = trim(getInput($input, 'description_fr', ''));

    if (empty($id)) {
        jsonResponse(false, null, 'Specialization ID is required.', 400);
    }

    if (empty($code) || empty($nameEn) || empty($nameFr)) {
        jsonResponse(false, null, 'Code, English name, and French name are required.', 400);
    }

    try {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "UPDATE specializations
             SET code = :code, name_en = :name_en, name_fr = :name_fr,
                 description_en = :description_en, description_fr = :description_fr
             WHERE id = :id"
        );
        $stmt->execute([
            ':id'             => (int) $id,
            ':code'           => strtoupper($code),
            ':name_en'        => $nameEn,
            ':name_fr'        => $nameFr,
            ':description_en' => $descriptionEn,
            ':description_fr' => $descriptionFr,
        ]);

        if ($stmt->rowCount() === 0) {
            jsonResponse(false, null, 'Specialization not found or no changes made.', 404);
        }

        jsonResponse(true, [
            'id'             => (int) $id,
            'code'           => strtoupper($code),
            'name_en'        => $nameEn,
            'name_fr'        => $nameFr,
            'description_en' => $descriptionEn,
            'description_fr' => $descriptionFr,
        ], 'Specialization updated successfully.');
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            jsonResponse(false, null, 'A specialization with this code already exists.', 409);
        }
        jsonResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
    }
}

/**
 * Delete a specialization. Admin only.
 * Cascade deletes associated evaluations.
 *
 * @param array $input Request data with 'id'
 * @return never
 */
function handleDelete(array $input): never
{
    requireAdmin();

    $id = getInput($input, 'id');

    if (empty($id)) {
        jsonResponse(false, null, 'Specialization ID is required.', 400);
    }

    try {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM specializations WHERE id = :id");
        $stmt->execute([':id' => (int) $id]);

        if ($stmt->rowCount() === 0) {
            jsonResponse(false, null, 'Specialization not found.', 404);
        }

        jsonResponse(true, null, 'Specialization and associated evaluations deleted successfully.');
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
    }
}
