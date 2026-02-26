<?php
// user/view_project.php - View & Manage Project
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
requireLogin();
if (isAdmin()) { header("Location: " . BASE_URL . "/admin/dashboard.php"); exit(); }

$userId = currentUserId();
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header("Location: " . BASE_URL . "/user/my_projects.php"); exit(); }

// Get project (only if assigned to this user)
$stmt = $conn->prepare("SELECT * FROM projects WHERE id = ? AND assigned_to = ?");
$stmt->bind_param("ii", $id, $userId);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project) { 
    setFlash('error', 'Project not found or not assigned to you.'); 
    header("Location: " . BASE_URL . "/user/my_projects.php"); 
    exit(); 
}

// Handle: Add task
if (isset($_POST['add_task'])) {
    $taskTitle = trim($_POST['task_title'] ?? '');
    $taskDesc  = trim($_POST['task_desc'] ?? '');
    if (!empty($taskTitle)) {
        $stmt = $conn->prepare("INSERT INTO tasks (project_id, title, description) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $id, $taskTitle, $taskDesc);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: ?id={$id}"); exit();
}

// Handle: Update task status
if (isset($_POST['update_task'])) {
    $taskId = (int)$_POST['task_id'];
    $status = $_POST['task_status'];
    $allowed = ['pending','in_progress','done'];
    if (in_array($status, $allowed)) {
        $conn->query("UPDATE tasks SET status = '{$status}' WHERE id = {$taskId} AND project_id = {$id}");
    }
    // Recalculate progress based on tasks
    $total = $conn->query("SELECT COUNT(*) c FROM tasks WHERE project_id = {$id}")->fetch_assoc()['c'];
    $done  = $conn->query("SELECT COUNT(*) c FROM tasks WHERE project_id = {$id} AND status = 'done'")->fetch_assoc()['c'];
    $progress = $total > 0 ? round(($done / $total) * 100) : 0;
    // Update project status
    $pStatus = 'pending';
    if ($progress > 0 && $progress < 100) $pStatus = 'in_progress';
    elseif ($progress === 100) $pStatus = 'in_progress'; // stays in_progress until submitted
    $conn->query("UPDATE projects SET progress = {$progress}, status = '{$pStatus}' WHERE id = {$id}");
    header("Location: ?id={$id}"); exit();
}

// Handle: Delete task
if (isset($_GET['delete_task'])) {
    $taskId = (int)$_GET['delete_task'];
    $conn->query("DELETE FROM tasks WHERE id = {$taskId} AND project_id = {$id}");
    header("Location: ?id={$id}"); exit();
}

// Handle: Submit project
if (isset($_POST['submit_project'])) {
    $notes = trim($_POST['notes'] ?? '');
    // Check not already submitted
    $existing = $conn->query("SELECT id FROM submissions WHERE project_id = {$id}")->num_rows;
    if ($existing === 0) {
        $stmt = $conn->prepare("INSERT INTO submissions (project_id, user_id, notes) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id, $userId, $notes);
        $stmt->execute();
        $stmt->close();
        $conn->query("UPDATE projects SET status = 'submitted' WHERE id = {$id}");
        setFlash('success', 'Project submitted successfully!');
    } else {
        setFlash('error', 'Project already submitted.');
    }
    header("Location: ?id={$id}"); exit();
}

// Get tasks
$tasks = $conn->query("SELECT * FROM tasks WHERE project_id = {$id} ORDER BY created_at");
$taskList = [];
while ($t = $tasks->fetch_assoc()) $taskList[] = $t;

// Get submission
$submitted = $conn->query("SELECT * FROM submissions WHERE project_id = {$id}")->fetch_assoc();

// Format requirements
function formatReqs($text) {
    $lines = explode("\n", trim($text));
    $html = '';
    foreach ($lines as $line) {
        $line = trim(ltrim(trim($line), '-*• '));
        if (!empty($line)) {
            $html .= '<li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>' . htmlspecialchars($line) . '</li>';
        }
    }
    return '<ul class="list-unstyled">' . $html . '</ul>';
}

$badges = ['pending'=>'warning','in_progress'=>'primary','submitted'=>'info','completed'=>'success'];
$statusBadge = $badges[$project['status']] ?? 'secondary';
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= BASE_URL ?>/user/my_projects.php" class="btn btn-outline-secondary me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h2 class="fw-bold mb-0"><?= htmlspecialchars($project['title']) ?></h2>
        <span class="badge bg-<?= $statusBadge ?>"><?= ucfirst(str_replace('_',' ',$project['status'])) ?></span>
        <?php if ($project['deadline']): ?>
            <span class="ms-2 text-muted small"><i class="bi bi-calendar me-1"></i><?= date('M d, Y', strtotime($project['deadline'])) ?></span>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Requirements -->
        <?php if ($project['requirements']): ?>
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between">
                <h5 class="fw-bold mb-0"><i class="bi bi-stars me-2 text-warning"></i>Project Requirements</h5>
                <span class="badge bg-warning text-dark">AI Generated</span>
            </div>
            <div class="card-body px-4 pb-4">
                <?= formatReqs($project['requirements']) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tasks -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0"><i class="bi bi-list-check me-2 text-primary"></i>My Tasks</h5>
            </div>
            <div class="card-body px-4 pb-4">
                <?php if (empty($taskList)): ?>
                    <p class="text-muted fst-italic">No tasks added yet. Add your first task below.</p>
                <?php else: ?>
                <div class="list-group list-group-flush mb-4">
                    <?php foreach ($taskList as $t):
                        $tc = ['pending'=>'warning','in_progress'=>'primary','done'=>'success'][$t['status']] ?? 'secondary';
                    ?>
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="fw-semibold <?= $t['status'] === 'done' ? 'text-decoration-line-through text-muted' : '' ?>">
                                    <?= htmlspecialchars($t['title']) ?>
                                </div>
                                <?php if ($t['description']): ?>
                                    <small class="text-muted"><?= htmlspecialchars($t['description']) ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex align-items-center gap-2 ms-3">
                                <!-- Status Update -->
                                <?php if ($project['status'] !== 'completed'): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="update_task" value="1">
                                    <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
                                    <select name="task_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <?php foreach (['pending','in_progress','done'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $t['status'] === $s ? 'selected' : '' ?>>
                                                <?= ucfirst(str_replace('_',' ',$s)) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                                <a href="?id=<?= $id ?>&delete_task=<?= $t['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Delete this task?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php else: ?>
                                <span class="badge bg-<?= $tc ?>"><?= ucfirst(str_replace('_',' ',$t['status'])) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Add Task Form -->
                <?php if ($project['status'] !== 'completed' && $project['status'] !== 'submitted'): ?>
                <form method="POST" class="border rounded-3 p-3 bg-light">
                    <h6 class="fw-semibold mb-3"><i class="bi bi-plus-circle me-2"></i>Add New Task</h6>
                    <div class="mb-2">
                        <input type="text" name="task_title" class="form-control" placeholder="Task title" required>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="task_desc" class="form-control" placeholder="Description (optional)">
                    </div>
                    <button type="submit" name="add_task" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus me-1"></i>Add Task
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Submit Project -->
        <?php if ($project['status'] !== 'completed' && $project['status'] !== 'submitted'): ?>
        <div class="card border-0 shadow-sm rounded-4 border-primary">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-send me-2 text-primary"></i>Submit Project</h5>
                <form method="POST" onsubmit="return confirm('Submit this project? This action cannot be undone.')">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Submission Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Any notes for your instructor..."></textarea>
                    </div>
                    <button type="submit" name="submit_project" class="btn btn-primary">
                        <i class="bi bi-send me-2"></i>Submit Project
                    </button>
                </form>
            </div>
        </div>
        <?php elseif ($project['status'] === 'submitted'): ?>
        <div class="alert alert-info d-flex align-items-center">
            <i class="bi bi-send-check-fill fs-4 me-3"></i>
            <div>
                <strong>Project Submitted!</strong><br>
                Your project has been submitted and is awaiting review from the admin.
            </div>
        </div>
        <?php elseif ($project['status'] === 'completed'): ?>
        <div class="alert alert-success d-flex align-items-center">
            <i class="bi bi-trophy-fill fs-4 me-3"></i>
            <div>
                <strong>Project Completed!</strong><br>
                Congratulations! Your project has been marked as completed.
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Progress -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 text-center">
                <h5 class="fw-bold mb-3"><i class="bi bi-graph-up me-2 text-success"></i>Progress</h5>
                <!-- Circular Progress (CSS) -->
                <div class="position-relative d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width:120px; height:120px;">
                    <svg class="position-absolute" width="120" height="120" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="50" fill="none" stroke="#e9ecef" stroke-width="10"/>
                        <circle cx="60" cy="60" r="50" fill="none" stroke="#198754" stroke-width="10"
                                stroke-dasharray="314"
                                stroke-dashoffset="<?= 314 - (314 * $project['progress'] / 100) ?>"
                                transform="rotate(-90 60 60)"/>
                    </svg>
                    <span class="fs-3 fw-bold text-success"><?= $project['progress'] ?>%</span>
                </div>
                <div class="text-muted small">
                    <?= count(array_filter($taskList, fn($t) => $t['status'] === 'done')) ?> of <?= count($taskList) ?> tasks done
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Details</h5>
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Status</td>
                        <td><span class="badge bg-<?= $statusBadge ?>"><?= ucfirst(str_replace('_',' ',$project['status'])) ?></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Deadline</td>
                        <td><?= $project['deadline'] ? date('M d, Y', strtotime($project['deadline'])) : '—' ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Assigned</td>
                        <td><?= date('M d, Y', strtotime($project['created_at'])) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
