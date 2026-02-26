<?php
// admin/delete_project.php - Delete Project
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        setFlash('success', 'Project deleted successfully.');
    } else {
        setFlash('error', 'Failed to delete project.');
    }
    $stmt->close();
}

header("Location: " . BASE_URL . "/admin/projects.php");
exit();
?>
