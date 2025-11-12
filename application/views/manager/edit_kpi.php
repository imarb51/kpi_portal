<div class="page-header">
    <div class="container">
        <h2><i class="fas fa-edit"></i> Edit KPI</h2>
        <p class="mb-0">Update KPI weightage and score for <?= htmlspecialchars($employee->first_name . ' ' . $employee->last_name) ?></p>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="fas fa-tasks"></i> Edit KPI Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= base_url('manager/edit_kpi/' . $kpi->employee_kpi_id) ?>">
                        
                        <!-- KPI Information (Read-only) -->
                        <div class="mb-4">
                            <h6 class="text-muted">KPI Information</h6>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <p><strong>KPI Name:</strong> <?= htmlspecialchars($kpi->kpi_name) ?></p>
                                    <p><strong>Category:</strong> <span class="badge badge-info"><?= htmlspecialchars($kpi->category_name) ?></span></p>
                                    <p><strong>Description:</strong> <?= htmlspecialchars($kpi->description ?: 'N/A') ?></p>
                                    <p><strong>Review Period:</strong> <?= htmlspecialchars($kpi->period_name) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Editable Fields -->
                        <div class="mb-4">
                            <h6 class="text-muted">Editable Values</h6>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="weightage">Weightage (%) <span class="text-danger">*</span></label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="weightage" 
                                               name="weightage" 
                                               min="0" 
                                               max="100" 
                                               step="0.1" 
                                               value="<?= number_format($kpi->weightage, 1) ?>" 
                                               required>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> Current: <?= number_format($kpi->weightage, 1) ?>%
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="score">Score (0-100)</label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="score" 
                                               name="score" 
                                               min="0" 
                                               max="100" 
                                               step="0.01" 
                                               value="<?= $kpi->score !== NULL ? number_format($kpi->score, 2) : '' ?>" 
                                               placeholder="Enter score">
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> Current: <?= $kpi->score !== NULL ? number_format($kpi->score, 2) : 'Not scored' ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Calculated Weighted Score -->
                        <div class="mb-4">
                            <div class="alert alert-info">
                                <h6 class="mb-2"><i class="fas fa-calculator"></i> Weighted Score Preview</h6>
                                <p class="mb-0">
                                    Weighted Score = Score × (Weightage / 100)<br>
                                    <strong>Current:</strong> <?= $kpi->score !== NULL ? number_format(($kpi->score * $kpi->weightage / 100), 2) : '0.00' ?>
                                </p>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> Update KPI
                            </button>
                            <a href="<?= base_url('manager/view_employee/' . $employee->employee_id) ?>" class="btn btn-secondary">
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
// Real-time weighted score calculation
document.getElementById('weightage').addEventListener('input', updateWeightedScore);
document.getElementById('score').addEventListener('input', updateWeightedScore);

function updateWeightedScore() {
    const weightage = parseFloat(document.getElementById('weightage').value) || 0;
    const score = parseFloat(document.getElementById('score').value) || 0;
    const weighted = (score * weightage / 100).toFixed(2);
    
    // Update preview (you can add a live preview element if needed)
    console.log('Weighted Score:', weighted);
}
</script>
