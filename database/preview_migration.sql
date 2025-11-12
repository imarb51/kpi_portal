-- ============================================
-- QUICK MIGRATION: Convert UUID user_ids to numerical format
-- Run this in MySQL command line or phpMyAdmin
-- ============================================

USE kpi_portal;

-- Step 1: Show what will be migrated
SELECT 
    user_id as 'Current UUID',
    employee_code,
    CONCAT(first_name, ' ', last_name) as name,
    'Will become user-1000, user-1001, etc.' as migration_note
FROM users
WHERE LENGTH(user_id) = 36 
  AND user_id LIKE '%-%-%-%-%'
  AND user_id NOT LIKE 'user-%'
ORDER BY created_at;

-- If the above looks good, run the full migration script:
-- SOURCE C:/xampp/htdocs/KPI-portal/database/migrate_user_ids_to_numerical.sql
