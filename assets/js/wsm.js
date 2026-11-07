/* ============================================================
   DSS University — Weighted Sum Model (WSM) Calculator
   ============================================================ */

/**
 * Calculate WSM results from evaluations, criteria, and specializations.
 *
 * @param {Object[]} evaluations   - Array of { specialization_id, criterion_id, score }
 * @param {Object[]} criteria      - Array of { id, name, type ('maximize'|'minimize'), weight }
 * @param {Object[]} specializations - Array of { id, name }
 * @returns {Object} { ranked: [...], matrix: {...}, normalized: {...}, weighted: {...} }
 */
function calculateWSM(evaluations, criteria, specializations) {
  if (!evaluations?.length || !criteria?.length || !specializations?.length) {
    return { ranked: [], matrix: {}, normalized: {}, weighted: {} };
  }

  // 1. Build evaluation matrix: matrix[specId][critId] = score
  const matrix = {};
  specializations.forEach((s) => {
    matrix[s.id] = {};
    criteria.forEach((c) => {
      matrix[s.id][c.id] = 0;
    });
  });

  evaluations.forEach((ev) => {
    if (matrix[ev.specialization_id] !== undefined) {
      matrix[ev.specialization_id][ev.criterion_id] = Number(ev.score) || 0;
    }
  });

  // 2. Find max and min per criterion column
  const columnStats = {};
  criteria.forEach((c) => {
    const values = specializations.map((s) => matrix[s.id][c.id]).filter((v) => v > 0);
    columnStats[c.id] = {
      max: values.length ? Math.max(...values) : 1,
      min: values.length ? Math.min(...values) : 1,
    };
  });

  // 3. Normalize
  const normalized = {};
  specializations.forEach((s) => {
    normalized[s.id] = {};
    criteria.forEach((c) => {
      const value = matrix[s.id][c.id];
      const { max, min } = columnStats[c.id];

      if (value === 0) {
        normalized[s.id][c.id] = 0;
      } else if (c.type === 'minimize') {
        // For minimize criteria: min_in_column / value
        normalized[s.id][c.id] = min / value;
      } else {
        // For maximize criteria: value / max_in_column
        normalized[s.id][c.id] = value / max;
      }
    });
  });

  // 4. Apply weights
  const weighted = {};
  const totalWeight = criteria.reduce((sum, c) => sum + Number(c.weight), 0) || 1;

  specializations.forEach((s) => {
    weighted[s.id] = {};
    criteria.forEach((c) => {
      const normalizedWeight = Number(c.weight) / totalWeight;
      weighted[s.id][c.id] = normalized[s.id][c.id] * normalizedWeight;
    });
  });

  // 5. Sum weighted scores per specialization
  const scores = specializations.map((s) => {
    const totalScore = criteria.reduce((sum, c) => sum + weighted[s.id][c.id], 0);
    return {
      specialization_id: s.id,
      specialization_name: s.name,
      score: Math.round(totalScore * 10000) / 10000,
      criteriaScores: { ...weighted[s.id] },
      rawScores: { ...matrix[s.id] },
      normalizedScores: { ...normalized[s.id] },
    };
  });

  // 6. Sort descending
  scores.sort((a, b) => b.score - a.score);

  // 7. Assign ranks & detect ties
  let currentRank = 1;
  scores.forEach((item, index) => {
    if (index > 0 && item.score === scores[index - 1].score) {
      item.rank = scores[index - 1].rank;
      item.isTied = true;
      scores[index - 1].isTied = true;
    } else {
      item.rank = currentRank;
      item.isTied = false;
    }
    currentRank = index + 2;
  });

  return {
    ranked: scores,
    matrix,
    normalized,
    weighted,
  };
}

// Make available globally
window.wsm = { calculateWSM };
