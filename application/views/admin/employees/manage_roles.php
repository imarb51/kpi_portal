<div class="container-fluid mt-4">
    <h1 class="h3 mb-4">Manage Roles: <?= $employee->first_name . ' ' . $employee->last_name ?></h1>

    <div class="card shadow">
        <div class="card-body">
            <form method="post" action="<?= base_url('admin/manage_roles/' . $employee->employee_id) ?>">
                <div class="form-group">
                    <label>Select Roles</label>
                    <?php 
                    $current_roles = [];
                    foreach ($employee_roles as $er) {
                        $current_roles[] = $er->role_id;
                    }
                    $primary_role_id = '';
                    foreach ($employee_roles as $er) {
                        if ($er->is_primary) {
                            $primary_role_id = $er->role_id;
                        }
                    }
                    ?>
                    <?php foreach ($all_roles as $role): ?>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input role-checkbox" 
                                   id="role_<?= $role->role_id ?>" 
                                   name="roles[]" 
                                   value="<?= $role->role_id ?>"
                                   <?= in_array($role->role_id, $current_roles) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="role_<?= $role->role_id ?>">
                                <?= $role->role_name ?>
                                <small class="text-muted">(<?= $role->description ?>)</small>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-group">
                    <label>Primary Role <span class="text-danger">*</span></label>
                    <select class="form-control" name="primary_role" id="primary_role" required>
                        <option value="">-- Select Primary Role --</option>
                        <?php foreach ($all_roles as $role): ?>
                            <option value="<?= $role->role_id ?>" 
                                    <?= $primary_role_id == $role->role_id ? 'selected' : '' ?>>
                                <?= $role->role_name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">The primary role determines the default dashboard</small>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Roles
                    </button>
                    <a href="<?= base_url('admin/employees') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Update primary role dropdown based on selected checkboxes
document.querySelectorAll('.role-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        updatePrimaryRoleOptions();
    });
});

function updatePrimaryRoleOptions() {
    const selectedRoles = Array.from(document.querySelectorAll('.role-checkbox:checked'))
        .map(cb => cb.value);
    
    const primarySelect = document.getElementById('primary_role');
    const options = primarySelect.querySelectorAll('option');
    
    options.forEach(option => {
        if (option.value === '') return; // Skip placeholder
        
        if (selectedRoles.includes(option.value)) {
            option.disabled = false;
        } else {
            option.disabled = true;
            if (option.selected) {
                primarySelect.value = '';
            }
        }
    });
}

// Initialize on page load
updatePrimaryRoleOptions();
</script>
