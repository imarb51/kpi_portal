-- ============================================================
-- FIX #6: Clarify Score Scale and Add Documentation
-- Document the 0-5 scoring system
-- ============================================================

-- Step 1: Add table and column comments for clarity
ALTER TABLE kpi_scores 
MODIFY COLUMN score DECIMAL(3,2) DEFAULT NULL
COMMENT 'Employee performance score on 0-5 scale (0=Poor, 1=Below Expectations, 2=Meets Expectations, 3=Good, 4=Excellent, 5=Outstanding)';

ALTER TABLE kpi_scores 
MODIFY COLUMN weightage DECIMAL(5,2) NOT NULL DEFAULT 0.00
COMMENT 'Weight percentage of this KPI (0-100%). Total weightages per employee per period must equal 100%';

ALTER TABLE kpi_scores 
MODIFY COLUMN weighted_score DECIMAL(5,2) 
GENERATED ALWAYS AS (weightage * IFNULL(score, 0) / 5) STORED
COMMENT 'Weighted contribution to final score (0-100 scale). Formula: (weightage × score ÷ 5). Sum of all weighted_scores gives final performance score out of 100';

-- ============================================================
-- Step 2: Create a reference table for score meanings
-- ============================================================

CREATE TABLE IF NOT EXISTS kpi_score_scale (
    score_value DECIMAL(3,2) PRIMARY KEY,
    rating_label VARCHAR(50) NOT NULL,
    description TEXT,
    color_code VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Reference table defining the 0-5 KPI score scale';

-- Insert standard 5-point scale definitions
INSERT INTO kpi_score_scale (score_value, rating_label, description, color_code) VALUES
(0.00, 'Not Started', 'Task not started or no effort demonstrated', '#dc3545'),
(1.00, 'Below Expectations', 'Performance significantly below expected standards', '#fd7e14'),
(2.00, 'Needs Improvement', 'Performance below expectations, requires development', '#ffc107'),
(3.00, 'Meets Expectations', 'Performance meets all expected standards', '#28a745'),
(4.00, 'Exceeds Expectations', 'Performance consistently exceeds standards', '#17a2b8'),
(5.00, 'Outstanding', 'Performance far exceeds all expectations', '#6f42c1')
ON DUPLICATE KEY UPDATE 
    rating_label = VALUES(rating_label),
    description = VALUES(description),
    color_code = VALUES(color_code);

-- ============================================================
-- Step 3: Add performance grade reference table
-- ============================================================

CREATE TABLE IF NOT EXISTS performance_grade_scale (
    grade_label VARCHAR(10) PRIMARY KEY,
    min_score DECIMAL(5,2) NOT NULL,
    max_score DECIMAL(5,2) NOT NULL,
    description TEXT,
    color_code VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Performance grade scale based on total weighted score (0-100)';

-- Insert grade definitions
INSERT INTO performance_grade_scale (grade_label, min_score, max_score, description, color_code) VALUES
('F', 0.00, 39.99, 'Poor Performance - Requires immediate improvement plan', '#dc3545'),
('D', 40.00, 59.99, 'Below Average - Performance needs significant improvement', '#fd7e14'),
('C', 60.00, 69.99, 'Average - Meets minimum expectations', '#ffc107'),
('B', 70.00, 79.99, 'Good - Solid performance, meets most expectations', '#28a745'),
('A', 80.00, 89.99, 'Excellent - Exceeds expectations consistently', '#17a2b8'),
('A+', 90.00, 100.00, 'Outstanding - Exceptional performance', '#6f42c1')
ON DUPLICATE KEY UPDATE 
    min_score = VALUES(min_score),
    max_score = VALUES(max_score),
    description = VALUES(description),
    color_code = VALUES(color_code);

-- ============================================================
-- Step 4: Create view for easy score interpretation
-- ============================================================

CREATE OR REPLACE VIEW v_employee_performance_summary AS
SELECT 
    ek.employee_id,
    e.employee_code,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    rp.period_name,
    rp.period_id,
    COUNT(ek.employee_kpi_id) as total_kpis,
    SUM(ks.weightage) as total_weightage,
    ROUND(AVG(ks.score), 2) as avg_score_0_5,
    ROUND(SUM(ks.weighted_score), 2) as final_score_0_100,
    CASE 
        WHEN SUM(ks.weighted_score) >= 90 THEN 'A+'
        WHEN SUM(ks.weighted_score) >= 80 THEN 'A'
        WHEN SUM(ks.weighted_score) >= 70 THEN 'B'
        WHEN SUM(ks.weighted_score) >= 60 THEN 'C'
        WHEN SUM(ks.weighted_score) >= 40 THEN 'D'
        ELSE 'F'
    END as performance_grade,
    CASE 
        WHEN SUM(ks.weighted_score) >= 90 THEN 'Outstanding'
        WHEN SUM(ks.weighted_score) >= 80 THEN 'Excellent'
        WHEN SUM(ks.weighted_score) >= 70 THEN 'Good'
        WHEN SUM(ks.weighted_score) >= 60 THEN 'Average'
        WHEN SUM(ks.weighted_score) >= 40 THEN 'Below Average'
        ELSE 'Poor Performance'
    END as performance_label,
    COUNT(CASE WHEN ks.score >= 4 THEN 1 END) as kpis_exceeding,
    COUNT(CASE WHEN ks.score >= 3 THEN 1 END) as kpis_meeting,
    COUNT(CASE WHEN ks.score < 3 THEN 1 END) as kpis_below,
    ek.is_finalized,
    ek.finalized_at
FROM employee_kpis ek
JOIN employees e ON ek.employee_id = e.employee_id
JOIN review_periods rp ON ek.review_period_id = rp.period_id
LEFT JOIN kpi_scores ks ON ek.employee_kpi_id = ks.employee_kpi_id
WHERE ks.score IS NOT NULL
GROUP BY ek.employee_id, ek.review_period_id;

-- ============================================================
-- Step 5: Add validation function for score entry
-- ============================================================

DELIMITER $$

CREATE FUNCTION validate_kpi_score(p_score DECIMAL(3,2))
RETURNS BOOLEAN
DETERMINISTIC
COMMENT 'Validates if a KPI score is within valid range (0-5)'
BEGIN
    IF p_score IS NULL THEN
        RETURN TRUE; -- NULL is allowed (not yet scored)
    END IF;
    
    IF p_score < 0 OR p_score > 5 THEN
        RETURN FALSE;
    END IF;
    
    RETURN TRUE;
END$$

CREATE FUNCTION calculate_performance_grade(p_final_score DECIMAL(5,2))
RETURNS VARCHAR(10)
DETERMINISTIC
COMMENT 'Returns performance grade (A+, A, B, C, D, F) based on final score (0-100)'
BEGIN
    DECLARE v_grade VARCHAR(10);
    
    IF p_final_score >= 90 THEN SET v_grade = 'A+';
    ELSEIF p_final_score >= 80 THEN SET v_grade = 'A';
    ELSEIF p_final_score >= 70 THEN SET v_grade = 'B';
    ELSEIF p_final_score >= 60 THEN SET v_grade = 'C';
    ELSEIF p_final_score >= 40 THEN SET v_grade = 'D';
    ELSE SET v_grade = 'F';
    END IF;
    
    RETURN v_grade;
END$$

DELIMITER ;

-- ============================================================
-- VERIFICATION: Test the scale and views
-- ============================================================

-- View score scale definitions
SELECT * FROM kpi_score_scale ORDER BY score_value;

-- View grade scale
SELECT * FROM performance_grade_scale ORDER BY min_score;

-- View employee performance summary
SELECT * FROM v_employee_performance_summary 
ORDER BY final_score_0_100 DESC;

-- Test validation function
SELECT 
    validate_kpi_score(3.5) as 'valid_score_3.5',
    validate_kpi_score(6.0) as 'invalid_score_6',
    validate_kpi_score(NULL) as 'null_allowed';

-- Test grade function
SELECT 
    calculate_performance_grade(95) as 'grade_A_plus',
    calculate_performance_grade(75) as 'grade_B',
    calculate_performance_grade(35) as 'grade_F';

-- ============================================================
-- Step 6: Add constraints to ensure data quality
-- ============================================================

-- Ensure weightages sum to 100% per employee per period
DELIMITER $$

CREATE TRIGGER check_weightage_sum_before_insert
BEFORE INSERT ON kpi_scores
FOR EACH ROW
BEGIN
    DECLARE v_employee_id VARCHAR(36);
    DECLARE v_period_id VARCHAR(36);
    DECLARE v_total_weightage DECIMAL(5,2);
    
    -- Get employee and period
    SELECT employee_id, review_period_id 
    INTO v_employee_id, v_period_id
    FROM employee_kpis 
    WHERE employee_kpi_id = NEW.employee_kpi_id;
    
    -- Calculate total weightage including new one
    SELECT COALESCE(SUM(ks.weightage), 0) + NEW.weightage
    INTO v_total_weightage
    FROM kpi_scores ks
    JOIN employee_kpis ek ON ks.employee_kpi_id = ek.employee_kpi_id
    WHERE ek.employee_id = v_employee_id
    AND ek.review_period_id = v_period_id
    AND ks.employee_kpi_id != NEW.employee_kpi_id;
    
    -- Allow if less than or equal to 100%
    IF v_total_weightage > 100 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Total weightage exceeds 100% for this employee in this period';
    END IF;
END$$

DELIMITER ;

-- ============================================================
-- ROLLBACK (if needed)
-- ============================================================

-- DROP TABLE IF EXISTS kpi_score_scale;
-- DROP TABLE IF EXISTS performance_grade_scale;
-- DROP VIEW IF EXISTS v_employee_performance_summary;
-- DROP FUNCTION IF EXISTS validate_kpi_score;
-- DROP FUNCTION IF EXISTS calculate_performance_grade;
-- DROP TRIGGER IF EXISTS check_weightage_sum_before_insert;
