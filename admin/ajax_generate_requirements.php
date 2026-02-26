<?php
// admin/ajax_generate_requirements.php - AJAX endpoint for AI requirement generation
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/gemini.php';

header('Content-Type: application/json');

// Validate input
$title = trim($_POST['title'] ?? '');

if (empty($title)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Project title is required']);
    exit;
}

if (strlen($title) < 3) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Project title must be at least 3 characters']);
    exit;
}

// Mock logic for generating requirements based on the project title
function generateProjectRequirements($title) {
    // Example logic: Generate requirements based on keywords in the title
    $requirements = [];

    if (stripos($title, 'website') !== false) {
        $requirements[] = 'Design the homepage';
        $requirements[] = 'Develop the backend';
        $requirements[] = 'Implement responsive design';
    }

    if (stripos($title, 'app') !== false) {
        $requirements[] = 'Create user authentication';
        $requirements[] = 'Develop API endpoints';
        $requirements[] = 'Test on multiple devices';
    }

    if (empty($requirements)) {
        $requirements[] = 'Define project scope';
        $requirements[] = 'Gather requirements from stakeholders';
        $requirements[] = 'Create a project timeline';
    }

    return ['requirements' => $requirements];
}

// Call Gemini API
$result = generateProjectRequirements($title);

// Check if there was an error
if (isset($result['error'])) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $result['error']]);
    exit;
}

// Return successful response
http_response_code(200);
echo json_encode([
    'success' => true,
    'requirements' => $result['requirements'] ?? ''
]);
