<?php
// admin/create_project.php - Create Project with AI Requirements
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/gemini.php';
requireAdmin();

// Get all users for assignment dropdown
$users = $conn->query("SELECT id, name, email FROM users WHERE role = 'user' ORDER BY name");

$error = '';
$generatedRequirements = '';

// Step 1: Generate requirements via AJAX or form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Action: Save project
    if ($action === 'save') {
        $title        = trim($_POST['title'] ?? '');
        $requirements = trim($_POST['requirements'] ?? '');
        $assigned_to  = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
        $deadline     = $_POST['deadline'] ?? null;
        $createdBy    = currentUserId();

        if (empty($title)) {
            $error = "Project title is required.";
        } else {
            $stmt = $conn->prepare("INSERT INTO projects (title, requirements, assigned_to, created_by, deadline) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiss", $title, $requirements, $assigned_to, $createdBy, $deadline);

            if ($stmt->execute()) {
                $projectId = $stmt->insert_id;
                $stmt->close();
                setFlash('success', "Project '{$title}' created successfully!");
                header("Location: " . BASE_URL . "/admin/view_project.php?id={$projectId}");
                exit();
            } else {
                $error = "Failed to save project: " . $conn->error;
                $stmt->close();
            }
        }
    }
}
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= BASE_URL ?>/admin/projects.php" class="btn btn-outline-secondary me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h2 class="fw-bold mb-0"><i class="bi bi-plus-circle me-2 text-primary"></i>Create New Project</h2>
        <p class="text-muted mb-0">AI will auto-generate project requirements</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <form method="POST" action="" id="projectForm">
            <input type="hidden" name="action" value="save">

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Project Info</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Project Title <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="title" id="projectTitle" class="form-control" 
                                   placeholder="e.g. Online Library Management System"
                                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                            <button type="button" class="btn btn-outline-primary" id="generateBtn">
                                <i class="bi bi-stars me-1"></i>Generate with AI
                            </button>
                        </div>
                        <div class="form-text">Enter a title and click "Generate with AI" to auto-generate requirements.</div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-stars me-2 text-warning"></i>AI-Generated Requirements</h5>
                    <span class="badge bg-warning text-dark">Powered by Gemini</span>
                </div>
                <div class="card-body px-4 pb-4">
                    <div id="aiLoader" class="text-center py-4 d-none">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Generating requirements with Gemini AI...</p>
                    </div>

                    <textarea name="requirements" id="requirementsField" class="form-control font-monospace" 
                              rows="12" placeholder="Requirements will appear here after AI generation, or you can type them manually..."><?= htmlspecialchars($_POST['requirements'] ?? '') ?></textarea>
                    <div class="form-text">You can edit the AI-generated requirements before saving.</div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save me-2"></i>Save Project
                </button>
                <a href="<?= BASE_URL ?>/admin/projects.php" class="btn btn-outline-secondary px-4">Cancel</a>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0"><i class="bi bi-gear me-2 text-secondary"></i>Settings</h5>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Assign To</label>
                    <select name="assigned_to" form="projectForm" class="form-select">
                        <option value="">-- Unassigned --</option>
                        <?php while ($u = $users->fetch_assoc()): ?>
                            <option value="<?= $u['id'] ?>" 
                                <?= (isset($_POST['assigned_to']) && $_POST['assigned_to'] == $u['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email']) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Deadline</label>
                    <input type="date" name="deadline" form="projectForm" class="form-control"
                           value="<?= htmlspecialchars($_POST['deadline'] ?? '') ?>"
                           min="<?= date('Y-m-d') ?>">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('generateBtn').addEventListener('click', function () {
    const btn = this;
    const title = document.getElementById('projectTitle').value.trim();
    const textarea = document.getElementById('requirementsField');
    const loader = document.getElementById('aiLoader');

    if (!title) {
        alert('Please enter project title first.');
        return;
    }

    btn.innerText = 'Generating...';
    btn.disabled = true;
    loader.classList.remove('d-none');
    textarea.style.opacity = '0.5';

    fetch('ajax_generate_requirements.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'title=' + encodeURIComponent(title)
    })
    .then(async res => {
        const contentType = res.headers.get('content-type');
        let data;
        
        try {
            data = await res.json();
        } catch (e) {
            throw new Error('Invalid JSON response from server');
        }

        // Check HTTP status and response structure
        if (!res.ok || !data.success) {
            throw new Error(data.error || 'Failed to generate requirements');
        }

        return data;
    })
    .then(data => {
        if (data.requirements && data.requirements.trim()) {
            textarea.value = data.requirements;
            // Auto-focus for better UX
            textarea.focus();
        } else {
            throw new Error('No requirements returned from API');
        }
    })
    .catch(err => {
        const errorMsg = err.message || 'Failed to fetch AI requirements. Check console for details.';
        console.error('Gemini API Error:', err);
        alert('❌ Error: ' + errorMsg);
        textarea.value = ''; // Clear any partial data
    })
    .finally(() => {
        btn.innerText = 'Generate with AI';
        btn.disabled = false;
        loader.classList.add('d-none');
        textarea.style.opacity = '1';
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
