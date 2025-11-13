<div class="page-header">
    <div class="container">
        <h2><i class="fas fa-chart-bar"></i> KPI Details</h2>
        <p class="mb-0">Detailed view of your KPI score and history</p>
    </div>
</div>

<div class="container">
    <div class="row">
        <!-- Back Button -->
        <div class="col-md-12 mb-3">
            <a href="<?php echo base_url('employee/my_kpis'); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to My KPIs
            </a>
            <a href="<?php echo base_url('employee/request_edit/' . $kpi->employee_kpi_id); ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Reques t Edit
            </a>
        </div>

        <!-- KPI Details Card -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> KPI Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%;">Category:</th>
                            <td><span class="badge badge-secondary"><?php echo $kpi->category_name; ?></span></td>
                        </tr>
                        <tr>
                            <th>KPI Name:</th>
                            <td><strong><?php echo $kpi->kpi_name; ?></strong></td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td><?php echo $kpi->description ?: 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th>Review Period:</th>
                            <td>
                                <?php echo $kpi->period_name; ?>
                                (<?php echo date('M d, Y', strtotime($kpi->start_date)); ?> - 
                                <?php echo date('M d, Y', strtotime($kpi->end_date)); ?>)
                            </td>
                        </tr>
                        <tr>
                            <th>Assigned Date:</th>
                            <td><?php echo date('M d, Y H:i', strtotime($kpi->assigned_date)); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Score Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-calculator"></i> Current Score</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-center">
                        <h6 class="text-muted">Weightage</h6>
                        <h2 class="text-info"><?php echo number_format($kpi->weightage, 1); ?>%</h2>
                    </div>

                    <div class="mb-3 text-center">
                        <h6 class="text-muted">Score (0-5)</h6>
                        <?php if ($kpi->score !== NULL): ?>
                            <h2 class="<?php 
                                if ($kpi->score >= 4) echo 'text-success';
                                elseif ($kpi->score >= 3) echo 'text-primary';
                                elseif ($kpi->score >= 2) echo 'text-warning';
                                else echo 'text-danger';
                            ?>"><?php echo number_format($kpi->score, 1); ?></h2>
                        <?php else: ?>
                            <h2 class="text-muted">Not Scored</h2>
                        <?php endif; ?>
                    </div>

                    <hr>

                    <div class="text-center">
                        <h6 class="text-muted">Weighted Score</h6>
                        <?php if ($kpi->weighted_score !== NULL): ?>
                            <h1 class="text-primary"><?php echo number_format($kpi->weighted_score, 2); ?></h1>
                            <small class="text-muted">
                                Formula: (<?php echo number_format($kpi->weightage, 1); ?> × <?php echo number_format($kpi->score, 1); ?>) ÷ 5
                            </small>
                        <?php else: ?>
                            <h1 class="text-muted">-</h1>
                        <?php endif; ?>
                    </div>

                    <?php if ($kpi->last_updated): ?>
                    <hr>
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> Last Updated: 
                        <?php echo date('M d, Y H:i', strtotime($kpi->last_updated)); ?>
                    </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Score History -->
        <div class="col-md-12 mt-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Score History (<?php echo count($history); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($history)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No score changes recorded yet.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Change Type</th>
                                        <th>Old Weightage</th>
                                        <th>New Weightage</th>
                                        <th>Old Score</th>
                                        <th>New Score</th>
                                        <th>Weighted Score Change</th>
                                        <th>Updated By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($history as $record): ?>
                                    <tr>
                                        <td>
                                            <small><?php echo date('M d, Y H:i', strtotime($record->changed_at)); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php 
                                                if ($record->change_type === 'ASSIGNED') echo 'success';
                                                elseif ($record->change_type === 'EDITED') echo 'warning';
                                                else echo 'secondary';
                                            ?>">
                                                <?php echo $record->change_type; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo $record->old_weightage !== NULL ? number_format($record->old_weightage, 1) . '%' : '-'; ?>
                                        </td>
                                        <td>
                                            <?php echo $record->new_weightage !== NULL ? number_format($record->new_weightage, 1) . '%' : '-'; ?>
                                        </td>
                                        <td>
                                            <?php echo $record->old_score !== NULL ? number_format($record->old_score, 1) : '-'; ?>
                                        </td>
                                        <td>
                                            <?php echo $record->new_score !== NULL ? number_format($record->new_score, 1) : '-'; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            if ($record->old_weighted_score !== NULL && $record->new_weighted_score !== NULL) {
                                                $change = $record->new_weighted_score - $record->old_weighted_score;
                                                $color = $change > 0 ? 'success' : ($change < 0 ? 'danger' : 'secondary');
                                                echo '<span class="text-' . $color . '">';
                                                echo ($change > 0 ? '+' : '') . number_format($change, 2);
                                                echo '</span>';
                                            } else {
                                                echo '<span class="text-muted">N/A</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <small><?php echo $record->changed_by_name; ?></small>
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
