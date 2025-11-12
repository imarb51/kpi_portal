-- Add department_id column to kpi_templates table
-- This allows KPI templates to be automatically fetched based on employee's department

ALTER TABLE kpi_templates
ADD COLUMN department_id VARCHAR(36) NULL AFTER template_name,
ADD KEY idx_department (department_id);

-- Add foreign key constraint
ALTER TABLE kpi_templates
ADD CONSTRAINT kpi_templates_department_fk 
FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE CASCADE;

-- Note: NULL department_id means the template is available for all departments
