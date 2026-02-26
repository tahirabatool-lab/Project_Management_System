<?php
// admin/projects.php - All Projects List
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
requireAdmin();

// Filter by status
$statusFilter = $_GET['status'] ?? '';
$where = $statusFilter ? "WHERE p.status = '" . $conn->real_escape_string($statusFilter) . "'" : "";

$projects = $conn->query("
    SELECT p.*, u.name AS assigned_name 
    FROM projects p 
    LEFT JOIN users u ON p.assigned_to = u.id 
    {$where}
    ORDER BY p.created_at DESC
");
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0"><i class="bi bi-folder2-open me-2 text-primary"></i>All Projects</h2>
    <a href="<?= BASE_URL ?>/admin/create_project.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>New Project
    </a>
</div>

<!-- Status Filter -->
<div class="mb-4 d-flex gap-2 flex-wrap">
    <?php
    $statuses = ['' => 'All', 'pending' => 'Pending', 'in_progress' => 'In Progress', 'submitted' => 'Submitted', 'completed' => 'Completed'];
    $colors = ['' => 'secondary', 'pending' => 'warning', 'in_progress' => 'primary', 'submitted' => 'info', 'completed' => 'success'];
    foreach ($statuses as $val => $label):
        $active = $statusFilter === $val ? '' : 'outline-';
    ?>
        <a href="?status=<?= $val ?>" class="btn btn-<?= $active . $colors[$val] ?>">
            <?= $label ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Deadline</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while ($p = $projects->fetch_assoc()): ?>
                    <tr>
                        <td class="text-muted"><?= $i++ ?></td>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($p['title']) ?></div>
                        </td>
                        <td><?= $p['assigned_name'] ? htmlspecialchars($p['assigned_name']) : '<span class="text-muted fst-italic">Unassigned</span>' ?></td>
                        <td><?php
                            $badges = ['pending'=>'warning','in_progress'=>'primary','submitted'=>'info','completed'=>'success'];
                            $b = $badges[$p['status']] ?? 'secondary';
                            echo "<span class='badge bg-{$b}'>" . ucfirst(str_replace('_',' ',$p['status'])) . "</span>";
                        ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:8px;">
                                    <div class="progress-bar bg-success" style="width:<?= $p['progress'] ?>%"></div>
                                </div>
                                <small><?= $p['progress'] ?>%</small>
                            </div>
                        </td>
                        <td>
                            <?php if ($p['deadline']): ?>
                                <?php
                                $deadlineDate = new DateTime($p['deadline']);
                                $today = new DateTime();
                                $isPast = $deadlineDate < $today && $p['status'] !== 'completed';
                                ?>
                                <span class="<?= $isPast ? 'text-danger fw-semibold' : '' ?>">
                                    <?= date('M d, Y', strtotime($p['deadline'])) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="<?= BASE_URL ?>/admin/view_project.php?id=<?= $p['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/admin/edit_project.php?id=<?= $p['id'] ?>" 
                                   class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/admin/delete_project.php?id=<?= $p['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger" title="Delete"
                                   onclick="return confirm('Delete this project? This cannot be undone.')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($projects->num_rows === 0): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-folder-x display-6 d-block mb-2"></i>
                            No projects found. <a href="<?= BASE_URL ?>/admin/create_project.php">Create one now</a>.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
