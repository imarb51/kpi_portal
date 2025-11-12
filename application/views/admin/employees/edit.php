<div class="container-fluid mt-4">
    <h1 class="h3 mb-4">Edit Employee</h1>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <strong>Success!</strong> <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Error!</strong> <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (validation_errors()): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Validation Failed!</strong><br>
            <?= validation_errors() ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('debug_info')): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <strong>Debug Information:</strong>
            <button type="button" class="btn btn-sm btn-outline-dark float-right" onclick="document.getElementById('debug-details').style.display = document.getElementById('debug-details').style.display === 'none' ? 'block' : 'none'">Toggle Details</button>
            <div id="debug-details" style="display: none; margin-top: 10px;">
                <pre style="background: #f5f5f5; padding: 10px; font-size: 11px; max-height: 400px; overflow-y: auto;"><?= htmlspecialchars(print_r(json_decode($this->session->flashdata('debug_info'), true), TRUE)) ?></pre>
            </div>
            <button type="button" class="close" data-dismiss="alert" style="margin-top: -20px;">&times;</button>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body">
            <form method="post" action="<?= base_url('admin/edit_employee/' . $employee->employee_id) ?>">
                <h5 class="mb-3">Personal Information</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Employee Code</label>
                            <input type="text" class="form-control" value="<?= $employee->employee_code ?>" disabled>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" 
                                   value="<?= set_value('first_name', $employee->first_name) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="last_name" 
                                   value="<?= set_value('last_name', $employee->last_name) ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" class="form-control" name="phone_number" 
                                   value="<?= set_value('phone_number', $employee->phone_number) ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?= set_value('email', $employee->email) ?>">
                        </div>
                    </div>
                </div>

                <hr>
                <h5 class="mb-3">Work Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Department <span class="text-danger">*</span></label>
                            <select class="form-control" name="department_id" id="department_id" required>
                                <option value="">-- Select --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept->department_id ?>" 
                                            <?= set_select('department_id', $dept->department_id, $employee->department_id == $dept->department_id) ?>>
                                        <?= $dept->department_name ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Designation <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="designation" 
                                   value="<?= set_value('designation', isset($employee->designation) ? $employee->designation : '') ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Reporting Managers <span class="text-danger">*</span></label>
                            <select class="form-control" name="reporting_manager_ids[]" id="reporting_managers" multiple required style="height: 120px;">
                                <option value="">Loading managers...</option>
                            </select>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Hold Ctrl (Windows) or Cmd (Mac) to select multiple managers. First selected will be primary. <strong>Managers are filtered by selected department.</strong>
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>HR Spokespersons <span class="text-danger">*</span></label>
                            <select class="form-control" name="hr_spokesperson_ids[]" id="hr_spokespersons" multiple required style="height: 120px;">
                                <option value="">Loading...</option>
                            </select>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Hold Ctrl (Windows) or Cmd (Mac) to select multiple HR persons. First selected will be primary. <strong>Shows all employees with HR role.</strong>
                            </small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Employment Type</label>
                            <select class="form-control" name="employment_type">
                                <option value="Full-time" <?= set_select('employment_type', 'Full-time', (isset($employee->employment_type) && $employee->employment_type == 'Full-time') || !isset($employee->employment_type)) ?>>Full-time</option>
                                <option value="Part-time" <?= set_select('employment_type', 'Part-time', isset($employee->employment_type) && $employee->employment_type == 'Part-time') ?>>Part-time</option>
                                <option value="Contract" <?= set_select('employment_type', 'Contract', isset($employee->employment_type) && $employee->employment_type == 'Contract') ?>>Contract</option>
                                <option value="Intern" <?= set_select('employment_type', 'Intern', isset($employee->employment_type) && $employee->employment_type == 'Intern') ?>>Intern</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Employee Status <span class="text-danger">*</span></label>
                            <select class="form-control" name="employee_status" required>
                                <option value="ACTIVE" <?= set_select('employee_status', 'ACTIVE', (isset($employee->employee_status) && $employee->employee_status == 'ACTIVE') || !isset($employee->employee_status)) ?>>Active</option>
                                <option value="INACTIVE" <?= set_select('employee_status', 'INACTIVE', isset($employee->employee_status) && $employee->employee_status == 'INACTIVE') ?>>Inactive</option>
                            </select>
                            <small class="form-text text-muted">Set to Inactive to disable employee account</small>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Update Employee
                    </button>
                    <a href="<?= base_url('admin/employees') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Debug form submission
document.querySelector('form').addEventListener('submit', function(e) {
    console.log('Form submitted!');
    console.log('Form action:', this.action);
    console.log('Form method:', this.method);
    
    // Log all form data
    const formData = new FormData(this);
    console.log('Form Data:');
    for (let [key, value] of formData.entries()) {
        console.log(`  ${key}: ${value}`);
    }
    
    // Check for empty required fields
    const requiredFields = this.querySelectorAll('[required]');
    let hasEmptyFields = false;
    requiredFields.forEach(field => {
        if (!field.value || field.value.trim() === '') {
            console.error(`Required field is empty: ${field.name}`);
            hasEmptyFields = true;
        }
    });
    
    if (hasEmptyFields) {
        console.error('Form has empty required fields! Submission may fail.');
    } else {
        console.log('All required fields are filled.');
    }
    
    // Disable submit button to prevent double submission
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
});

// Log current employee data on page load
console.log('Current Employee Data:', <?= json_encode($employee) ?>);
console.log('Employee ID:', '<?= $employee->employee_id ?>');

// Check if work_info exists
<?php 
$work_info_exists = $this->db->where('employee_id', $employee->employee_id)->get('employee_work_info')->num_rows() > 0;
?>
console.log('Work Info Exists:', <?= $work_info_exists ? 'true' : 'false' ?>);

<?php if (!$work_info_exists): ?>
console.warn('⚠️ WARNING: This employee does not have work_info! Update may fail if required fields are missing.');
<?php endif; ?>

// ============================================
// Department-based filtering for managers and HR (EDIT MODE)
// ============================================
const departmentSelect = document.getElementById('department_id');
const managersSelect = document.getElementById('reporting_managers');
const hrSelect = document.getElementById('hr_spokespersons');

// Store current selections from PHP
const currentManagerIds = <?= !empty($employee->reporting_managers) ? json_encode(array_column($employee->reporting_managers, 'employee_id')) : '[]' ?>;
const currentHrIds = <?= !empty($employee->hr_spokespersons) ? json_encode(array_column($employee->hr_spokespersons, 'employee_id')) : '[]' ?>;

console.log('Current Manager IDs:', currentManagerIds);
console.log('Current HR IDs:', currentHrIds);

// Load managers when department changes
departmentSelect.addEventListener('change', function() {
    const departmentId = this.value;
    
    if (!departmentId) {
        managersSelect.innerHTML = '<option value="">-- Select Department First --</option>';
        return;
    }
    
    // Show loading
    managersSelect.innerHTML = '<option value="">Loading managers...</option>';
    
    // Fetch managers for this department
    fetch('<?= base_url("admin/get_managers_by_department") ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'department_id=' + departmentId
    })
    .then(response => response.json())
    .then(data => {
        managersSelect.innerHTML = '';
        
        if (data.length === 0) {
            managersSelect.innerHTML = '<option value="">No managers found in this department</option>';
        } else {
            data.forEach(manager => {
                if (manager.employee_id !== '<?= $employee->employee_id ?>') {
                    const option = document.createElement('option');
                    option.value = manager.employee_id;
                    option.textContent = manager.employee_code + ' - ' + manager.first_name + ' ' + manager.last_name + ' (' + manager.designation + ')';
                    // Pre-select if this manager was previously selected
                    option.selected = currentManagerIds.includes(manager.employee_id);
                    managersSelect.appendChild(option);
                }
            });
        }
    })
    .catch(error => {
        console.error('Error loading managers:', error);
        managersSelect.innerHTML = '<option value="">Error loading managers</option>';
    });
});

// Load HR spokespersons on page load
document.addEventListener('DOMContentLoaded', function() {
    // Show loading
    hrSelect.innerHTML = '<option value="">Loading HR persons...</option>';
    
    // Fetch HR spokespersons
    fetch('<?= base_url("admin/get_hr_spokespersons") ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    })
    .then(response => response.json())
    .then(data => {
        hrSelect.innerHTML = '';
        
        if (data.length === 0) {
            hrSelect.innerHTML = '<option value="">No HR persons found</option>';
        } else {
            data.forEach(hr => {
                if (hr.employee_id !== '<?= $employee->employee_id ?>') {
                    const option = document.createElement('option');
                    option.value = hr.employee_id;
                    option.textContent = hr.employee_code + ' - ' + hr.first_name + ' ' + hr.last_name + ' (' + hr.designation + ')';
                    // Pre-select if this HR person was previously selected
                    option.selected = currentHrIds.includes(hr.employee_id);
                    hrSelect.appendChild(option);
                }
            });
        }
    })
    .catch(error => {
        console.error('Error loading HR persons:', error);
        hrSelect.innerHTML = '<option value="">Error loading HR persons</option>';
    });
    
    // Trigger manager load based on current department
    if (departmentSelect.value) {
        departmentSelect.dispatchEvent(new Event('change'));
    }
});
</script>
