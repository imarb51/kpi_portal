<div class="container-fluid mt-4">
    <h1 class="h3 mb-4">Reports & Analytics</h1>

    <!-- Period Filter -->
    <div class="card shadow mb-3">
        <div class="card-body">
            <form method="get" action="<?= base_url('admin/reports') ?>" class="form-inline">
                <label class="mr-2">Review Period:</label>
                <select name="period_id" class="form-control mr-2" onchange="this.form.submit()">
                    <?php foreach ($all_periods as $period): ?>
                        <?php 
                            $start_year = date('Y', strtotime($period->start_date));
                            $end_year = date('Y', strtotime($period->end_date));
                            $display_year = ($start_year === $end_year) ? $start_year : $start_year . '-' . $end_year;
                        ?>
                        <option value="<?= $period->period_id ?>" 
                                <?= $selected_period_id == $period->period_id ? 'selected' : '' ?>>
                            <?= $display_year . ' - ' . $period->period_name ?> (<?= $period->period_type ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <?php if (isset($department_performance)): ?>
    <!-- Department Performance -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Department Performance</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Employees</th>
                            <th>Average Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($department_performance as $dept): ?>
                        <tr>
                            <td><?= $dept->department_name ?></td>
                            <td><?= $dept->employee_count ?></td>
                            <td><?= number_format($dept->avg_score, 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Performers -->
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-success">Top Performers</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $rank = 1; foreach ($top_performers as $emp): ?>
                                <tr>
                                    <td><?= $rank++ ?></td>
                                    <td><?= $emp->first_name . ' ' . $emp->last_name ?></td>
                                    <td><?= $emp->department_name ?></td>
                                    <td><strong><?= number_format($emp->total_score, 2) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Performers -->
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-warning">Needs Improvement</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($low_performers as $emp): ?>
                                <tr>
                                    <td><?= $emp->first_name . ' ' . $emp->last_name ?></td>
                                    <td><?= $emp->department_name ?></td>
                                    <td><?= number_format($emp->total_score, 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
