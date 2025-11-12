<div class="page-header">
    <div class="container">
        <h2><i class="fas fa-plus-circle"></i> Assign KPI</h2>
        <p class="mb-0">Assign KPI to <?php echo $employee->first_name . ' ' . $employee->last_name; ?></p>
    </div>
</div>

<div class="container">
    <?php if (!$active_period): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>No Active Review Period</strong>
        <p class="mb-0">Cannot assign KPIs without an active review period.</p>
    </div>
    <?php else: ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Assign New KPI</h4>
                </div>
                <div class="card-body">
                    <form method="post" action="<?php echo base_url('manager/assign_kpi/' . $employee->employee_id); ?>">
                        
                        <div class="form-group">
                            <label>Review Period</label>
                            <?php 
                                $start_year = date('Y', strtotime($active_period->start_date));
                                $end_year = date('Y', strtotime($active_period->end_date));
                                $display_year = ($start_year === $end_year) ? $start_year : $start_year . '-' . $end_year;
                            ?>
                            <input type="text" class="form-control" value="<?php echo $display_year . ' - ' . $active_period->period_name; ?>" readonly>
                            <input type="hidden" name="period_id" value="<?php echo $active_period->period_id; ?>">
                        </div>

                        <div class="form-group">
                            <label for="template_id">Select KPI <span class="text-danger">*</span></label>
                            <select name="template_id" id="template_id" class="form-control" required onchange="updateSuggestedWeightage(this)">
                                <option value="">-- Select KPI --</option>
                                
                                <?php if (!empty($work_kpis)): ?>
                                <optgroup label="Work KPIs">
                                    <?php foreach ($work_kpis as $kpi): ?>
                                    <option value="<?php echo $kpi->template_id; ?>" 
                                            data-description="<?php echo htmlspecialchars($kpi->description); ?>"
                                            data-weightage="<?php 
                                                // Extract suggested weightage from description
                                                if ($kpi->description && preg_match('/Suggested weightage: (\d+)%/', $kpi->description, $matches)) {
                                                    echo $matches[1];
                                                }
                                            ?>">
                                        <?php echo $kpi->kpi_name; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <?php endif; ?>
                                
                                <?php if (!empty($competency_kpis)): ?>
                                <optgroup label="Competencies">
                                    <?php foreach ($competency_kpis as $kpi): ?>
                                    <option value="<?php echo $kpi->template_id; ?>"
                                            data-description="<?php echo htmlspecialchars($kpi->description); ?>"
                                            data-weightage="<?php 
                                                // Extract suggested weightage from description
                                                if ($kpi->description && preg_match('/Suggested weightage: (\d+)%/', $kpi->description, $matches)) {
                                                    echo $matches[1];
                                                }
                                            ?>">
                                        <?php echo $kpi->kpi_name; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted" id="kpi-description"></small>
                        </div>

                        <div class="form-group">
                            <label for="weightage">Weightage (%) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   name="weightage" 
                                   id="weightage" 
                                   class="form-control" 
                                   min="0" 
                                   max="100" 
                                   step="0.1" 
                                   required>
                            <small class="form-text text-muted">
                                Total weightage must equal 100%. 
                                Current total: <span id="current-total" class="font-weight-bold">
                                    <?php 
                                    $current_total = 0;
                                    if (!empty($assigned_kpis)) {
                                        foreach ($assigned_kpis as $kpi) {
                                            $current_total += $kpi->weightage;
                                        }
                                    }
                                    echo number_format($current_total, 1);
                                    ?>%
                                </span>
                            </small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Assign KPI
                            </button>
                            <a href="<?php echo base_url('manager/view_employee/' . $employee->employee_id); ?>" class="btn btn-secondary">
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
                    <h5 class="mb-0">Currently Assigned KPIs</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($assigned_kpis)): ?>
                        <p class="text-muted">No KPIs assigned yet.</p>
                    <?php else: ?>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>KPI</th>
                                    <th>Weight</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assigned_kpis as $kpi): ?>
                                <tr>
                                    <td><?php echo $kpi->kpi_name; ?></td>
                                    <td><?php echo number_format($kpi->weightage, 1); ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="font-weight-bold">
                                    <td>Total:</td>
                                    <td><?php echo number_format($current_total, 1); ?>%</td>
                                </tr>
                            </tfoot>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php endif; ?>
</div>

<script>
function updateSuggestedWeightage(select) {
    const selectedOption = select.options[select.selectedIndex];
    const description = selectedOption.getAttribute('data-description');
    const suggestedWeightage = selectedOption.getAttribute('data-weightage');
    const descriptionElement = document.getElementById('kpi-description');
    const weightageInput = document.getElementById('weightage');
    
    if (description) {
        descriptionElement.innerHTML = '<i class="fas fa-info-circle"></i> ' + description;
    } else {
        descriptionElement.innerHTML = '';
    }
    
    // Auto-fill suggested weightage if available
    if (suggestedWeightage && suggestedWeightage !== '') {
        weightageInput.value = suggestedWeightage;
        weightageInput.placeholder = 'Suggested: ' + suggestedWeightage + '%';
    } else {
        weightageInput.value = '';
        weightageInput.placeholder = 'Enter weightage (%)';
    }
}
</script>
