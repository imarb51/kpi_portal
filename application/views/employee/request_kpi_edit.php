<div class="page-header">
    <div class="container">
        <h2><i class="fas fa-edit"></i> Request KPI Edit</h2>
        <p class="mb-0">Request changes to your assigned KPIs</p>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-pen"></i> KPI Edit Request</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Before submitting:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Review all assigned KPIs and their weightages carefully</li>
                            <li>Clearly explain what changes you would like and why</li>
                            <li>Your manager(s) will be notified and can respond to your request</li>
                        </ul>
                    </div>

                    <?php if (!empty($kpis)): ?>
                    <!-- Current KPIs Summary -->
                    <h6 class="mb-3"><i class="fas fa-list"></i> Current Assigned KPIs:</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Weightage</th>
                                    <th>KPI Name</th>
                                    <th>Category</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($kpis as $kpi): ?>
                                <tr>
                                    <td class="text-center">
                                        <strong><?= number_format($kpi->weightage, 1) ?>%</strong>
                                    </td>
                                    <td><?= htmlspecialchars($kpi->kpi_name) ?></td>
                                    <td>
                                        <span class="badge badge-info"><?= htmlspecialchars($kpi->category_name) ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                    <!-- Edit Request Form -->
                    <form method="post" action="<?= base_url('employee/request_kpi_edit') ?>">
                        <input type="hidden" name="period_id" value="<?= $period_id ?>">
                        
                        <div class="form-group">
                            <label for="edit_notes">
                                <strong>Describe the changes you would like:</strong> 
                                <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="edit_notes" name="edit_notes" 
                                      rows="6" required 
                                      placeholder="Example: I would like to increase the weightage for 'Project Completion' from 20% to 30% because...&#10;&#10;OR&#10;&#10;I would like to add a new KPI for 'Team Leadership' as this is a significant part of my role..."></textarea>
                            <small class="form-text text-muted">
                                Please be specific about:
                                <ul>
                                    <li>Which KPI(s) need changes</li>
                                    <li>What changes you're requesting (weightage adjustment, add/remove KPI, etc.)</li>
                                    <li>Why these changes are needed</li>
                                </ul>
                            </small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-paper-plane"></i> Submit Edit Request
                            </button>
                            <a href="<?= base_url('employee/my_kpis?period_id=' . $period_id) ?>" 
                               class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
