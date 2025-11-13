-- Option: Reset specific employee back to PENDING status 
-- so they can click "I Agree" again (which will now clear descriptions)
-- Replace 'user-1007' with the actual employee_id
-- Replace 'period-h2-2025' with the actual period_id

UPDATE employee_kpis 
SET 
    employee_agreement_status = 'PENDING',
    employee_agreement_date = NULL,
    is_locked = 0,
    is_finalized = 0
WHERE employee_id = 'user-1007' 
  AND review_period_id = 'period-h2-2025';

-- Verify the reset
SELECT 
    employee_kpi_id,
    employee_id,
    kpi_name,
    employee_agreement_status,
    is_finalized,
    description
FROM employee_kpis
WHERE employee_id = 'user-1007' 
  AND review_period_id = 'period-h2-2025'
ORDER BY kpi_name;
