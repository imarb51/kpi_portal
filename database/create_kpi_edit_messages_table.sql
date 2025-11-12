-- Table to store chat-like conversation for KPI edit requests
-- Each message is a back-and-forth between employee and manager/admin

CREATE TABLE IF NOT EXISTS `kpi_edit_messages` (
    `message_id` VARCHAR(36) PRIMARY KEY,
    `employee_kpi_id` VARCHAR(36) NOT NULL,
    `employee_id` VARCHAR(36) NOT NULL,
    `sender_id` VARCHAR(36) NOT NULL,
    `sender_type` ENUM('EMPLOYEE', 'MANAGER', 'ADMIN') NOT NULL,
    `message` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`employee_kpi_id`) REFERENCES `employee_kpis`(`employee_kpi_id`) ON DELETE CASCADE,
    FOREIGN KEY (`employee_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    
    INDEX `idx_employee_kpi` (`employee_kpi_id`),
    INDEX `idx_employee` (`employee_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add comment for documentation
ALTER TABLE `kpi_edit_messages` COMMENT = 'Stores chat messages for KPI edit request conversations between employees and managers/admins';
