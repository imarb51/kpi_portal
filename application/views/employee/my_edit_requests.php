<div class="page-header">
    <div class="container">
        <h2><i class="fas fa-exchange-alt"></i> My Edit Requests</h2>
        <p class="mb-0">Track the status of your KPI edit requests</p>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Edit Requests (<?php echo count($requests); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($requests)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> You haven't submitted any edit requests yet.
                        </div>
                        <a href="<?php echo base_url('employee/my_kpis'); ?>" class="btn btn-primary">
                            <i class="fas fa-list"></i> View My KPIs
                        </a>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Request Date</th>
                                        <th>KPI Name</th>
                                        <th>Field</th>
                                        <th>Current → Requested</th>
                                        <th>Status</th>
                                        <th>Reviewed</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($requests as $request): ?>
                                    <tr>
                                        <td>
                                            <small><?php echo date('M d, Y H:i', strtotime($request->requested_at)); ?></small>
                                        </td>
                                        <td>
                                            <strong><?php echo $request->kpi_name; ?></strong><br>
                                            <small class="text-muted"><?php echo $request->category_name; ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                <?php echo ucfirst($request->field_to_change); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted"><?php echo $request->current_value; ?></span>
                                            <i class="fas fa-arrow-right text-primary"></i>
                                            <strong class="text-primary"><?php echo $request->requested_value; ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge status-<?php echo strtolower($request->status); ?>">
                                                <?php echo $request->status; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($request->reviewed_at): ?>
                                                <small>
                                                    <?php echo date('M d, Y H:i', strtotime($request->reviewed_at)); ?><br>
                                                    by <?php echo $request->reviewer_name; ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="text-muted">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" 
                                                    data-toggle="modal" data-target="#requestModal<?php echo $request->request_id; ?>">
                                                <i class="fas fa-eye"></i> Details
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Request Details Modal -->
                                    <div class="modal fade" id="requestModal<?php echo $request->request_id; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-info text-white">
                                                    <h5 class="modal-title">
                                                        <i class="fas fa-file-alt"></i> Edit Request Details
                                                    </h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6>KPI Information</h6>
                                                            <table class="table table-sm table-bordered">
                                                                <tr>
                                                                    <th>Category:</th>
                                                                    <td><?php echo $request->category_name; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>KPI Name:</th>
                                                                    <td><?php echo $request->kpi_name; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Field:</th>
                                                                    <td><?php echo ucfirst($request->field_to_change); ?></td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Request Status</h6>
                                                            <table class="table table-sm table-bordered">
                                                                <tr>
                                                                    <th>Status:</th>
                                                                    <td>
                                                                        <span class="badge status-<?php echo strtolower($request->status); ?>">
                                                                            <?php echo $request->status; ?>
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Requested:</th>
                                                                    <td><?php echo date('M d, Y H:i', strtotime($request->requested_at)); ?></td>
                                                                </tr>
                                                                <?php if ($request->reviewed_at): ?>
                                                                <tr>
                                                                    <th>Reviewed:</th>
                                                                    <td>
                                                                        <?php echo date('M d, Y H:i', strtotime($request->reviewed_at)); ?><br>
                                                                        <small>by <?php echo $request->reviewer_name; ?></small>
                                                                    </td>
                                                                </tr>
                                                                <?php endif; ?>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    
                                                    <hr>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6>Value Change</h6>
                                                            <div class="alert alert-light">
                                                                <p class="mb-1"><strong>Current Value:</strong></p>
                                                                <h4 class="text-muted"><?php echo $request->current_value; ?></h4>
                                                                
                                                                <p class="mb-1 mt-3"><strong>Requested Value:</strong></p>
                                                                <h4 class="text-primary"><?php echo $request->requested_value; ?></h4>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Your Reason</h6>
                                                            <div class="alert alert-info">
                                                                <?php echo nl2br(htmlspecialchars($request->remark)); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if ($request->reviewer_comment): ?>
                                                    <hr>
                                                    <h6>Manager's Response</h6>
                                                    <div class="alert alert-<?php echo $request->status === 'APPROVED' ? 'success' : 'danger'; ?>">
                                                        <?php echo nl2br(htmlspecialchars($request->reviewer_comment)); ?>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                        Close
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
