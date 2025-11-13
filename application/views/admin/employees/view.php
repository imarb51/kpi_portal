<div class="container-fluid mt-4">
    <h1 class="h3 mb-4">Employee Details: <?= $employee->first_name . ' ' . $employee->last_name ?></h1>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Basic Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="30%">Employee Code:</th>
                            <td><?= $employee->employee_code ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?= $employee->email ?></td>
                        </tr>
                        <tr>
                            <th>Designation:</th>
                            <td><?= $employee->designation ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>Department:</th>
                            <td><?= $employee->department_name ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>Reporting Managers:</th>
                            <td>
                                <?php if (!empty($employee->reporting_managers)): ?>
                                    <?php foreach ($employee->reporting_managers as $mgr): ?>
                                        <span class="badge badge-<?= $mgr->is_primary ? 'primary' : 'info' ?> mr-1">
                                            <?= $mgr->employee_code ?> - <?= $mgr->first_name . ' ' . $mgr->last_name ?>
                                            <?= $mgr->is_primary ? '(Primary)' : '' ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>HR Spokespersons:</th>
                            <td>
                                <?php if (!empty($employee->hr_spokespersons)): ?>
                                    <?php foreach ($employee->hr_spokespersons as $hr): ?>
                                        <span class="badge badge-<?= $hr->is_primary ? 'success' : 'secondary' ?> mr-1">
                                            <?= $hr->employee_code ?> - <?= $hr->first_name . ' ' . $hr->last_name ?>
                                            <?= $hr->is_primary ? '(Primary)' : '' ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <?php if ($employee->employee_status == 'ACTIVE'): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <?php if (isset($kpis) && !empty($kpis)): ?>
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Current KPIs</h6>
                    <div>
                        <a href="<?= base_url('admin/assign_kpi/' . $employee->employee_id) ?>" 
                           class="btn btn-sm btn-success mr-2">
                            <i class="fas fa-plus-circle"></i> Assign KPI Template
                        </a>
                        <?php if (!empty($active_period)): ?>
                        <a href="<?= base_url('admin/edit_employee_kpis/' . $employee->employee_id . '?period_id=' . $active_period->period_id) ?>" 
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit KPIs
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
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
                            <table class="table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 10%;">Weightage</th>
                                        <th style="width: 40%;">KPI Name</th>
                                        <th style="width: 10%;">Score</th>
                                        <th style="width: 15%;">Weighted Score</th>
                                        <th style="width: 25%;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($category_kpis as $kpi): ?>
                                    <tr>
                                        <td><?= number_format($kpi->weightage, 1) ?>%</td>
                                        <td><strong><?= htmlspecialchars($kpi->kpi_name) ?></strong></td>
                                        <td><?= $kpi->score !== null ? number_format($kpi->score, 2) : '-' ?></td>
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
                                            <?php if ($kpi->is_locked): ?>
                                                <span class="badge badge-secondary"><i class="fas fa-lock"></i> Locked</span>
                                            <?php endif; ?>
                                            <?php if ($kpi->employee_agreement_status === 'REQUESTED_EDIT'): ?>
                                                <span class="badge badge-warning"><i class="fas fa-exclamation-circle"></i> Edit Requested</span>
                                            <?php elseif ($kpi->employee_agreement_status === 'AGREED'): ?>
                                                <span class="badge badge-success"><i class="fas fa-check-circle"></i> Agreed</span>
                                            <?php elseif ($kpi->employee_agreement_status === 'PENDING'): ?>
                                                <span class="badge badge-info"><i class="fas fa-clock"></i> Pending Review</span>
                                            <?php endif; ?>
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
                                        <td></td>
                                    </tr>
                                </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
            <!-- No KPIs Assigned Yet -->
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">Current KPIs</h6>
                </div>
                <div class="card-body text-center py-5">
                    <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No KPIs Assigned Yet</h5>
                    <p class="text-muted mb-4">This employee has no KPIs assigned for the current period.</p>
                    <?php if (isset($active_period)): ?>
                        <a href="<?= base_url('admin/assign_kpi/' . $employee->employee_id) ?>" 
                           class="btn btn-success btn-lg">
                            <i class="fas fa-plus-circle"></i> Assign KPI Template
                        </a>
                    <?php else: ?>
                        <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> No active review period available</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- KPI Edit Request Chat Section -->
            <?php if (isset($kpis) && !empty($kpis) && $has_edit_request): ?>
            
            <?php if (isset($show_both_chats) && $show_both_chats): ?>
                <!-- SETUP MODE CONVERSATION (Read-Only / Collapsed) -->
                <?php if (!empty($setup_chat_messages)): ?>
                <div class="card shadow mt-4 border-secondary">
                    <div class="card-header bg-secondary text-white" style="cursor: pointer;" data-toggle="collapse" data-target="#setupConversationAdmin">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-history"></i> Setup Mode Discussion (Completed)
                            <small class="float-right"><i class="fas fa-chevron-down"></i></small>
                        </h6>
                    </div>
                    <div id="setupConversationAdmin" class="collapse">
                        <div class="card-body" style="background-color: #f5f5f5;">
                            <div class="chat-container" style="max-height: 300px; overflow-y: auto; padding: 10px;">
                                <?php foreach ($setup_chat_messages as $msg): ?>
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
                            </div>
                            <div class="alert alert-secondary mb-0 mt-2">
                                <i class="fas fa-check-circle"></i> <strong>Setup Phase Completed</strong> - This conversation is now read-only.
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- SCORING MODE CONVERSATION (Active or Read-Only) -->
                <div class="card shadow mt-4">
                    <div class="card-header bg-warning text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-comments"></i> Performance Evaluation Discussion
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chat-container" style="max-height: 400px; overflow-y: auto; margin-bottom: 20px; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; background-color: #f8f9fa;">
                            <?php if (!empty($scoring_chat_messages)): ?>
                                <?php foreach ($scoring_chat_messages as $msg): ?>
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
                                <p class="text-muted text-center mb-0">No scoring discussion yet.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Admin Response Form -->
                        <?php if ($chat_is_locked): ?>
                            <div class="alert alert-success mt-3 mb-0">
                                <i class="fas fa-lock"></i> <strong>Performance Evaluation Completed</strong><br>
                                The employee has accepted the final report. The conversation has been closed.
                            </div>
                        <?php else: ?>
                            <form method="post" action="<?= base_url('admin/send_kpi_chat_message') ?>">
                                <input type="hidden" name="employee_kpi_id" value="<?= $edit_request_employee_kpi_id ?>">
                                <input type="hidden" name="employee_id" value="<?= $employee->employee_id ?>">
                                <div class="form-group">
                                    <label><strong>Send Response to Employee:</strong></label>
                                    <textarea name="message" class="form-control" rows="3" 
                                              placeholder="Type your response here..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Send Message
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            
            <?php else: ?>
                <!-- SINGLE CONVERSATION (Setup Mode Only) -->
                <div class="card shadow mt-4">
                    <div class="card-header bg-warning text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-comments"></i> KPI Edit Request Conversation
                        </h6>
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
                                <p class="text-muted text-center">No messages yet. Employee has requested edit but not sent a message.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Admin Response Form -->
                        <?php if ($chat_is_locked): ?>
                            <div class="alert alert-success mt-3 mb-0">
                                <i class="fas fa-lock"></i> <strong>KPIs Locked</strong><br>
                                The employee has agreed to these KPIs. The conversation has been closed and KPIs are now locked.
                            </div>
                        <?php else: ?>
                            <form method="post" action="<?= base_url('admin/send_kpi_chat_message') ?>">
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
                                        <a href="<?= base_url('admin/edit_employee_kpis/' . $employee->employee_id . '?period_id=' . $active_period->period_id) ?>" 
                                           class="btn btn-warning">
                                            <i class="fas fa-edit"></i> Edit KPIs
                                        </a>
                                        <a href="<?= base_url('admin/mark_kpi_agreed/' . $edit_request_employee_kpi_id) ?>" 
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
            <?php endif; ?>
            
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <a href="<?= base_url('admin/edit_employee/' . $employee->employee_id) ?>" 
                       class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-edit"></i> Edit Employee
                    </a>
                    <a href="<?= base_url('admin/manage_roles/' . $employee->employee_id) ?>" 
                       class="btn btn-warning btn-block mb-2">
                        <i class="fas fa-user-tag"></i> Manage Roles
                    </a>
                    <a href="<?= base_url('admin/employees') ?>" 
                       class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Roles</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($employee_roles)): ?>
                        <?php foreach ($employee_roles as $role): ?>
                            <span class="badge badge-<?= $role->is_primary ? 'primary' : 'secondary' ?> mb-1">
                                <?= $role->role_name ?>
                                <?= $role->is_primary ? '(Primary)' : '' ?>
                            </span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No roles assigned</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
