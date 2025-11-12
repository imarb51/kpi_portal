-- ============================================================
-- FIX #5: Add Missing Composite Indexes for Performance
-- Optimize query performance
-- ============================================================

-- Step 1: Analyze current query patterns
EXPLAIN SELECT * FROM employee_kpis 
WHERE employee_id = 'emp-imran-001' 
AND review_period_id = 'period-h2-2025';

-- ============================================================
-- Step 2: Add composite index for most common query pattern
-- ============================================================

-- Most common query: Get all KPIs for an employee in a specific period
CREATE INDEX idx_employee_period ON employee_kpis(employee_id, review_period_id);

-- ============================================================
-- Step 3: Add other useful composite indexes
-- ============================================================

-- Query pattern: Manager viewing all pending KPIs
CREATE INDEX idx_status_period ON employee_kpis(status, review_period_id);

-- Query pattern: Get all KPIs needing employee agreement
CREATE INDEX idx_agreement_status ON employee_kpis(employee_agreement_status, employee_id);

-- Query pattern: Find finalized KPIs in a period
CREATE INDEX idx_finalized_period ON employee_kpis(is_finalized, review_period_id, finalized_at);

-- ============================================================
-- Step 4: Add indexes to kpi_scores for faster joins
-- ============================================================

-- Already has unique index on employee_kpi_id
-- Add index for filtering by score/weightage ranges
CREATE INDEX idx_score_range ON kpi_scores(score, weightage) 
WHERE score IS NOT NULL;

-- ============================================================
-- Step 5: Add indexes to employee_reporting_managers
-- ============================================================

-- Query pattern: Find all managers of an employee
CREATE INDEX idx_employee_managers ON employee_reporting_managers(employee_id, is_primary);

-- Query pattern: Find all employees of a manager
CREATE INDEX idx_manager_employees ON employee_reporting_managers(manager_id, is_primary);

-- ============================================================
-- Step 6: Add indexes to other frequently queried tables
-- ============================================================

-- employee_work_info - query by department
CREATE INDEX idx_department_status ON employee_work_info(department_id, employee_status);

-- employee_roles - query by role
CREATE INDEX idx_role_primary ON employee_roles(role_id, is_primary);

-- review_periods - query active periods
CREATE INDEX idx_period_active ON review_periods(is_active, status, start_date);

-- kpi_templates - query by category and active status
CREATE INDEX idx_category_active ON kpi_templates(category_id, is_active);

-- ============================================================
-- Step 7: Add full-text indexes for search functionality
-- ============================================================

-- Enable full-text search on KPI names and descriptions
ALTER TABLE kpi_templates 
ADD FULLTEXT INDEX ft_kpi_search (kpi_name, description);

-- Enable full-text search on employee names
ALTER TABLE employees 
ADD FULLTEXT INDEX ft_employee_search (first_name, last_name, employee_code);

-- ============================================================
-- VERIFICATION: Analyze index usage
-- ============================================================

-- Show all indexes on employee_kpis
SHOW INDEX FROM employee_kpis;

-- Show table size before and after indexes
SELECT 
    TABLE_NAME,
    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS 'Total_MB',
    ROUND((INDEX_LENGTH / 1024 / 1024), 2) AS 'Index_MB',
    ROUND((DATA_LENGTH / 1024 / 1024), 2) AS 'Data_MB'
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'kpi_portal'
AND TABLE_NAME IN ('employee_kpis', 'kpi_scores', 'employee_reporting_managers')
ORDER BY Total_MB DESC;

-- Test query performance improvement
EXPLAIN SELECT 
    ek.*,
    ks.weightage,
    ks.score,
    kt.kpi_name
FROM employee_kpis ek
JOIN kpi_scores ks ON ek.employee_kpi_id = ks.employee_kpi_id
JOIN kpi_templates kt ON ek.template_id = kt.template_id
WHERE ek.employee_id = 'emp-imran-001'
AND ek.review_period_id = 'period-h2-2025'
AND ek.status = 'ASSIGNED';

-- ============================================================
-- Step 8: Analyze and optimize existing indexes
-- ============================================================

-- Check for duplicate/redundant indexes
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) as columns,
    INDEX_TYPE,
    NON_UNIQUE
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = 'kpi_portal'
GROUP BY TABLE_NAME, INDEX_NAME
ORDER BY TABLE_NAME, INDEX_NAME;

-- ============================================================
-- Step 9: Remove redundant indexes (if any found)
-- ============================================================

-- Example: If idx_employee_id is redundant after adding idx_employee_period
-- (Because idx_employee_period can be used for employee_id lookups too)

-- Check if idx_employee_id is still being used
-- ALTER TABLE employee_kpis DROP INDEX idx_employee_id;

-- NOTE: Don't drop indexes without verifying they're truly redundant!

-- ============================================================
-- MAINTENANCE: Set up regular index optimization
-- ============================================================

-- Add this to your regular maintenance script
-- ANALYZE TABLE employee_kpis, kpi_scores, employee_reporting_managers;
-- OPTIMIZE TABLE employee_kpis, kpi_scores, employee_reporting_managers;

-- ============================================================
-- ROLLBACK (if indexes cause issues)
-- ============================================================

-- Drop new indexes if they cause problems:
-- ALTER TABLE employee_kpis DROP INDEX idx_employee_period;
-- ALTER TABLE employee_kpis DROP INDEX idx_status_period;
-- ALTER TABLE employee_kpis DROP INDEX idx_agreement_status;
-- ALTER TABLE employee_kpis DROP INDEX idx_finalized_period;
-- ALTER TABLE kpi_scores DROP INDEX idx_score_range;
-- ALTER TABLE employee_reporting_managers DROP INDEX idx_employee_managers;
-- ALTER TABLE employee_reporting_managers DROP INDEX idx_manager_employees;
-- ALTER TABLE kpi_templates DROP INDEX ft_kpi_search;
-- ALTER TABLE employees DROP INDEX ft_employee_search;
