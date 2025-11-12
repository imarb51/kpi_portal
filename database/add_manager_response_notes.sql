-- Add manager response notes column to employee_kpis table
-- This stores the manager's explanation when they edit KPIs in response to employee requests

ALTER TABLE `employee_kpis` 
ADD COLUMN `manager_response_notes` TEXT NULL COMMENT 'Manager response/explanation after editing KPIs',
ADD COLUMN `manager_responded_at` DATETIME NULL COMMENT 'When manager responded to edit request',
ADD COLUMN `manager_responded_by` VARCHAR(36) NULL COMMENT 'Manager who responded',
ADD CONSTRAINT `fk_manager_responded_by` FOREIGN KEY (`manager_responded_by`) REFERENCES `users`(`user_id`) ON DELETE SET NULL;
