<?php
// admin/edit_project.php - Edit Project
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header("Location: " . BASE_URL . "/admin/projects.php"); exit(); }

// Get project
$stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project) { setFlash('error', 'Project not found.'); header("Location: " . BASE_URL . "/admin/projects.php"); exit(); }

// Get users for dropdown
$users = $conn->query("SELECT id, name, email FROM users WHERE role='user' ORDER BY name");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title'] ?? '');

    $requirements = trim($_POST['requirements'] ?? '');
    $assigned_to  = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
    $status       = $_POST['status'] ?? 'pending';
    $progress     = (int)($_POST['progress'] ?? 0);
    $deadline     = $_POST['deadline'] ?: null;

    if (empty($title)) {
        $error = "Title is required.";
    } else {
        $stmt = $conn->prepare("UPDATE projects SET title=?, requirements=?, assigned_to=?, status=?, progress=?, deadline=? WHERE id=?");
        $stmt->bind_param("ssisiis", $title, $requirements, $assigned_to, $status, $progress, $deadline, $id);

        if ($stmt->execute()) {
            $stmt->close();
            setFlash('success', 'Project updated successfully!');
            header("Location: " . BASE_URL . "/admin/view_project.php?id={$id}");
            exit();
        } else {
            $error = "Update failed: " . $conn->error;
            $stmt->close();
        }
    }

    // Keep POST data on error
    $project = array_merge($project, $_POST);
}
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= BASE_URL ?>/admin/view_project.php?id=<?= $id ?>" class="btn btn-outline-secondary me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h2 class="fw-bold mb-0"><i class="bi bi-pencil me-2 text-warning"></i>Edit Project</h2>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <form method="POST" action="">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Project Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control"
                               value="<?= htmlspecialchars($project['title']) ?>" required>
                    </div>
                    <div class="mb-3">

                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Requirements</label>
                        <textarea name="requirements" class="form-control font-monospace" rows="12"><?= htmlspecialchars($project['requirements'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning px-4">
                    <i class="bi bi-save me-2"></i>Save Changes
                </button>
                <a href="<?= BASE_URL ?>/admin/view_project.php?id=<?= $id ?>" class="btn btn-outline-secondary px-4">Cancel</a>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <form method="POST">
            <!-- Include all main fields as hidden so form submits correctly -->
            <input type="hidden" name="title" value="<?= htmlspecialchars($project['title']) ?>">
            <input type="hidden" name="description" value="<?= htmlspecialchars($project['description'] ?? '') ?>">
            <input type="hidden" name="requirements" value="<?= htmlspecialchars($project['requirements'] ?? '') ?>">

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-gear me-2"></i>Settings</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Assign To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">-- Unassigned --</option>
                            <?php
                            // Reset users result
                            $users->data_seek(0);
                            while ($u = $users->fetch_assoc()):
                            ?>
                                <option value="<?= $u['id'] ?>" <?= $project['assigned_to'] == $u['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach (['pending','in_progress','submitted','completed'] as $s): ?>
                                <option value="<?= $s ?>" <?= $project['status'] === $s ? 'selected' : '' ?>>
                                    <?= ucfirst(str_replace('_',' ',$s)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Progress (%)</label>
                        <input type="number" name="progress" class="form-control" 
                               min="0" max="100" value="<?= $project['progress'] ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deadline</label>
                        <input type="date" name="deadline" class="form-control"
                               value="<?= htmlspecialchars($project['deadline'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="bi bi-save me-1"></i>Save All Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
