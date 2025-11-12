-- ============================================================
-- FIX #8: Merge Users and Employees Tables
-- Consolidate authentication and employee data into single table
-- ============================================================

-- Step 1: Backup existing tables
CREATE TABLE IF NOT EXISTS backup_users AS SELECT * FROM users;
CREATE TABLE IF NOT EXISTS backup_employees AS SELECT * FROM employees;

-- ============================================================
-- Step 2: Create new merged users table structure
-- ============================================================

CREATE TABLE IF NOT EXISTS users_new (
    user_id VARCHAR(36) NOT NULL PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    
    -- Employee Information (from employees table)
    employee_code VARCHAR(50) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT NULL,
    phone_number VARCHAR(20) DEFAULT NULL,
    profile_picture_url VARCHAR(500) DEFAULT NULL,
    
    -- Status and timestamps
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL,
    
    -- Soft delete
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    deleted_by VARCHAR(36) DEFAULT NULL,
    
    -- Indexes
    KEY idx_email (email),
    KEY idx_employee_code (employee_code),
    KEY idx_is_active (is_active),
    KEY idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Merged users and employees - single source of truth for authentication and employee data';

-- ============================================================
-- Step 3: Migrate data from users and employees to new table
-- ============================================================

INSERT INTO users_new (
    user_id,
    email,
    password_hash,
    employee_code,
    first_name,
    last_name,
    middle_name,
    phone_number,
    profile_picture_url,
    is_active,
    created_at,
    updated_at,
    last_login
)
SELECT 
    u.user_id,
    u.email,
    u.password_hash,
    e.employee_code,
    e.first_name,
    e.last_name,
    e.middle_name,
    e.phone_number,
    e.profile_picture_url,
    u.is_active,
    -- Use earlier creation date
    LEAST(u.created_at, e.created_at) as created_at,
    -- Use later update date
    GREATEST(u.updated_at, e.updated_at) as updated_at,
    u.last_login
FROM users u
JOIN employees e ON u.user_id = e.user_id;

-- ============================================================
-- Step 4: Verify data migration
-- ============================================================

SELECT 'Data Migration Verification' as status;

SELECT 
    'Original users count' as metric,
    COUNT(*) as count 
FROM users;

SELECT 
    'Original employees count' as metric,
    COUNT(*) as count 
FROM employees;

SELECT 
    'Merged users_new count' as metric,
    COUNT(*) as count 
FROM users_new;

-- Check for any missing records
SELECT 'Checking for missing records...' as status;

SELECT 
    'Users without employees' as issue,
    u.user_id,
    u.email
FROM users u
LEFT JOIN employees e ON u.user_id = e.user_id
WHERE e.employee_id IS NULL;

SELECT 
    'Employees without users' as issue,
    e.employee_id,
    e.employee_code,
    e.first_name,
    e.last_name
FROM employees e
LEFT JOIN users u ON e.user_id = u.user_id
WHERE u.user_id IS NULL;

-- ============================================================
-- Step 5: Create mapping table for backward compatibility
-- ============================================================

CREATE TABLE IF NOT EXISTS user_employee_mapping (
    employee_id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'Old employee_id',
    user_id VARCHAR(36) NOT NULL COMMENT 'New user_id (same value)',
    migrated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    KEY idx_user_id (user_id),
    CONSTRAINT fk_mapping_user FOREIGN KEY (user_id) REFERENCES users_new(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Mapping table for backward compatibility during migration';

-- Populate mapping table
INSERT INTO user_employee_mapping (employee_id, user_id)
SELECT employee_id, user_id FROM employees;

-- ============================================================
-- Step 6: Update foreign key references
-- ============================================================

SELECT 'Updating foreign key references...' as status;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Update employee_kpis (employee_id references)
-- The employee_id in employee_kpis is actually the same as user_id in users
-- So no data change needed, just update the foreign key

-- Update employee_work_info
ALTER TABLE employee_work_info DROP FOREIGN KEY employee_work_info_ibfk_1;
ALTER TABLE employee_work_info 
ADD CONSTRAINT employee_work_info_ibfk_1 
FOREIGN KEY (employee_id) REFERENCES users_new(user_id) ON DELETE CASCADE;

-- Update employee_roles
ALTER TABLE employee_roles DROP FOREIGN KEY employee_roles_ibfk_1;
ALTER TABLE employee_roles 
ADD CONSTRAINT employee_roles_ibfk_1 
FOREIGN KEY (employee_id) REFERENCES users_new(user_id) ON DELETE CASCADE;

-- Update employee_reporting_managers (both employee_id and manager_id)
ALTER TABLE employee_reporting_managers DROP FOREIGN KEY employee_reporting_managers_ibfk_1;
ALTER TABLE employee_reporting_managers DROP FOREIGN KEY employee_reporting_managers_ibfk_2;

ALTER TABLE employee_reporting_managers 
ADD CONSTRAINT employee_reporting_managers_ibfk_1 
FOREIGN KEY (employee_id) REFERENCES users_new(user_id) ON DELETE CASCADE;

ALTER TABLE employee_reporting_managers 
ADD CONSTRAINT employee_reporting_managers_ibfk_2 
FOREIGN KEY (manager_id) REFERENCES users_new(user_id) ON DELETE CASCADE;

-- Update employee_hr_spokespersons
ALTER TABLE employee_hr_spokespersons DROP FOREIGN KEY employee_hr_spokespersons_ibfk_1;
ALTER TABLE employee_hr_spokespersons DROP FOREIGN KEY employee_hr_spokespersons_ibfk_2;

ALTER TABLE employee_hr_spokespersons 
ADD CONSTRAINT employee_hr_spokespersons_ibfk_1 
FOREIGN KEY (employee_id) REFERENCES users_new(user_id) ON DELETE CASCADE;

ALTER TABLE employee_hr_spokespersons 
ADD CONSTRAINT employee_hr_spokespersons_ibfk_2 
FOREIGN KEY (hr_id) REFERENCES users_new(user_id) ON DELETE CASCADE;

-- Update employee_kpis
ALTER TABLE employee_kpis DROP FOREIGN KEY employee_kpis_ibfk_1;
ALTER TABLE employee_kpis 
ADD CONSTRAINT employee_kpis_ibfk_1 
FOREIGN KEY (employee_id) REFERENCES users_new(user_id) ON DELETE CASCADE;

-- Update kpi_scores (last_edited_by)
ALTER TABLE kpi_scores DROP FOREIGN KEY kpi_scores_ibfk_2;
ALTER TABLE kpi_scores 
ADD CONSTRAINT kpi_scores_ibfk_2 
FOREIGN KEY (last_edited_by) REFERENCES users_new(user_id) ON DELETE SET NULL;

-- Update audit_logs
ALTER TABLE audit_logs DROP FOREIGN KEY audit_logs_ibfk_1;
ALTER TABLE audit_logs 
ADD CONSTRAINT audit_logs_ibfk_1 
FOREIGN KEY (user_id) REFERENCES users_new(user_id) ON DELETE SET NULL;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Step 7: Rename tables (POINT OF NO RETURN)
-- ============================================================

-- WARNING: After this point, rollback is complex
-- Make sure you have backups!

SELECT 'POINT OF NO RETURN - Renaming tables...' as warning;
SELECT 'Press Ctrl+C to abort if you are not ready!' as warning;
SELECT 'Waiting 5 seconds...' as warning;

-- Uncomment the following to enable automatic execution:
-- DO SLEEP(5);

-- Rename old tables to backup
RENAME TABLE users TO users_old;
RENAME TABLE employees TO employees_old;

-- Rename new table to production
RENAME TABLE users_new TO users;

-- ============================================================
-- Step 8: Create views for backward compatibility
-- ============================================================

CREATE OR REPLACE VIEW employees AS
SELECT 
    user_id as employee_id,
    user_id,
    employee_code,
    first_name,
    last_name,
    middle_name,
    phone_number,
    email as employee_email,
    profile_picture_url,
    created_at,
    updated_at
FROM users
WHERE deleted_at IS NULL;

-- ============================================================
-- Step 9: Update stored procedures and functions
-- ============================================================

-- Drop old soft delete procedures that reference employees
DROP PROCEDURE IF EXISTS sp_soft_delete_employee;

-- Create new soft delete procedure
DELIMITER $$

CREATE PROCEDURE sp_soft_delete_user(
    IN p_user_id VARCHAR(36),
    IN p_deleted_by VARCHAR(36)
)
BEGIN
    -- Check if already deleted
    IF EXISTS (SELECT 1 FROM users WHERE user_id = p_user_id AND deleted_at IS NOT NULL) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'User already deleted';
    END IF;
    
    -- Soft delete user
    UPDATE users 
    SET deleted_at = NOW(),
        deleted_by = p_deleted_by
    WHERE user_id = p_user_id;
    
    -- Soft delete all KPIs
    UPDATE employee_kpis 
    SET deleted_at = NOW(),
        deleted_by = p_deleted_by
    WHERE employee_id = p_user_id
    AND deleted_at IS NULL;
    
    -- Soft delete all scores
    UPDATE kpi_scores ks
    JOIN employee_kpis ek ON ks.employee_kpi_id = ek.employee_kpi_id
    SET ks.deleted_at = NOW(),
        ks.deleted_by = p_deleted_by
    WHERE ek.employee_id = p_user_id
    AND ks.deleted_at IS NULL;
    
    SELECT 'User and all associated data soft deleted successfully' as message;
END$$

DELIMITER ;

-- ============================================================
-- Step 10: Verification
-- ============================================================

SELECT '========================================' as '';
SELECT 'MIGRATION VERIFICATION' as '';
SELECT '========================================' as '';

-- Check table structure
SELECT 'New users table structure:' as '';
SHOW COLUMNS FROM users;

-- Check data counts
SELECT 'Data counts:' as '';
SELECT COUNT(*) as active_users FROM users WHERE deleted_at IS NULL;

-- Check foreign keys
SELECT 'Foreign key constraints:' as '';
SELECT 
    TABLE_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'kpi_portal'
AND REFERENCED_TABLE_NAME = 'users'
ORDER BY TABLE_NAME;

-- Test the employees view
SELECT 'Testing employees view:' as '';
SELECT * FROM employees LIMIT 5;

-- Check for any issues
SELECT 'Checking for orphaned records...' as '';
SELECT 
    'employee_work_info' as table_name,
    COUNT(*) as orphaned_count
FROM employee_work_info ew
LEFT JOIN users u ON ew.employee_id = u.user_id
WHERE u.user_id IS NULL

UNION ALL

SELECT 
    'employee_roles',
    COUNT(*)
FROM employee_roles er
LEFT JOIN users u ON er.employee_id = u.user_id
WHERE u.user_id IS NULL

UNION ALL

SELECT 
    'employee_kpis',
    COUNT(*)
FROM employee_kpis ek
LEFT JOIN users u ON ek.employee_id = u.user_id
WHERE u.user_id IS NULL;

SELECT '========================================' as '';
SELECT 'MIGRATION COMPLETED SUCCESSFULLY!' as '';
SELECT '========================================' as '';
SELECT 'Old tables renamed to: users_old, employees_old' as '';
SELECT 'You can drop them after verifying everything works' as '';
SELECT '' as '';
SELECT 'IMPORTANT: Update application code to use new schema!' as '';
SELECT '========================================' as '';

-- ============================================================
-- OPTIONAL: Drop old tables after verification (DO NOT RUN IMMEDIATELY)
-- ============================================================

-- Wait at least 1 week and verify everything works before running this:
-- DROP TABLE IF EXISTS employees_old;
-- DROP TABLE IF EXISTS users_old;
-- DROP TABLE IF EXISTS backup_users;
-- DROP TABLE IF EXISTS backup_employees;
-- DROP TABLE IF EXISTS user_employee_mapping;

-- ============================================================
-- ROLLBACK (Emergency only - before Step 7)
-- ============================================================

/*
-- If you need to rollback BEFORE renaming tables:
DROP TABLE IF EXISTS users_new;
DROP TABLE IF EXISTS user_employee_mapping;

-- If you need to rollback AFTER renaming tables:
RENAME TABLE users TO users_new;
RENAME TABLE users_old TO users;
RENAME TABLE employees_old TO employees;

-- Restore foreign keys...
-- (You'll need to manually restore each foreign key constraint)
*/
