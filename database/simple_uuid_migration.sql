-- ============================================
-- SIMPLE UUID TO NUMERICAL MIGRATION
-- Run these commands ONE BY ONE in MySQL
-- ============================================

USE kpi_portal;

-- Step 1: Disable FK checks
SET FOREIGN_KEY_CHECKS = 0;

-- Step 2: Manually update the 2 UUID users
-- User 1: Adarsh kulkarni (1450) -> user-1001
UPDATE users SET user_id = 'user-1001' WHERE user_id = '3e871462-fca0-4c2f-b9f5-21d5ce52c897';
UPDATE employee_work_info SET employee_id = 'user-1001' WHERE employee_id = '3e871462-fca0-4c2f-b9f5-21d5ce52c897';
UPDATE employee_work_info SET reporting_manager_id = 'user-1001' WHERE reporting_manager_id = '3e871462-fca0-4c2f-b9f5-21d5ce52c897';
UPDATE employee_work_info SET hr_spokesperson_id = 'user-1001' WHERE hr_spokesperson_id = '3e871462-fca0-4c2f-b9f5-21d5ce52c897';
UPDATE employee_roles SET employee_id = 'user-1001' WHERE employee_id = '3e871462-fca0-4c2f-b9f5-21d5ce52c897';
UPDATE employee_reporting_managers SET employee_id = 'user-1001' WHERE employee_id = '3e871462-fca0-4c2f-b9f5-21d5ce52c897';
UPDATE employee_reporting_managers SET manager_id = 'user-1001' WHERE manager_id = '3e871462-fca0-4c2f-b9f5-21d5ce52c897';
UPDATE employee_hr_spokespersons SET employee_id = 'user-1001' WHERE employee_id = '3e871462-fca0-4c2f-b9f5-21d5ce52c897';
UPDATE employee_hr_spokespersons SET hr_id = 'user-1001' WHERE hr_id = '3e871462-fca0-4c2f-b9f5-21d5ce52c897';

-- User 2: Imran Shaikh (1500) -> user-1002
UPDATE users SET user_id = 'user-1002' WHERE user_id = '487a8fb3-223d-40d8-a58a-591f2c5886db';
UPDATE employee_work_info SET employee_id = 'user-1002' WHERE employee_id = '487a8fb3-223d-40d8-a58a-591f2c5886db';
UPDATE employee_work_info SET reporting_manager_id = 'user-1002' WHERE reporting_manager_id = '487a8fb3-223d-40d8-a58a-591f2c5886db';
UPDATE employee_work_info SET hr_spokesperson_id = 'user-1002' WHERE hr_spokesperson_id = '487a8fb3-223d-40d8-a58a-591f2c5886db';
UPDATE employee_roles SET employee_id = 'user-1002' WHERE employee_id = '487a8fb3-223d-40d8-a58a-591f2c5886db';
UPDATE employee_reporting_managers SET employee_id = 'user-1002' WHERE employee_id = '487a8fb3-223d-40d8-a58a-591f2c5886db';
UPDATE employee_reporting_managers SET manager_id = 'user-1002' WHERE manager_id = '487a8fb3-223d-40d8-a58a-591f2c5886db';
UPDATE employee_hr_spokespersons SET employee_id = 'user-1002' WHERE employee_id = '487a8fb3-223d-40d8-a58a-591f2c5886db';
UPDATE employee_hr_spokespersons SET hr_id = 'user-1002' WHERE hr_id = '487a8fb3-223d-40d8-a58a-591f2c5886db';

-- Step 3: Re-enable FK checks
SET FOREIGN_KEY_CHECKS = 1;

-- Step 4: Verify the migration
SELECT user_id, employee_code, CONCAT(first_name, ' ', last_name) as name 
FROM users 
WHERE user_id IN ('user-1001', 'user-1002');

-- Step 5: Check all users
SELECT user_id, employee_code, CONCAT(first_name, ' ', last_name) as name, LENGTH(user_id) as id_length
FROM users
WHERE deleted_at IS NULL
ORDER BY created_at;
