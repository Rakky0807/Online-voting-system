<?php
// Set proper content type for JSON response
header('Content-Type: application/json');

// Allow CORS from specific origins
header("Access-Control-Allow-Origin: *"); // In production, replace '*' with your domain
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

session_start();

// Ensure request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get and validate input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

// Validate required fields
if (!isset($data['username']) || !isset($data['candidate'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$candidate_id = filter_var($data['candidate'], FILTER_VALIDATE_INT);
$username = filter_var($data['username'], FILTER_SANITIZE_STRING);
$candidate_name = filter_var($data['candidate_name'], FILTER_SANITIZE_STRING);

if (!$candidate_id || !$username || !$candidate_name ) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

try {
    require_once 'db.php';
    $conn = getDB();

    // Check if user has already voted
    $check_sql = "SELECT COUNT(*) FROM votes WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_stmt->bind_result($vote_count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($vote_count > 0) {
        echo json_encode(['success' => false, 'message' => 'You have already voted']);
        exit;
    }

    // Insert vote
    $sql = "INSERT INTO votes (username, candidate_id,candidate_name) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sis", $username, $candidate_id, $candidate_name);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Vote registered successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to register vote']);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>