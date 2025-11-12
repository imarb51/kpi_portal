-- ============================================
-- Migration Script: Convert UUID user_ids to numerical format
-- Date: 2025-11-07
-- Purpose: Convert existing UUID-based user_ids to user-1000, user-1001, etc.
--          while preserving user-* format IDs (like user-admin-001, user-cto-001)
-- ============================================

-- STEP 1: Create a temporary mapping table
DROP TABLE IF EXISTS user_id_migration_map;
CREATE TABLE user_id_migration_map (
    old_user_id VARCHAR(36) NOT NULL,
    new_user_id VARCHAR(36) NOT NULL,
    migrated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (old_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- STEP 2: Generate new user_ids for UUID-based users only
-- Find UUID users (36 character UUIDs with dashes)
SET @counter = 999;

INSERT INTO user_id_migration_map (old_user_id, new_user_id)
SELECT 
    user_id,
    CONCAT('user-', @counter := @counter + 1) as new_user_id
FROM users
WHERE LENGTH(user_id) = 36 
  AND user_id LIKE '%-%-%-%-%'
  AND user_id NOT LIKE 'user-%'
ORDER BY created_at ASC;

-- STEP 3: Display the migration mapping
SELECT 
    u.employee_code,
    CONCAT(u.first_name, ' ', u.last_name) as employee_name,
    m.old_user_id,
    m.new_user_id,
    u.created_at
FROM user_id_migration_map m
JOIN users u ON m.old_user_id = u.user_id
ORDER BY u.created_at;

-- STEP 4: Update all tables with the new user_ids
-- IMPORTANT: Run these updates one by one and verify each step

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Update users table
UPDATE users u
INNER JOIN user_id_migration_map m ON u.user_id = m.old_user_id COLLATE utf8mb4_general_ci
SET u.user_id = m.new_user_id;

-- Update employee_work_info (employee_id)
UPDATE employee_work_info ewi
INNER JOIN user_id_migration_map m ON ewi.employee_id = m.old_user_id COLLATE utf8mb4_general_ci
SET ewi.employee_id = m.new_user_id;

-- Update employee_work_info (reporting_manager_id)
UPDATE employee_work_info ewi
INNER JOIN user_id_migration_map m ON ewi.reporting_manager_id = m.old_user_id COLLATE utf8mb4_general_ci
SET ewi.reporting_manager_id = m.new_user_id;

-- Update employee_work_info (hr_spokesperson_id)
UPDATE employee_work_info ewi
INNER JOIN user_id_migration_map m ON ewi.hr_spokesperson_id = m.old_user_id COLLATE utf8mb4_general_ci
SET ewi.hr_spokesperson_id = m.new_user_id;

-- Update employee_roles
UPDATE employee_roles er
INNER JOIN user_id_migration_map m ON er.employee_id = m.old_user_id COLLATE utf8mb4_general_ci
SET er.employee_id = m.new_user_id;

-- Update employee_reporting_managers (employee_id)
UPDATE employee_reporting_managers erm
INNER JOIN user_id_migration_map m ON erm.employee_id = m.old_user_id COLLATE utf8mb4_general_ci
SET erm.employee_id = m.new_user_id;

-- Update employee_reporting_managers (manager_id)
UPDATE employee_reporting_managers erm
INNER JOIN user_id_migration_map m ON erm.manager_id = m.old_user_id COLLATE utf8mb4_general_ci
SET erm.manager_id = m.new_user_id;

-- Update employee_hr_spokespersons (employee_id)
UPDATE employee_hr_spokespersons ehs
INNER JOIN user_id_migration_map m ON ehs.employee_id = m.old_user_id COLLATE utf8mb4_general_ci
SET ehs.employee_id = m.new_user_id;

-- Update employee_hr_spokespersons (hr_id)
UPDATE employee_hr_spokespersons ehs
INNER JOIN user_id_migration_map m ON ehs.hr_id = m.old_user_id COLLATE utf8mb4_general_ci
SET ehs.hr_id = m.new_user_id;

-- Update kpi_templates (created_by)
UPDATE kpi_templates kt
INNER JOIN user_id_migration_map m ON kt.created_by = m.old_user_id COLLATE utf8mb4_general_ci
SET kt.created_by = m.new_user_id;

-- Update employee_kpis (employee_id)
UPDATE employee_kpis ek
INNER JOIN user_id_migration_map m ON ek.employee_id = m.old_user_id COLLATE utf8mb4_general_ci
SET ek.employee_id = m.new_user_id;

-- Update employee_kpis (assigned_by)
UPDATE employee_kpis ek
INNER JOIN user_id_migration_map m ON ek.assigned_by = m.old_user_id COLLATE utf8mb4_general_ci
SET ek.assigned_by = m.new_user_id;

-- Update kpi_edit_requests (requested_by)
UPDATE kpi_edit_requests ker
INNER JOIN user_id_migration_map m ON ker.requested_by = m.old_user_id COLLATE utf8mb4_general_ci
SET ker.requested_by = m.new_user_id
WHERE ker.requested_by IS NOT NULL;

-- Update kpi_edit_requests (reviewed_by)
UPDATE kpi_edit_requests ker
INNER JOIN user_id_migration_map m ON ker.reviewed_by = m.old_user_id COLLATE utf8mb4_general_ci
SET ker.reviewed_by = m.new_user_id
WHERE ker.reviewed_by IS NOT NULL;

-- Update kpi_score_history (changed_by)
UPDATE kpi_score_history ksh
INNER JOIN user_id_migration_map m ON ksh.changed_by = m.old_user_id COLLATE utf8mb4_general_ci
SET ksh.changed_by = m.new_user_id
WHERE ksh.changed_by IS NOT NULL;

-- Update performance_reviews (employee_id)
UPDATE performance_reviews pr
INNER JOIN user_id_migration_map m ON pr.employee_id = m.old_user_id COLLATE utf8mb4_general_ci
SET pr.employee_id = m.new_user_id;

-- Update performance_reviews (reviewer_id)
UPDATE performance_reviews pr
INNER JOIN user_id_migration_map m ON pr.reviewer_id = m.old_user_id COLLATE utf8mb4_general_ci
SET pr.reviewer_id = m.new_user_id
WHERE pr.reviewer_id IS NOT NULL;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- STEP 5: Verification - Check the migration results
SELECT 'Migration Summary' as Status;

SELECT 
    COUNT(*) as total_migrated_users
FROM user_id_migration_map;

SELECT 
    'Users with numerical IDs' as description,
    COUNT(*) as count
FROM users
WHERE user_id REGEXP '^user-[0-9]+$';

SELECT 
    'Users with UUID format' as description,
    COUNT(*) as count
FROM users
WHERE LENGTH(user_id) = 36 
  AND user_id LIKE '%-%-%-%-%'
  AND user_id NOT LIKE 'user-%';

-- Show all current users
SELECT 
    user_id,
    employee_code,
    CONCAT(first_name, ' ', last_name) as name,
    email,
    LENGTH(user_id) as id_length
FROM users
WHERE deleted_at IS NULL
ORDER BY created_at;

-- STEP 6: After verifying everything works, you can optionally drop the mapping table
-- DROP TABLE user_id_migration_map;

-- ============================================
-- NOTES:
-- 1. This script preserves existing user-* format IDs (like user-admin-001, user-cto-001)
-- 2. Only converts UUID format IDs (36 characters with dashes)
-- 3. New IDs will be user-1000, user-1001, etc.
-- 4. Keep user_id_migration_map table for reference until you're sure everything works
-- 5. Test on a backup database first!
-- ============================================
