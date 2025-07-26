<?php
// Include database configuration
require_once('db.php');

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch queries
$queries = [];
$result = $conn->query("SELECT id, query_text, execution_time FROM performance_log");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $queries[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MySQL Query Optimizer</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-database"></i> Query Performance Dashboard</h1>
            <p class="subtitle">Analyze and optimize MySQL query performance</p>
        </header>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <i class="fas fa-list"></i>
                <span><?= count($queries) ?></span>
                <p>Queries</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-bolt"></i>
                <span><?= array_sum(array_column($queries, 'execution_time')) ?>ms</span>
                <p>Total Time</p>
            </div>
        </div>
        
        <div class="query-list">
            <?php if (count($queries) > 0): ?>
                <?php foreach ($queries as $query): ?>
                <div class="query-item" data-id="<?= $query['id'] ?>">
                    <div class="query-header">
                        <span class="query-id">#<?= $query['id'] ?></span>
                        <span class="execution-time">
                            <i class="fas fa-clock"></i> <?= $query['execution_time'] ?>ms
                        </span>
                    </div>
                    
                    <div class="query-text">
                        <pre><?= htmlspecialchars($query['query_text']) ?></pre>
                    </div>
                    
                    <div class="query-actions">
                        <button class="optimize-btn">
                            <i class="fas fa-magic"></i> Optimize
                        </button>
                        <button class="explain-btn">
                            <i class="fas fa-search"></i> Explain
                        </button>
                    </div>
                    
                    <div class="suggestion-container">
                        <div class="loader">
                            <div class="spinner"></div>
                            <span>Analyzing query...</span>
                        </div>
                        <div class="suggestion"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No queries found</h3>
                    <p>Add some queries to the database to get started</p>
                </div>
            <?php endif; ?>
        </div>
        
        <footer>
            <p>Powered by Docker, PHP, and MySQL | Container ID: <?= gethostname() ?></p>
        </footer>
    </div>

    <script>
    $(document).ready(function() {
        // Optimize button handler
        $('.optimize-btn').click(function() {
            const btn = $(this);
            const queryItem = btn.closest('.query-item');
            const id = queryItem.data('id');
            const suggestionBox = queryItem.find('.suggestion');
            const loader = queryItem.find('.loader');
            
            // Disable button during request
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Analyzing');
            
            // Show loader
            loader.show();
            suggestionBox.empty();
            
            $.get('optimize.php?id=' + id)
                .done(function(data) {
                    suggestionBox.html(`
                        <div class="suggestion-card">
                            <div class="suggestion-header">
                                <i class="fas fa-lightbulb"></i>
                                <strong>Optimization Suggestion</strong>
                            </div>
                            <div class="suggestion-content">
                                <p>${data.suggestion}</p>
                                <div class="suggestion-meta">
                                    <span>Query ID: ${data.query_id}</span>
                                    <span>Severity: ${data.severity}</span>
                                </div>
                            </div>
                        </div>
                    `);
                })
                .fail(function() {
                    suggestionBox.html(`
                        <div class="error-card">
                            <i class="fas fa-exclamation-triangle"></i>
                            Failed to analyze query. Please try again.
                        </div>
                    `);
                })
                .always(function() {
                    loader.hide();
                    btn.prop('disabled', false).html('<i class="fas fa-magic"></i> Optimize');
                });
        });
        
        // Explain button handler
        $('.explain-btn').click(function() {
            alert('EXPLAIN feature would show query execution plan');
        });
    });
    </script>
</body>
</html>