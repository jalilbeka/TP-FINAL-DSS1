<?php
/**
 * DSS Calculation API - Weighted Sum Model (WSM)
 *
 * Core decision support calculation endpoint.
 * Implements the Weighted Sum Model to rank specializations for a student.
 *
 * Algorithm:
 *   1. Fetch all evaluations for the student
 *   2. Normalize scores per criterion column:
 *      - Maximize criteria: score / max_in_column
 *      - Minimize criteria: min_in_column / score
 *   3. Multiply normalized scores by criterion weights
 *   4. Sum weighted scores per specialization → overall score
 *   5. Rank specializations by overall score DESC
 *   6. Detect ties (identical overall scores)
 *
 * Endpoint:
 *   GET ?student_id=N  - Calculate WSM ranking for a student
 *
 * @package DSS University
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

setCorsHeaders();
startSession();
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, null, 'Method not allowed. Use GET.', 405);
}

$studentId = $_GET['student_id'] ?? null;

if (empty($studentId)) {
    jsonResponse(false, null, 'student_id parameter is required.', 400);
}

$studentId = (int) $studentId;
$user = getCurrentUser();

// Students can only calculate for themselves; admins can calculate for anyone
if (!isAdmin() && $user['student_id'] !== $studentId) {
    jsonResponse(false, null, 'Access denied. You can only view your own results.', 403);
}

try {
    $pdo = Database::getConnection();

    // -----------------------------------------------------------------------
    // 1. Verify student exists
    // -----------------------------------------------------------------------
    $stmt = $pdo->prepare(
        "SELECT id, first_name, last_name, email FROM students WHERE id = :id"
    );
    $stmt->execute([':id' => $studentId]);
    $student = $stmt->fetch();

    if (!$student) {
        jsonResponse(false, null, 'Student not found.', 404);
    }

    // -----------------------------------------------------------------------
    // 2. Fetch all criteria (for weights and types)
    // -----------------------------------------------------------------------
    $stmt = $pdo->query("SELECT id, name_en, name_fr, type, weight FROM criteria ORDER BY id");
    $criteriaRows = $stmt->fetchAll();

    if (empty($criteriaRows)) {
        jsonResponse(false, null, 'No criteria defined. Please add criteria first.', 400);
    }

    $criteria = [];
    foreach ($criteriaRows as $c) {
        $criteria[(int) $c['id']] = [
            'id'      => (int) $c['id'],
            'name_en' => $c['name_en'],
            'name_fr' => $c['name_fr'],
            'type'    => $c['type'],
            'weight'  => (float) $c['weight'],
        ];
    }

    // -----------------------------------------------------------------------
    // 3. Fetch all evaluations for this student
    // -----------------------------------------------------------------------
    $stmt = $pdo->prepare(
        "SELECT e.specialization_id, e.criterion_id, e.score,
                sp.code, sp.name_en AS spec_name_en, sp.name_fr AS spec_name_fr
         FROM evaluations e
         JOIN specializations sp ON sp.id = e.specialization_id
         WHERE e.student_id = :student_id
         ORDER BY sp.code, e.criterion_id"
    );
    $stmt->execute([':student_id' => $studentId]);
    $evaluations = $stmt->fetchAll();

    if (empty($evaluations)) {
        jsonResponse(false, null, 'No evaluations found for this student. Please complete the evaluation matrix first.', 400);
    }

    // -----------------------------------------------------------------------
    // 4. Build evaluation matrix and compute column min/max for normalization
    // -----------------------------------------------------------------------
    // Structure: $matrix[spec_id][criterion_id] = raw_score
    $matrix          = [];
    $specializations = [];
    $columnMax       = []; // max score per criterion (across all specializations)
    $columnMin       = []; // min score per criterion (across all specializations)

    foreach ($evaluations as $eval) {
        $specId = (int) $eval['specialization_id'];
        $critId = (int) $eval['criterion_id'];
        $score  = (float) $eval['score'];

        // Track specialization info
        if (!isset($specializations[$specId])) {
            $specializations[$specId] = [
                'id'      => $specId,
                'code'    => $eval['code'],
                'name_en' => $eval['spec_name_en'],
                'name_fr' => $eval['spec_name_fr'],
            ];
        }

        $matrix[$specId][$critId] = $score;

        // Track column min/max
        if (!isset($columnMax[$critId]) || $score > $columnMax[$critId]) {
            $columnMax[$critId] = $score;
        }
        if (!isset($columnMin[$critId]) || $score < $columnMin[$critId]) {
            $columnMin[$critId] = $score;
        }
    }

    // -----------------------------------------------------------------------
    // 5. Normalize scores and calculate weighted sums
    // -----------------------------------------------------------------------
    $results = [];

    foreach ($matrix as $specId => $scores) {
        $specScores   = [];
        $overallScore = 0.0;

        foreach ($scores as $critId => $rawScore) {
            if (!isset($criteria[$critId])) {
                continue; // Skip unknown criteria
            }

            $crit      = $criteria[$critId];
            $maxInCol  = $columnMax[$critId];
            $minInCol  = $columnMin[$critId];

            // Normalize based on criterion type
            if ($crit['type'] === 'maximize') {
                // Higher is better: score / max_in_column
                $normalized = ($maxInCol > 0) ? $rawScore / $maxInCol : 0.0;
            } else {
                // Minimize: lower is better: min_in_column / score
                $normalized = ($rawScore > 0) ? $minInCol / $rawScore : 0.0;
            }

            // Weighted score
            $weighted = $normalized * $crit['weight'];
            $overallScore += $weighted;

            $specScores[$critId] = [
                'criterion_id'   => $critId,
                'criterion_name' => $crit['name_en'],
                'criterion_type' => $crit['type'],
                'weight'         => $crit['weight'],
                'raw'            => round($rawScore, 2),
                'normalized'     => round($normalized, 4),
                'weighted'       => round($weighted, 4),
            ];
        }

        $results[] = [
            'id'            => $specId,
            'code'          => $specializations[$specId]['code'],
            'name_en'       => $specializations[$specId]['name_en'],
            'name_fr'       => $specializations[$specId]['name_fr'],
            'scores'        => $specScores,
            'overall_score' => round($overallScore, 4),
            'rank'          => 0, // Will be set after sorting
        ];
    }

    // -----------------------------------------------------------------------
    // 6. Rank specializations by overall score (descending)
    // -----------------------------------------------------------------------
    usort($results, function ($a, $b) {
        return $b['overall_score'] <=> $a['overall_score'];
    });

    // Assign ranks (handle ties: same score = same rank)
    $currentRank = 1;
    $hasTies     = false;
    $tiedSpecs   = [];

    for ($i = 0; $i < count($results); $i++) {
        if ($i > 0 && $results[$i]['overall_score'] === $results[$i - 1]['overall_score']) {
            // Tie: same rank as previous
            $results[$i]['rank'] = $results[$i - 1]['rank'];
            $hasTies = true;

            // Track tied specializations
            if (!in_array($results[$i - 1]['code'], array_column($tiedSpecs, 'code'))) {
                $tiedSpecs[] = [
                    'id'      => $results[$i - 1]['id'],
                    'code'    => $results[$i - 1]['code'],
                    'name_en' => $results[$i - 1]['name_en'],
                    'name_fr' => $results[$i - 1]['name_fr'],
                    'score'   => $results[$i - 1]['overall_score'],
                ];
            }
            $tiedSpecs[] = [
                'id'      => $results[$i]['id'],
                'code'    => $results[$i]['code'],
                'name_en' => $results[$i]['name_en'],
                'name_fr' => $results[$i]['name_fr'],
                'score'   => $results[$i]['overall_score'],
            ];
        } else {
            $results[$i]['rank'] = $currentRank;
        }
        $currentRank++;
    }

    // -----------------------------------------------------------------------
    // 7. Build recommendation
    // -----------------------------------------------------------------------
    $recommendation = null;
    if (!empty($results)) {
        $top = $results[0];
        $recommendation = [
            'id'      => $top['id'],
            'code'    => $top['code'],
            'name_en' => $top['name_en'],
            'name_fr' => $top['name_fr'],
            'score'   => $top['overall_score'],
        ];
    }

    // -----------------------------------------------------------------------
    // 8. Return results
    // -----------------------------------------------------------------------
    jsonResponse(true, [
        'student'              => $student,
        'specializations'      => $results,
        'recommendation'       => $recommendation,
        'has_ties'             => $hasTies,
        'tied_specializations' => $tiedSpecs,
        'criteria_used'        => array_values($criteria),
        'method'               => 'Weighted Sum Model (WSM)',
    ], 'DSS calculation completed successfully.');

} catch (PDOException $e) {
    jsonResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
}
