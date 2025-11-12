<div class="page-header">
    <div class="container">
        <h2><i class="fas fa-gavel"></i> Review Edit Request</h2>
        <p class="mb-0">Approve or reject employee's score edit request</p>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h4 class="mb-0">Edit Request Details</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($request)): ?>
                    
                    <div class="mb-4">
                        <h5>Employee Information</h5>
                        <p><strong>Name:</strong> <?php echo $request->employee_name ?? 'N/A'; ?></p>
                        <p><strong>Employee Code:</strong> <?php echo $request->employee_code ?? 'N/A'; ?></p>
                        <p><strong>Request Date:</strong> <?php echo date('F d, Y H:i', strtotime($request->requested_at)); ?></p>
                    </div>

                    <div class="mb-4">
                        <h5>KPI Information</h5>
                        <p><strong>KPI Name:</strong> <?php echo $request->kpi_name; ?></p>
                        <p><strong>Category:</strong> <?php echo $request->category_name ?? 'N/A'; ?></p>
                        <p><strong>Description:</strong> <?php echo $request->kpi_description ?? 'N/A'; ?></p>
                    </div>

                    <div class="mb-4">
                        <h5>Score Change</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6>Current Score</h6>
                                        <h2 class="text-secondary">
                                            <?php echo $request->current_score !== NULL ? number_format($request->current_score, 2) : 'N/A'; ?>
                                        </h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h6>Requested Score</h6>
                                        <h2><?php echo number_format($request->requested_score, 2); ?></h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Reason for Request</h5>
                        <div class="alert alert-info">
                            <?php echo nl2br(htmlspecialchars($request->reason)); ?>
                        </div>
                    </div>

                    <form method="post" action="<?php echo base_url('manager/review_edit_request/' . $request->request_id); ?>">
                        
                        <div class="form-group">
                            <label for="comments">Manager Comments</label>
                            <textarea name="comments" 
                                      id="comments" 
                                      class="form-control" 
                                      rows="4" 
                                      placeholder="Add your comments (optional)"></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" name="status" value="APPROVED" class="btn btn-success btn-lg">
                                <i class="fas fa-check"></i> Approve Request
                            </button>
                            <button type="submit" name="status" value="REJECTED" class="btn btn-danger btn-lg">
                                <i class="fas fa-times"></i> Reject Request
                            </button>
                            <a href="<?php echo base_url('manager/edit_requests'); ?>" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>

                    </form>

                    <?php else: ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Request not found or you don't have permission to view it.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
