<div class="page-header">
    <h2><i class="fas fa-edit"></i> KPI Edit Requests</h2>
    <p class="mb-0">Review and respond to employee KPI edit requests</p>
</div>

<div class="content-wrapper">
    <!-- Debug Info (Remove after testing) -->
    <div class="alert alert-info">
        <strong>Debug Info:</strong><br>
        Your Manager ID: <?= $this->session->userdata('employee_id') ?><br>
        Total Requests Found: <?= count($requests) ?><br>
        <?php if (empty($requests)): ?>
            <em>Make sure you're logged in as a manager who has team members with pending edit requests.</em>
        <?php endif; ?>
    </div>

    <?php if (empty($requests)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No pending KPI edit requests.
        </div>
    <?php else: ?>
        <?php foreach ($requests as $request): ?>
        <div class="card shadow mb-4">
            <div class="card-header bg-warning text-dark">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-0">
                            <i class="fas fa-user"></i> 
                            <?= htmlspecialchars($request->first_name . ' ' . $request->last_name) ?>
                            <span class="badge badge-secondary"><?= htmlspecialchars($request->employee_code) ?></span>
                        </h5>
                        <small class="text-muted">
                            Period: <?= htmlspecialchars($request->period_name) ?> | 
                            Requested: <?= date('M d, Y', strtotime($request->employee_agreement_date)) ?>
                        </small>
                    </div>
                    <div class="col-md-4 text-right">
                        <span class="badge badge-warning badge-lg">PENDING REVIEW</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Employee's Request -->
                <div class="mb-4">
                    <h6><i class="fas fa-comment-alt"></i> Employee's Request:</h6>
                    <div class="alert alert-light border">
                        <?= nl2br(htmlspecialchars($request->employee_agreement_notes)) ?>
                    </div>
                </div>

                <!-- Current KPIs -->
                <h6><i class="fas fa-list"></i> Current Assigned KPIs:</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 15%;">Weightage (%)</th>
                                <th style="width: 50%;">KPI Name</th>
                                <th style="width: 20%;">Category</th>
                                <th style="width: 15%;">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $employee_kpis = $this->kpi_model->get_employee_kpis($request->employee_id, $request->review_period_id);
                            $total_weight = 0;
                            foreach ($employee_kpis as $kpi): 
                                $total_weight += $kpi->weightage;
                            ?>
                            <tr>
                                <td class="text-center">
                                    <strong><?= number_format($kpi->weightage, 1) ?>%</strong>
                                </td>
                                <td><?= htmlspecialchars($kpi->kpi_name) ?></td>
                                <td>
                                    <span class="badge badge-info"><?= htmlspecialchars($kpi->category_name) ?></span>
                                </td>
                                <td><small><?= htmlspecialchars($kpi->description ?: 'N/A') ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="font-weight-bold bg-light">
                                <td class="text-center">
                                    <span class="<?= $total_weight == 100 ? 'text-success' : 'text-danger' ?>">
                                        <?= number_format($total_weight, 1) ?>%
                                    </span>
                                </td>
                                <td colspan="3">Total Weightage</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Actions -->
                <div class="btn-group">
                    <a href="<?= base_url('manager/edit_employee_kpis/' . $request->employee_id . '?period_id=' . $request->review_period_id) ?>" 
                       class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit KPIs & Approve
                    </a>
                    <button type="button" class="btn btn-danger" 
                            onclick="rejectRequest('<?= $request->employee_id ?>', '<?= $request->review_period_id ?>')">
                        <i class="fas fa-times"></i> Reject Request
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function rejectRequest(employeeId, periodId) {
    const reason = prompt('Please provide a reason for rejecting this request:');
    
    if (reason === null || reason.trim() === '') {
        return;
    }
    
    if (confirm('Are you sure you want to reject this edit request?')) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= base_url('manager/reject_kpi_edit_request') ?>';
        
        const empInput = document.createElement('input');
        empInput.type = 'hidden';
        empInput.name = 'employee_id';
        empInput.value = employeeId;
        form.appendChild(empInput);
        
        const periodInput = document.createElement('input');
        periodInput.type = 'hidden';
        periodInput.name = 'period_id';
        periodInput.value = periodId;
        form.appendChild(periodInput);
        
        const reasonInput = document.createElement('input');
        reasonInput.type = 'hidden';
        reasonInput.name = 'rejection_reason';
        reasonInput.value = reason;
        form.appendChild(reasonInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
</div> <!-- End content-wrapper -->