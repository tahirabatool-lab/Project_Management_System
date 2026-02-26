<?php
// admin/dashboard.php - Admin Dashboard
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
requireAdmin();

// Stats
$totalProjects = $conn->query("SELECT COUNT(*) as c FROM projects")->fetch_assoc()['c'];
$totalUsers    = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'user'")->fetch_assoc()['c'];
$completed     = $conn->query("SELECT COUNT(*) as c FROM projects WHERE status = 'completed'")->fetch_assoc()['c'];
$pending       = $conn->query("SELECT COUNT(*) as c FROM projects WHERE status = 'pending'")->fetch_assoc()['c'];
$submitted     = $conn->query("SELECT COUNT(*) as c FROM projects WHERE status = 'submitted'")->fetch_assoc()['c'];

// Recent projects
$recentProjects = $conn->query("
    SELECT p.*, u.name AS assigned_name 
    FROM projects p 
    LEFT JOIN users u ON p.assigned_to = u.id 
    ORDER BY p.created_at DESC LIMIT 5
");
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0"><i class="bi bi-speedometer2 me-2 text-primary"></i>Admin Dashboard</h2>
        <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars(currentUserName()) ?>!</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/create_project.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>New Project
    </a>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <a href="<?= BASE_URL ?>/admin/projects.php" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white h-100 stat-card">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <i class="bi bi-folder2-open display-5"></i>
                    <div>
                        <div class="fs-2 fw-bold"><?= $totalProjects ?></div>
                        <div>Total Projects</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?= BASE_URL ?>/admin/users.php" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 bg-success text-white h-100 stat-card">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <i class="bi bi-people display-5"></i>
                    <div>
                        <div class="fs-2 fw-bold"><?= $totalUsers ?></div>
                        <div>Total Users</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?= BASE_URL ?>/admin/projects.php?status=pending" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 bg-warning text-white h-100 stat-card">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <i class="bi bi-clock display-5"></i>
                    <div>
                        <div class="fs-2 fw-bold"><?= $pending ?></div>
                        <div>Pending</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?= BASE_URL ?>/admin/projects.php?status=completed" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 bg-info text-white h-100 stat-card">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <i class="bi bi-check2-circle display-5"></i>
                    <div>
                        <div class="fs-2 fw-bold"><?= $completed ?></div>
                        <div>Completed</div>
                    </div>
                </div>
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

<!-- Submitted Projects Alert -->
<?php if ($submitted > 0): ?>
<div class="alert alert-warning d-flex align-items-center mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
    <div>
        <strong><?= $submitted ?> project(s)</strong> have been submitted by users and are awaiting your review.
        <a href="<?= BASE_URL ?>/admin/projects.php?status=submitted" class="ms-2 btn btn-sm btn-warning">Review Now</a>
    </div>
</div>
<?php endif; ?>

<!-- Recent Projects Table -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between">
        <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2"></i>Recent Projects</h5>
        <a href="<?= BASE_URL ?>/admin/projects.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Project</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Deadline</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($p = $recentProjects->fetch_assoc()): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($p['title']) ?></td>
                        <td><?= $p['assigned_name'] ? htmlspecialchars($p['assigned_name']) : '<span class="text-muted">Unassigned</span>' ?></td>
                        <td><?php
                            $badges = ['pending'=>'warning','in_progress'=>'primary','submitted'=>'info','completed'=>'success'];
                            $b = $badges[$p['status']] ?? 'secondary';
                            echo "<span class='badge bg-{$b}'>" . ucfirst(str_replace('_',' ',$p['status'])) . "</span>";
                        ?></td>
                        <td>
                            <div class="progress" style="height:8px; width:100px;">
                                <div class="progress-bar bg-success" style="width:<?= $p['progress'] ?>%"></div>
                            </div>
                            <small><?= $p['progress'] ?>%</small>
                        </td>
                        <td><?= $p['deadline'] ? date('M d, Y', strtotime($p['deadline'])) : '—' ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/admin/view_project.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($totalProjects == 0): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No projects yet. <a href="<?= BASE_URL ?>/admin/create_project.php">Create one</a>.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
