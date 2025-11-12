-- ============================================================
-- FIX #1: Remove Duplicate Imran Shaikh Records
-- CRITICAL: Run this carefully after verifying which record to keep
-- ============================================================

-- Step 1: Check which record has more data/KPIs assigned
SELECT 
    e.employee_id,
    e.employee_code,
    e.first_name,
    e.last_name,
    u.email,
    COUNT(ek.employee_kpi_id) as kpi_count,
    COUNT(erm.id) as manager_count,
    e.created_at
FROM employees e
LEFT JOIN users u ON e.user_id = u.user_id
LEFT JOIN employee_kpis ek ON e.employee_id = ek.employee_id
LEFT JOIN employee_reporting_managers erm ON e.employee_id = erm.employee_id
WHERE e.first_name = 'Imran' AND e.last_name = 'Shaikh'
GROUP BY e.employee_id
ORDER BY kpi_count DESC, e.created_at ASC;

-- ============================================================
-- OPTION A: Keep 'emp-imran-001' (older record, has 4 KPIs)
-- ============================================================

-- Step 2A: Backup data from newer record (1440) before deletion
CREATE TABLE IF NOT EXISTS backup_duplicate_employees AS
SELECT * FROM employees 
WHERE employee_id = '00c28abb-b68e-414a-ad59-c063ff5f2787';

-- Step 3A: Update any references from new ID to old ID
-- Update employee_kpis
UPDATE employee_kpis 
SET employee_id = 'emp-imran-001'
WHERE employee_id = '00c28abb-b68e-414a-ad59-c063ff5f2787'
AND NOT EXISTS (
    SELECT 1 FROM employee_kpis ek2 
    WHERE ek2.employee_id = 'emp-imran-001' 
    AND ek2.template_id = employee_kpis.template_id
    AND ek2.review_period_id = employee_kpis.review_period_id
);

-- Update employee_reporting_managers
UPDATE employee_reporting_managers 
SET employee_id = 'emp-imran-001'
WHERE employee_id = '00c28abb-b68e-414a-ad59-c063ff5f2787'
AND NOT EXISTS (
    SELECT 1 FROM employee_reporting_managers erm2 
    WHERE erm2.employee_id = 'emp-imran-001' 
    AND erm2.manager_id = employee_reporting_managers.manager_id
);

-- Update employee_hr_spokespersons
UPDATE employee_hr_spokespersons 
SET employee_id = 'emp-imran-001'
WHERE employee_id = '00c28abb-b68e-414a-ad59-c063ff5f2787'
AND NOT EXISTS (
    SELECT 1 FROM employee_hr_spokespersons ehs2 
    WHERE ehs2.employee_id = 'emp-imran-001' 
    AND ehs2.hr_id = employee_hr_spokespersons.hr_id
);

-- Update employee_work_info
UPDATE employee_work_info 
SET employee_id = 'emp-imran-001'
WHERE employee_id = '00c28abb-b68e-414a-ad59-c063ff5f2787';

-- Update employee_roles
UPDATE employee_roles 
SET employee_id = 'emp-imran-001'
WHERE employee_id = '00c28abb-b68e-414a-ad59-c063ff5f2787'
AND NOT EXISTS (
    SELECT 1 FROM employee_roles er2 
    WHERE er2.employee_id = 'emp-imran-001' 
    AND er2.role_id = employee_roles.role_id
);

-- Step 4A: Update the kept record with better email if needed
UPDATE employees e
JOIN users u ON e.user_id = u.user_id
SET 
    e.phone_number = COALESCE(e.phone_number, '+919689550530'),
    e.employee_email = COALESCE(NULLIF(e.employee_email, ''), u.email)
WHERE e.employee_id = 'emp-imran-001';

-- Step 5A: Delete the duplicate employee record
DELETE FROM employees 
WHERE employee_id = '00c28abb-b68e-414a-ad59-c063ff5f2787';

-- Step 6A: Delete the associated user record
DELETE FROM users 
WHERE user_id = '7c47a13a-0cc3-47c5-a7e6-9edb83cbd0be';

-- ============================================================
-- VERIFICATION: Check if duplicate is removed
-- ============================================================
SELECT 
    COUNT(*) as imran_count,
    GROUP_CONCAT(employee_id) as employee_ids,
    GROUP_CONCAT(employee_code) as codes
FROM employees 
WHERE first_name = 'Imran' AND last_name = 'Shaikh';

-- Expected result: imran_count = 1

-- ============================================================
-- ROLLBACK (if something goes wrong)
-- ============================================================
-- INSERT INTO employees SELECT * FROM backup_duplicate_employees;
-- Then manually restore foreign key relationships
