-- Modify employee_work_info to support comma-separated multiple managers
-- Change reporting_manager_id to TEXT to support multiple comma-separated IDs

ALTER TABLE `employee_work_info` 
MODIFY COLUMN `reporting_manager_id` TEXT NOT NULL;

-- Migrate existing data: Combine reporting_manager_id and reporting_manager_id_backup into comma-separated list
UPDATE `employee_work_info`
SET `reporting_manager_id` = CONCAT_WS(',', 
    `reporting_manager_id`, 
    NULLIF(`reporting_manager_id_backup`, '')
)
WHERE `reporting_manager_id_backup` IS NOT NULL AND `reporting_manager_id_backup` != '';

-- Remove the backup column as it's now merged
ALTER TABLE `employee_work_info`
DROP COLUMN `reporting_manager_id_backup`;

-- Example of how to store multiple managers:
-- UPDATE employee_work_info 
-- SET reporting_manager_id = 'user-sd-001,user-wd-001,user-admin-001'
-- WHERE employee_id = 'user-1007';

-- To query employees of a specific manager, use:
-- SELECT * FROM employee_work_info 
-- WHERE FIND_IN_SET('user-sd-001', reporting_manager_id) > 0;
