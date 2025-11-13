-- Add remark column to kpi_scores table for manager's score remarks
-- This allows each employee to have their own remarks, separate from the template description

ALTER TABLE kpi_scores 
ADD COLUMN remark TEXT NULL AFTER score;

-- Verify the column was added
DESCRIBE kpi_scores;
