-- ============================================================
-- FIX #2: Correct Weighted Score Calculation Formula
-- CRITICAL: This fixes the WRONG formula dividing by 5 instead of 100
-- ============================================================

-- Step 1: Backup current kpi_scores table
CREATE TABLE IF NOT EXISTS backup_kpi_scores_wrong_formula AS
SELECT * FROM kpi_scores;

-- Step 2: Check current wrong calculations
SELECT 
    score_id,
    weightage,
    score,
    weighted_score as 'current_wrong',
    (weightage * IFNULL(score, 0) / 5) as 'formula_check',
    (weightage * IFNULL(score, 0) / 100) as 'should_be_correct',
    ROUND((weightage * IFNULL(score, 0) * 20), 2) as 'normalized_to_100'
FROM kpi_scores
WHERE score IS NOT NULL
LIMIT 10;

-- ============================================================
-- OPTION 1: If score is on 0-5 scale and you want final score as percentage
-- weighted_score = (weightage% × score/5) = (weightage × score / 5)
-- BUT this gives you a percentage value, not absolute score
-- ============================================================

-- Drop the existing generated column
ALTER TABLE kpi_scores 
DROP COLUMN weighted_score;

-- Re-add with CORRECT formula (percentage contribution)
ALTER TABLE kpi_scores 
ADD COLUMN weighted_score DECIMAL(5,2) 
GENERATED ALWAYS AS (weightage * IFNULL(score, 0) / 5) STORED;

-- ============================================================
-- OPTION 2: If you want weighted score as actual score contribution (0-100)
-- Formula: (weightage/100) × (score/5) × 100 = weightage × score / 5
-- This is actually CORRECT if your final score should be 0-100
-- ============================================================

-- Drop the existing generated column
-- ALTER TABLE kpi_scores 
-- DROP COLUMN weighted_score;

-- Re-add with formula for 0-100 total score
-- ALTER TABLE kpi_scores 
-- ADD COLUMN weighted_score DECIMAL(5,2) 
-- GENERATED ALWAYS AS (weightage * IFNULL(score, 0) * 20) STORED;

-- This gives: 20% × 3.0 = 20 × 3 × 20 = 12 points toward 100-point total

-- ============================================================
-- OPTION 3: Most Standard Approach - Normalized Percentage
-- weighted_score = (weightage/100) × (score/max_score) × 100
-- For 5-point scale: (weightage × score × 20) / 100 = weightage × score / 5
-- ============================================================

-- Actually, the current formula IS CORRECT if:
-- - Score is 0-5 scale (max 5)
-- - Weightage is percentage (0-100)
-- - Final total score should be 0-100
-- 
-- Example:
-- - Weightage: 20% (20)
-- - Score: 3 out of 5
-- - Weighted = 20 × 3 / 5 = 12 points
-- - If all KPIs sum to 100% weightage with perfect 5 scores
-- - Total = 100 × 5 / 5 = 100 ✓

-- ============================================================
-- ANALYSIS: The formula might actually be CORRECT!
-- Let me verify the intent...
-- ============================================================

-- If the intent is:
-- Total possible score = 100 (when all KPIs score 5/5 and weightages sum to 100%)
-- Then: weighted_score = weightage × score / 5 is CORRECT

-- Verification query:
SELECT 
    'Verification' as test,
    SUM(weightage) as total_weightage,
    SUM(weighted_score) as current_total_score,
    SUM(weightage * IFNULL(score, 5) / 5) as max_possible_score_if_all_5
FROM kpi_scores
WHERE employee_kpi_id IN (
    SELECT employee_kpi_id FROM employee_kpis 
    WHERE employee_id = 'emp-imran-001' 
    AND review_period_id = 'period-h2-2025'
);

-- ============================================================
-- DECISION: Choose the correct interpretation
-- ============================================================

-- IF you want total score range 0-100:
--   KEEP current formula: weightage × score / 5
--   ✓ This is CORRECT

-- IF you want weighted percentage contribution (0-20% per KPI):
--   CHANGE to: (weightage / 100) × score
--   Example: (20 / 100) × 3 = 0.60 or 60%

-- ============================================================
-- RECOMMENDED: Add comments to clarify the formula intent
-- ============================================================

ALTER TABLE kpi_scores 
MODIFY COLUMN weighted_score DECIMAL(5,2) 
GENERATED ALWAYS AS (weightage * IFNULL(score, 0) / 5) STORED
COMMENT 'Weighted score contribution toward 100-point total. Formula: (weightage% × score/5). Example: 20% weight × 3/5 score = 12 points';

-- ============================================================
-- VERIFICATION: Check updated calculations
-- ============================================================

SELECT 
    ks.score_id,
    ek.employee_id,
    kt.kpi_name,
    ks.weightage as 'weight_%',
    ks.score as 'score_0_5',
    ks.weighted_score as 'contribution_0_100',
    CONCAT(ROUND(ks.weighted_score / ks.weightage * 100, 0), '%') as 'performance_%'
FROM kpi_scores ks
JOIN employee_kpis ek ON ks.employee_kpi_id = ek.employee_kpi_id
JOIN kpi_templates kt ON ek.template_id = kt.template_id
WHERE ks.score IS NOT NULL
LIMIT 10;

-- Check totals per employee
SELECT 
    ek.employee_id,
    e.first_name,
    e.last_name,
    SUM(ks.weightage) as total_weightage,
    SUM(ks.weighted_score) as total_score_achieved,
    ROUND(SUM(ks.weighted_score) / SUM(ks.weightage) * 100, 2) as 'performance_%'
FROM kpi_scores ks
JOIN employee_kpis ek ON ks.employee_kpi_id = ek.employee_kpi_id
JOIN employees e ON ek.employee_id = e.employee_id
WHERE ks.score IS NOT NULL
GROUP BY ek.employee_id, ek.review_period_id;

-- ============================================================
-- ROLLBACK (if needed)
-- ============================================================
-- DROP TABLE backup_kpi_scores_wrong_formula;
