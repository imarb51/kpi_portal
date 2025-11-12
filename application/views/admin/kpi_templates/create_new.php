<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fas fa-plus-circle"></i> Create KPI Template</h1>
        <a href="<?= base_url('admin/kpi_templates') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Templates
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

    <?php if (validation_errors()): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= validation_errors() ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-file-alt"></i> Template Information</h5>
        </div>
        <div class="card-body">
            <form method="post" action="<?= base_url('admin/create_template') ?>" id="templateForm">
                
                <!-- Template Name -->
                <div class="form-group">
                    <label for="template_name">Template Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="template_name" name="template_name" 
                           placeholder="e.g., Tech Team Template, Accounts Team Template" 
                           value="<?= set_value('template_name') ?>" required>
                    <small class="form-text text-muted">This name will be used to assign the template to employees</small>
                </div>

                <!-- Department Selection -->
                <div class="form-group">
                    <label for="department_id">Department</label>
                    <select class="form-control" id="department_id" name="department_id">
                        <option value="">-- All Departments (No Restriction) --</option>
                        <?php if (isset($departments) && !empty($departments)): ?>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept->department_id ?>" <?= set_select('department_id', $dept->department_id) ?>>
                                    <?= htmlspecialchars($dept->department_name) ?> (<?= htmlspecialchars($dept->department_code) ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle"></i> If a department is selected, this template will auto-appear when assigning KPIs to employees in that department. 
                        Leave empty to make it available for all departments.
                    </small>
                </div>

                <hr class="my-4">

                <!-- KPIs Section -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5><i class="fas fa-tasks"></i> KPI Items</h5>
                    <button type="button" class="btn btn-success btn-sm" onclick="addKpiRow()">
                        <i class="fas fa-plus"></i> Add KPI
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="kpiTable">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 35%;">KPI Name / Task <span class="text-danger">*</span></th>
                                <th style="width: 30%;">Category <span class="text-danger">*</span></th>
                                <th style="width: 25%;">Description</th>
                                <th style="width: 10%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="kpiTableBody">
                            <!-- Initial row -->
                            <tr class="kpi-row">
                                <td>
                                    <textarea class="form-control" name="kpis[0][kpi_name]" 
                                              rows="2" placeholder="Enter KPI task name" required></textarea>
                                </td>
                                <td>
                                    <select class="form-control" name="kpis[0][category_id]" required>
                                        <option value="">-- Select --</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category->category_id ?>">
                                                <?= $category->category_name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <textarea class="form-control" name="kpis[0][description]" 
                                              rows="2" placeholder="Optional details"></textarea>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeKpiRow(this)" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <strong>Note:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Weightage will be assigned by Manager when assigning KPIs to employees</li>
                        <li>Each KPI can have its own category</li>
                        <li>You can add multiple KPIs to one template</li>
                        <li>This template can be assigned to multiple employees</li>
                    </ul>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                        <i class="fas fa-save"></i> Create Template
                    </button>
                    <a href="<?= base_url('admin/kpi_templates') ?>" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
let rowIndex = 1;

// Category options for new rows
const categoryOptions = `
    <option value="">-- Select --</option>
    <?php foreach ($categories as $category): ?>
    <option value="<?= $category->category_id ?>"><?= htmlspecialchars($category->category_name) ?></option>
    <?php endforeach; ?>
`;

function addKpiRow() {
    const tbody = document.getElementById('kpiTableBody');
    const newRow = document.createElement('tr');
    newRow.className = 'kpi-row';
    newRow.innerHTML = `
        <td>
            <textarea class="form-control" name="kpis[${rowIndex}][kpi_name]" 
                      rows="2" placeholder="Enter KPI task name" required></textarea>
        </td>
        <td>
            <select class="form-control" name="kpis[${rowIndex}][category_id]" required>
                ${categoryOptions}
            </select>
        </td>
        <td>
            <textarea class="form-control" name="kpis[${rowIndex}][description]" 
                      rows="2" placeholder="Optional details"></textarea>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeKpiRow(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(newRow);
    rowIndex++;
    updateRemoveButtons();
}

function removeKpiRow(button) {
    button.closest('tr').remove();
    updateRemoveButtons();
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.kpi-row');
    rows.forEach((row, index) => {
        const removeBtn = row.querySelector('.btn-danger');
        removeBtn.disabled = rows.length === 1;
    });
}
</script>
