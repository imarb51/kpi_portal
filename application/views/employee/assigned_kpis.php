<div class="page-header">
    <div class="container">
        <h2><i class="fas fa-clipboard-check"></i> My Assigned KPIs</h2>
        <p class="mb-0">Review and confirm your KPI assignments for the current period</p>
    </div>
</div>

<div class="container">
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

    <!-- Period Filter -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="get" class="form-inline">
                        <label class="mr-2">Select Period:</label>
                        <select name="period_id" class="form-control mr-2" onchange="this.form.submit()">
                            <?php foreach ($all_periods as $period): ?>
                                <option value="<?= $period->period_id ?>" 
                                        <?= ($selected_period && $selected_period->period_id === $period->period_id) ? 'selected' : '' ?>>
                                    <?= $period->period_name ?> 
                                    (<?= date('M Y', strtotime($period->start_date)) ?> - 
                                    <?= date('M Y', strtotime($period->end_date)) ?>)
                                    <?= ($period->is_active) ? ' - Active' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Status -->
    <?php if ($assignment): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert <?= ($assignment->employee_agreement_status == 'AGREED') ? 'alert-success' : (($assignment->employee_agreement_status == 'QUERIED') ? 'alert-warning' : 'alert-info') ?>">
                <h5>
                    <i class="fas <?= ($assignment->employee_agreement_status == 'AGREED') ? 'fa-check-circle' : (($assignment->employee_agreement_status == 'QUERIED') ? 'fa-question-circle' : 'fa-clock') ?>"></i>
                    Assignment Status: <strong><?= $assignment->employee_agreement_status ?></strong>
                </h5>
                <?php if ($assignment->employee_agreement_status == 'AGREED'): ?>
                    <p class="mb-0">You agreed to these KPIs on <?= date('M d, Y h:i A', strtotime($assignment->employee_agreement_at)) ?></p>
                <?php elseif ($assignment->employee_agreement_status == 'QUERIED'): ?>
                    <p class="mb-0">You submitted a query on <?= date('M d, Y h:i A', strtotime($assignment->employee_query_at)) ?></p>
                    <div class="mt-2 p-3 bg-white rounded">
                        <strong>Your Query:</strong><br>
                        <?= nl2br(htmlspecialchars($assignment->employee_query)) ?>
                    </div>
                <?php else: ?>
                    <p class="mb-0">Please review the KPIs below and either agree or submit a query if you have concerns.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- KPI Details Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-tasks"></i> Assigned KPI Tasks (<?= count($kpis) ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($kpis)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No KPIs assigned for this period yet. 
                            Please contact your manager if you believe this is an error.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th style="width: 10%;">Weightage</th>
                                        <th style="width: 40%;">KPI Tasks</th>
                                        <th style="width: 20%;">Category</th>
                                        <th style="width: 15%;">Score</th>
                                        <th style="width: 15%;">Weighted Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($kpis as $kpi): ?>
                                    <tr>
                                        <td class="text-center">
                                            <span class="badge badge-info badge-lg" style="font-size: 1.1em;">
                                                <?= number_format($kpi->weightage, 1) ?>%
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($kpi->kpi_name) ?></strong>
                                            <?php if ($kpi->description): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($kpi->description) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary"><?= htmlspecialchars($kpi->category_name) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($kpi->score !== NULL): ?>
                                                <span class="badge score-badge <?php 
                                                    if ($kpi->score >= 4) echo 'badge-success';
                                                    elseif ($kpi->score >= 3) echo 'badge-primary';
                                                    elseif ($kpi->score >= 2) echo 'badge-warning';
                                                    else echo 'badge-danger';
                                                ?>" style="font-size: 1em;"><?= number_format($kpi->score, 1) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <strong class="text-primary">
                                                <?= ($kpi->weighted_score !== NULL) ? number_format($kpi->weighted_score, 2) : '0.00' ?>
                                            </strong>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <td class="text-right"><strong>Total:</strong></td>
                                        <td colspan="2"></td>
                                        <td class="text-center"><strong>-</strong></td>
                                        <td class="text-center">
                                            <strong class="text-primary" style="font-size: 1.2em;">
                                                <?= number_format($total_weighted_score, 2) ?>
                                            </strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <span class="badge badge-<?= ($total_weightage == 100) ? 'success' : 'danger' ?>" style="font-size: 1em;">
                                                Total Weightage: <?= number_format($total_weightage, 1) ?>%
                                                <?= ($total_weightage == 100) ? '✓' : '(Should be 100%)' ?>
                                            </span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Action Buttons -->
                        <?php if ($assignment && $assignment->is_locked && $assignment->employee_agreement_status == 'PENDING'): ?>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5><i class="fas fa-hand-point-right"></i> Please Confirm Your KPI Assignment</h5>
                                        <p>Review the KPIs listed above. You can either:</p>
                                        <div class="btn-group btn-group-lg" role="group">
                                            <button type="button" class="btn btn-success" onclick="agreeToKPIs()">
                                                <i class="fas fa-check-circle"></i> I Agree with These KPIs
                                            </button>
                                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#queryModal">
                                                <i class="fas fa-edit"></i> I Have a Query/Concern
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Query Modal -->
<div class="modal fade" id="queryModal" tabindex="-1" role="dialog" aria-labelledby="queryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="queryModalLabel">
                    <i class="fas fa-question-circle"></i> Submit Query About KPI Assignment
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="<?= base_url('employee/submit_kpi_query') ?>">
                <input type="hidden" name="assignment_id" value="<?= $assignment ? $assignment->assignment_id : '' ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="query_text"><strong>Please describe your concerns or questions:</strong></label>
                        <textarea class="form-control" id="query_text" name="query_text" rows="6" required
                                  placeholder="Example: I believe the weightage for KPI X should be higher because..."></textarea>
                        <small class="form-text text-muted">
                            Your query will be sent to your reporting manager(s) and HR spokesperson(s).
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-paper-plane"></i> Submit Query
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function agreeToKPIs() {
    if (confirm('Are you sure you want to agree to these KPI assignments? This will notify your manager and HR.')) {
        // Submit agreement
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= base_url('employee/agree_to_kpis') ?>';
        
        var assignmentInput = document.createElement('input');
        assignmentInput.type = 'hidden';
        assignmentInput.name = 'assignment_id';
        assignmentInput.value = '<?= $assignment ? $assignment->assignment_id : '' ?>';
        form.appendChild(assignmentInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<style>
.badge-lg {
    padding: 0.5em 0.8em;
}
.score-badge {
    padding: 0.4em 0.6em;
}
</style>
