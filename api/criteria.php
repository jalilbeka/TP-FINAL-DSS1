<?php
/**
 * Criteria API
 *
 * CRUD operations for decision criteria.
 * - All authenticated users can read criteria.
 * - Only admins can create, update, or delete.
 *
 * Endpoints:
 *   GET                 - List all criteria with type and weight
 *   POST action=create  - Add a new criterion (admin only)
 *   POST action=update  - Update a criterion (admin only)
 *   POST action=delete  - Delete a criterion (admin only)
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
 * List all criteria with type (maximize/minimize) and weight.
 *
 * @return never
 */
function handleList(): never
{
    try {
        $pdo  = Database::getConnection();
        $stmt = $pdo->query(
            "SELECT id, name_en, name_fr, type, weight, created_at
             FROM criteria
             ORDER BY id"
        );
        $criteria = $stmt->fetchAll();

        // Cast numeric fields
        foreach ($criteria as &$c) {
            $c['id']     = (int) $c['id'];
            $c['weight'] = (float) $c['weight'];
        }
        unset($c);

        jsonResponse(true, $criteria, 'Criteria retrieved successfully.');
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
    }
}

/**
 * Create a new criterion. Admin only.
 *
 * @param array $input Request data
 * @return never
 */
function handleCreate(array $input): never
{
    requireAdmin();

    $nameEn = trim(getInput($input, 'name_en', ''));
    $nameFr = trim(getInput($input, 'name_fr', ''));
    $type   = getInput($input, 'type', 'maximize');
    $weight = getInput($input, 'weight', 1.00);

    if (empty($nameEn) || empty($nameFr)) {
        jsonResponse(false, null, 'English name and French name are required.', 400);
    }

    if (!in_array($type, ['maximize', 'minimize'], true)) {
        jsonResponse(false, null, 'Type must be "maximize" or "minimize".', 400);
    }

    $weight = (float) $weight;
    if ($weight < 0.00 || $weight > 1.00) {
        jsonResponse(false, null, 'Weight must be between 0.00 and 1.00.', 400);
    }

    try {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO criteria (name_en, name_fr, type, weight)
             VALUES (:name_en, :name_fr, :type, :weight)"
        );
        $stmt->execute([
            ':name_en' => $nameEn,
            ':name_fr' => $nameFr,
            ':type'    => $type,
            ':weight'  => $weight,
        ]);

        $id = (int) $pdo->lastInsertId();

        jsonResponse(true, [
            'id'      => $id,
            'name_en' => $nameEn,
            'name_fr' => $nameFr,
            'type'    => $type,
            'weight'  => $weight,
        ], 'Criterion created successfully.', 201);
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
    }
}

/**
 * Update an existing criterion. Admin only.
 *
 * @param array $input Request data with 'id' and fields to update
 * @return never
 */
function handleUpdate(array $input): never
{
    requireAdmin();

    $id     = getInput($input, 'id');
    $nameEn = trim(getInput($input, 'name_en', ''));
    $nameFr = trim(getInput($input, 'name_fr', ''));
    $type   = getInput($input, 'type', 'maximize');
    $weight = getInput($input, 'weight', 1.00);

    if (empty($id)) {
        jsonResponse(false, null, 'Criterion ID is required.', 400);
    }

    if (empty($nameEn) || empty($nameFr)) {
        jsonResponse(false, null, 'English name and French name are required.', 400);
    }

    if (!in_array($type, ['maximize', 'minimize'], true)) {
        jsonResponse(false, null, 'Type must be "maximize" or "minimize".', 400);
    }

    $weight = (float) $weight;
    if ($weight < 0.00 || $weight > 1.00) {
        jsonResponse(false, null, 'Weight must be between 0.00 and 1.00.', 400);
    }

    try {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "UPDATE criteria
             SET name_en = :name_en, name_fr = :name_fr, type = :type, weight = :weight
             WHERE id = :id"
        );
        $stmt->execute([
            ':id'      => (int) $id,
            ':name_en' => $nameEn,
            ':name_fr' => $nameFr,
            ':type'    => $type,
            ':weight'  => $weight,
        ]);

        if ($stmt->rowCount() === 0) {
            jsonResponse(false, null, 'Criterion not found or no changes made.', 404);
        }

        jsonResponse(true, [
            'id'      => (int) $id,
            'name_en' => $nameEn,
            'name_fr' => $nameFr,
            'type'    => $type,
            'weight'  => $weight,
        ], 'Criterion updated successfully.');
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
    }
}

/**
 * Delete a criterion. Admin only.
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
        jsonResponse(false, null, 'Criterion ID is required.', 400);
    }

    try {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM criteria WHERE id = :id");
        $stmt->execute([':id' => (int) $id]);

        if ($stmt->rowCount() === 0) {
            jsonResponse(false, null, 'Criterion not found.', 404);
        }

        jsonResponse(true, null, 'Criterion and associated evaluations deleted successfully.');
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
    }
}
