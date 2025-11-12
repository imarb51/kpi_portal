-- ============================================================
-- FIX #4: Remove Empty/Unused Tables
-- Clean up database schema
-- ============================================================

-- Step 1: Verify tables are actually empty
SELECT 
    'employee_kpi_assignments' as table_name,
    COUNT(*) as row_count,
    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS 'Size_MB'
FROM employee_kpi_assignments
UNION ALL
SELECT 
    'kpi_assignment_logs',
    COUNT(*),
    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2)
FROM kpi_assignment_logs
UNION ALL
SELECT 
    'performance_reviews',
    COUNT(*),
    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2)
FROM performance_reviews
UNION ALL
SELECT 
    'audit_logs',
    COUNT(*),
    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2)
FROM audit_logs;

-- ============================================================
-- Step 2: Backup table structures (just in case)
-- ============================================================

CREATE TABLE backup_employee_kpi_assignments_structure LIKE employee_kpi_assignments;
CREATE TABLE backup_kpi_assignment_logs_structure LIKE kpi_assignment_logs;
CREATE TABLE backup_performance_reviews_structure LIKE performance_reviews;
CREATE TABLE backup_audit_logs_structure LIKE audit_logs;

-- ============================================================
-- Step 3A: Remove employee_kpi_assignments (if truly unused)
-- ============================================================

-- Check if employee_kpis.assignment_id references this table
SELECT 
    COUNT(*) as non_null_assignments,
    COUNT(DISTINCT assignment_id) as unique_assignments
FROM employee_kpis 
WHERE assignment_id IS NOT NULL;

-- If count = 0, safe to drop

-- Drop foreign key in employee_kpis first
ALTER TABLE employee_kpis 
DROP FOREIGN KEY fk_employee_kpis_assignment;

-- Drop the unused column
ALTER TABLE employee_kpis 
DROP COLUMN assignment_id;

-- Drop the empty table
DROP TABLE IF EXISTS employee_kpi_assignments;

-- ============================================================
-- Step 3B: Remove kpi_assignment_logs (if unused)
-- ============================================================

-- This table seems to be for tracking fallback assignments
-- If you're not using the fallback system, drop it

DROP TABLE IF EXISTS kpi_assignment_logs;

-- ============================================================
-- Step 3C: Keep or Remove performance_reviews
-- ============================================================

-- RECOMMENDATION: KEEP this table - it's for final performance reviews
-- This is different from individual KPI scores
-- You'll likely need this for annual/bi-annual reviews

-- Just add a comment for clarity
ALTER TABLE performance_reviews 
COMMENT = 'Final performance review summaries aggregating all KPI scores per period';

-- ============================================================
-- Step 3D: audit_logs - KEEP but consider population
-- ============================================================

-- RECOMMENDATION: KEEP audit_logs for compliance
-- But you should start populating it!

ALTER TABLE audit_logs 
COMMENT = 'Audit trail for all critical operations (KPI changes, score updates, etc.)';

-- Add indexes for better query performance
CREATE INDEX idx_audit_timestamp ON audit_logs(timestamp);
CREATE INDEX idx_audit_entity ON audit_logs(entity_type, entity_id);

-- ============================================================
-- Step 4: Document remaining tables
-- ============================================================

-- Add helpful comments to other tables
ALTER TABLE employee_kpis 
COMMENT = 'Individual KPI assignments to employees per review period';

ALTER TABLE kpi_scores 
COMMENT = 'Scores and weightages for each assigned KPI';

ALTER TABLE employee_reporting_managers 
COMMENT = 'Manager-employee relationships (supports multiple managers per employee)';

ALTER TABLE employee_hr_spokespersons 
COMMENT = 'HR spokesperson assignments for employees';

-- ============================================================
-- VERIFICATION
-- ============================================================

-- Show remaining table structure
SELECT 
    TABLE_NAME,
    TABLE_ROWS,
    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS 'Size_MB',
    TABLE_COMMENT
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'kpi_portal'
ORDER BY TABLE_ROWS DESC, Size_MB DESC;

-- ============================================================
-- ROLLBACK (if needed)
-- ============================================================

-- To restore employee_kpi_assignments:
-- CREATE TABLE employee_kpi_assignments LIKE backup_employee_kpi_assignments_structure;

-- To restore kpi_assignment_logs:
-- CREATE TABLE kpi_assignment_logs LIKE backup_kpi_assignment_logs_structure;

-- Then restore foreign keys as needed
