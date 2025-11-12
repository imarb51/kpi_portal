<div class="page-header">
    <div class="container">
        <h2><i class="fas fa-edit"></i> Request KPI Edit</h2>
        <p class="mb-0">Submit a request to modify your KPI score or weightage</p>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- KPI Details Card -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> KPI Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%;">Category:</th>
                            <td><?php echo $kpi->category_name; ?></td>
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
                            <th>Current Weightage:</th>
                            <td><span class="badge badge-info"><?php echo number_format($kpi->weightage, 1); ?>%</span></td>
                        </tr>
                        <tr>
                            <th>Current Score:</th>
                            <td>
                                <?php if ($kpi->score !== NULL): ?>
                                    <span class="badge badge-primary"><?php echo number_format($kpi->score, 1); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Not Scored</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Weighted Score:</th>
                            <td>
                                <?php if ($kpi->weighted_score !== NULL): ?>
                                    <strong class="text-primary"><?php echo number_format($kpi->weighted_score, 2); ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Edit Request Form -->
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-pen"></i> Submit Edit Request</h5>
                </div>
                <div class="card-body">
                    <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
                    
                    <form method="post" action="<?php echo base_url('employee/request_edit/' . $kpi->employee_kpi_id); ?>">
                        <div class="form-group">
                            <label for="field_to_change">What do you want to change? <span class="text-danger">*</span></label>
                            <select class="form-control" id="field_to_change" name="field_to_change" required>
                                <option value="">-- Select Field --</option>
                                <option value="weightage">Weightage (%)</option>
                                <option value="score">Score (0-5)</option>
                            </select>
                        </div>

                        <div class="form-group" id="current_value_group" style="display:none;">
                            <label for="current_value">Current Value</label>
                            <input type="text" class="form-control" id="current_value" name="current_value" readonly>
                        </div>

                        <div class="form-group">
                            <label for="requested_value">Requested Value <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" class="form-control" id="requested_value" 
                                   name="requested_value" placeholder="Enter the value you're requesting" required>
                            <small class="form-text text-muted" id="value_hint"></small>
                        </div>

                        <div class="form-group">
                            <label for="remark">Reason for Change <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="remark" name="remark" rows="4" 
                                      placeholder="Please provide a detailed explanation for why this change is needed (minimum 10 characters)" 
                                      required minlength="10"></textarea>
                            <small class="form-text text-muted">
                                Provide a clear justification for your manager to review.
                            </small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Your manager will review this request and can approve or reject it with comments.
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-paper-plane"></i> Submit Request
                            </button>
                            <a href="<?php echo base_url('employee/my_kpis'); ?>" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const currentWeightage = <?php echo $kpi->weightage; ?>;
    const currentScore = <?php echo $kpi->score !== NULL ? $kpi->score : 0; ?>;
    
    $('#field_to_change').on('change', function() {
        const field = $(this).val();
        
        if (field === 'weightage') {
            $('#current_value').val(currentWeightage + '%');
            $('#current_value_group').show();
            $('#value_hint').text('Enter weightage percentage (0-100)');
            $('#requested_value').attr({
                'min': 0,
                'max': 100,
                'placeholder': 'e.g., 25'
            });
        } else if (field === 'score') {
            $('#current_value').val(currentScore);
            $('#current_value_group').show();
            $('#value_hint').text('Enter score (0-5 scale)');
            $('#requested_value').attr({
                'min': 0,
                'max': 5,
                'placeholder': 'e.g., 4.5'
            });
        } else {
            $('#current_value_group').hide();
            $('#value_hint').text('');
        }
    });
});
</script>
