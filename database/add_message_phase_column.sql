-- Add phase column to kpi_edit_messages to separate Setup Mode and Scoring Mode conversations
-- SETUP: Messages during KPI structure setup (before employee agrees)
-- SCORING: Messages during performance scoring (after agree, before finalize)

ALTER TABLE kpi_edit_messages 
ADD COLUMN phase ENUM('SETUP', 'SCORING') NOT NULL DEFAULT 'SETUP' AFTER message;

-- Add index for faster filtering
ALTER TABLE kpi_edit_messages 
ADD INDEX idx_phase_created (phase, created_at);

-- Verify the changes
DESCRIBE kpi_edit_messages;
