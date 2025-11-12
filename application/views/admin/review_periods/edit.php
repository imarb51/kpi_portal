<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Edit Review Period</h1>
        <a href="<?= base_url('admin/review_periods') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form method="post" action="<?= base_url('admin/edit_period/' . $period->period_id) ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="period_name">Period Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="period_name" name="period_name" 
                                   value="<?= set_value('period_name', $period->period_name) ?>" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Period Type</label>
                            <input type="text" class="form-control" value="<?= $period->period_type ?>" disabled>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Cycle Year</label>
                            <input type="text" class="form-control" value="<?= $period->cycle_year ?>" disabled>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?= set_value('start_date', $period->start_date) ?>" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?= set_value('end_date', $period->end_date) ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="UPCOMING" <?= set_select('status', 'UPCOMING', $period->status == 'UPCOMING') ?>>Upcoming</option>
                        <option value="ACTIVE" <?= set_select('status', 'ACTIVE', $period->status == 'ACTIVE') ?>>Active</option>
                        <option value="CLOSED" <?= set_select('status', 'CLOSED', $period->status == 'CLOSED') ?>>Closed</option>
                    </select>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Period
                    </button>
                    <a href="<?= base_url('admin/review_periods') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
