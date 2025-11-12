<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Employees</h1>
        <a href="<?= base_url('admin/create_employee') ?>" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Add Employee
        </a>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Search & Filter -->
    <div class="card shadow mb-3">
        <div class="card-body">
            <form method="get" action="<?= base_url('admin/employees') ?>" class="form-inline">
                <input type="text" name="search" class="form-control mr-2" placeholder="Search by name, code, email..." 
                       value="<?= $search ?? '' ?>">
                <select name="status" class="form-control mr-2">
                    <option value="">All Status</option>
                    <option value="ACTIVE" <?= ($status ?? '') == 'ACTIVE' ? 'selected' : '' ?>>Active</option>
                    <option value="INACTIVE" <?= ($status ?? '') == 'INACTIVE' ? 'selected' : '' ?>>Inactive</option>
                </select>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="<?= base_url('admin/employees') ?>" class="btn btn-secondary ml-2">Clear</a>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <?php if (!empty($employees)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Designation</th>
                                <th>Department</th>
                                <th>Managers</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $emp): ?>
                            <tr>
                                <td><strong><?= $emp->employee_code ?></strong></td>
                                <td><?= $emp->first_name . ' ' . $emp->last_name ?></td>
                                <td><?= $emp->email ?></td>
                                <td><?= $emp->designation ?? '-' ?></td>
                                <td><?= $emp->department_name ?? '-' ?></td>
                                <td>
                                    <?php if (!empty($emp->reporting_managers)): ?>
                                        <?php foreach ($emp->reporting_managers as $mgr): ?>
                                            <span class="badge badge-<?= $mgr->is_primary ? 'primary' : 'info' ?> mr-1 mb-1" style="font-size: 0.75rem;">
                                                <?= $mgr->first_name . ' ' . $mgr->last_name ?>
                                                <?= $mgr->is_primary ? '★' : '' ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($emp->employee_status == 'ACTIVE' && $emp->user_active): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('admin/view_employee/' . $emp->employee_id) ?>" 
                                           class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= base_url('admin/edit_employee/' . $emp->employee_id) ?>" 
                                           class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= base_url('admin/manage_roles/' . $emp->employee_id) ?>" 
                                           class="btn btn-sm btn-warning" title="Manage Roles">
                                            <i class="fas fa-user-tag"></i>
                                        </a>
                                        <a href="<?= base_url('admin/manage_team/' . $emp->employee_id) ?>" 
                                           class="btn btn-sm btn-success" title="Manage Team">
                                            <i class="fas fa-users"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="confirmDelete('<?= $emp->employee_id ?>', '<?= addslashes($emp->first_name . ' ' . $emp->last_name) ?>')"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No employees found.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Form (hidden) -->
<form id="deleteForm" method="post" style="display: none;">
    <input type="hidden" name="employee_id" id="deleteEmployeeId">
</form>

<script>
function confirmDelete(employeeId, employeeName) {
    if (confirm('Are you sure you want to DELETE employee: ' + employeeName + '?\n\n⚠️ WARNING: This action cannot be undone!\n\nThis will permanently delete:\n- Employee account\n- User login credentials\n- All KPI assignments\n- All work information\n- All role assignments\n\nType "DELETE" in the next prompt to confirm.')) {
        
        var confirmation = prompt('Please type DELETE to confirm:');
        
        if (confirmation === 'DELETE') {
            var form = document.getElementById('deleteForm');
            form.action = '<?= base_url('admin/delete_employee/') ?>' + employeeId;
            document.getElementById('deleteEmployeeId').value = employeeId;
            form.submit();
        } else if (confirmation !== null) {
            alert('Deletion cancelled. You must type "DELETE" exactly to confirm.');
        }
    }
}
</script>
