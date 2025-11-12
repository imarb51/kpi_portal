-- ============================================================
-- FIX #3: Sync Manager Data Between Old and New Systems
-- Remove redundancy from employee_work_info table
-- ============================================================

-- Step 1: Verify current inconsistencies
SELECT 
    ew.employee_id,
    e.employee_code,
    e.first_name,
    e.last_name,
    ew.reporting_manager_id as 'old_system_manager',
    GROUP_CONCAT(
        CONCAT(erm.manager_id, IF(erm.is_primary=1, ' (PRIMARY)', ' (SECONDARY)'))
        ORDER BY erm.is_primary DESC
    ) as 'new_system_managers'
FROM employee_work_info ew
JOIN employees e ON ew.employee_id = e.employee_id
LEFT JOIN employee_reporting_managers erm ON ew.employee_id = erm.employee_id
GROUP BY ew.employee_id;

-- ============================================================
-- Step 2: Sync OLD system to match NEW system (primary manager)
-- ============================================================

UPDATE employee_work_info ew
JOIN (
    SELECT employee_id, manager_id
    FROM employee_reporting_managers
    WHERE is_primary = 1
) erm ON ew.employee_id = erm.employee_id
SET ew.reporting_manager_id = erm.manager_id
WHERE ew.reporting_manager_id != erm.manager_id
   OR ew.reporting_manager_id IS NULL;

-- ============================================================
-- Step 3: Add any missing entries to NEW system from OLD system
-- ============================================================

INSERT INTO employee_reporting_managers (id, employee_id, manager_id, is_primary, created_at)
SELECT 
    UUID() as id,
    ew.employee_id,
    ew.reporting_manager_id as manager_id,
    1 as is_primary,
    NOW() as created_at
FROM employee_work_info ew
WHERE ew.reporting_manager_id IS NOT NULL
AND NOT EXISTS (
    SELECT 1 FROM employee_reporting_managers erm
    WHERE erm.employee_id = ew.employee_id
    AND erm.manager_id = ew.reporting_manager_id
);

-- ============================================================
-- Step 4: OPTIONAL - Remove reporting_manager_id from employee_work_info
-- This eliminates redundancy and makes NEW system the single source of truth
-- ============================================================

-- First, verify all data is in new system
SELECT 
    'Missing in NEW system' as issue,
    ew.employee_id,
    e.employee_code,
    ew.reporting_manager_id
FROM employee_work_info ew
JOIN employees e ON ew.employee_id = e.employee_id
LEFT JOIN employee_reporting_managers erm ON ew.employee_id = erm.employee_id 
    AND ew.reporting_manager_id = erm.manager_id
WHERE ew.reporting_manager_id IS NOT NULL
AND erm.id IS NULL;

-- If above query returns 0 rows, safe to proceed

-- Backup column data
ALTER TABLE employee_work_info 
ADD COLUMN reporting_manager_id_backup VARCHAR(36) AFTER reporting_manager_id;

UPDATE employee_work_info 
SET reporting_manager_id_backup = reporting_manager_id;

-- Drop the foreign key constraint first
ALTER TABLE employee_work_info 
DROP FOREIGN KEY employee_work_info_ibfk_3;

-- Drop the redundant column
ALTER TABLE employee_work_info 
DROP COLUMN reporting_manager_id;

-- Add comment to table
ALTER TABLE employee_work_info 
COMMENT = 'Employee work information. Manager relationships now in employee_reporting_managers table.';

-- ============================================================
-- Step 5: Update application code references
-- ============================================================

-- NOTE: You'll need to update these methods in Employee_model.php:
-- 1. get_direct_reports() - Already updated to use employee_reporting_managers
-- 2. get_manager() - Should use employee_reporting_managers with is_primary=1
-- 3. Any other code using reporting_manager_id

-- ============================================================
-- VERIFICATION
-- ============================================================

-- Verify all employees have managers in new system
SELECT 
    e.employee_id,
    e.employee_code,
    e.first_name,
    e.last_name,
    GROUP_CONCAT(
        CONCAT(m.employee_code, ' (', m.first_name, ' ', m.last_name, ')',
               IF(erm.is_primary=1, ' PRIMARY', ''))
        ORDER BY erm.is_primary DESC
    ) as managers
FROM employees e
LEFT JOIN employee_reporting_managers erm ON e.employee_id = erm.employee_id
LEFT JOIN employees m ON erm.manager_id = m.employee_id
GROUP BY e.employee_id
ORDER BY e.employee_code;

-- ============================================================
-- ROLLBACK (if needed)
-- ============================================================

-- If you need to restore the column:
-- ALTER TABLE employee_work_info 
-- ADD COLUMN reporting_manager_id VARCHAR(36) AFTER hr_spokesperson_id;

-- UPDATE employee_work_info 
-- SET reporting_manager_id = reporting_manager_id_backup;

-- ALTER TABLE employee_work_info 
-- ADD CONSTRAINT employee_work_info_ibfk_3 
-- FOREIGN KEY (reporting_manager_id) REFERENCES employees(employee_id);

-- ALTER TABLE employee_work_info 
-- DROP COLUMN reporting_manager_id_backup;
