-- Create employee_managers junction table for multiple manager mapping
-- This allows each employee to have unlimited managers

CREATE TABLE IF NOT EXISTS `employee_managers` (
  `id` VARCHAR(36) NOT NULL,
  `employee_id` VARCHAR(36) NOT NULL,
  `manager_id` VARCHAR(36) NOT NULL,
  `priority_order` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_employee_manager` (`employee_id`, `manager_id`),
  KEY `idx_employee_id` (`employee_id`),
  KEY `idx_manager_id` (`manager_id`),
  CONSTRAINT `fk_em_employee` FOREIGN KEY (`employee_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_em_manager` FOREIGN KEY (`manager_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrate existing data from employee_work_info (only valid manager IDs)
INSERT INTO `employee_managers` (`id`, `employee_id`, `manager_id`, `priority_order`)
SELECT 
    UUID(),
    ewi.`employee_id`,
    ewi.`reporting_manager_id`,
    1
FROM `employee_work_info` ewi
INNER JOIN `users` u ON ewi.`reporting_manager_id` = u.`user_id`
WHERE ewi.`reporting_manager_id` IS NOT NULL;

-- Add backup managers as priority 2 (only if they exist in users table)
INSERT INTO `employee_managers` (`id`, `employee_id`, `manager_id`, `priority_order`)
SELECT 
    UUID(),
    ewi.`employee_id`,
    ewi.`reporting_manager_id_backup`,
    2
FROM `employee_work_info` ewi
INNER JOIN `users` u ON ewi.`reporting_manager_id_backup` = u.`user_id`
WHERE ewi.`reporting_manager_id_backup` IS NOT NULL;

-- Now you can add as many managers as needed:
-- Example: Add a 3rd manager
-- INSERT INTO employee_managers (id, employee_id, manager_id, priority_order)
-- VALUES (UUID(), 'user-1007', 'user-new-manager', 3);
