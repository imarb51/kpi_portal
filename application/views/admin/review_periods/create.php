<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Create Review Period</h1>
        <a href="<?= base_url('admin/review_periods') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form method="post" action="<?= base_url('admin/create_period') ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="period_name">Period Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="period_name" name="period_name" 
                                   value="<?= set_value('period_name') ?>" placeholder="e.g., H1 2025" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="period_type">Period Type <span class="text-danger">*</span></label>
                            <select class="form-control" id="period_type" name="period_type" required>
                                <option value="">-- Select --</option>
                                <option value="H1" <?= set_select('period_type', 'H1') ?>>H1 (Apr-Sep)</option>
                                <option value="H2" <?= set_select('period_type', 'H2') ?>>H2 (Oct-Mar)</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cycle_year">Cycle Year <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="cycle_year" name="cycle_year" 
                                   value="<?= set_value('cycle_year', date('Y')) ?>" placeholder="2025" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?= set_value('start_date') ?>" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?= set_value('end_date') ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fiscal_year">Fiscal Year</label>
                            <input type="text" class="form-control" id="fiscal_year" name="fiscal_year" 
                                   value="<?= set_value('fiscal_year', date('Y')) ?>" placeholder="2025">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="UPCOMING" <?= set_select('status', 'UPCOMING', TRUE) ?>>Upcoming</option>
                                <option value="ACTIVE" <?= set_select('status', 'ACTIVE') ?>>Active</option>
                                <option value="CLOSED" <?= set_select('status', 'CLOSED') ?>>Closed</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <strong>Standard Cycles:</strong><br>
                    <strong>H1:</strong> April 1 - September 30<br>
                    <strong>H2:</strong> October 1 - March 31
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Period
                    </button>
                    <a href="<?= base_url('admin/review_periods') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
