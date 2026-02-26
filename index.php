<?php
// index.php - Landing page redirect
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/session.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: " . BASE_URL . "/admin/dashboard.php");
    } else {
        header("Location: " . BASE_URL . "/user/dashboard.php");
    }
    exit();
}
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<!-- Hero Section -->
<div class="py-5 text-center">
    <div class="py-3">
        <i class="bi bi-kanban-fill display-1 text-primary"></i>
        <h1 class="display-5 fw-bold mt-3">Project Management System</h1>
        <p class="lead text-muted col-md-6 mx-auto">
            Manage university projects with ease. AI-powered requirements generation, 
            task tracking, and real-time progress monitoring.
        </p>
        <div class="d-flex gap-3 justify-content-center mt-4 flex-wrap">
            <a href="<?= BASE_URL ?>/auth/admin_login.php" class="btn btn-danger btn-lg px-5">
                <i class="bi bi-shield-lock me-2"></i>Admin Login
            </a>
            <a href="<?= BASE_URL ?>/auth/user_login.php" class="btn btn-primary btn-lg px-5">
                <i class="bi bi-person me-2"></i>User Login
            </a>
            <a href="<?= BASE_URL ?>/auth/signup.php" class="btn btn-outline-success btn-lg px-5">
                <i class="bi bi-person-plus me-2"></i>Sign Up
            </a>
        </div>
    </div>
</div>

<hr class="my-5">

<!-- Features -->
<div class="row g-4 py-3 text-center">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
            <i class="bi bi-stars display-4 text-warning mb-3"></i>
            <h5 class="fw-bold">AI-Powered Requirements</h5>
            <p class="text-muted">Auto-generate detailed project requirements from just a title using Google Gemini AI.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
            <i class="bi bi-list-check display-4 text-primary mb-3"></i>
            <h5 class="fw-bold">Task Management</h5>
            <p class="text-muted">Break down projects into tasks, track completion, and auto-calculate progress.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
            <i class="bi bi-graph-up display-4 text-success mb-3"></i>
            <h5 class="fw-bold">Progress Monitoring</h5>
            <p class="text-muted">Admin can monitor all projects, review submissions, and mark projects as completed.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
