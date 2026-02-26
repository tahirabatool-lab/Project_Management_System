<?php
// auth/user_login.php - Styled like Signup Page
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: " . BASE_URL . "/auth/logout.php");
    } else {
        header("Location: " . BASE_URL . "/user/dashboard.php");
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? AND role = 'user'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            setFlash('success', "Welcome back, " . $user['name'] . "!");
            header("Location: " . BASE_URL . "/user/dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="row justify-content-center my-5">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-person-circle display-4 text-primary"></i>
                    <h3 class="fw-bold mt-2">User Login</h3>
                    <p class="text-muted small">Access your projects and tasks</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-lg" 
                               placeholder="your.email@example.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        <div class="form-text">Enter your registered email</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control form-control-lg" 
                               placeholder="Enter your password" required>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2 fw-semibold btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i>User Login
                    </button>
                </form>

                <hr class="my-4">
                <p class="text-center text-muted small mb-2">Don't have an account?</p>
                <a href="<?= BASE_URL ?>/auth/signup.php" class="btn btn-outline-success w-100 mb-2">
                    <i class="bi bi-person-plus me-1"></i>Create Account
                </a>

                <div class="text-center mt-3">
                    <a href="<?= BASE_URL ?>/auth/admin_login.php" class="text-muted text-decoration-none small">
                        <i class="bi bi-shield-lock me-1"></i>Admin Login
                    </a>
                </div>

                <div class="text-center mt-2">
                    <a href="<?= BASE_URL ?>/auth/login.php" class="text-muted text-decoration-none small">
                        <i class="bi bi-arrow-left me-1"></i>Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>