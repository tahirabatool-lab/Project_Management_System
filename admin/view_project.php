<?php
// admin/view_project.php - View Project Details
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header("Location: " . BASE_URL . "/admin/projects.php"); exit(); }

// Get project
$stmt = $conn->prepare("
    SELECT p.*, u.name AS assigned_name, u.email AS assigned_email,
           a.name AS creator_name
    FROM projects p
    LEFT JOIN users u ON p.assigned_to = u.id
    LEFT JOIN users a ON p.created_by = a.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project) { setFlash('error', 'Project not found.'); header("Location: " . BASE_URL . "/admin/projects.php"); exit(); }

// Handle mark as completed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_completed'])) {
    $conn->query("UPDATE projects SET status = 'completed', progress = 100 WHERE id = {$id}");
    setFlash('success', 'Project marked as completed!');
    header("Location: ?id={$id}");
    exit();
}

// Get tasks
$tasks = $conn->query("SELECT * FROM tasks WHERE project_id = {$id} ORDER BY created_at");

// Get submission
$sub = $conn->query("SELECT s.*, u.name FROM submissions s JOIN users u ON s.user_id = u.id WHERE s.project_id = {$id}")->fetch_assoc();

// Format requirements as HTML list
function formatRequirements($text) {
    $lines = explode("\n", trim($text));
    $html = '<ul class="list-group list-group-flush">';
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        // Remove leading '- ' or '* ' or '• '
        $line = ltrim($line, '-*• ');
        $line = trim($line);
        if (!empty($line)) {
            $html .= '<li class="list-group-item px-0"><i class="bi bi-check-circle-fill text-success me-2"></i>' . htmlspecialchars($line) . '</li>';
        }
    }
    $html .= '</ul>';
    return $html;
}

$badges = ['pending'=>'warning','in_progress'=>'primary','submitted'=>'info','completed'=>'success'];
$statusBadge = $badges[$project['status']] ?? 'secondary';
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= BASE_URL ?>/admin/projects.php" class="btn btn-outline-secondary me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="flex-grow-1">
        <h2 class="fw-bold mb-0"><?= htmlspecialchars($project['title']) ?></h2>
        <span class="badge bg-<?= $statusBadge ?>"><?= ucfirst(str_replace('_',' ', $project['status'])) ?></span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/admin/edit_project.php?id=<?= $id ?>" class="btn btn-warning">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <?php if ($project['status'] !== 'completed'): ?>
        <form method="POST">
            <button name="mark_completed" class="btn btn-success" onclick="return confirm('Mark this project as completed?')">
                <i class="bi bi-check2-circle me-1"></i>Mark Completed
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <!-- Main Info -->
    <div class="col-lg-8">
        <!-- Requirements -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between">
                <h5 class="fw-bold mb-0"><i class="bi bi-stars me-2 text-warning"></i>Project Requirements</h5>
                <span class="badge bg-warning text-dark">AI Generated</span>
            </div>
            <div class="card-body px-4 pb-4">
                <?php if ($project['requirements']): ?>
                    <?= formatRequirements($project['requirements']) ?>
                <?php else: ?>
                    <p class="text-muted fst-italic">No requirements added yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tasks -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0"><i class="bi bi-list-check me-2 text-primary"></i>Tasks</h5>
            </div>
            <div class="card-body px-4 pb-4">
                <?php if ($tasks->num_rows > 0): ?>
                <div class="list-group list-group-flush">
                    <?php while ($t = $tasks->fetch_assoc()):
                        $tc = ['pending'=>'warning','in_progress'=>'primary','done'=>'success'][$t['status']] ?? 'secondary';
                    ?>
                    <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold"><?= htmlspecialchars($t['title']) ?></div>
                        </div>
                        <span class="badge bg-<?= $tc ?>"><?= ucfirst(str_replace('_',' ',$t['status'])) ?></span>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                    <p class="text-muted fst-italic">No tasks yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Submission -->
        <?php if ($sub): ?>
        <div class="card border-0 shadow-sm rounded-4 border-info">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-send-check me-2 text-info"></i>Project Submission</h5>
                <p class="mb-1"><strong>Submitted by:</strong> <?= htmlspecialchars($sub['name']) ?></p>
                <p class="mb-1"><strong>Date:</strong> <?= date('M d, Y H:i', strtotime($sub['submitted_at'])) ?></p>
                <?php if ($sub['notes']): ?>
                    <p class="mb-0"><strong>Notes:</strong> <?= nl2br(htmlspecialchars($sub['notes'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Project Details</h5>
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td class="text-muted">Assigned To</td>
                        <td class="fw-semibold"><?= $project['assigned_name'] ?? '<em class="text-muted">None</em>' ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td><span class="badge bg-<?= $statusBadge ?>"><?= ucfirst(str_replace('_',' ',$project['status'])) ?></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Progress</td>
                        <td><?= $project['progress'] ?>%</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Deadline</td>
                        <td><?= $project['deadline'] ? date('M d, Y', strtotime($project['deadline'])) : '—' ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created</td>
                        <td><?= date('M d, Y', strtotime($project['created_at'])) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-graph-up me-2 text-success"></i>Progress</h5>
                <div class="progress" style="height:20px;">
                    <div class="progress-bar bg-success progress-bar-striped" 
                         style="width:<?= $project['progress'] ?>%">
                        <?= $project['progress'] ?>%
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
