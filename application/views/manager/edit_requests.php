<div class="page-header">
    <div class="container">
        <h2><i class="fas fa-clipboard-list"></i> Edit Requests</h2>
        <p class="mb-0">Review employee edit requests</p>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h4 class="mb-0">Pending Edit Requests</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($pending_requests)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            No pending edit requests at this time.
                        </div>
                    <?php else: ?>
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
                                        <td>
                                            <strong><?php echo $request->employee_name ?? 'N/A'; ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo $request->employee_code ?? ''; ?></small>
                                        </td>
                                        <td><?php echo $request->kpi_name; ?></td>
                                        <td>
                                            <?php if ($request->current_score !== NULL): ?>
                                                <span class="badge badge-secondary"><?php echo number_format($request->current_score, 2); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong class="text-primary"><?php echo number_format($request->requested_score, 2); ?></strong>
                                        </td>
                                        <td>
                                            <?php 
                                            $reason = $request->reason;
                                            echo strlen($reason) > 50 ? substr($reason, 0, 50) . '...' : $reason;
                                            ?>
                                        </td>
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
