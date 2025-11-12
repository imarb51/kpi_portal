-- ============================================================
-- MASTER SCRIPT: Execute All Fixes in Order
-- Run this to apply all database fixes
-- ============================================================

-- WARNING: This script will make significant changes to your database
-- BACKUP YOUR DATABASE before running this script!
-- 
-- To backup:
-- mysqldump -u root kpi_portal > kpi_portal_backup_$(date +%Y%m%d_%H%M%S).sql

SET @start_time = NOW();
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

SELECT '========================================' as '';
SELECT 'KPI PORTAL DATABASE FIXES' as '';
SELECT 'Started at:', @start_time as '';
SELECT '========================================' as '';

-- ============================================================
-- FIX #1: Remove Duplicate Imran Shaikh (CRITICAL)
-- ============================================================
SELECT '>>> FIX #1: Removing duplicate Imran Shaikh records...' as '';
SOURCE fix_01_remove_duplicate_imran.sql;
SELECT 'Fix #1 completed.' as '';

-- ============================================================
-- FIX #2: Verify/Document Weighted Score Formula
-- ============================================================
SELECT '>>> FIX #2: Documenting weighted score formula...' as '';
SOURCE fix_02_correct_weighted_score_formula.sql;
SELECT 'Fix #2 completed.' as '';

-- ============================================================
-- FIX #3: Sync Manager Systems
-- ============================================================
SELECT '>>> FIX #3: Syncing manager data...' as '';
SOURCE fix_03_sync_manager_systems.sql;
SELECT 'Fix #3 completed.' as '';

-- ============================================================
-- FIX #4: Remove Unused Tables
-- ============================================================
SELECT '>>> FIX #4: Cleaning up unused tables...' as '';
SOURCE fix_04_remove_unused_tables.sql;
SELECT 'Fix #4 completed.' as '';

-- ============================================================
-- FIX #5: Add Performance Indexes
-- ============================================================
SELECT '>>> FIX #5: Adding performance indexes...' as '';
SOURCE fix_05_add_performance_indexes.sql;
SELECT 'Fix #5 completed.' as '';

-- ============================================================
-- FIX #6: Document Score Scale
-- ============================================================
SELECT '>>> FIX #6: Adding score scale documentation...' as '';
SOURCE fix_06_document_score_scale.sql;
SELECT 'Fix #6 completed.' as '';

-- ============================================================
-- FIX #7: Add Soft Delete
-- ============================================================
SELECT '>>> FIX #7: Implementing soft delete...' as '';
SOURCE fix_07_add_soft_delete.sql;
SELECT 'Fix #7 completed.' as '';

-- ============================================================
-- FINAL VERIFICATION
-- ============================================================

SELECT '========================================' as '';
SELECT 'VERIFICATION REPORT' as '';
SELECT '========================================' as '';

-- Check for duplicate employees
SELECT 'Checking for duplicate employees...' as '';
SELECT 
    first_name, 
    last_name, 
    COUNT(*) as count 
FROM employees 
WHERE deleted_at IS NULL
GROUP BY first_name, last_name 
HAVING COUNT(*) > 1;

-- Check weightage totals
SELECT 'Checking weightage totals per employee...' as '';
SELECT 
    ek.employee_id,
    e.employee_code,
    SUM(ks.weightage) as total_weightage,
    CASE 
        WHEN ABS(SUM(ks.weightage) - 100) < 0.1 THEN 'OK'
        ELSE 'ERROR'
    END as status
FROM employee_kpis ek
JOIN employees e ON ek.employee_id = e.employee_id
JOIN kpi_scores ks ON ek.employee_kpi_id = ks.employee_kpi_id
WHERE ek.deleted_at IS NULL
GROUP BY ek.employee_id, ek.review_period_id
HAVING status = 'ERROR';

-- Show table sizes after optimization
SELECT 'Database size after fixes:' as '';
SELECT 
    TABLE_NAME,
    TABLE_ROWS,
    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS 'Size_MB',
    ROUND((INDEX_LENGTH / 1024 / 1024), 2) AS 'Index_MB'
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'kpi_portal'
ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC
LIMIT 10;

-- Show index count
SELECT 'Index count per table:' as '';
SELECT 
    TABLE_NAME,
    COUNT(*) as index_count
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = 'kpi_portal'
GROUP BY TABLE_NAME
ORDER BY index_count DESC;

-- Check performance view
SELECT 'Employee performance summary:' as '';
SELECT * FROM v_employee_performance_summary
ORDER BY final_score_0_100 DESC;

-- Summary
SET @end_time = NOW();
SELECT '========================================' as '';
SELECT 'ALL FIXES COMPLETED SUCCESSFULLY!' as '';
SELECT 'Started at:', @start_time as '';
SELECT 'Completed at:', @end_time as '';
SELECT CONCAT('Duration: ', TIMESTAMPDIFF(SECOND, @start_time, @end_time), ' seconds') as '';
SELECT '========================================' as '';

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- RECOMMENDED POST-FIX ACTIONS
-- ============================================================

SELECT 'POST-FIX ACTIONS REQUIRED:' as '';
SELECT '1. Update application code to use soft deletes' as '';
SELECT '2. Update queries to filter deleted_at IS NULL' as '';
SELECT '3. Update Employee_model->get_manager() to use employee_reporting_managers' as '';
SELECT '4. Test all CRUD operations thoroughly' as '';
SELECT '5. Set up automated backup schedule' as '';
SELECT '6. Run ANALYZE TABLE on main tables' as '';
SELECT '7. Monitor query performance with new indexes' as '';
SELECT '' as '';

-- Analyze tables for optimizer
ANALYZE TABLE 
    employee_kpis,
    kpi_scores,
    employee_reporting_managers,
    employees,
    users,
    kpi_templates;
