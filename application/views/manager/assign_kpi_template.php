<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3"><i class="fas fa-plus-circle"></i> Assign KPI Template</h1>
            <p class="text-muted mb-0">Assign KPI to <?= $employee->first_name ?> <?= $employee->last_name ?></p>
        </div>
        <?php
        // Determine the correct back URL based on which controller is being used
        $back_url = (strpos(uri_string(), 'admin/') === 0) ? 
                    base_url('admin/view_employee/' . $employee->employee_id) : 
                    base_url('manager/view_employee/' . $employee->employee_id);
        ?>
        <a href="<?= $back_url ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <strong>Success!</strong> <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Error!</strong> <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (!$active_period): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>No Active Review Period</strong>
            <p class="mb-0">Cannot assign KPIs without an active review period.</p>
        </div>
    <?php else: ?>

    <div class="row">
        <!-- Assign Form -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Select KPI Template</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Determine the correct form action based on which controller is being used
                    $form_action = (strpos(uri_string(), 'admin/') === 0) ? 
                                   base_url('admin/assign_kpi/' . $employee->employee_id) : 
                                   base_url('manager/assign_kpi/' . $employee->employee_id);
                    ?>
                    <form method="post" action="<?= $form_action ?>" id="assignForm">
                        <input type="hidden" name="period_id" value="<?= $active_period->period_id ?>">
                        
                        <!-- Review Period -->
                        <div class="form-group">
                            <label>Review Period</label>
                            <?php 
                                $start_year = date('Y', strtotime($active_period->start_date));
                                $end_year = date('Y', strtotime($active_period->end_date));
                                $display_year = ($start_year === $end_year) ? $start_year : $start_year . '-' . $end_year;
                            ?>
                            <input type="text" class="form-control" value="<?= $display_year . ' - ' . $active_period->period_name ?> (<?= date('M d, Y', strtotime($active_period->start_date)) ?> to <?= date('M d, Y', strtotime($active_period->end_date)) ?>)" readonly>
                        </div>

                        <!-- Template Selection -->
                        <div class="form-group">
                            <label for="template_select">Select KPI Template <span class="text-danger">*</span></label>
                            <select class="form-control" id="template_select" required onchange="loadTemplateKPIs(this.value)">
                                <option value="">-- Select Template --</option>
                                <?php foreach ($templates as $template): ?>
                                    <option value="<?= htmlspecialchars($template->template_name) ?>">
                                        <?= htmlspecialchars($template->template_name) ?> 
                                        (<?= $template->kpi_count ?> KPIs - <?= htmlspecialchars($template->categories) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Select a template to load all KPIs. You can customize weightages below.</small>
                        </div>

                        <input type="hidden" name="template_name" id="template_name">

                        <!-- KPIs Table (will be loaded via AJAX) -->
                        <div id="kpisContainer" style="display:none;">
                            <hr class="my-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5><i class="fas fa-tasks"></i> Customize KPIs</h5>
                                <small class="text-muted">You can adjust weightages before assigning</small>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered" id="kpiTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 15%;">Weightage (%)</th>
                                            <th style="width: 45%;">KPI Name / Task</th>
                                            <th style="width: 20%;">Category</th>
                                            <th style="width: 15%;">Target Score (0-5)</th>
                                            <th style="width: 5%;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="kpiTableBody">
                                        <!-- Will be populated via AJAX with category sections -->
                                    </tbody>
                                    <tfoot>
                                        <tr class="font-weight-bold">
                                            <td>
                                                <span id="totalWeightage">0</span>%
                                                <span id="weightageWarning" class="text-danger ml-2" style="display:none;">
                                                    <i class="fas fa-exclamation-triangle"></i> Must equal 100%
                                                </span>
                                            </td>
                                            <td colspan="4">Total Weightage</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> <strong>Note:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Total weightage must equal 100%</li>
                                    <li>You can adjust individual weightages to fit your team member's role</li>
                                    <li>Target scores will be set to 0 (can be updated later during performance review)</li>
                                    <li>Use the <span class="badge badge-success">+</span> button in each category to add additional KPIs</li>
                                </ul>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                                    <i class="fas fa-check"></i> Assign All KPIs
                                </button>
                                <a href="<?= base_url('manager/view_employee/' . $employee->employee_id) ?>" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Currently Assigned KPIs -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-list-check"></i> Currently Assigned</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($assigned_kpis)): ?>
                        <p class="text-muted">No KPIs assigned yet.</p>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($assigned_kpis as $kpi): ?>
                                <li class="list-group-item">
                                    <strong><?= htmlspecialchars($kpi->kpi_name) ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <span class="badge badge-info"><?= htmlspecialchars($kpi->category_name) ?></span>
                                        Weightage: <?= $kpi->weightage ?>%
                                    </small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php endif; ?>
</div>

<script>
let kpiData = [];
let kpiIndex = 0;
let categories = {};

function loadTemplateKPIs(templateName) {
    if (!templateName) {
        document.getElementById('kpisContainer').style.display = 'none';
        return;
    }

    // Set template name in hidden field
    document.getElementById('template_name').value = templateName;

    // Determine the correct AJAX URL based on which controller is being used
    <?php
    $ajax_url = (strpos(uri_string(), 'admin/') === 0) ? 
                base_url('admin/assign_kpi/' . $employee->employee_id) : 
                base_url('manager/assign_kpi/' . $employee->employee_id);
    ?>
    
    // Load KPIs via AJAX
    $.ajax({
        url: '<?= $ajax_url ?>',
        method: 'POST',
        data: { 
            action: 'load_template',
            template_name: templateName 
        },
        success: function(response) {
            const data = JSON.parse(response);
            kpiData = data.kpis;
            
            if (kpiData && kpiData.length > 0) {
                // Group KPIs by category
                groupKPIsByCategory(kpiData);
                renderKPIs();
                document.getElementById('kpisContainer').style.display = 'block';
                calculateTotal();
            } else {
                alert('No KPIs found in this template');
            }
        },
        error: function() {
            alert('Error loading template KPIs');
        }
    });
}

function groupKPIsByCategory(kpis) {
    categories = {};
    kpis.forEach(function(kpi) {
        const catId = kpi.category_id;
        const catName = kpi.category_name || 'Uncategorized';
        
        if (!categories[catId]) {
            categories[catId] = {
                name: catName,
                kpis: []
            };
        }
        categories[catId].kpis.push(kpi);
    });
}

function renderKPIs() {
    const tbody = document.getElementById('kpiTableBody');
    tbody.innerHTML = '';
    kpiIndex = 0;
    
    // Render each category section
    Object.keys(categories).forEach(function(catId) {
        const category = categories[catId];
        
        // Category header row
        const headerRow = document.createElement('tr');
        headerRow.className = 'table-primary';
        headerRow.innerHTML = `
            <td colspan="4">
                <strong><i class="fas fa-folder"></i> ${escapeHtml(category.name)}</strong>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-success" onclick="addKpiToCategory('${catId}', '${escapeHtml(category.name)}')">
                    <i class="fas fa-plus"></i>
                </button>
            </td>
        `;
        tbody.appendChild(headerRow);
        
        // KPI rows for this category
        category.kpis.forEach(function(kpi) {
            const row = createKpiRow(kpi, catId, category.name);
            tbody.appendChild(row);
            kpiIndex++;
        });
    });
}

function createKpiRow(kpi, categoryId, categoryName) {
    const row = document.createElement('tr');
    row.className = 'kpi-row';
    row.dataset.categoryId = categoryId;
    
    row.innerHTML = `
        <td>
            <input type="number" 
                   class="form-control weightage-input" 
                   name="kpis[${kpiIndex}][weightage]" 
                   min="0" 
                   max="100" 
                   step="0.1" 
                   value="0" 
                   required 
                   onchange="calculateTotal()">
        </td>
        <td>
            <textarea class="form-control" 
                      name="kpis[${kpiIndex}][kpi_name]" 
                      rows="2" 
                      required>${escapeHtml(kpi.kpi_name || '')}</textarea>
            <input type="hidden" name="kpis[${kpiIndex}][template_id]" value="${kpi.template_id || ''}">
        </td>
        <td>
            <span class="badge badge-info">${escapeHtml(categoryName)}</span>
            <input type="hidden" name="kpis[${kpiIndex}][category_id]" value="${categoryId}">
        </td>
        <td>
            <input type="number" 
                   class="form-control" 
                   name="kpis[${kpiIndex}][target_score]" 
                   min="0" 
                   max="5" 
                   step="0.1" 
                   value="0" 
                   readonly 
                   style="background-color: #e9ecef;">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeKpiRow(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    return row;
}

function addKpiToCategory(categoryId, categoryName) {
    const tbody = document.getElementById('kpiTableBody');
    
    // Find the last row of this category
    const rows = tbody.querySelectorAll(`tr.kpi-row[data-category-id="${categoryId}"]`);
    let insertAfter = null;
    
    // Find the category header
    const allRows = Array.from(tbody.children);
    for (let i = 0; i < allRows.length; i++) {
        if (allRows[i].classList.contains('table-primary') && 
            allRows[i].textContent.includes(categoryName)) {
            // Found the header, now find last KPI row in this section
            for (let j = i + 1; j < allRows.length; j++) {
                if (allRows[j].classList.contains('table-primary')) {
                    break; // Next category header
                }
                if (allRows[j].classList.contains('kpi-row')) {
                    insertAfter = allRows[j];
                }
            }
            break;
        }
    }
    
    // Create new KPI row
    const newKpi = {
        kpi_name: '',
        template_id: ''
    };
    
    const newRow = createKpiRow(newKpi, categoryId, categoryName);
    
    if (insertAfter) {
        insertAfter.after(newRow);
    } else {
        // Insert right after category header
        const headerRow = Array.from(tbody.children).find(row => 
            row.classList.contains('table-primary') && row.textContent.includes(categoryName)
        );
        if (headerRow) {
            headerRow.after(newRow);
        }
    }
    
    kpiIndex++;
    calculateTotal();
}

function removeKpiRow(button) {
    if (confirm('Are you sure you want to remove this KPI?')) {
        button.closest('tr').remove();
        calculateTotal();
    }
}

function calculateTotal() {
    const weightageInputs = document.querySelectorAll('.weightage-input');
    let total = 0;
    
    weightageInputs.forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    
    document.getElementById('totalWeightage').textContent = total.toFixed(1);
    
    const warning = document.getElementById('weightageWarning');
    const submitBtn = document.getElementById('submitBtn');
    
    if (Math.abs(total - 100) > 0.1) {
        warning.style.display = 'inline';
        submitBtn.disabled = true;
    } else {
        warning.style.display = 'none';
        submitBtn.disabled = false;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Form validation
document.getElementById('assignForm').addEventListener('submit', function(e) {
    const weightageInputs = document.querySelectorAll('.weightage-input');
    let total = 0;
    
    weightageInputs.forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    
    if (Math.abs(total - 100) > 0.1) {
        e.preventDefault();
        alert('Total weightage must equal 100%. Current total: ' + total.toFixed(1) + '%');
        return false;
    }
});
</script>
