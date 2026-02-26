<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: " . BASE_URL . (isAdmin() ? "/admin/dashboard.php" : "/user/dashboard.php"));
    exit();
}
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<!-- Full screen center aligned -->
<div class="d-flex align-items-center justify-content-center min-vh-100 bg-light">
    <div class="container py-0">
        <!-- Hero Section -->
        <div class="text-center mb-4">
            <i class="bi bi-person-check display-3 text-primary mb-3"></i>
            <h1 class="h3 fw-bold mb-1">Choose Your Login</h1>
            <p class="text-muted mb-4">Select your account type to continue</p>
        </div>

        <!-- Login Cards -->
        <div class="row justify-content-center g-4">
            <!-- Admin Card -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-4 h-100 login-card">
                    <div class="card-body p-5 text-center">
                        <div class="bg-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-shield-lock text-white display-4"></i>
                        </div>
                        <h4 class="fw-bold mt-3">Admin Portal</h4>
                        <p class="text-muted">Manage projects, users, and system settings</p>
                        <ul class="text-start text-muted small mb-4">
                            <li><i class="bi bi-check-circle text-danger me-2"></i>Create & manage projects</li>
                            <li><i class="bi bi-check-circle text-danger me-2"></i>Assign tasks to users</li>
                            <li><i class="bi bi-check-circle text-danger me-2"></i>Monitor progress</li>
                            <li><i class="bi bi-check-circle text-danger me-2"></i>User management</li>
                        </ul>
                        <a href="<?= BASE_URL ?>/auth/admin_login.php" class="btn btn-danger w-100 py-2 fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Admin Login
                        </a>
                    </div>
                </div>
            </div>

            <!-- User Card -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-4 h-100 login-card">
                    <div class="card-body p-5 text-center">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-person-circle text-white display-4"></i>
                        </div>
                        <h4 class="fw-bold mt-3">User Account</h4>
                        <p class="text-muted">Access your projects and collaborate</p>
                        <ul class="text-start text-muted small mb-4">
                            <li><i class="bi bi-check-circle text-primary me-2"></i>View assigned projects</li>
                            <li><i class="bi bi-check-circle text-primary me-2"></i>Track project status</li>
                            <li><i class="bi bi-check-circle text-primary me-2"></i>Update task progress</li>
                            <li><i class="bi bi-check-circle text-primary me-2"></i>Manage your work</li>
                        </ul>
                        <a href="<?= BASE_URL ?>/auth/user_login.php" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-2"></i>User Login
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sign up link -->
        <div class="mt-4 text-center">
            <p class="text-muted mb-0">Don't have an account? 
                <a href="<?= BASE_URL ?>/auth/signup.php" class="text-primary fw-semibold">Sign up here</a>
            </p>
        </div>
    </div>
</div>

<style>
    .login-card {
        transition: all 0.3s ease;
    }
    .login-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,0.15) !important;
    }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>