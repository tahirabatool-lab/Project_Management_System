<?php
// user/dashboard.php - User Dashboard
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
requireLogin();
if (isAdmin()) { header("Location: " . BASE_URL . "/admin/dashboard.php"); exit(); }

$userId = currentUserId();

// Get stats
$total     = $conn->query("SELECT COUNT(*) c FROM projects WHERE assigned_to = {$userId}")->fetch_assoc()['c'];
$completed = $conn->query("SELECT COUNT(*) c FROM projects WHERE assigned_to = {$userId} AND status = 'completed'")->fetch_assoc()['c'];
$inProg    = $conn->query("SELECT COUNT(*) c FROM projects WHERE assigned_to = {$userId} AND status = 'in_progress'")->fetch_assoc()['c'];
$pending   = $conn->query("SELECT COUNT(*) c FROM projects WHERE assigned_to = {$userId} AND status = 'pending'")->fetch_assoc()['c'];

// Recent projects
$projects = $conn->query("SELECT * FROM projects WHERE assigned_to = {$userId} ORDER BY created_at DESC LIMIT 5");
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="mb-4">
    <h2 class="fw-bold mb-0"><i class="bi bi-house me-2 text-primary"></i>My Dashboard</h2>
    <p class="text-muted">Welcome back, <strong><?= htmlspecialchars(currentUserName()) ?></strong>!</p>
</div>

<!-- Stats -->
<div class="row g-4 mb-5">
    <div class="col-6 col-md-3">
        <a href="<?= BASE_URL ?>/user/my_projects.php" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4 h-100 stat-card">
                <div class="fs-1 fw-bold text-primary"><?= $total ?></div>
                <div class="text-muted small">Total Projects</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= BASE_URL ?>/user/my_projects.php?status=pending" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4 h-100 stat-card">
                <div class="fs-1 fw-bold text-warning"><?= $pending ?></div>
                <div class="text-muted small">Pending</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= BASE_URL ?>/user/my_projects.php?status=in_progress" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4 h-100 stat-card">
                <div class="fs-1 fw-bold text-info"><?= $inProg ?></div>
                <div class="text-muted small">In Progress</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= BASE_URL ?>/user/my_projects.php?status=completed" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4 h-100 stat-card">
                <div class="fs-1 fw-bold text-success"><?= $completed ?></div>
                <div class="text-muted small">Completed</div>
            </div>
        </a>
    </div>
</div>

<style>
    .stat-card {
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.2) !important;
    }
</style>

<!-- Recent Projects -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between">
        <h5 class="mb-0 fw-bold"><i class="bi bi-folder me-2"></i>My Projects</h5>
        <a href="<?= BASE_URL ?>/user/my_projects.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Project</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Deadline</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($p = $projects->fetch_assoc()):
                        $badges = ['pending'=>'warning','in_progress'=>'primary','submitted'=>'info','completed'=>'success'];
                        $b = $badges[$p['status']] ?? 'secondary';
                    ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($p['title']) ?></td>
                        <td><span class="badge bg-<?= $b ?>"><?= ucfirst(str_replace('_',' ',$p['status'])) ?></span></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:8px;">
                                    <div class="progress-bar bg-success" style="width:<?= $p['progress'] ?>%"></div>
                                </div>
                                <small><?= $p['progress'] ?>%</small>
                            </div>
                        </td>
                        <td><?= $p['deadline'] ? date('M d, Y', strtotime($p['deadline'])) : '—' ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/user/view_project.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($total == 0): ?>
                    <tr><td colspan="5" class="text-center text-muted py-5">
                        <i class="bi bi-folder-x display-6 d-block mb-2"></i>
                        No projects assigned to you yet.
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
