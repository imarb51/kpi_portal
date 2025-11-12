<div class="page-header">
    <h2><i class="fas fa-key"></i> Change Password</h2>
    <p class="mb-0">Update your account password</p>
</div>

<div class="content-wrapper">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow">
                <div class="card-body">
                    <?php 
                    $user_role = strtolower($this->session->userdata('primary_role'));
                    $primary_role = strtolower($this->session->userdata('primary_role'));
                    $action_url = ($primary_role === 'super_admin' ? 'admin' : $primary_role) . '/change_password';
                    ?>
                    <form method="post" action="<?= base_url($action_url) ?>" id="changePasswordForm">
                        
                        <div class="form-group">
                            <label for="current_password">
                                Current Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="current_password" 
                                       name="current_password" 
                                       required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="current_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <?= form_error('current_password', '<div class="text-danger small">', '</div>') ?>
                        </div>

                        <div class="form-group">
                            <label for="new_password">
                                New Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="new_password" 
                                       name="new_password" 
                                       required 
                                       minlength="6">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="new_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Minimum 6 characters. Use a mix of letters, numbers and symbols for better security.
                            </small>
                            <?= form_error('new_password', '<div class="text-danger small">', '</div>') ?>
                            <div id="password-strength" class="mt-2"></div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">
                                Confirm New Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="confirm_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <?= form_error('confirm_password', '<div class="text-danger small">', '</div>') ?>
                            <div id="password-match-msg" class="mt-1"></div>
                        </div>

                        <div class="alert alert-info">
                            <strong><i class="fas fa-info-circle"></i> Password Requirements:</strong>
                            <ul class="mb-0 mt-2">
                                <li>At least 6 characters long</li>
                                <li>Should not be the same as your current password</li>
                                <li>Recommended: Mix of uppercase, lowercase, numbers and special characters</li>
                            </ul>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save"></i> Change Password
                            </button>
                            
                            <a href="<?= base_url(uri: ($user_role === 'super_admin' ? 'admin' : $user_role) . '/dashboard') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = this.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
});

// Password strength checker
document.getElementById('new_password').addEventListener('input', function() {
    const password = this.value;
    const strengthDiv = document.getElementById('password-strength');
    
    if (password.length === 0) {
        strengthDiv.innerHTML = '';
        return;
    }
    
    let strength = 0;
    let message = '';
    let colorClass = '';
    
    // Length check
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    
    // Character type checks
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    // Determine strength level
    if (strength <= 2) {
        message = '❌ Weak';
        colorClass = 'text-danger';
    } else if (strength <= 4) {
        message = '⚠️ Medium';
        colorClass = 'text-warning';
    } else {
        message = '✅ Strong';
        colorClass = 'text-success';
    }
    
    strengthDiv.innerHTML = `<small class="${colorClass}"><strong>Password Strength: ${message}</strong></small>`;
});

// Password match checker
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    const matchMsg = document.getElementById('password-match-msg');
    const submitBtn = document.getElementById('submitBtn');
    
    if (confirmPassword.length === 0) {
        matchMsg.innerHTML = '';
        submitBtn.disabled = false;
        return;
    }
    
    if (newPassword === confirmPassword) {
        matchMsg.innerHTML = '<small class="text-success">✅ Passwords match</small>';
        submitBtn.disabled = false;
    } else {
        matchMsg.innerHTML = '<small class="text-danger">❌ Passwords do not match</small>';
        submitBtn.disabled = true;
    }
});

// Form validation
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('New password and confirm password do not match!');
        return false;
    }
    
    if (newPassword.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters long!');
        return false;
    }
});
</script>
</div> <!-- End content-wrapper -->
