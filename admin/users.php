<?php
// admin/users.php - Manage Users
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
requireAdmin();

// Handle delete user
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    if ($delId !== currentUserId()) {
        $conn->query("DELETE FROM users WHERE id = {$delId} AND role = 'user'");
        setFlash('success', 'User deleted.');
    } else {
        setFlash('error', 'You cannot delete your own account.');
    }
    header("Location: " . BASE_URL . "/admin/users.php");
    exit();
}

$users = $conn->query("
    SELECT u.*, COUNT(p.id) AS project_count
    FROM users u
    LEFT JOIN projects p ON p.assigned_to = u.id
    WHERE u.role = 'user'
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0"><i class="bi bi-people me-2 text-primary"></i>Registered Users</h2>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Projects Assigned</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while ($u = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                     style="width:36px;height:36px;">
                                    <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                </div>
                                <span class="fw-semibold"><?= htmlspecialchars($u['name']) ?></span>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <span class="badge bg-primary rounded-pill"><?= $u['project_count'] ?></span>
                        </td>
                        <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/admin/users.php?delete=<?= $u['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Delete this user? Their projects will become unassigned.')">
                                <i class="bi bi-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($users->num_rows === 0): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-people display-6 d-block mb-2"></i>No users registered yet.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
