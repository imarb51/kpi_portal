<div class="page-header">
    <h2><i class="fas fa-list-alt"></i> My KPIs</h2>
    <p class="mb-0">View and track all your Key Performance Indicators</p>
</div>

<div class="content-wrapper">
    <!-- Flash Messages -->
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
                                <?php 
                                    $start_year = date('Y', strtotime($period->start_date));
                                    $end_year = date('Y', strtotime($period->end_date));
                                    $display_year = ($start_year === $end_year) ? $start_year : $start_year . '-' . $end_year;
                                ?>
                                <option value="<?php echo $period->period_id; ?>" 
                                        <?php echo ($selected_period && $selected_period->period_id === $period->period_id) ? 'selected' : ''; ?>>
                                    <?php echo $display_year . ' - ' . $period->period_name; ?> 
                                    (<?php echo date('M Y', strtotime($period->start_date)); ?> - 
                                    <?php echo date('M Y', strtotime($period->end_date)); ?>)
                                    <?php echo ($period->is_active) ? ' - Active' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($kpis)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No KPIs assigned for this period. 
            Please contact your manager if you believe this is an error.
        </div>
    <?php else: ?>
        <?php
        // Get manager response if available
        $manager_response = null;
        if (isset($kpis[0])) {
            $manager_response = isset($kpis[0]->manager_response_notes) ? $kpis[0]->manager_response_notes : null;
        }
        ?>

        <!-- Manager Response Message -->
        <?php if (!empty($manager_response)): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <h5><i class="fas fa-reply"></i> Manager's Response:</h5>
            <p class="mb-0" style="white-space: pre-wrap;"><?= nl2br(htmlspecialchars($manager_response)) ?></p>
            <hr>
            <small class="text-muted">
                <i class="fas fa-clock"></i> 
                Responded: <?= isset($kpis[0]->manager_responded_at) ? date('M d, Y g:i A', strtotime($kpis[0]->manager_responded_at)) : 'N/A' ?>
            </small>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php endif; ?>

        <!-- Agreement Status Banner -->
        <?php if ($has_agreed): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <strong>You have agreed to all KPIs for this period.</strong> 
            Your manager can now assign scores during the review.
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> <strong>Action Required:</strong> 
            Please review your assigned KPIs below and confirm your agreement.
        </div>
        <?php endif; ?>

        <!-- KPI Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-tasks"></i> Assigned KPIs 
                            <span class="badge badge-light ml-2"><?php echo count($kpis); ?> Tasks</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 10%;">Weightage (%)</th>
                                        <th style="width: 35%;">KPI Name / Task</th>
                                        <th style="width: 10%;">Score (0-5)</th>
                                        <th style="width: 12%;">Weighted Score</th>
                                        <th style="width: 15%;">Category</th>
                                        <th style="width: 18%;">Remark</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Group by category and calculate totals
                                    $grouped_kpis = [];
                                    $overall_score_sum = 0;
                                    $overall_score_count = 0;
                                    $overall_weighted_total = 0;
                                    
                                    foreach ($kpis as $kpi) {
                                        $grouped_kpis[$kpi->category_name][] = $kpi;
                                        
                                        if ($kpi->score !== null && $kpi->score !== '') {
                                            $overall_score_sum += floatval($kpi->score);
                                            $overall_score_count++;
                                            $overall_weighted_total += (floatval($kpi->score) * floatval($kpi->weightage) / 100);
                                        }
                                    }
                                    
                                    foreach ($grouped_kpis as $category => $category_kpis): 
                                    ?>
                                        <!-- Category Header -->
                                        <tr class="table-info">
                                            <td colspan="6">
                                                <strong><i class="fas fa-folder"></i> <?= htmlspecialchars($category) ?></strong>
                                            </td>
                                        </tr>
                                        <!-- KPI Rows -->
                                        <?php foreach ($category_kpis as $kpi): ?>
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge badge-primary"><?= number_format($kpi->weightage, 1) ?>%</span>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($kpi->kpi_name) ?></strong>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($kpi->score !== NULL): ?>
                                                    <span class="badge score-badge <?php 
                                                        if ($kpi->score >= 4) echo 'badge-success';
                                                        elseif ($kpi->score >= 3) echo 'badge-primary';
                                                        elseif ($kpi->score >= 2) echo 'badge-warning';
                                                        else echo 'badge-danger';
                                                    ?>"><?= number_format($kpi->score, 2) ?></span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Not Scored</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                if ($kpi->score !== null) {
                                                    $weighted = floatval($kpi->score) * floatval($kpi->weightage) / 100;
                                                    echo '<strong>' . number_format($weighted, 2) . '</strong>';
                                                } else {
                                                    echo '<span class="text-muted">-</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-info"><?= htmlspecialchars($kpi->category_name) ?></span>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?= htmlspecialchars($kpi->remark ?: 'N/A') ?></small>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="thead-dark font-weight-bold">
                                    <tr>
                                        <td class="text-center">
                                            <span class="badge <?= abs($total_weightage - 100) < 0.1 ? 'badge-success' : 'badge-danger' ?>">
                                                <?= number_format($total_weightage, 1) ?>%
                                            </span>
                                        </td>
                                        <td>
                                            <strong>Overall Total</strong>
                                            <?php if (abs($total_weightage - 100) >= 0.1): ?>
                                                <span class="badge badge-danger ml-2">Should be 100%</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <strong>
                                                <?php 
                                                $avg_score = $overall_score_count > 0 ? $overall_score_sum / $overall_score_count : 0;
                                                echo number_format($avg_score, 2);
                                                ?>
                                            </strong>
                                        </td>
                                        <td class="text-center">
                                            <strong><?= number_format($overall_weighted_total, 2) ?></strong>
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Chat Conversation (if exists) -->
                        <?php if ($has_chat_conversation): ?>
                        <hr>
                        
                        <?php if (isset($show_both_chats) && $show_both_chats): ?>
                            <!-- SETUP MODE CONVERSATION (Read-Only / Collapsed) -->
                            <?php if (!empty($setup_chat_messages)): ?>
                            <div class="card border-secondary mb-3">
                                <div class="card-header bg-secondary text-white" style="cursor: pointer;" data-toggle="collapse" data-target="#setupConversation">
                                    <h6 class="mb-0">
                                        <i class="fas fa-history"></i> Setup Mode Discussion (Completed)
                                        <small class="float-right"><i class="fas fa-chevron-down"></i></small>
                                    </h6>
                                </div>
                                <div id="setupConversation" class="collapse">
                                    <div class="card-body" style="background-color: #f5f5f5;">
                                        <!-- Setup Chat Messages -->
                                        <div class="chat-container" style="max-height: 250px; overflow-y: auto; padding: 10px;">
                                            <?php foreach ($setup_chat_messages as $msg): ?>
                                                <div class="chat-message mb-3 <?= $msg->sender_type === 'EMPLOYEE' ? 'text-left' : 'text-right' ?>">
                                                    <div class="d-inline-block p-3 rounded" style="max-width: 70%; <?= $msg->sender_type === 'EMPLOYEE' ? 'background-color: #e3f2fd;' : 'background-color: #fff3cd;' ?>">
                                                        <div class="mb-1">
                                                            <strong>
                                                                <?php if ($msg->sender_type === 'EMPLOYEE'): ?>
                                                                    <i class="fas fa-user text-primary"></i> You
                                                                <?php else: ?>
                                                                    <i class="fas fa-user-tie text-success"></i> <?= $msg->first_name . ' ' . $msg->last_name ?>
                                                                <?php endif; ?>
                                                            </strong>
                                                            <small class="text-muted">(<?= $msg->sender_type ?>)</small>
                                                        </div>
                                                        <p class="mb-1"><?= nl2br(htmlspecialchars($msg->message)) ?></p>
                                                        <small class="text-muted"><?= date('M d, Y H:i', strtotime($msg->created_at)) ?></small>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="alert alert-secondary mb-0 mt-2">
                                            <i class="fas fa-check-circle"></i> <strong>Setup Phase Completed</strong> - This conversation is now read-only.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- SCORING MODE CONVERSATION (Active or Read-Only based on finalization) -->
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-comments"></i> Performance Evaluation Discussion
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <!-- Scoring Chat Messages -->
                                    <div class="chat-container" style="max-height: 300px; overflow-y: auto; margin-bottom: 15px; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; background-color: #f8f9fa;">
                                        <?php if (!empty($scoring_chat_messages)): ?>
                                            <?php foreach ($scoring_chat_messages as $msg): ?>
                                                <div class="chat-message mb-3 <?= $msg->sender_type === 'EMPLOYEE' ? 'text-left' : 'text-right' ?>">
                                                    <div class="d-inline-block p-3 rounded" style="max-width: 70%; <?= $msg->sender_type === 'EMPLOYEE' ? 'background-color: #e3f2fd;' : 'background-color: #fff3cd;' ?>">
                                                        <div class="mb-1">
                                                            <strong>
                                                                <?php if ($msg->sender_type === 'EMPLOYEE'): ?>
                                                                    <i class="fas fa-user text-primary"></i> You
                                                                <?php else: ?>
                                                                    <i class="fas fa-user-tie text-success"></i> <?= $msg->first_name . ' ' . $msg->last_name ?>
                                                                <?php endif; ?>
                                                            </strong>
                                                            <small class="text-muted">(<?= $msg->sender_type ?>)</small>
                                                        </div>
                                                        <p class="mb-1"><?= nl2br(htmlspecialchars($msg->message)) ?></p>
                                                        <small class="text-muted"><?= date('M d, Y H:i', strtotime($msg->created_at)) ?></small>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-muted text-center mb-0">No scoring discussion yet.</p>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Employee Response Form -->
                                    <?php if ($chat_is_locked): ?>
                                        <div class="alert alert-success mb-0">
                                            <i class="fas fa-lock"></i> <strong>Performance Evaluation Completed</strong><br>
                                            You have accepted the final report. The conversation has been closed.
                                        </div>
                                    <?php else: ?>
                                        <form method="post" action="<?= base_url('employee/send_kpi_reply') ?>">
                                            <input type="hidden" name="employee_kpi_id" value="<?= $chat_employee_kpi_id ?>">
                                            <input type="hidden" name="period_id" value="<?= $selected_period->period_id ?>">
                                            <div class="form-group mb-2">
                                                <textarea name="message" class="form-control" rows="3" 
                                                          placeholder="Type your reply here..." required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-paper-plane"></i> Send Reply
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        
                        <?php else: ?>
                            <!-- SINGLE CONVERSATION (Setup Mode Only) -->
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-comments"></i> Conversation with Manager
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <!-- Chat Messages -->
                                    <div class="chat-container" style="max-height: 300px; overflow-y: auto; margin-bottom: 15px; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; background-color: #f8f9fa;">
                                        <?php foreach ($chat_messages as $msg): ?>
                                            <div class="chat-message mb-3 <?= $msg->sender_type === 'EMPLOYEE' ? 'text-left' : 'text-right' ?>">
                                                <div class="d-inline-block p-3 rounded" style="max-width: 70%; <?= $msg->sender_type === 'EMPLOYEE' ? 'background-color: #e3f2fd;' : 'background-color: #fff3cd;' ?>">
                                                    <div class="mb-1">
                                                        <strong>
                                                            <?php if ($msg->sender_type === 'EMPLOYEE'): ?>
                                                                <i class="fas fa-user text-primary"></i> You
                                                            <?php else: ?>
                                                                <i class="fas fa-user-tie text-success"></i> <?= $msg->first_name . ' ' . $msg->last_name ?>
                                                            <?php endif; ?>
                                                        </strong>
                                                        <small class="text-muted">(<?= $msg->sender_type ?>)</small>
                                                    </div>
                                                    <p class="mb-1"><?= nl2br(htmlspecialchars($msg->message)) ?></p>
                                                    <small class="text-muted"><?= date('M d, Y H:i', strtotime($msg->created_at)) ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Employee Response Form -->
                                    <?php if ($chat_is_locked): ?>
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-lock"></i> <strong>Conversation Locked</strong><br>
                                            You have agreed to the KPIs. The conversation has been closed.
                                        </div>
                                    <?php else: ?>
                                        <form method="post" action="<?= base_url('employee/send_kpi_reply') ?>">
                                            <input type="hidden" name="employee_kpi_id" value="<?= $chat_employee_kpi_id ?>">
                                            <input type="hidden" name="period_id" value="<?= $selected_period->period_id ?>">
                                            <div class="form-group mb-2">
                                                <textarea name="message" class="form-control" rows="3" 
                                                          placeholder="Type your reply here..." required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-paper-plane"></i> Send Reply
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php endif; ?>

                        <!-- Action Buttons Based on Workflow Phase -->
                        <hr>
                        <?php if ($is_finalized): ?>
                        <!-- Finalized: Review complete -->
                        <div class="card border-success">
                            <div class="card-body">
                                <h6 class="mb-3 text-success">
                                    <i class="fas fa-check-circle"></i> 
                                    Performance Review Finalized
                                </h6>
                                <div class="alert alert-success mb-0">
                                    <strong><i class="fas fa-info-circle"></i> Your performance review for this period has been completed and finalized.</strong><br>
                                    <small class="text-muted">
                                        • All scores have been accepted<br>
                                        • No further changes can be made<br>
                                        • This review is now locked for this period
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php elseif ($in_scoring_mode): ?>
                        <!-- Scoring Mode: Manager is adding scores -->
                        <div class="card border-info">
                            <div class="card-body">
                                <h6 class="mb-3">
                                    <i class="fas fa-chart-line"></i> 
                                    Performance Evaluation in Progress
                                </h6>
                                <div class="row">
                                    <div class="col-md-7">
                                        <small class="text-muted">
                                            <strong>Your manager is evaluating your performance.</strong><br>
                                            • Review your scores above<br>
                                            • Request changes if needed via chat<br>
                                            • Click "Accept Final Report" when you agree with all scores
                                        </small>
                                    </div>
                                    <div class="col-md-5 text-right">
                                        <form method="post" action="<?= base_url('employee/finalize_kpis') ?>" style="display:inline;" onsubmit="return confirm('Are you sure you accept all scores and want to finalize this performance review? This action cannot be undone.')">
                                            <input type="hidden" name="period_id" value="<?= $selected_period->period_id ?>">
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="fas fa-check-double"></i> Accept Final Report
                                            </button>
                                        </form>
                                        <a href="<?= base_url('employee/request_kpi_edit') ?>?period_id=<?= $selected_period->period_id ?>" 
                                           class="btn btn-warning btn-lg">
                                            <i class="fas fa-edit"></i> Request Changes
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Setup Mode: Employee needs to agree to KPIs -->
                        <div class="card border-warning">
                            <div class="card-body">
                                <h6 class="mb-3">
                                    <i class="fas fa-question-circle"></i> 
                                    Do you agree with the KPIs and weightages assigned to you?
                                </h6>
                                <div class="row">
                                    <div class="col-md-7">
                                        <small class="text-muted">
                                            <strong>Choose an option:</strong><br>
                                            • <strong>I Agree</strong> - You accept all KPIs and their weightages as assigned<br>
                                            • <strong>Request Edit</strong> - You want to request changes to your KPIs
                                        </small>
                                    </div>
                                    <div class="col-md-5 text-right">
                                        <form method="post" action="<?= base_url('employee/agree_all_kpis') ?>" style="display:inline;" onsubmit="return confirm('Are you sure you agree with all assigned KPIs and their weightages? This will move to performance evaluation phase.')">
                                            <input type="hidden" name="period_id" value="<?= $selected_period->period_id ?>">
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="fas fa-check-circle"></i> I Agree
                                            </button>
                                        </form>
                                        <?php if (!$has_chat_conversation): ?>
                                        <a href="<?= base_url('employee/request_kpi_edit') ?>?period_id=<?= $selected_period->period_id ?>" 
                                           class="btn btn-warning btn-lg">
                                            <i class="fas fa-edit"></i> Request Edit
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div> <!-- End content-wrapper -->
