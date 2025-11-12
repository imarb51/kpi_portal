<div class="container-fluid mt-4">
    <h1 class="h3 mb-4">
        <i class="fas fa-random"></i> KPI Assignment Fallback System
    </h1>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Fallback Hierarchy Explanation -->
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-info-circle"></i> How Fallback Works</h5>
        </div>
        <div class="card-body">
            <p>When a reporting manager is unavailable (inactive, absent, or unassigned), KPIs are assigned by the following hierarchy:</p>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card border-left-success mb-3">
                        <div class="card-body">
                            <h6 class="text-success"><i class="fas fa-check-circle"></i> <strong>Priority 1: Reporting Manager</strong></h6>
                            <p class="mb-0 small">The designated reporting manager (if active and available)</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-left-warning mb-3">
                        <div class="card-body">
                            <h6 class="text-warning"><i class="fas fa-arrow-right"></i> <strong>Priority 2: HR Spokesperson</strong></h6>
                            <p class="mb-0 small">The designated HR spokesperson (if manager unavailable)</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-left-info mb-3">
                        <div class="card-body">
                            <h6 class="text-info"><i class="fas fa-arrow-right"></i> <strong>Priority 3: Department Head</strong></h6>
                            <p class="mb-0 small">Any active manager in the same department</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-left-danger mb-3">
                        <div class="card-body">
                            <h6 class="text-danger"><i class="fas fa-arrow-right"></i> <strong>Priority 4: Super Admin</strong></h6>
                            <p class="mb-0 small">Any active super admin (last resort)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Fallback for Employee -->
    <div class="card shadow mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-search"></i> Check Fallback Assignor</h5>
        </div>
        <div class="card-body">
            <form method="post" action="<?= base_url('admin/check_kpi_fallback') ?>">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Select Employee</label>
                            <select class="form-control" name="employee_id" required>
                                <option value="">-- Select Employee --</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= $emp->employee_id ?>" <?= (isset($selected_employee) && $selected_employee == $emp->employee_id) ? 'selected' : '' ?>>
                                        <?= $emp->employee_code ?> - <?= $emp->first_name ?> <?= $emp->last_name ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-info btn-block">
                            <i class="fas fa-search"></i> Check Fallback
                        </button>
                    </div>
                </div>
            </form>

            <?php if (isset($fallback_result)): ?>
                <hr>
                <h6>Fallback Result:</h6>
                <?php if ($fallback_result): ?>
                    <div class="alert <?= $fallback_result->is_fallback ? 'alert-warning' : 'alert-success' ?>">
                        <h5>
                            <?php if ($fallback_result->is_fallback): ?>
                                <i class="fas fa-exclamation-triangle"></i> Fallback Assignment Required
                            <?php else: ?>
                                <i class="fas fa-check-circle"></i> Normal Assignment
                            <?php endif; ?>
                        </h5>
                        <table class="table table-sm mb-0 mt-3">
                            <tr>
                                <th width="200">Assignor:</th>
                                <td><strong><?= $fallback_result->assignor_name ?></strong></td>
                            </tr>
                            <tr>
                                <th>Role:</th>
                                <td><?= $fallback_result->assignor_role ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?= $fallback_result->assignor_email ?></td>
                            </tr>
                            <?php if ($fallback_result->is_fallback): ?>
                            <tr>
                                <th>Reason:</th>
                                <td><span class="badge badge-warning"><?= $fallback_result->fallback_reason ?></span></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle"></i> <strong>No assignor available!</strong>
                        <p class="mb-0">This employee has no active reporting manager, HR spokesperson, department head, or super admin.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Fallback Assignment History -->
    <div class="card shadow">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-history"></i> Recent Fallback Assignments</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($fallback_logs)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Assigned By</th>
                                <th>Role</th>
                                <th>Period</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fallback_logs as $log): ?>
                            <tr>
                                <td><?= date('d M Y H:i', strtotime($log->assigned_at)) ?></td>
                                <td><?= $log->employee_name ?></td>
                                <td><?= $log->assignor_name ?></td>
                                <td><span class="badge badge-warning"><?= $log->assignor_role ?></span></td>
                                <td><?= $log->period_name ?></td>
                                <td><small><?= $log->fallback_reason ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle"></i> No fallback assignments recorded yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
