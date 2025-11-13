<div class="page-header">
    <h2><i class="fas fa-edit"></i> Edit Employee KPIs</h2>
    <p class="mb-0">
        <?php if ($employee_has_agreed): ?>
            Update scores for <?= htmlspecialchars($employee->first_name . ' ' . $employee->last_name) ?>
        <?php else: ?>
            Update KPI weightages for <?= htmlspecialchars($employee->first_name . ' ' . $employee->last_name) ?>
        <?php endif; ?>
    </p>
</div>

<?php if ($employee_has_agreed): ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> <strong>Scoring Mode:</strong> Employee has agreed to the KPIs. You can now add/edit scores (0-5 scale). Weightages are locked.
</div>
<?php else: ?>
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i> <strong>Setup Mode:</strong> Employee has not agreed yet. You can adjust weightages and KPI tasks. Scores will be available after employee agreement.
</div>
<?php endif; ?>

<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-tasks"></i> Edit KPIs</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Determine the correct form action based on which controller is being used
                    $form_action = (strpos(uri_string(), 'admin/') === 0) ? 
                                   base_url('admin/edit_employee_kpis/' . $employee->employee_id . '?period_id=' . $period_id) : 
                                   base_url('manager/edit_employee_kpis/' . $employee->employee_id . '?period_id=' . $period_id);
                    ?>
                    <form method="post" action="<?= $form_action ?>">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: <?= $employee_has_agreed ? '12%' : '10%' ?>;">Weightage (%)</th>
                                        <th style="width: <?= $employee_has_agreed ? '28%' : '30%' ?>;">KPI Name / Task</th>
                                        <?php if ($employee_has_agreed): ?>
                                        <th style="width: 10%;">Score (out of 5)</th>
                                        <th style="width: 12%;">Weighted Score</th>
                                        <th style="width: 18%;">Remark</th>
                                        <?php endif; ?>
                                        <th style="width: 15%;">Category</th>
                                        <th style="width: <?= $employee_has_agreed ? '5%' : '8%' ?>;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Group by category
                                    $grouped_kpis = [];
                                    $categories_info = [];
                                    foreach ($kpis as $kpi) {
                                        $grouped_kpis[$kpi->category_name][] = $kpi;
                                        if (!isset($categories_info[$kpi->category_name])) {
                                            $categories_info[$kpi->category_name] = $kpi->category_id;
                                        }
                                    }
                                    
                                    // Get all categories for adding new KPIs
                                    $this->db->select('category_id, category_name');
                                    $this->db->from('kpi_categories');
                                    $this->db->where('is_active', 1);
                                    $all_categories = $this->db->get()->result();
                                    
                                    // Ensure all categories are represented
                                    foreach ($all_categories as $cat) {
                                        if (!isset($grouped_kpis[$cat->category_name])) {
                                            $grouped_kpis[$cat->category_name] = [];
                                            $categories_info[$cat->category_name] = $cat->category_id;
                                        }
                                    }
                                    
                                    foreach ($grouped_kpis as $category => $category_kpis): 
                                    ?>
                                        <!-- Category Header -->
                                        <tr class="table-info category-header" data-category="<?= htmlspecialchars($category) ?>" data-category-id="<?= $categories_info[$category] ?>">
                                            <td colspan="<?= $employee_has_agreed ? '5' : '6' ?>">
                                                <strong><i class="fas fa-folder"></i> <?= htmlspecialchars($category) ?></strong>
                                            </td>
                                            <?php if (!$employee_has_agreed): ?>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-success add-kpi-btn" 
                                                        data-category="<?= htmlspecialchars($category) ?>"
                                                        data-category-id="<?= $categories_info[$category] ?>"
                                                        title="Add new KPI task">
                                                    <i class="fas fa-plus"></i> Add
                                                </button>
                                            </td>
                                            <?php else: ?>
                                            <td class="text-center">
                                                <span class="text-muted" title="Cannot add KPIs after employee agreement">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                        <!-- KPI Rows -->
                                        <?php foreach ($category_kpis as $kpi): ?>
                                        <tr class="kpi-row" data-category="<?= htmlspecialchars($category) ?>">
                                            <td>
                                                <?php if ($employee_has_agreed): ?>
                                                    <!-- Locked weightage after agreement -->
                                                    <input type="number" 
                                                           class="form-control weightage-input" 
                                                           name="kpis[<?= $kpi->employee_kpi_id ?>][weightage]" 
                                                           value="<?= number_format($kpi->weightage, 1) ?>" 
                                                           readonly 
                                                           style="background-color: #e9ecef;">
                                                <?php else: ?>
                                                    <!-- Editable weightage before agreement -->
                                                    <input type="number" 
                                                           class="form-control weightage-input" 
                                                           name="kpis[<?= $kpi->employee_kpi_id ?>][weightage]" 
                                                           min="0" 
                                                           max="100" 
                                                           step="0.1" 
                                                           value="<?= number_format($kpi->weightage, 1) ?>" 
                                                           required 
                                                           onchange="calculateTotal()">
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($kpi->kpi_name) ?></strong>
                                            </td>
                                            <?php if ($employee_has_agreed): ?>
                                            <!-- Show score fields only after agreement -->
                                            <td>
                                                <input type="number" 
                                                       class="form-control score-input" 
                                                       name="kpis[<?= $kpi->employee_kpi_id ?>][score]" 
                                                       min="0" 
                                                       max="5" 
                                                       step="0.01" 
                                                       value="<?= $kpi->score !== NULL ? number_format($kpi->score, 2) : '' ?>" 
                                                       placeholder="0.00"
                                                       data-kpi-id="<?= $kpi->employee_kpi_id ?>"
                                                       oninput="validateScore(this); calculateWeightedScore(this)">
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-secondary weighted-score" id="weighted_<?= $kpi->employee_kpi_id ?>">
                                                    <?php 
                                                    $weighted = ($kpi->score !== NULL) ? ($kpi->score * $kpi->weightage / 100) : 0;
                                                    echo number_format($weighted, 2);
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <input type="text" 
                                                       class="form-control form-control-sm" 
                                                       name="kpis[<?= $kpi->employee_kpi_id ?>][remark]" 
                                                       value="<?= htmlspecialchars($kpi->remark ?: '') ?>" 
                                                       placeholder="Add remark">
                                            </td>
                                            <?php endif; ?>
                                            <td>
                                                <span class="badge badge-info"><?= htmlspecialchars($kpi->category_name) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!$employee_has_agreed): ?>
                                                    <!-- Only allow deletion before employee agrees -->
                                                    <button type="button" class="btn btn-sm btn-danger delete-kpi-btn" 
                                                            data-kpi-id="<?= $kpi->employee_kpi_id ?>"
                                                            title="Delete this KPI">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <input type="hidden" name="delete_kpis[]" value="" class="delete-flag">
                                                <?php else: ?>
                                                    <span class="text-muted" title="Cannot delete after employee agreement">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="font-weight-bold bg-light">
                                    <tr>
                                        <td class="text-center">
                                            <h5 class="mb-0">
                                                <span id="totalWeightage" class="badge badge-secondary">0</span>%
                                            </h5>
                                            <small id="weightageWarning" class="text-danger" style="display:none;">
                                                <i class="fas fa-exclamation-triangle"></i> Must equal 100%
                                            </small>
                                        </td>
                                        <td>
                                            <strong>Total Weightage</strong>
                                        </td>
                                        <?php if ($employee_has_agreed): ?>
                                        <td class="text-center">
                                            <h5 class="mb-0">
                                                <span id="averageScore" class="badge badge-info">0.00</span>
                                            </h5>
                                        </td>
                                        <td class="text-center">
                                            <h5 class="mb-0">
                                                <span id="totalWeightedScore" class="badge badge-success">0.00</span>
                                            </h5>
                                        </td>
                                        <td colspan="2">
                                            <strong>Average Score / Total Weighted Score</strong>
                                        </td>
                                        <?php else: ?>
                                        <td colspan="2">
                                            <em class="text-muted">Scores available after employee agrees</em>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> <strong>Note:</strong>
                            <ul class="mb-0 mt-2">
                                <?php if ($employee_has_agreed): ?>
                                <li>Weightages are now locked as employee has agreed to the KPIs</li>
                                <li>You can add/edit scores (0-5 scale) for performance evaluation</li>
                                <li>Scores will be visible to the employee after you save</li>
                                <li>Weighted Score = (Score × Weightage) / 100</li>
                                <?php else: ?>
                                <li>Total weightage must equal 100%</li>
                                <li>You can add, edit, or delete KPIs at this stage</li>
                                <li>After saving, the employee will be notified to review and agree</li>
                                <li>Scores can only be added AFTER employee agreement</li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <!-- <div class="form-group">
                            <label for="manager_notes"><strong><i class="fas fa-comment"></i> Response to Employee:</strong></label>
                            <textarea class="form-control" 
                                      id="manager_notes" 
                                      name="manager_notes" 
                                      rows="4" 
                                      placeholder="Explain the changes you made to the KPIs and why (optional but recommended)"><?= set_value('manager_notes') ?></textarea>
                            <small class="form-text text-muted">
                                This message will be sent to the employee along with the updated KPIs.
                            </small>
                        </div> -->

                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-lg" id="submitBtn" <?= $employee_has_agreed ? '' : 'disabled' ?>>
                                <i class="fas fa-save"></i> Save Changes & Notify Employee
                            </button>
                            <?php
                            // Determine the correct back URL based on which controller is being used
                            $back_url = (strpos(uri_string(), 'admin/') === 0) ? 
                                        base_url('admin/view_employee/' . $employee->employee_id) : 
                                        base_url('manager/view_employee/' . $employee->employee_id);
                            ?>
                            <a href="<?= $back_url ?>" class="btn btn-secondary btn-lg">
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
let newKpiCounter = 0;

// Validate score (max 5)
function validateScore(input) {
    const value = parseFloat(input.value);
    if (value > 5) {
        alert('Score cannot be more than 5!');
        input.value = '';
        input.focus();
        return false;
    }
    if (value < 0) {
        alert('Score cannot be negative!');
        input.value = '';
        input.focus();
        return false;
    }
    return true;
}

// Calculate weighted score for individual KPI
function calculateWeightedScore(scoreInput) {
    const row = scoreInput.closest('tr');
    const weightageInput = row.querySelector('.weightage-input');
    const kpiId = scoreInput.dataset.kpiId;
    const weightedBadge = document.getElementById('weighted_' + kpiId);
    
    if (weightedBadge && scoreInput.value && weightageInput.value) {
        const score = parseFloat(scoreInput.value) || 0;
        const weightage = parseFloat(weightageInput.value) || 0;
        const weighted = (score * weightage) / 100;
        weightedBadge.textContent = weighted.toFixed(2);
    } else if (weightedBadge) {
        weightedBadge.textContent = '0.00';
    }
    
    calculateTotal();
}

function calculateTotal() {
    const weightageInputs = document.querySelectorAll('.kpi-row:not(.deleted) .weightage-input');
    const scoreInputs = document.querySelectorAll('.kpi-row:not(.deleted) .score-input');
    
    let totalWeightage = 0;
    let totalWeightedScore = 0;
    let scoreCount = 0;
    let scoreSum = 0;
    
    weightageInputs.forEach(input => {
        totalWeightage += parseFloat(input.value) || 0;
    });
    
    scoreInputs.forEach(input => {
        const row = input.closest('tr');
        const weightageInput = row.querySelector('.weightage-input');
        const score = parseFloat(input.value);
        const weightage = parseFloat(weightageInput.value) || 0;
        
        if (!isNaN(score) && score !== '') {
            scoreSum += score;
            scoreCount++;
            totalWeightedScore += (score * weightage) / 100;
        }
    });
    
    // Update total weightage
    document.getElementById('totalWeightage').textContent = totalWeightage.toFixed(1);
    
    // Check if in scoring mode (employee has agreed)
    const isScoreMode = <?= $employee_has_agreed ? 'true' : 'false' ?>;
    
    // Update average score and total weighted score only in scoring mode
    if (isScoreMode) {
        const averageScore = scoreCount > 0 ? (scoreSum / scoreCount) : 0;
        const avgScoreElement = document.getElementById('averageScore');
        const totalWeightedElement = document.getElementById('totalWeightedScore');
        
        if (avgScoreElement) {
            avgScoreElement.textContent = averageScore.toFixed(2);
        }
        if (totalWeightedElement) {
            totalWeightedElement.textContent = totalWeightedScore.toFixed(2);
        }
    }
    
    const warning = document.getElementById('weightageWarning');
    const submitBtn = document.getElementById('submitBtn');
    const badge = document.getElementById('totalWeightage');
    
    if (isScoreMode) {
        // In scoring mode, always enable submit (weightages are locked)
        submitBtn.disabled = false;
        badge.className = 'badge badge-success';
        if (warning) warning.style.display = 'none';
    } else {
        // In setup mode, validate weightage totals
        if (Math.abs(totalWeightage - 100) > 0.1) {
            warning.style.display = 'block';
            submitBtn.disabled = true;
            badge.className = 'badge badge-danger';
        } else {
            warning.style.display = 'none';
            submitBtn.disabled = false;
            badge.className = 'badge badge-success';
        }
    }
}

// Add new KPI row
function addKpiRow(categoryName, categoryId) {
    newKpiCounter++;
    const newId = 'new_' + newKpiCounter;
    
    // Find the category header to insert after
    const categoryHeaders = document.querySelectorAll('.category-header');
    let insertAfter = null;
    
    categoryHeaders.forEach(header => {
        if (header.dataset.category === categoryName) {
            // Find the last row in this category
            let nextRow = header.nextElementSibling;
            while (nextRow && nextRow.classList.contains('kpi-row') && nextRow.dataset.category === categoryName) {
                insertAfter = nextRow;
                nextRow = nextRow.nextElementSibling;
            }
            if (!insertAfter) {
                insertAfter = header;
            }
        }
    });
    
    const newRow = document.createElement('tr');
    newRow.className = 'kpi-row new-kpi';
    newRow.dataset.category = categoryName;
    
    newRow.innerHTML = `
        <td>
            <input type="number" 
                   class="form-control weightage-input" 
                   name="new_kpis[${newId}][weightage]" 
                   min="0" 
                   max="100" 
                   step="0.1" 
                   value="0.0" 
                   required 
                   onchange="calculateTotal()">
        </td>
        <td>
            <input type="text" 
                   class="form-control" 
                   name="new_kpis[${newId}][kpi_name]" 
                   placeholder="Enter KPI task name" 
                   required>
        </td>
        <td>
            <input type="number" 
                   class="form-control score-input" 
                   name="new_kpis[${newId}][score]" 
                   min="0" 
                   max="5" 
                   step="0.01" 
                   value="" 
                   placeholder="0.00"
                   data-kpi-id="${newId}"
                   oninput="validateScore(this); calculateWeightedScore(this)">
        </td>
        <td class="text-center">
            <span class="badge badge-secondary weighted-score" id="weighted_${newId}">0.00</span>
        </td>
        <td>
            <span class="badge badge-info">${categoryName}</span>
            <input type="hidden" name="new_kpis[${newId}][category_id]" value="${categoryId}">
        </td>
        <td>
            <input type="text" 
                   class="form-control form-control-sm" 
                   name="new_kpis[${newId}][description]" 
                   placeholder="Add remark">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger remove-new-kpi-btn" 
                    title="Remove this row">
                <i class="fas fa-times"></i>
            </button>
        </td>
    `;
    
    if (insertAfter) {
        insertAfter.parentNode.insertBefore(newRow, insertAfter.nextSibling);
    }
    
    // Add event listener for remove button
    newRow.querySelector('.remove-new-kpi-btn').addEventListener('click', function() {
        newRow.remove();
        calculateTotal();
    });
    
    calculateTotal();
}

// Delete existing KPI
function deleteKpi(button) {
    const row = button.closest('tr');
    const kpiId = button.dataset.kpiId;
    
    if (confirm('Are you sure you want to delete this KPI task?')) {
        // Mark row as deleted visually
        row.classList.add('deleted');
        row.style.opacity = '0.4';
        row.style.textDecoration = 'line-through';
        
        // Disable inputs
        row.querySelectorAll('input').forEach(input => input.disabled = true);
        
        // Set the delete flag
        row.querySelector('.delete-flag').value = kpiId;
        
        // Change button to undo
        button.innerHTML = '<i class="fas fa-undo"></i>';
        button.classList.remove('btn-danger');
        button.classList.add('btn-warning');
        button.title = 'Undo delete';
        button.onclick = function() { undoDelete(button, kpiId); };
        
        calculateTotal();
    }
}

// Undo delete
function undoDelete(button, kpiId) {
    const row = button.closest('tr');
    
    // Restore row
    row.classList.remove('deleted');
    row.style.opacity = '1';
    row.style.textDecoration = 'none';
    
    // Re-enable inputs
    row.querySelectorAll('input').forEach(input => input.disabled = false);
    
    // Clear the delete flag
    row.querySelector('.delete-flag').value = '';
    
    // Change button back to delete
    button.innerHTML = '<i class="fas fa-trash"></i>';
    button.classList.remove('btn-warning');
    button.classList.add('btn-danger');
    button.title = 'Delete this KPI';
    button.onclick = function() { deleteKpi(button); };
    
    calculateTotal();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Add KPI buttons
    document.querySelectorAll('.add-kpi-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoryName = this.dataset.category;
            const categoryId = this.dataset.categoryId;
            addKpiRow(categoryName, categoryId);
        });
    });
    
    // Delete KPI buttons
    document.querySelectorAll('.delete-kpi-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            deleteKpi(this);
        });
    });
    
    calculateTotal();
});
</script>
</div> <!-- End content-wrapper -->
