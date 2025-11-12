<div class="page-header">
    <div class="container">
        <h2><i class="fas fa-tachometer-alt"></i> Employee Dashboard</h2>
        <p class="mb-0">View your KPIs and track your performance</p>
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
                <p class="mb-0">There is currently no active review period. Please contact your manager or HR.</p>
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
                    <?php if ($manager): ?>
                    <p class="mb-0">
                        <strong>Reporting Manager:</strong> 
                        <?php echo $manager->first_name . ' ' . $manager->last_name; ?>
                        <?php if (isset($manager->email) && $manager->email): ?>
                            (<?php echo $manager->email; ?>)
                        <?php endif; ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Total Score Card -->
        <div class="col-md-4">
            <div class="card kpi-card">
                <div class="card-body total-score text-center">
                    <h5 class="mb-3"><i class="fas fa-trophy"></i> Total Score</h5>
                    <h1 class="display-3 mb-0"><?php echo number_format($total_score, 2); ?></h1>
                    <p class="mb-0">out of 100</p>
                </div>
            </div>
        </div>

        <!-- Total Weightage Card -->
        <div class="col-md-4">
            <div class="card kpi-card">
                <div class="card-body text-center">
                    <h5 class="mb-3"><i class="fas fa-balance-scale"></i> Total Weightage</h5>
                    <h1 class="display-3 mb-0 <?php echo $total_weightage == 100 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo number_format($total_weightage, 1); ?>%
                    </h1>
                    <p class="mb-0">
                        <?php if ($total_weightage == 100): ?>
                            <span class="badge badge-success">Perfect!</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Should be 100%</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Pending Requests Card -->
        <div class="col-md-4">
            <div class="card kpi-card">
                <div class="card-body text-center">
                    <h5 class="mb-3"><i class="fas fa-clock"></i> Pending Requests</h5>
                    <h1 class="display-3 mb-0 text-warning"><?php echo $pending_requests_count; ?></h1>
                    <a href="<?php echo base_url('employee/my_edit_requests'); ?>" class="btn btn-sm btn-outline-primary mt-2">
                        View Requests
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Accept Final Report Section -->
    <?php if (!empty($kpis)): ?>
    <div class="row mt-4">
        <div class="col-md-12">
            <?php if ($is_finalized): ?>
            <!-- Already Finalized -->
            <div class="alert alert-success">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <i class="fas fa-check-circle fa-3x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading mb-2">
                            <i class="fas fa-lock"></i> Review Finalized
                        </h5>
                        <p class="mb-0">
                            You have already accepted and finalized your performance review for this period. 
                            Your manager has been notified. You can view your finalized scores above.
                        </p>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Accept Final Report Card -->
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-check"></i> Ready to Finalize Your Review?
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="text-success"><i class="fas fa-info-circle"></i> What happens when you accept?</h6>
                            <ul class="mb-3">
                                <li><strong>Confirms your acknowledgment</strong> of all KPI scores assigned by your manager</li>
                                <li><strong>Notifies your reporting manager</strong> via email that you have reviewed and accepted the performance evaluation</li>
                                <li><strong>Finalizes the review period</strong> - no further changes can be made after acceptance</li>
                                <li><strong>Creates a permanent record</strong> of your performance for this period</li>
                            </ul>
                            
                            <?php 
                            // Check readiness
                            $all_scored = true;
                            foreach ($kpis as $kpi) {
                                if ($kpi->score === NULL) {
                                    $all_scored = false;
                                    break;
                                }
                            }
                            $weightage_correct = ($total_weightage == 100);
                            $ready_to_finalize = $all_scored && $weightage_correct && !empty($kpis);
                            ?>
                            
                            <?php if (!$ready_to_finalize): ?>
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle"></i> Cannot Finalize Yet</h6>
                                    <ul class="mb-0">
                                        <?php if (!$all_scored): ?>
                                            <li>Some KPIs are not yet scored by your manager</li>
                                        <?php endif; ?>
                                        <?php if (!$weightage_correct): ?>
                                            <li>Total weightage must be exactly 100% (currently <?php echo number_format($total_weightage, 1); ?>%)</li>
                                        <?php endif; ?>
                                    </ul>
                                    <p class="mb-0 mt-2"><small>Please wait for your manager to complete the scoring, or request changes if needed.</small></p>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-lightbulb"></i> <strong>Tip:</strong> 
                                    Review all your KPI scores carefully before accepting. If you have concerns about any scores, 
                                    use the "Request Edit" button to discuss with your manager first.
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-4 text-center d-flex flex-column justify-content-center">
                            <?php if ($ready_to_finalize): ?>
                                <?php
                                // Calculate average score from KPIs
                                $score_sum = 0;
                                $score_count = 0;
                                foreach ($kpis as $kpi) {
                                    if ($kpi->score !== null && $kpi->score !== '') {
                                        $score_sum += floatval($kpi->score);
                                        $score_count++;
                                    }
                                }
                                $avg_overall_score = $score_count > 0 ? $score_sum / $score_count : 0;
                                ?>
                                <div class="p-3 bg-light rounded mb-3">
                                    <h3 class="text-success mb-2"><?php echo number_format($avg_overall_score, 2); ?></h3>
                                    <p class="text-muted mb-0">Your Average Score</p>
                                </div>
                                
                                <form method="post" action="<?php echo base_url('employee/accept_final_report'); ?>" 
                                      onsubmit="return confirm('Are you sure you want to finalize this review?\n\nOnce accepted, you cannot make changes.\n\nYour manager will be notified via email.');">
                                    <button type="submit" class="btn btn-success btn-lg btn-block" style="box-shadow: 0 4px 6px rgba(40, 167, 69, 0.3);">
                                        <i class="fas fa-check-circle"></i> Accept Final Report
                                    </button>
                                </form>
                                
                                <small class="text-muted mt-2">
                                    <i class="fas fa-envelope"></i> Email notification will be sent to your manager
                                </small>
                            <?php else: ?>
                                <button type="button" class="btn btn-secondary btn-lg btn-block" disabled>
                                    <i class="fas fa-lock"></i> Not Ready to Accept
                                </button>
                                <small class="text-muted mt-2">Complete the requirements above to finalize</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- KPI List -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list"></i> My KPIs (<?php echo count($kpis); ?>)</h5>
                    <a href="<?php echo base_url('employee/my_kpis'); ?>" class="btn btn-sm btn-light">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($kpis)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No KPIs assigned yet for this period. 
                            Please contact your manager.
                        </div>
                    <?php else: ?>
                        <?php
                        // Group KPIs by category
                        $kpis_by_category = [];
                        $overall_total_weightage = 0;
                        $overall_score_sum = 0;
                        $overall_score_count = 0;
                        $overall_weighted_total = 0;
                        
                        foreach ($kpis as $kpi) {
                            $kpis_by_category[$kpi->category_name][] = $kpi;
                            $overall_total_weightage += floatval($kpi->weightage);
                            
                            if ($kpi->score !== null && $kpi->score !== '') {
                                $overall_score_sum += floatval($kpi->score);
                                $overall_score_count++;
                                // Calculate weighted score: (score * weightage / 100)
                                $overall_weighted_total += (floatval($kpi->score) * floatval($kpi->weightage) / 100);
                            }
                        }
                        ?>
                        
                        <?php foreach ($kpis_by_category as $category_name => $category_kpis): ?>
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <span class="badge badge-secondary"><?php echo $category_name; ?></span>
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="10%">Weightage</th>
                                            <th width="50%">KPI Task</th>
                                            <th width="15%">Score (0-5)</th>
                                            <th width="15%">Weighted Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($category_kpis as $kpi): ?>
                                        <tr>
                                            <td>
                                                <span class="badge badge-info"><?php echo number_format($kpi->weightage, 1); ?>%</span>
                                            </td>
                                            <td>
                                                <strong><?php echo $kpi->kpi_name; ?></strong>
                                                <?php if ($kpi->description): ?>
                                                    <br><small class="text-muted"><?php echo $kpi->description; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($kpi->score !== NULL): ?>
                                                    <span class="badge score-badge <?php 
                                                        if ($kpi->score >= 4) echo 'badge-success';
                                                        elseif ($kpi->score >= 3) echo 'badge-primary';
                                                        elseif ($kpi->score >= 2) echo 'badge-warning';
                                                        else echo 'badge-danger';
                                                    ?>"><?php echo number_format($kpi->score, 2); ?></span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Not Scored</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if ($kpi->score !== null) {
                                                    $weighted = floatval($kpi->score) * floatval($kpi->weightage) / 100;
                                                    echo '<strong>' . number_format($weighted, 2) . '</strong>';
                                                } else {
                                                    echo '<span class="text-muted">-</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <!-- Overall Total -->
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="10%">Total Weightage</th>
                                        <th width="50%">Overall Total</th>
                                        <th width="15%">Avg Score</th>
                                        <th width="15%">Total Weighted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="font-weight-bold">
                                        <td>
                                            <span class="badge <?= abs($overall_total_weightage - 100) < 0.1 ? 'badge-success' : 'badge-danger' ?>">
                                                <?= number_format($overall_total_weightage, 1) ?>%
                                            </span>
                                        </td>
                                        <td></td>
                                        <td>
                                            <h5 class="mb-0">
                                                <?php 
                                                $avg_score = $overall_score_count > 0 ? $overall_score_sum / $overall_score_count : 0;
                                                echo number_format($avg_score, 2);
                                                ?>
                                            </h5>
                                        </td>
                                        <td>
                                            <h5 class="mb-0"><strong><?= number_format($overall_weighted_total, 2) ?></strong></h5>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Request Edit Button -->
                        <?php if (!empty($kpis) && $active_period): ?>
                        <div class="mt-3 text-center">
                            <a href="<?php echo base_url('employee/request_kpi_edit?period_id=' . $active_period->period_id); ?>" 
                               class="btn btn-warning btn-lg">
                                <i class="fas fa-edit"></i> Request Edit for KPIs
                            </a>
                            <small class="d-block mt-2 text-muted">
                                Click here if you want to request changes to your assigned KPIs
                            </small>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?> <!-- End of active_period check -->
</div>
