<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fas fa-file-alt"></i> KPI Templates</h1>
        <a href="<?= base_url('admin/create_template') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Template
        </a>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Filter -->
    <div class="card shadow mb-3">
        <div class="card-body">
            <form method="get" action="<?= base_url('admin/kpi_templates') ?>" class="form-inline">
                <label class="mr-2"><i class="fas fa-filter"></i> Filter by Category:</label>
                <select name="category" class="form-control mr-2" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat->category_id ?>" <?= $selected_category == $cat->category_id ? 'selected' : '' ?>>
                            <?= $cat->category_name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <!-- Templates List -->
    <div class="card shadow">
        <div class="card-body">
            <?php if (!empty($grouped_templates)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 40%;">Template Name</th>
                                <th style="width: 20%;">Category</th>
                                <th style="width: 15%;" class="text-center">KPI Count</th>
                                <th style="width: 10%;" class="text-center">Status</th>
                                <th style="width: 15%;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grouped_templates as $template): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($template->template_name ?? 'Unnamed Template') ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?php if ($template->creator_first_name): ?>
                                            Created by <?= $template->creator_first_name ?> <?= $template->creator_last_name ?>
                                        <?php endif; ?>
                                    </small>
                                </td>
                                <td>
                                    <?php 
                                    // Split multiple categories and display as badges
                                    $categories = explode(', ', $template->categories);
                                    foreach ($categories as $category): 
                                    ?>
                                        <span class="badge badge-info mr-1"><?= htmlspecialchars($category) ?></span>
                                    <?php endforeach; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary badge-pill"><?= $template->kpi_count ?> KPIs</span>
                                </td>
                                <td class="text-center">
                                    <?php if ($template->is_active): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-info" 
                                                onclick="viewTemplateKPIs('<?= htmlspecialchars($template->template_name, ENT_QUOTES) ?>')" 
                                                title="View KPIs">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-primary" 
                                                onclick="editTemplate('<?= htmlspecialchars($template->template_name, ENT_QUOTES) ?>')" 
                                                title="Edit Template">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger" 
                                                onclick="deleteTemplate('<?= htmlspecialchars($template->template_name, ENT_QUOTES) ?>')" 
                                                title="Delete Template">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <!-- Hidden row for KPI details -->
                            <tr id="kpis-<?= md5($template->template_name) ?>" style="display:none;" class="bg-light">
                                <td colspan="5">
                                    <div class="p-3">
                                        <h6><i class="fas fa-tasks"></i> KPIs in this template:</h6>
                                        <div class="kpi-details-loading">
                                            <i class="fas fa-spinner fa-spin"></i> Loading...
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No templates found</h5>
                    <p class="text-muted">Create your first KPI template to get started.</p>
                    <a href="<?= base_url('admin/create_template') ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Template
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal for viewing KPIs -->
<div class="modal fade" id="kpiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-tasks"></i> <span id="modalTemplateName"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalKPIContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Loading KPIs...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for editing template -->
<form id="editTemplateForm" method="POST" action="<?= base_url('admin/edit_template_group') ?>" style="display:none;">
    <input type="hidden" name="template_name_param" id="editTemplateNameInput">
</form>

<script>
function viewTemplateKPIs(templateName) {
    $('#modalTemplateName').text(templateName);
    $('#kpiModal').modal('show');
    
    // Load KPIs via AJAX
    $.ajax({
        url: '<?= base_url('admin/get_template_kpis') ?>',
        method: 'POST',
        data: { template_name: templateName },
        success: function(response) {
            const data = JSON.parse(response);
            let html = '<table class="table table-bordered">';
            html += '<thead class="thead-light"><tr><th>#</th><th>KPI Task</th><th>Weightage</th><th>Default Score</th></tr></thead>';
            html += '<tbody>';
            
            if (data.kpis && data.kpis.length > 0) {
                data.kpis.forEach(function(kpi, index) {
                    // Extract weightage from description
                    let weightage = '-';
                    if (kpi.description) {
                        const match = kpi.description.match(/Weightage: (\d+)%/);
                        if (match) weightage = match[1] + '%';
                    }
                    
                    html += '<tr>';
                    html += '<td>' + (index + 1) + '</td>';
                    html += '<td>' + kpi.kpi_name + '</td>';
                    html += '<td><span class="badge badge-info">' + weightage + '</span></td>';
                    html += '<td>' + (kpi.default_score || '-') + '</td>';
                    html += '</tr>';
                });
            } else {
                html += '<tr><td colspan="4" class="text-center text-muted">No KPIs found</td></tr>';
            }
            
            html += '</tbody></table>';
            $('#modalKPIContent').html(html);
        },
        error: function() {
            $('#modalKPIContent').html('<div class="alert alert-danger">Error loading KPIs</div>');
        }
    });
}

function editTemplate(templateName) {
    // Use POST form to avoid URL encoding issues
    $('#editTemplateNameInput').val(templateName);
    $('#editTemplateForm').submit();
}

function deleteTemplate(templateName) {
    if (confirm('Are you sure you want to delete the template "' + templateName + '" and all its KPIs?')) {
        window.location.href = '<?= base_url('admin/delete_template_group/') ?>' + encodeURIComponent(templateName);
    }
}
</script>
