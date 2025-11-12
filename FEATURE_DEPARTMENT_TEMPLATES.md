# KPI Template Department & Admin Assignment Feature

## Summary
This update implements two major features:
1. **Department-based KPI Template Auto-Selection**: Templates can now be assigned to specific departments, and will automatically appear when assigning KPIs to employees in that department
2. **Super Admin KPI Assignment**: Super admins can now assign and edit KPI templates just like managers

## Database Changes

### 1. Added `department_id` to `kpi_templates` Table
**File**: `database/add_department_to_kpi_templates.sql`

```sql
ALTER TABLE kpi_templates
ADD COLUMN department_id VARCHAR(36) NULL AFTER template_name,
ADD KEY idx_department (department_id);

ALTER TABLE kpi_templates
ADD CONSTRAINT kpi_templates_department_fk 
FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE CASCADE;
```

**Purpose**: 
- Allows templates to be linked to specific departments
- NULL value = template available for all departments
- Non-NULL value = template auto-appears for employees in that department

## Model Changes

### 1. Admin_model.php - `get_templates_grouped()`
**Location**: Lines ~764-798

**Changes**:
- Added `$department_id` parameter
- Added department JOIN and filtering logic
- Returns templates filtered by department OR NULL department (available to all)

**Usage**:
```php
// Get templates for specific department
$templates = $this->admin_model->get_templates_grouped(NULL, $department_id);

// Get all templates
$templates = $this->admin_model->get_templates_grouped();
```

### 2. Admin_model.php - `create_template_batch()`
**Location**: Lines ~944-985

**Changes**:
- Accepts `department_id` in template_data array
- Inserts department_id into kpi_templates table
- NULL value means available to all departments

## Controller Changes

### 1. Manager.php - `assign_kpi()`
**Location**: Lines ~177-201

**Changes**:
- Fetches employee's department using `get_employee_full_details()`
- Passes department_id to `get_templates_grouped()`
- Templates are now auto-filtered by employee's department

**Before**:
```php
$data['templates'] = $this->admin_model->get_templates_grouped();
```

**After**:
```php
$employee_details = $this->admin_model->get_employee_full_details($employee_id);
$department_id = $employee_details->department_id ?? NULL;
$data['templates'] = $this->admin_model->get_templates_grouped(NULL, $department_id);
```

### 2. Admin.php - NEW `assign_kpi()` Method
**Location**: Lines ~1440-1514

**Features**:
- Complete KPI assignment functionality for super admin
- Same workflow as manager assignment
- Validates total weightage = 100%
- Uses department-based template filtering
- Reuses manager's `assign_kpi_template.php` view

**Access**: `http://localhost/KPI-portal/admin/assign_kpi/{employee_id}`

### 3. Admin.php - `create_template()`
**Location**: Lines ~757-806

**Changes**:
- Accepts `department_id` from POST data
- Passes departments list to view
- Handles NULL department_id for "all departments" templates

## View Changes

### 1. admin/employees/view.php
**Changes**:
- Added "Assign KPI Template" button next to "Edit KPIs" (when KPIs exist)
- Added card with "Assign KPI Template" button when NO KPIs exist
- Updated chat section condition to show properly

**New Buttons**:
```php
<!-- When KPIs exist -->
<a href="<?= base_url('admin/assign_kpi/' . $employee->employee_id) ?>" 
   class="btn btn-sm btn-success mr-2">
    <i class="fas fa-plus-circle"></i> Assign KPI Template
</a>

<!-- When no KPIs exist -->
<div class="card-body text-center py-5">
    <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
    <h5 class="text-muted">No KPIs Assigned Yet</h5>
    <a href="<?= base_url('admin/assign_kpi/' . $employee->employee_id) ?>" 
       class="btn btn-success btn-lg">
        <i class="fas fa-plus-circle"></i> Assign KPI Template
    </a>
</div>
```

### 2. admin/kpi_templates/create_new.php
**Changes**:
- Added department selection dropdown after template name field
- Department is OPTIONAL (NULL = available to all)
- Shows helpful info text explaining auto-selection

**New Field**:
```php
<div class="form-group">
    <label for="department_id">Department</label>
    <select class="form-control" id="department_id" name="department_id">
        <option value="">-- All Departments (No Restriction) --</option>
        <?php foreach ($departments as $dept): ?>
            <option value="<?= $dept->department_id ?>">
                <?= $dept->department_name ?> (<?= $dept->department_code ?>)
            </option>
        <?php endforeach; ?>
    </select>
    <small class="form-text text-muted">
        <i class="fas fa-info-circle"></i> If a department is selected, this template will auto-appear when assigning KPIs to employees in that department. 
        Leave empty to make it available for all departments.
    </small>
</div>
```

## How It Works

### Department-Based Template Selection
1. Employee belongs to "Copywriter" department
2. Manager/Admin opens employee profile to assign KPIs
3. System automatically shows:
   - Templates assigned to "Copywriter" department
   - Templates with NULL department (available to all)

### Super Admin KPI Assignment
1. Admin navigates to employee profile
2. Clicks "Assign KPI Template" button
3. Sees department-filtered template list
4. Customizes weightages
5. Assigns KPIs with single click

### Creating Department-Specific Templates
1. Admin creates new KPI template
2. Selects department from dropdown (or leaves empty)
3. Adds KPI items
4. Template automatically appears for employees in that department

## Benefits

1. **Automatic Template Filtering**: 
   - No need to search through irrelevant templates
   - Copywriter sees copywriter templates
   - Developer sees developer templates

2. **Flexible Template Management**:
   - Department-specific templates for specialized roles
   - General templates (NULL department) for common KPIs
   - Mix and match approach possible

3. **Super Admin Control**:
   - Admin can assign KPIs to any employee
   - Full access like managers
   - Consistent workflow across roles

4. **Scalability**:
   - Easy to add new department-specific templates
   - No code changes needed for new departments
   - Template reuse across employees in same department

## Testing Scenarios

### Scenario 1: Department-Specific Template
1. Create template "Copywriter KPIs" for "Copywriting" department
2. Open employee in Copywriting department
3. Click "Assign KPI Template"
4. Verify "Copywriter KPIs" template appears in list

### Scenario 2: General Template
1. Create template "General Performance" with NO department
2. Open any employee
3. Click "Assign KPI Template"
4. Verify "General Performance" appears for ALL employees

### Scenario 3: Super Admin Assignment
1. Login as super admin
2. Navigate to any employee: `http://localhost/KPI-portal/admin/view_employee/user-1007`
3. Click "Assign KPI Template"
4. Select template, customize weightages
5. Submit and verify KPIs assigned

## Migration Steps

If you already have existing templates:

```sql
-- Check current templates
SELECT template_name, COUNT(*) as kpi_count 
FROM kpi_templates 
GROUP BY template_name;

-- Update specific templates to be department-specific
UPDATE kpi_templates 
SET department_id = (SELECT department_id FROM departments WHERE department_code = 'COPY')
WHERE template_name = 'Copywriter Template';

-- Leave general templates as NULL (available to all)
-- No action needed - department_id defaults to NULL
```

## API Endpoints

### Admin Endpoints
- `GET  /admin/assign_kpi/{employee_id}` - Show KPI assignment form
- `POST /admin/assign_kpi/{employee_id}` - Process KPI assignment
- `POST /admin/assign_kpi/{employee_id}` (AJAX) - Load template KPIs

### Manager Endpoints (Updated)
- `GET  /manager/assign_kpi/{employee_id}` - Now uses department filtering
- `POST /manager/assign_kpi/{employee_id}` - Process with department awareness

## Notes

- Department filtering uses OR logic: `(department_id = X OR department_id IS NULL)`
- NULL department_id is intentional for general templates
- Foreign key cascade deletes templates when department is deleted
- Index on department_id improves query performance
- Admin and Manager share same assignment view (`manager/assign_kpi_template.php`)
