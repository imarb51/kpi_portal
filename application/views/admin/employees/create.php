<div class="container-fluid mt-4">
    <h1 class="h3 mb-4">Create Employee</h1>

    <div class="card shadow">
        <div class="card-body">
            <form method="post" action="<?= base_url('admin/create_employee') ?>">
                <h5 class="mb-3">Account Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required value="<?= set_value('email') ?>">
                            <small class="form-text text-muted">This will be used for login</small>
                            <?= form_error('email', '<div class="text-danger small">', '</div>') ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" id="password" required minlength="6">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info" id="generatePassword" title="Generate Random Password">
                                        <i class="fas fa-random"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Minimum 6 characters. Click <i class="fas fa-random"></i> to generate.</small>
                            <?= form_error('password', '<div class="text-danger small">', '</div>') ?>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="send_welcome_email" name="send_welcome_email" value="1" checked>
                                <label class="custom-control-label" for="send_welcome_email">
                                    <i class="fas fa-envelope"></i> Send welcome email with login credentials to employee
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Email will be sent to the email address above with username and password
                            </small>
                        </div>
                    </div>
                </div>

                <hr>
                <h5 class="mb-3">Personal Information</h5>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Employee Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="employee_code" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                        
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" class="form-control" name="phone_number" placeholder="+91-9999999999">
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
                                    <option value="<?= $dept->department_id ?>"><?= $dept->department_name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Designation <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="designation" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Reporting Managers <span class="text-danger">*</span></label>
                            <select class="form-control" name="reporting_manager_ids[]" id="reporting_managers" multiple required style="height: 120px;">
                                <option value="">-- Select Department First --</option>
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
                            <label>Date of Joining <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="date_of_joining" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Employment Type</label>
                            <select class="form-control" name="employment_type">
                                <option value="Full-time">Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Contract">Contract</option>
                                <option value="Intern">Intern</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Employee
                    </button>
                    <a href="<?= base_url('admin/employees') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Generate random password
document.getElementById('generatePassword').addEventListener('click', function() {
    const length = 12;
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
    let password = "";
    
    for (let i = 0; i < length; i++) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
    }
    
    document.getElementById('password').value = password;
    document.getElementById('password').type = 'text'; // Show generated password
    
    // Update toggle button icon
    const icon = document.getElementById('togglePassword').querySelector('i');
    icon.classList.remove('fa-eye');
    icon.classList.add('fa-eye-slash');
    
    // Show notification
    alert('Random password generated! Please copy it before saving.');
});

// ============================================
// Department-based filtering for managers and HR
// ============================================
const departmentSelect = document.getElementById('department_id');
const managersSelect = document.getElementById('reporting_managers');
const hrSelect = document.getElementById('hr_spokespersons');

// Load managers when department changes
departmentSelect.addEventListener('change', function() {
    const departmentId = this.value;
    
    console.log('Department changed to:', departmentId);
    
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
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Managers data received:', data);
        managersSelect.innerHTML = '';
        
        if (data.length === 0) {
            managersSelect.innerHTML = '<option value="">No managers found in this department</option>';
        } else {
            data.forEach(manager => {
                const option = document.createElement('option');
                option.value = manager.employee_id;
                option.textContent = manager.employee_code + ' - ' + manager.first_name + ' ' + manager.last_name + ' (' + manager.designation + ')';
                managersSelect.appendChild(option);
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
                const option = document.createElement('option');
                option.value = hr.employee_id;
                option.textContent = hr.employee_code + ' - ' + hr.first_name + ' ' + hr.last_name + ' (' + hr.designation + ')';
                hrSelect.appendChild(option);
            });
        }
    })
    .catch(error => {
        console.error('Error loading HR persons:', error);
        hrSelect.innerHTML = '<option value="">Error loading HR persons</option>';
    });
    
    // IMPORTANT: Trigger department change if a department is already selected
    if (departmentSelect.value) {
        console.log('Department already selected:', departmentSelect.value);
        departmentSelect.dispatchEvent(new Event('change'));
    }
});
</script>
