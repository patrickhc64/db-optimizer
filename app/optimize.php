<?php
require_once('db.php');

// Connect to database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Validate and sanitize input
$id = (int)($_GET['id'] ?? 0);
$suggestion = "";
$severity = "medium";

if ($id > 0) {
    // Get query text
    $stmt = $conn->prepare("SELECT query_text FROM performance_log WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $query = $row['query_text'];
        
        // Analyze query
        if (stripos($query, "SELECT *") !== false) {
            $suggestion = "Avoid SELECT * - specify columns explicitly to reduce data transfer";
            $severity = "high";
        } 
        elseif (preg_match("/\bIN\s*\(SELECT/i", $query)) {
            $suggestion = "Convert subquery to JOIN for better performance (correlated subqueries can be slow)";
            $severity = "high";
        }
        elseif (stripos($query, "COUNT(*)") !== false) {
            $suggestion = "For large tables, consider using approximate COUNT methods or caching strategies";
            $severity = "medium";
        }
        elseif (stripos($query, "LIKE '%") !== false) {
            $suggestion = "Leading wildcards in LIKE queries prevent index usage - consider full-text search alternatives";
            $severity = "high";
        }
        elseif (stripos($query, "SELECT id, name") !== false) {
            $suggestion = "Consider adding an index on the 'status' column to optimize this query";
            $severity = "medium";
        }
    }
    $stmt->close();
}

// Fallback to mock suggestions
if (empty($suggestion)) {
    $mock_suggestions = [
        "Add index on frequently filtered columns (especially in WHERE clauses)" => "high",
        "Use WHERE instead of HAVING for pre-aggregation filtering" => "medium",
        "Partition large tables by date ranges or key values" => "medium",
        "Enable query caching for repeated identical queries" => "low",
        "Consider denormalization for frequently joined tables" => "medium"
    ];
    
    $random_index = $id % count($mock_suggestions);
    $suggestion = array_keys($mock_suggestions)[$random_index];
    $severity = array_values($mock_suggestions)[$random_index];
}

$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'suggestion' => $suggestion,
    'query_id' => $id,
    'severity' => $severity
]);
?>