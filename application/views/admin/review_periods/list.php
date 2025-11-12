<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Review Periods</h1>
        <a href="<?= base_url('admin/create_period') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Review Period
        </a>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body">
            <?php if (!empty($periods)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Period Name</th>
                                <th>Type</th>
                                <th>Year</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($periods as $period): ?>
                            <tr>
                                <td><strong><?= $period->period_name ?></strong></td>
                                <td><span class="badge badge-info"><?= $period->period_type ?></span></td>
                                <td><?= $period->cycle_year ?></td>
                                <td><?= date('M d, Y', strtotime($period->start_date)) ?></td>
                                <td><?= date('M d, Y', strtotime($period->end_date)) ?></td>
                                <td>
                                    <?php if ($period->status == 'ACTIVE'): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php elseif ($period->status == 'CLOSED'): ?>
                                        <span class="badge badge-secondary">Closed</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Upcoming</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('admin/edit_period/' . $period->period_id) ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($period->status != 'ACTIVE'): ?>
                                        <a href="<?= base_url('admin/activate_period/' . $period->period_id) ?>" 
                                           class="btn btn-sm btn-success"
                                           onclick="return confirm('This will deactivate all other periods. Continue?')">
                                            <i class="fas fa-play"></i> Activate
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($period->status == 'ACTIVE'): ?>
                                        <a href="<?= base_url('admin/close_period/' . $period->period_id) ?>" 
                                           class="btn btn-sm btn-warning"
                                           onclick="return confirm('Are you sure you want to close this period?')">
                                            <i class="fas fa-stop"></i> Close
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No review periods found.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
