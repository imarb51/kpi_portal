<div class="container-fluid mt-4">
    <h1 class="h3 mb-4">Manage Team: <?= $manager->first_name . ' ' . $manager->last_name ?></h1>
    
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <strong>Manager:</strong> <?= $manager->employee_code ?> - <?= $manager->first_name . ' ' . $manager->last_name ?>
                <br>
                <strong>Designation:</strong> <?= $manager->designation ?? 'N/A' ?>
                <br>
                <strong>Department:</strong> <?= $manager->department_name ?? 'N/A' ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Current Team Members -->
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0"><i class="fas fa-users"></i> Current Team Members (<?= count($current_team) ?>)</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($current_team)): ?>
                        <div class="list-group">
                            <?php foreach ($current_team as $member): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= $member->first_name . ' ' . $member->last_name ?></h6>
                                        <small class="text-muted"><?= $member->employee_code ?></small>
                                    </div>
                                    <p class="mb-1"><small><?= $member->designation ?></small></p>
                                    <small class="text-muted"><?= $member->department_name ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> No team members assigned yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Assign Team Members Form -->
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0"><i class="fas fa-user-plus"></i> Assign Team Members</h6>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= base_url('admin/manage_team/' . $manager->employee_id) ?>">
                        <div class="form-group">
                            <label>Select Employees to Report to This Manager</label>
                            <small class="form-text text-muted mb-2">
                                Check employees who should report to <?= $manager->first_name ?>. 
                                Unchecked employees will be removed from the team.
                            </small>
                            
                            <div style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; padding: 10px; border-radius: 4px;">
                                <?php 
                                $current_team_ids = array_column($current_team, 'employee_id');
                                foreach ($all_employees as $emp): 
                                    if ($emp->employee_id == $manager->employee_id) continue; // Skip the manager
                                ?>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="emp_<?= $emp->employee_id ?>" 
                                               name="team_members[]" 
                                               value="<?= $emp->employee_id ?>"
                                               <?= in_array($emp->employee_id, $current_team_ids) ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="emp_<?= $emp->employee_id ?>">
                                            <strong><?= $emp->employee_code ?></strong> - <?= $emp->first_name . ' ' . $emp->last_name ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= $emp->designation ?? 'N/A' ?> | <?= $emp->department_name ?? 'N/A' ?>
                                            </small>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Update Team
                            </button>
                            <a href="<?= base_url('admin/employees') ?>" class="btn btn-secondary btn-block">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Add search functionality for team members
document.addEventListener('DOMContentLoaded', function() {
    // You can add a search box here if needed
});
</script>
