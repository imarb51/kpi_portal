-- TEST QUERIES FOR MULTIPLE MANAGERS/HR FEATURE
-- Run these after creating a new employee
-- November 6, 2025

-- ==================================================
-- 1. Find your newly created employee
-- ==================================================
SELECT 
    e.employee_id,
    e.employee_code,
    e.first_name,
    e.last_name,
    e.phone_number,
    u.email,
    ewi.designation,
    d.department_name
FROM employees e
JOIN users u ON e.user_id = u.user_id
JOIN employee_work_info ewi ON e.employee_id = ewi.employee_id
JOIN departments d ON ewi.department_id = d.department_id
ORDER BY e.created_at DESC
LIMIT 5;

-- ==================================================
-- 2. Check ALL managers for an employee
-- ==================================================
-- Replace 'EMPLOYEE_ID_HERE' with the employee_id from query above
SELECT 
    erm.id,
    erm.employee_id,
    erm.manager_id,
    erm.is_primary,
    e.employee_code as manager_code,
    e.first_name as manager_first_name,
    e.last_name as manager_last_name,
    erm.created_at
FROM employee_reporting_managers erm
JOIN employees e ON erm.manager_id = e.employee_id
WHERE erm.employee_id = 'EMPLOYEE_ID_HERE'
ORDER BY erm.is_primary DESC;

-- Expected: Should see 2 rows if you selected 2 managers
-- First row: is_primary = 1
-- Second row: is_primary = 0

-- ==================================================
-- 3. Check ALL HR spokespersons for an employee
-- ==================================================
-- Replace 'EMPLOYEE_ID_HERE' with the employee_id from query above
SELECT 
    ehr.id,
    ehr.employee_id,
    ehr.hr_id,
    ehr.is_primary,
    e.employee_code as hr_code,
    e.first_name as hr_first_name,
    e.last_name as hr_last_name,
    ehr.created_at
FROM employee_hr_spokespersons ehr
JOIN employees e ON ehr.hr_id = e.employee_id
WHERE ehr.employee_id = 'EMPLOYEE_ID_HERE'
ORDER BY ehr.is_primary DESC;

-- Expected: Should see 2 rows if you selected 2 HR persons
-- First row: is_primary = 1
-- Second row: is_primary = 0

-- ==================================================
-- 4. Get complete employee info with all managers and HR
-- ==================================================
-- Replace 'EMPLOYEE_ID_HERE' with actual employee_id
SELECT 
    'Employee' as Type,
    e.employee_code as Code,
    CONCAT(e.first_name, ' ', e.last_name) as Name,
    ewi.designation as Role,
    d.department_name as Department
FROM employees e
JOIN employee_work_info ewi ON e.employee_id = ewi.employee_id
JOIN departments d ON ewi.department_id = d.department_id
WHERE e.employee_id = 'EMPLOYEE_ID_HERE'

UNION ALL

SELECT 
    'Manager' as Type,
    e.employee_code as Code,
    CONCAT(e.first_name, ' ', e.last_name) as Name,
    IF(erm.is_primary = 1, 'Primary', 'Secondary') as Role,
    '' as Department
FROM employee_reporting_managers erm
JOIN employees e ON erm.manager_id = e.employee_id
WHERE erm.employee_id = 'EMPLOYEE_ID_HERE'
ORDER BY FIELD(Role, 'Primary', 'Secondary')

UNION ALL

SELECT 
    'HR' as Type,
    e.employee_code as Code,
    CONCAT(e.first_name, ' ', e.last_name) as Name,
    IF(ehr.is_primary = 1, 'Primary', 'Secondary') as Role,
    '' as Department
FROM employee_hr_spokespersons ehr
JOIN employees e ON ehr.hr_id = e.employee_id
WHERE ehr.employee_id = 'EMPLOYEE_ID_HERE'
ORDER BY FIELD(Role, 'Primary', 'Secondary');

-- ==================================================
-- 5. Count managers and HR for all employees
-- ==================================================
SELECT 
    e.employee_code,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    COUNT(DISTINCT erm.manager_id) as total_managers,
    COUNT(DISTINCT ehr.hr_id) as total_hr_persons,
    e.created_at as created_date
FROM employees e
LEFT JOIN employee_reporting_managers erm ON e.employee_id = erm.employee_id
LEFT JOIN employee_hr_spokespersons ehr ON e.employee_id = ehr.employee_id
GROUP BY e.employee_id
ORDER BY e.created_at DESC
LIMIT 10;

-- ==================================================
-- 6. Check if pradeep user has multiple managers
-- ==================================================
SELECT 
    e.employee_code,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    u.email,
    'Manager' as type,
    m.employee_code as related_code,
    CONCAT(m.first_name, ' ', m.last_name) as related_name,
    erm.is_primary
FROM employees e
JOIN users u ON e.user_id = u.user_id
JOIN employee_reporting_managers erm ON e.employee_id = erm.employee_id
JOIN employees m ON erm.manager_id = m.employee_id
WHERE e.employee_code = '1440'

UNION ALL

SELECT 
    e.employee_code,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    u.email,
    'HR' as type,
    h.employee_code as related_code,
    CONCAT(h.first_name, ' ', h.last_name) as related_name,
    ehr.is_primary
FROM employees e
JOIN users u ON e.user_id = u.user_id
JOIN employee_hr_spokespersons ehr ON e.employee_id = ehr.employee_id
JOIN employees h ON ehr.hr_id = h.employee_id
WHERE e.employee_code = '1440';

-- ==================================================
-- 7. Verify backward compatibility (old system)
-- ==================================================
-- Check if primary manager/HR is also in employee_work_info
SELECT 
    e.employee_code,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    ewi.reporting_manager_id as work_info_manager,
    ewi.hr_spokesperson_id as work_info_hr,
    (SELECT manager_id FROM employee_reporting_managers 
     WHERE employee_id = e.employee_id AND is_primary = 1) as primary_manager,
    (SELECT hr_id FROM employee_hr_spokespersons 
     WHERE employee_id = e.employee_id AND is_primary = 1) as primary_hr,
    CASE 
        WHEN ewi.reporting_manager_id = (SELECT manager_id FROM employee_reporting_managers 
                                         WHERE employee_id = e.employee_id AND is_primary = 1)
        THEN '✓ Match'
        ELSE '✗ Mismatch'
    END as manager_check,
    CASE 
        WHEN ewi.hr_spokesperson_id = (SELECT hr_id FROM employee_hr_spokespersons 
                                      WHERE employee_id = e.employee_id AND is_primary = 1)
        THEN '✓ Match'
        ELSE '✗ Mismatch'
    END as hr_check
FROM employees e
JOIN employee_work_info ewi ON e.employee_id = ewi.employee_id
WHERE e.created_at >= '2025-11-06'
ORDER BY e.created_at DESC;

-- ==================================================
-- 8. Test query - Should work after fix
-- ==================================================
-- This should return 2 managers for pradeep (1440)
SELECT COUNT(*) as manager_count
FROM employee_reporting_managers
WHERE employee_id = (SELECT employee_id FROM employees WHERE employee_code = '1440');

-- Expected: 2 (if you selected 2 managers when creating pradeep)

-- ==================================================
-- TROUBLESHOOTING
-- ==================================================

-- If you don't see multiple managers, check application logs:
-- File: application/logs/log-2025-11-06.php
-- Look for lines with: "Inserted manager" and "Inserted HR"

-- If still not working, enable database query logging:
-- In application/config/database.php, set: 'save_queries' => TRUE
-- Then check: $this->db->last_query() in the code
