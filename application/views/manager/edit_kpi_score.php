<div class="page-header">
    <div class="container">
        <h2><i class="fas fa-edit"></i> Edit KPI Score</h2>
        <p class="mb-0">Update weightage and score for this KPI</p>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><?php echo $kpi->kpi_name; ?></h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <p><strong>Category:</strong> <?php echo $kpi->category_name; ?></p>
                        <p><strong>Description:</strong> <?php echo $kpi->description; ?></p>
                        <p><strong>Employee:</strong> <?php echo $kpi->employee_name ?? 'N/A'; ?></p>
                    </div>

                    <form method="post" action="<?php echo base_url('manager/edit_kpi_score/' . $kpi->employee_kpi_id); ?>">
                        
                        <div class="form-group">
                            <label for="weightage">Weightage (%)</label>
                            <input type="number" 
                                   name="weightage" 
                                   id="weightage" 
                                   class="form-control" 
                                   min="0" 
                                   max="100" 
                                   step="0.1" 
                                   value="<?php echo $kpi->weightage; ?>">
                            <small class="form-text text-muted">
                                Current: <?php echo number_format($kpi->weightage, 1); ?>%
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="score">Score</label>
                            <input type="number" 
                                   name="score" 
                                   id="score" 
                                   class="form-control" 
                                   min="0" 
                                   max="100" 
                                   step="0.01" 
                                   value="<?php echo $kpi->score ?? ''; ?>">
                            <small class="form-text text-muted">
                                Current: <?php echo $kpi->score !== NULL ? number_format($kpi->score, 2) : 'Not scored'; ?>
                            </small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Score
                            </button>
                            <a href="<?php echo base_url('manager/view_employee/' . $kpi->employee_id); ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Score History</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($history)): ?>
                        <p class="text-muted">No history available.</p>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($history as $item): ?>
                            <div class="mb-3">
                                <small class="text-muted">
                                    <?php echo date('M d, Y H:i', strtotime($item->last_edited_at)); ?>
                                </small>
                                <p class="mb-0">
                                    <strong>Score:</strong> <?php echo $item->score !== NULL ? number_format($item->score, 2) : 'N/A'; ?>
                                    <br>
                                    <strong>Weightage:</strong> <?php echo number_format($item->weightage, 1); ?>%
                                </p>
                                <hr>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
