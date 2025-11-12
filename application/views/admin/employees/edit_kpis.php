<div class="page-header">
    <h2><i class="fas fa-edit"></i> Edit Employee KPIs (Admin)</h2>
    <p class="mb-0">Update KPI weightages and scores for <?= htmlspecialchars($employee->first_name . ' ' . $employee->last_name) ?></p>
</div>

<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-user-shield"></i> Admin KPI Editor</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= base_url('admin/edit_employee_kpi/' . $employee->employee_id) ?>">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th style="width: 10%;">Weightage (%)</th>
                                        <th style="width: 35%;">KPI Name / Task</th>
                                        <th style="width: 15%;">Category</th>
                                        <th style="width: 20%;">Description</th>
                                        <th style="width: 10%;">Actions</th>
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
                                    
                                    foreach ($grouped_kpis as $category => $category_kpis): 
                                    ?>
                                        <!-- Category Header -->
                                        <tr class="table-info category-header" data-category="<?= htmlspecialchars($category) ?>" data-category-id="<?= $categories_info[$category] ?>">
                                            <td colspan="4">
                                                <strong><i class="fas fa-folder"></i> <?= htmlspecialchars($category) ?></strong>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-success add-kpi-btn" 
                                                        data-category="<?= htmlspecialchars($category) ?>"
                                                        data-category-id="<?= $categories_info[$category] ?>"
                                                        title="Add new KPI task">
                                                    <i class="fas fa-plus"></i> Add
                                                </button>
                                            </td>
                                        </tr>
                                        <!-- KPI Rows -->
                                        <?php foreach ($category_kpis as $kpi): ?>
                                        <tr class="kpi-row" data-category="<?= htmlspecialchars($category) ?>">
                                            <td>
                                                <input type="hidden" name="kpi_id[]" value="<?= $kpi->employee_kpi_id ?>">
                                                <input type="number" 
                                                       class="form-control weightage-input" 
                                                       name="weightage[]" 
                                                       min="0" 
                                                       max="100" 
                                                       step="0.1" 
                                                       value="<?= number_format($kpi->weightage, 1) ?>" 
                                                       required 
                                                       onchange="calculateTotal()">
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($kpi->kpi_name) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge badge-info"><?= htmlspecialchars($kpi->category_name) ?></span>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?= htmlspecialchars($kpi->description ?: 'N/A') ?></small>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger delete-kpi-btn" 
                                                        data-kpi-id="<?= $kpi->employee_kpi_id ?>"
                                                        title="Delete this KPI">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <input type="hidden" name="delete_kpi[]" value="" class="delete-flag">
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="font-weight-bold bg-light">
                                    <tr>
                                        <td class="text-center">
                                            <h5 class="mb-0">
                                                <span id="totalWeightage" class="badge badge-secondary"><?= number_format($total_weightage, 1) ?></span>%
                                            </h5>
                                            <small id="weightageWarning" class="text-danger" style="display:none;">
                                                <i class="fas fa-exclamation-triangle"></i> Must equal 100%
                                            </small>
                                        </td>
                                        <td colspan="3">
                                            <strong>Total Weightage</strong>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Add New KPI Section -->
                        <div id="newKpisContainer"></div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-circle"></i> <strong>Admin Note:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Total weightage must equal 100%</li>
                                <li>You can add or remove KPIs using the buttons above</li>
                                <li>After saving, the employee's agreement status will reset to PENDING</li>
                                <li>Click "Add" next to a category to add a new KPI in that category</li>
                            </ul>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <a href="<?= base_url('admin/view_employee/' . $employee->employee_id) ?>" class="btn btn-secondary btn-lg">
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

function calculateTotal() {
    const weightageInputs = document.querySelectorAll('.kpi-row:not(.deleted) .weightage-input');
    let total = 0;
    
    weightageInputs.forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    
    // Add new KPIs
    const newWeightageInputs = document.querySelectorAll('#newKpisContainer .weightage-input');
    newWeightageInputs.forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    
    document.getElementById('totalWeightage').textContent = total.toFixed(1);
    
    const warning = document.getElementById('weightageWarning');
    const submitBtn = document.getElementById('submitBtn');
    const badge = document.getElementById('totalWeightage');
    
    if (Math.abs(total - 100) > 0.1) {
        warning.style.display = 'block';
        submitBtn.disabled = true;
        badge.className = 'badge badge-danger';
    } else {
        warning.style.display = 'none';
        submitBtn.disabled = false;
        badge.className = 'badge badge-success';
    }
}

// Add new KPI row
function addKpiRow(categoryName, categoryId) {
    newKpiCounter++;
    const container = document.getElementById('newKpisContainer');
    
    const newRow = document.createElement('div');
    newRow.className = 'card mb-3 new-kpi-card';
    newRow.innerHTML = `
        <div class="card-body bg-light">
            <h6 class="text-success"><i class="fas fa-plus-circle"></i> New KPI for ${categoryName}</h6>
            <div class="row">
                <div class="col-md-3">
                    <label>Weightage (%)</label>
                    <input type="number" 
                           class="form-control weightage-input" 
                           name="new_kpi_weightage[]" 
                           min="0" 
                           max="100" 
                           step="0.1" 
                           value="0.0" 
                           required 
                           onchange="calculateTotal()">
                </div>
                <div class="col-md-9">
                    <label>KPI Name</label>
                    <select class="form-control" name="new_kpi_template[]" required>
                        <option value="">-- Select KPI Template --</option>
                        <?php foreach ($available_templates as $template): ?>
                            <?php if ($template->category_name === '${categoryName}'): ?>
                                <option value="<?= $template->template_id ?>"><?= htmlspecialchars($template->kpi_name) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-danger mt-2 remove-new-kpi" onclick="this.closest('.new-kpi-card').remove(); calculateTotal();">
                <i class="fas fa-times"></i> Remove
            </button>
        </div>
    `;
    
    // Replace placeholder with actual category name
    newRow.innerHTML = newRow.innerHTML.replace(/\$\{categoryName\}/g, categoryName);
    
    container.appendChild(newRow);
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
