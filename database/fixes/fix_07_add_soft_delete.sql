-- ============================================================
-- FIX #7: Add Soft Delete Capability
-- Prevent permanent data loss
-- ============================================================

-- Step 1: Add deleted_at and deleted_by columns to critical tables
-- ============================================================

-- employee_kpis
ALTER TABLE employee_kpis 
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete timestamp',
ADD COLUMN deleted_by VARCHAR(36) NULL DEFAULT NULL COMMENT 'User who deleted this record',
ADD KEY idx_deleted (deleted_at);

ALTER TABLE employee_kpis 
ADD CONSTRAINT fk_kpi_deleted_by 
FOREIGN KEY (deleted_by) REFERENCES users(user_id) ON DELETE SET NULL;

-- kpi_scores (already cascades from employee_kpis, but add for safety)
ALTER TABLE kpi_scores 
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete timestamp',
ADD COLUMN deleted_by VARCHAR(36) NULL DEFAULT NULL COMMENT 'User who deleted this record',
ADD KEY idx_deleted (deleted_at);

ALTER TABLE kpi_scores 
ADD CONSTRAINT fk_score_deleted_by 
FOREIGN KEY (deleted_by) REFERENCES users(user_id) ON DELETE SET NULL;

-- employees
ALTER TABLE employees 
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete timestamp',
ADD COLUMN deleted_by VARCHAR(36) NULL DEFAULT NULL COMMENT 'User who deleted this record',
ADD KEY idx_deleted (deleted_at);

ALTER TABLE employees 
ADD CONSTRAINT fk_employee_deleted_by 
FOREIGN KEY (deleted_by) REFERENCES users(user_id) ON DELETE SET NULL;

-- users
ALTER TABLE users 
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete timestamp',
ADD COLUMN deleted_by VARCHAR(36) NULL DEFAULT NULL COMMENT 'Admin who deleted this user',
ADD KEY idx_deleted (deleted_at);

-- kpi_templates
ALTER TABLE kpi_templates 
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete timestamp',
ADD COLUMN deleted_by VARCHAR(36) NULL DEFAULT NULL COMMENT 'User who deleted this template',
ADD KEY idx_deleted (deleted_at);

ALTER TABLE kpi_templates 
ADD CONSTRAINT fk_template_deleted_by 
FOREIGN KEY (deleted_by) REFERENCES users(user_id) ON DELETE SET NULL;

-- ============================================================
-- Step 2: Create views that automatically filter deleted records
-- ============================================================

CREATE OR REPLACE VIEW v_active_employees AS
SELECT * FROM employees 
WHERE deleted_at IS NULL;

CREATE OR REPLACE VIEW v_active_users AS
SELECT * FROM users 
WHERE deleted_at IS NULL;

CREATE OR REPLACE VIEW v_active_employee_kpis AS
SELECT * FROM employee_kpis 
WHERE deleted_at IS NULL;

CREATE OR REPLACE VIEW v_active_kpi_scores AS
SELECT * FROM kpi_scores 
WHERE deleted_at IS NULL;

CREATE OR REPLACE VIEW v_active_kpi_templates AS
SELECT * FROM kpi_templates 
WHERE deleted_at IS NULL;

-- ============================================================
-- Step 3: Create stored procedures for soft delete operations
-- ============================================================

DELIMITER $$

-- Soft delete an employee KPI
CREATE PROCEDURE sp_soft_delete_employee_kpi(
    IN p_employee_kpi_id VARCHAR(36),
    IN p_deleted_by VARCHAR(36)
)
BEGIN
    -- Check if already deleted
    IF EXISTS (SELECT 1 FROM employee_kpis WHERE employee_kpi_id = p_employee_kpi_id AND deleted_at IS NOT NULL) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'KPI already deleted';
    END IF;
    
    -- Soft delete the KPI
    UPDATE employee_kpis 
    SET deleted_at = NOW(),
        deleted_by = p_deleted_by
    WHERE employee_kpi_id = p_employee_kpi_id;
    
    -- Also soft delete associated score
    UPDATE kpi_scores 
    SET deleted_at = NOW(),
        deleted_by = p_deleted_by
    WHERE employee_kpi_id = p_employee_kpi_id
    AND deleted_at IS NULL;
    
    SELECT 'KPI soft deleted successfully' as message;
END$$

-- Restore a soft-deleted KPI
CREATE PROCEDURE sp_restore_employee_kpi(
    IN p_employee_kpi_id VARCHAR(36)
)
BEGIN
    -- Check if deleted
    IF NOT EXISTS (SELECT 1 FROM employee_kpis WHERE employee_kpi_id = p_employee_kpi_id AND deleted_at IS NOT NULL) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'KPI is not deleted';
    END IF;
    
    -- Restore the KPI
    UPDATE employee_kpis 
    SET deleted_at = NULL,
        deleted_by = NULL
    WHERE employee_kpi_id = p_employee_kpi_id;
    
    -- Restore associated score
    UPDATE kpi_scores 
    SET deleted_at = NULL,
        deleted_by = NULL
    WHERE employee_kpi_id = p_employee_kpi_id;
    
    SELECT 'KPI restored successfully' as message;
END$$

-- Soft delete an employee
CREATE PROCEDURE sp_soft_delete_employee(
    IN p_employee_id VARCHAR(36),
    IN p_deleted_by VARCHAR(36)
)
BEGIN
    DECLARE v_user_id VARCHAR(36);
    
    -- Get associated user_id
    SELECT user_id INTO v_user_id FROM employees WHERE employee_id = p_employee_id;
    
    -- Check if already deleted
    IF EXISTS (SELECT 1 FROM employees WHERE employee_id = p_employee_id AND deleted_at IS NOT NULL) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Employee already deleted';
    END IF;
    
    -- Soft delete employee
    UPDATE employees 
    SET deleted_at = NOW(),
        deleted_by = p_deleted_by
    WHERE employee_id = p_employee_id;
    
    -- Soft delete associated user
    UPDATE users 
    SET deleted_at = NOW(),
        deleted_by = p_deleted_by
    WHERE user_id = v_user_id;
    
    -- Soft delete all KPIs
    UPDATE employee_kpis 
    SET deleted_at = NOW(),
        deleted_by = p_deleted_by
    WHERE employee_id = p_employee_id
    AND deleted_at IS NULL;
    
    -- Soft delete all scores
    UPDATE kpi_scores ks
    JOIN employee_kpis ek ON ks.employee_kpi_id = ek.employee_kpi_id
    SET ks.deleted_at = NOW(),
        ks.deleted_by = p_deleted_by
    WHERE ek.employee_id = p_employee_id
    AND ks.deleted_at IS NULL;
    
    SELECT 'Employee and all associated data soft deleted successfully' as message;
END$$

-- Permanently delete old soft-deleted records (run periodically)
CREATE PROCEDURE sp_purge_old_deleted_records(
    IN p_days_old INT
)
BEGIN
    DECLARE v_cutoff_date TIMESTAMP;
    SET v_cutoff_date = DATE_SUB(NOW(), INTERVAL p_days_old DAY);
    
    -- Delete scores older than cutoff
    DELETE FROM kpi_scores 
    WHERE deleted_at IS NOT NULL 
    AND deleted_at < v_cutoff_date;
    
    -- Delete KPIs older than cutoff
    DELETE FROM employee_kpis 
    WHERE deleted_at IS NOT NULL 
    AND deleted_at < v_cutoff_date;
    
    -- Don't permanently delete employees/users - keep for historical reference
    
    SELECT CONCAT('Purged records deleted before ', v_cutoff_date) as message;
END$$

DELIMITER ;

-- ============================================================
-- Step 4: Create deleted records archive view
-- ============================================================

CREATE OR REPLACE VIEW v_deleted_records_summary AS
SELECT 
    'employee_kpis' as table_name,
    COUNT(*) as deleted_count,
    MIN(deleted_at) as oldest_deletion,
    MAX(deleted_at) as newest_deletion
FROM employee_kpis 
WHERE deleted_at IS NOT NULL
UNION ALL
SELECT 
    'kpi_scores',
    COUNT(*),
    MIN(deleted_at),
    MAX(deleted_at)
FROM kpi_scores 
WHERE deleted_at IS NOT NULL
UNION ALL
SELECT 
    'employees',
    COUNT(*),
    MIN(deleted_at),
    MAX(deleted_at)
FROM employees 
WHERE deleted_at IS NOT NULL
UNION ALL
SELECT 
    'users',
    COUNT(*),
    MIN(deleted_at),
    MAX(deleted_at)
FROM users 
WHERE deleted_at IS NOT NULL
UNION ALL
SELECT 
    'kpi_templates',
    COUNT(*),
    MIN(deleted_at),
    MAX(deleted_at)
FROM kpi_templates 
WHERE deleted_at IS NOT NULL;

-- ============================================================
-- Step 5: Update foreign key constraints to prevent cascade deletes
-- ============================================================

-- Note: You'll need to update your application code to use soft deletes
-- instead of hard deletes. The CASCADE constraints should remain but 
-- won't be triggered if you use soft deletes properly.

-- Add audit trigger for deletions
DELIMITER $$

CREATE TRIGGER audit_employee_kpi_deletion
BEFORE UPDATE ON employee_kpis
FOR EACH ROW
BEGIN
    IF NEW.deleted_at IS NOT NULL AND OLD.deleted_at IS NULL THEN
        INSERT INTO audit_logs (log_id, user_id, action, entity_type, entity_id, old_values, timestamp)
        VALUES (
            UUID(),
            NEW.deleted_by,
            'SOFT_DELETE',
            'employee_kpis',
            NEW.employee_kpi_id,
            JSON_OBJECT(
                'employee_id', OLD.employee_id,
                'template_id', OLD.template_id,
                'review_period_id', OLD.review_period_id,
                'status', OLD.status
            ),
            NOW()
        );
    END IF;
END$$

DELIMITER ;

-- ============================================================
-- VERIFICATION
-- ============================================================

-- Show soft delete columns
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    COLUMN_TYPE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'kpi_portal'
AND COLUMN_NAME IN ('deleted_at', 'deleted_by')
ORDER BY TABLE_NAME, COLUMN_NAME;

-- Test soft delete (DONT RUN IN PRODUCTION)
-- CALL sp_soft_delete_employee_kpi('some-kpi-id', 'user-admin-001');

-- View deleted records
SELECT * FROM v_deleted_records_summary;

-- ============================================================
-- ROLLBACK (if needed)
-- ============================================================

-- ALTER TABLE employee_kpis DROP COLUMN deleted_at, DROP COLUMN deleted_by;
-- ALTER TABLE kpi_scores DROP COLUMN deleted_at, DROP COLUMN deleted_by;
-- ALTER TABLE employees DROP COLUMN deleted_at, DROP COLUMN deleted_by;
-- ALTER TABLE users DROP COLUMN deleted_at, DROP COLUMN deleted_by;
-- ALTER TABLE kpi_templates DROP COLUMN deleted_at, DROP COLUMN deleted_by;
-- DROP PROCEDURE IF EXISTS sp_soft_delete_employee_kpi;
-- DROP PROCEDURE IF EXISTS sp_restore_employee_kpi;
-- DROP PROCEDURE IF EXISTS sp_soft_delete_employee;
-- DROP PROCEDURE IF EXISTS sp_purge_old_deleted_records;
-- DROP VIEW IF EXISTS v_active_employees;
-- DROP VIEW IF EXISTS v_active_users;
-- DROP VIEW IF EXISTS v_active_employee_kpis;
-- DROP VIEW IF EXISTS v_active_kpi_scores;
-- DROP VIEW IF EXISTS v_active_kpi_templates;
-- DROP VIEW IF EXISTS v_deleted_records_summary;
-- DROP TRIGGER IF EXISTS audit_employee_kpi_deletion;
