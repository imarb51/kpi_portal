<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">KPI Templates</h1>
        <a href="<?= base_url('admin/create_template') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Template
        </a>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Category Filter -->
    <div class="card shadow mb-3">
        <div class="card-body">
            <form method="get" action="<?= base_url('admin/kpi_templates') ?>" class="form-inline">
                <label class="mr-2">Filter by Category:</label>
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

    <div class="card shadow">
        <div class="card-body">
            <?php if (!empty($templates)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Category</th>
                                <th>KPI Name</th>
                                <th>Description</th>
                                <th>Unit</th>
                                <th>Suggested Weightage</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($templates as $template): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-info"><?= $template->category_name ?></span>
                                </td>
                                <td><strong><?= $template->kpi_name ?></strong></td>
                                <td><?= $template->description ? substr($template->description, 0, 100) . '...' : '<em class="text-muted">No description</em>' ?></td>
                                <td><?= $template->measurement_unit ?? '-' ?></td>
                                <td>
                                    <?php 
                                    // Extract weightage from description if it exists (from CSV import)
                                    if (isset($template->weightage)) {
                                        echo $template->weightage . '%';
                                    } elseif ($template->description && preg_match('/Suggested weightage: (\d+)%/', $template->description, $matches)) {
                                        echo '<span class="text-muted">' . $matches[1] . '%</span>';
                                    } else {
                                        echo '<em class="text-muted">Set on assignment</em>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($template->is_active): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('admin/edit_template/' . $template->template_id) ?>" 
                                           class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= base_url('admin/delete_template/' . $template->template_id) ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this template?')"
                                           title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No templates found. 
                    <a href="<?= base_url('admin/create_template') ?>">Create your first template</a>.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
