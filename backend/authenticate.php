<?php
// Allow CORS from specific origins or all origins
header("Access-Control-Allow-Origin: *"); // Replace '*' with a specific domain, e.g., "http://example.com"

    // Allow specific HTTP methods
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    
    // Allow specific headers
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

session_start();

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$username = $data['username'] ?? null;
$password = $data['password'] ?? null;

require_once 'db.php';
$conn = getDB();

// Validate user
$sql = "SELECT * FROM users WHERE username = ? AND password = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Authentication successful
    $_SESSION['username'] = $username;
    echo json_encode(['success' => true, 'message' => 'Login successful']);
} else {
    // Authentication failed
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
}

$stmt->close();
$conn->close();
?>
