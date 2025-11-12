<div class="container-fluid mt-4">
    <h1 class="h3 mb-4">System Audit Logs</h1>

    <div class="card shadow">
        <div class="card-body">
            <?php if (!empty($logs)): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Entity Type</th>
                                <th>Entity ID</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= date('M d, Y H:i:s', strtotime($log->timestamp)) ?></td>
                                <td>
                                    <?php if ($log->first_name): ?>
                                        <?= $log->first_name . ' ' . $log->last_name ?>
                                        <br><small class="text-muted"><?= $log->email ?></small>
                                    <?php else: ?>
                                        <?= $log->email ?? 'System' ?>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge badge-info"><?= $log->action ?></span></td>
                                <td><?= $log->entity_type ?></td>
                                <td><code><?= substr($log->entity_id, 0, 8) ?>...</code></td>
                                <td><?= $log->ip_address ?? '-' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($current_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('admin/audit_logs?page=' . ($current_page - 1)) ?>">
                                    Previous
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                            <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= base_url('admin/audit_logs?page=' . $i) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('admin/audit_logs?page=' . ($current_page + 1)) ?>">
                                    Next
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No audit logs found.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
