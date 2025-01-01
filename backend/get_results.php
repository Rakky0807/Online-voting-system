<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'db.php';

try {
    $conn = getDB(); // Function that establishes a database connection

    // SQL query to count votes directly from the votes table
    $sql = "SELECT candidate_id AS id, COUNT(*) AS vote_count, candidate_name AS name 
            FROM votes 
            GROUP BY candidate_id,candidate_name";
            
    $result = $conn->query($sql);

    $candidates = array();
    $colors = ['#4CAF50', '#2196F3', '#9C27B0']; // Colors for each candidate
    $i = 0;

    while ($row = $result->fetch_assoc()) {
        $candidates[] = array(
            'id' => $row['id'], // Candidate ID
            'name' => $row['name'], // Candidate name
            'votes' => (int)$row['vote_count'], // Total votes
            'color' => $colors[$i % count($colors)] // Color assignment
        );
        $i++;
    }

    echo json_encode([
        'success' => true,
        'results' => $candidates
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching results: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
