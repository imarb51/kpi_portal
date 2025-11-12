-- Add employee agreement status column to employee_kpis table
-- This tracks whether the employee has agreed to the assigned KPIs

ALTER TABLE `employee_kpis` 
ADD COLUMN `employee_agreement_status` VARCHAR(20) DEFAULT 'PENDING' 
    COMMENT 'PENDING, AGREED, REQUESTED_EDIT' AFTER `status`,
ADD COLUMN `employee_agreement_date` DATETIME NULL 
    COMMENT 'Date when employee agreed or requested edit' AFTER `employee_agreement_status`,
ADD COLUMN `employee_agreement_notes` TEXT NULL 
    COMMENT 'Employee notes when requesting edit' AFTER `employee_agreement_date`;

-- Update existing records to PENDING
UPDATE `employee_kpis` 
SET `employee_agreement_status` = 'PENDING' 
WHERE `employee_agreement_status` IS NULL;
