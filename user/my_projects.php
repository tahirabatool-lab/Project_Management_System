<?php
// user/my_projects.php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
requireLogin();
if (isAdmin()) { header("Location: " . BASE_URL . "/admin/dashboard.php"); exit(); }

$userId = currentUserId();

// Filter by status
$statusFilter = $_GET['status'] ?? '';
$where = $statusFilter ? "AND status = '" . $conn->real_escape_string($statusFilter) . "'" : "";

$projects = $conn->query("SELECT * FROM projects WHERE assigned_to = {$userId} {$where} ORDER BY created_at DESC");
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<h2 class="fw-bold mb-4"><i class="bi bi-folder me-2 text-primary"></i>My Projects</h2>

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

<?php if ($projects->num_rows === 0): ?>
    <div class="alert alert-info">No projects found. Please check back later.</div>
<?php else: ?>
<div class="row g-4">
    <?php while ($p = $projects->fetch_assoc()):
        $badges = ['pending'=>'warning','in_progress'=>'primary','submitted'=>'info','completed'=>'success'];
        $b = $badges[$p['status']] ?? 'secondary';
    ?>
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h5 class="fw-bold mb-0"><?= htmlspecialchars($p['title']) ?></h5>
                    <span class="badge bg-<?= $b ?>"><?= ucfirst(str_replace('_',' ',$p['status'])) ?></span>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Progress</span><span><?= $p['progress'] ?>%</span>
                    </div>
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar bg-success" style="width:<?= $p['progress'] ?>%"></div>
                    </div>
                </div>
                <?php if ($p['deadline']): ?>
                <p class="small text-muted mb-3">
                    <i class="bi bi-calendar me-1"></i>Deadline: <?= date('M d, Y', strtotime($p['deadline'])) ?>
                </p>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/user/view_project.php?id=<?= $p['id'] ?>" 
                   class="btn btn-primary w-100">
                    <i class="bi bi-arrow-right me-1"></i>Open Project
                </a>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
