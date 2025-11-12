<div class="page-header">
    <div class="container">
        <h2><i class="fas fa-user"></i> <?php echo $employee->first_name . ' ' . $employee->last_name; ?></h2>
        <p class="mb-0">Employee Code: <?php echo $employee->employee_code; ?></p>
    </div>
</div>

<div class="container">
    <?php if (!$active_period): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>No Active Review Period</strong>
        <p class="mb-0">There is currently no active review period.</p>
    </div>
    <?php else: ?>
    
    <!-- Employee Info Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Employee Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?php echo $employee->first_name . ' ' . $employee->last_name; ?></p>
                            <p><strong>Employee Code:</strong> <?php echo $employee->employee_code; ?></p>
                            <p><strong>Email:</strong> <?php echo $employee->email ?? 'N/A'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Review Period:</strong> 
                                <?php 
                                    $start_year = date('Y', strtotime($active_period->start_date));
                                    $end_year = date('Y', strtotime($active_period->end_date));
                                    $display_year = ($start_year === $end_year) ? $start_year : $start_year . '-' . $end_year;
                                    echo $display_year . ' - ' . $active_period->period_name; 
                                ?>
                            </p>
                            <p><strong>Total Score:</strong> <span class="badge badge-primary"><?php echo number_format($total_score, 2); ?></span></p>
                            <p><strong>Total Weightage:</strong> 
                                <span class="badge <?php echo $total_weightage == 100 ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo number_format($total_weightage, 1); ?>%
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="<?php echo base_url('manager/assign_kpi/' . $employee->employee_id); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Assign New KPI
                        </a>
                        <?php if (!empty($kpis)): ?>
                        <a href="<?php echo base_url('manager/edit_employee_kpis/' . $employee->employee_id . '?period_id=' . $active_period->period_id); ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit KPIs
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo base_url('manager/team'); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Team
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs List -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-tasks"></i> Assigned KPIs</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($kpis)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            No KPIs assigned for this review period.
                        </div>
                    <?php else: ?>
                        <?php
                        // Group KPIs by category
                        $kpis_by_category = [];
                        $category_totals = [];
                        $overall_total_weightage = 0;
                        $overall_score_sum = 0;
                        $overall_score_count = 0;
                        
                        foreach ($kpis as $kpi) {
                            $category = $kpi->category_name ?? 'Uncategorized';
                            if (!isset($kpis_by_category[$category])) {
                                $kpis_by_category[$category] = [];
                                $category_totals[$category] = [
                                    'weightage' => 0,
                                    'score_sum' => 0,
                                    'score_count' => 0
                                ];
                            }
                            $kpis_by_category[$category][] = $kpi;
                            $category_totals[$category]['weightage'] += floatval($kpi->weightage);
                            $overall_total_weightage += floatval($kpi->weightage);
                            
                            if ($kpi->score !== null && $kpi->score !== '') {
                                $category_totals[$category]['score_sum'] += floatval($kpi->score);
                                $category_totals[$category]['score_count']++;
                                $overall_score_sum += floatval($kpi->score);
                                $overall_score_count++;
                            }
                        }
                        ?>
                        
                        <?php foreach ($kpis_by_category as $category => $category_kpis): ?>
                        <div class="mb-4">
                            <h6 class="text-primary mb-2"><i class="fas fa-folder"></i> <?= htmlspecialchars($category) ?></h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 10%;">Weightage</th>
                                            <th style="width: 35%;">KPI Name</th>
                                            <th style="width: 10%;">Score</th>
                                            <th style="width: 12%;">Weighted Score</th>
                                            <th style="width: 15%;">Status</th>
                                            <th style="width: 8%;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($category_kpis as $kpi): ?>
                                        <tr>
                                            <td><?= number_format($kpi->weightage, 1) ?>%</td>
                                            <td>
                                                <strong><?= htmlspecialchars($kpi->kpi_name) ?></strong>
                                                <?php if (!empty($kpi->description)): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($kpi->description) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($kpi->score !== NULL): ?>
                                                    <span class="badge badge-info"><?= number_format($kpi->score, 2) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if ($kpi->score !== null) {
                                                    $weighted = floatval($kpi->score) * floatval($kpi->weightage) / 100;
                                                    echo number_format($weighted, 2);
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = [
                                                    'ASSIGNED' => 'badge-secondary',
                                                    'IN_PROGRESS' => 'badge-info',
                                                    'COMPLETED' => 'badge-success',
                                                    'APPROVED' => 'badge-primary'
                                                ];
                                                $class = $status_class[$kpi->status] ?? 'badge-secondary';
                                                ?>
                                                <span class="badge <?= $class ?>"><?= $kpi->status ?></span>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('manager/delete_kpi/' . $kpi->employee_kpi_id) ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Are you sure you want to permanently delete this KPI? This action cannot be undone.')"
                                                   title="Delete KPI">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    
                                    <?php 
                                    // Check if this is the last category to add overall total
                                    end($kpis_by_category);
                                    $last_category = key($kpis_by_category);
                                    reset($kpis_by_category);
                                    if ($category === $last_category): 
                                    ?>
                                    <tfoot class="thead-dark">
                                        <tr class="font-weight-bold">
                                            <td>
                                                <span class="badge <?= abs($overall_total_weightage - 100) < 0.1 ? 'badge-success' : 'badge-danger' ?>">
                                                    <?= number_format($overall_total_weightage, 1) ?>%
                                                </span>
                                            </td>
                                            <td><strong>Overall Total</strong></td>
                                            <td>
                                                <?php 
                                                $avg_score = $overall_score_count > 0 ? $overall_score_sum / $overall_score_count : 0;
                                                echo number_format($avg_score, 2);
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $total_weighted = 0;
                                                foreach ($kpis as $kpi) {
                                                    if ($kpi->score !== null) {
                                                        $total_weighted += floatval($kpi->score) * floatval($kpi->weightage) / 100;
                                                    }
                                                }
                                                echo number_format($total_weighted, 2);
                                                ?>
                                            </td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Edit Request Conversation (Show if has messages or status is REQUESTED_EDIT) -->
    <?php if ($has_bulk_edit_request): ?>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-comments"></i> KPI Edit Request Conversation
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Chat Messages -->
                    <div class="chat-container" style="max-height: 400px; overflow-y: auto; margin-bottom: 20px; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; background-color: #f8f9fa;">
                        <?php if (!empty($chat_messages)): ?>
                            <?php foreach ($chat_messages as $msg): ?>
                                <div class="chat-message mb-3 <?= $msg->sender_type === 'EMPLOYEE' ? 'text-left' : 'text-right' ?>">
                                    <div class="d-inline-block p-3 rounded" style="max-width: 70%; <?= $msg->sender_type === 'EMPLOYEE' ? 'background-color: #e3f2fd;' : 'background-color: #fff3cd;' ?>">
                                        <div class="mb-1">
                                            <strong>
                                                <?php if ($msg->sender_type === 'EMPLOYEE'): ?>
                                                    <i class="fas fa-user text-primary"></i>
                                                <?php elseif ($msg->sender_type === 'MANAGER'): ?>
                                                    <i class="fas fa-user-tie text-success"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-user-shield text-danger"></i>
                                                <?php endif; ?>
                                                <?= $msg->first_name . ' ' . $msg->last_name ?>
                                            </strong>
                                            <small class="text-muted">(<?= $msg->sender_type ?>)</small>
                                        </div>
                                        <p class="mb-1"><?= nl2br(htmlspecialchars($msg->message)) ?></p>
                                        <small class="text-muted"><?= date('M d, Y H:i', strtotime($msg->created_at)) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">
                                <p class="mb-2">
                                    <strong><i class="fas fa-user"></i> Employee:</strong> 
                                    <?php echo $employee->first_name . ' ' . $employee->last_name; ?>
                                </p>
                                <p class="mb-2">
                                    <strong><i class="fas fa-calendar"></i> Request Date:</strong> 
                                    <?php echo date('M d, Y H:i', strtotime($bulk_edit_date)); ?>
                                </p>
                                <p class="mb-0">
                                    <strong><i class="fas fa-comment"></i> Employee's Initial Request:</strong><br>
                                    <em><?php echo nl2br(htmlspecialchars($bulk_edit_notes)); ?></em>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Manager Response Form -->
                    <?php if ($chat_is_locked): ?>
                        <div class="alert alert-success mt-3 mb-0">
                            <i class="fas fa-lock"></i> <strong>KPIs Locked</strong><br>
                            The employee has agreed to these KPIs. The conversation has been closed and KPIs are now locked.
                        </div>
                    <?php else: ?>
                        <form method="post" action="<?= base_url('manager/send_kpi_chat_message') ?>">
                            <input type="hidden" name="employee_kpi_id" value="<?= $edit_request_employee_kpi_id ?>">
                            <input type="hidden" name="employee_id" value="<?= $employee->employee_id ?>">
                            <div class="form-group">
                                <label><strong>Send Response to Employee:</strong></label>
                                <textarea name="message" class="form-control" rows="3" 
                                          placeholder="Type your response here..." required></textarea>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Send Message
                                </button>
                                <div>
                                    <a href="<?= base_url('manager/edit_employee_kpis/' . $employee->employee_id . '?period_id=' . $active_period->period_id) ?>" 
                                       class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Edit KPIs
                                    </a>
                                    <a href="<?= base_url('manager/mark_kpi_agreed/' . $edit_request_employee_kpi_id) ?>" 
                                       class="btn btn-success"
                                       onclick="return confirm('Are you sure you want to lock these KPIs? This action cannot be undone.')">
                                        <i class="fas fa-lock"></i> Mark as Agreed & Lock
                                    </a>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php endif; ?>
</div>



<script>
function openReviewModal(requestId, action, kpiName) {
    // Set form action
    document.getElementById('reviewForm').action = '<?= base_url("manager/review_edit_request/") ?>' + requestId;
    
    // Set action value
    document.getElementById('modal-action').value = action;
    
    // Set KPI name
    document.getElementById('modal-kpi-name').textContent = kpiName;
    
    // Set action text and styling
    const actionText = document.getElementById('modal-action-text');
    const submitBtn = document.getElementById('modal-submit-btn');
    
    if (action === 'approve') {
        actionText.textContent = 'APPROVE';
        actionText.className = 'badge badge-success';
        submitBtn.className = 'btn btn-success';
        submitBtn.innerHTML = '<i class="fas fa-check"></i> Approve Request';
    } else {
        actionText.textContent = 'REJECT';
        actionText.className = 'badge badge-danger';
        submitBtn.className = 'btn btn-danger';
        submitBtn.innerHTML = '<i class="fas fa-times"></i> Reject Request';
    }
    
    // Clear previous comments
    document.getElementById('review_comments').value = '';
    
    // Show modal
    $('#reviewModal').modal('show');
}
</script>
