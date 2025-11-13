-- Clear old description/remark data for KPIs that are in Scoring Mode (AGREED but not finalized)
-- This is a one-time cleanup for existing data
-- Run this in your database to clear the old "test" and "N/A" remarks

UPDATE employee_kpis 
SET description = NULL 
WHERE employee_agreement_status = 'AGREED' 
  AND (is_finalized = 0 OR is_finalized IS NULL);

-- Verify the update
SELECT 
    employee_kpi_id,
    employee_id,
    kpi_name,
    employee_agreement_status,
    is_finalized,
    description
FROM employee_kpis
WHERE employee_agreement_status = 'AGREED' 
  AND (is_finalized = 0 OR is_finalized IS NULL)
ORDER BY employee_id, kpi_name;
