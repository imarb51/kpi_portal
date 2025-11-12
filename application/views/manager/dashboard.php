<div class="page-header"></div>
    <div class="container">
        <h2><i class="fas fa-users-cog"></i> Manager Dashboard</h2>
        <p class="mb-0">Manage your team and review KPIs</p>
    </div>
</div>

<div class="container">
    <?php if (!$active_period): ?>
    <!-- No Active Period Warning -->
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>No Active Review Period</strong>
                <p class="mb-0">There is currently no active review period.</p>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="row">
        <!-- Current Period Info -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-calendar-alt"></i> Current Review Period
                    </h5>
                    <h3 class="text-primary">
                        <?php 
                            $start_year = date('Y', strtotime($active_period->start_date));
                            $end_year = date('Y', strtotime($active_period->end_date));
                            $display_year = ($start_year === $end_year) ? $start_year : $start_year . '-' . $end_year;
                            echo $display_year . ' - ' . $active_period->period_name; 
                        ?> 
                        (<?php echo date('M d, Y', strtotime($active_period->start_date)); ?> - 
                        <?php echo date('M d, Y', strtotime($active_period->end_date)); ?>)
                    </h3>
                </div>
            </div>
        </div>

        <!-- Team Members Card -->
        <div class="col-md-6 mb-4">
            <div class="card kpi-card">
                <div class="card-body text-center">
                    <h5 class="mb-3"><i class="fas fa-users"></i> Team Members</h5>
                    <h1 class="display-3 mb-0 text-primary"><?php echo count($team_members); ?></h1>
                    <a href="<?php echo base_url('manager/team'); ?>" class="btn btn-sm btn-outline-primary mt-2">
                        View Team
                    </a>
                </div>
            </div>
        </div>

        <!-- Pending Edit Requests Card -->
        <div class="col-md-6 mb-4">
            <div class="card kpi-card">
                <div class="card-body text-center">
                    <h5 class="mb-3"><i class="fas fa-clock"></i> Pending Edit Requests</h5>
                    <h1 class="display-3 mb-0 text-warning"><?php echo count($pending_requests); ?></h1>
                    <a href="<?php echo base_url('manager/team'); ?>" class="btn btn-sm btn-outline-warning mt-2">
                        View Team
                    </a>
                    <small class="d-block mt-2 text-muted">
                        Click "View KPI" on team member to see their edit requests
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Members List -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-users"></i> My Team</h4>
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
                                                <i class="fas fa-eye"></i> View
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

    <!-- Pending Edit Requests -->
    <?php if (!empty($pending_requests)): ?>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h4 class="mb-0"><i class="fas fa-exclamation-circle"></i> Pending Edit Requests</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Employee</th>
                                    <th>KPI</th>
                                    <th>Current Score</th>
                                    <th>Requested Score</th>
                                    <th>Reason</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_requests as $request): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($request->requested_at)); ?></td>
                                    <td><?php echo $request->employee_name ?? 'N/A'; ?></td>
                                    <td><?php echo $request->kpi_name; ?></td>
                                    <td><?php echo $request->current_score ?? 'N/A'; ?></td>
                                    <td><strong class="text-primary"><?php echo $request->requested_score; ?></strong></td>
                                    <td><?php echo substr($request->reason, 0, 50) . '...'; ?></td>
                                    <td>
                                        <a href="<?php echo base_url('manager/review_request/' . $request->request_id); ?>" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-eye"></i> Review
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php endif; ?> <!-- End of active_period check -->
</div>
