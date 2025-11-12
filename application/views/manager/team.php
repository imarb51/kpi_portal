<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-users"></i> My Team</h2>
            <p class="mb-0">Manage your team members</p>
        </div>
        <div class="col-md-4 text-right">
            <!-- <a href="<?= base_url('manager/kpi_edit_requests') ?>" class="btn btn-light">
                <i class="fas fa-edit"></i> KPI Edit Requests
                <?php if (isset($pending_requests_count) && $pending_requests_count > 0): ?>
                    <span class="badge badge-danger"><?= $pending_requests_count ?></span>
                <?php endif; ?>
            </a> -->
        </div>
    </div>
</div>

<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Team Members</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($team_members)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            You don't have any team members assigned yet.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Employee Code</th>
                                        <th>Name</th>
                                        <th>Designation</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($team_members as $member): ?>
                                    <tr>
                                        <td><?php echo $member->employee_code; ?></td>
                                        <td>
                                            <strong><?php echo $member->first_name . ' ' . $member->last_name; ?></strong>
                                        </td>
                                        <td><?php echo $member->designation ?? 'N/A'; ?></td>
                                        <td><?php echo $member->department_name ?? 'N/A'; ?></td>
                                        <td>
                                            <?php if ($member->employee_status === 'ACTIVE'): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary"><?php echo $member->employee_status; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo base_url('manager/view_employee/' . $member->employee_id); ?>" 
                                               class="btn btn-sm btn-info" title="View KPIs">
                                                <i class="fas fa-eye"></i> View KPIs
                                            </a>
                                            <a href="<?php echo base_url('manager/assign_kpi/' . $member->employee_id); ?>" 
                                               class="btn btn-sm btn-primary" title="Assign KPI">
                                                <i class="fas fa-plus"></i> Assign KPI
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div> <!-- End content-wrapper -->
