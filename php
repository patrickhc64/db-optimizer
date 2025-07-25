<?php
// index.php
$conn = new mysqli("localhost", "user", "pass", "sample_db");

// Get slow queries
$queries = $conn->query("SELECT * FROM performance_log")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<body>
  <h1>Query Optimizer</h1>
  
  <?php foreach ($queries as $q): ?>
  <div class="query">
    <p><?= substr($q['query'], 0, 50) ?>...</p>
    <button onclick="optimize(<?= $q['id'] ?>)">Optimize</button>
    <div id="suggestion-<?= $q['id'] ?>"></div>
  </div>
  <?php endforeach; ?>

  <script>
  function optimize(id) {
    fetch(`optimize.php?id=${id}`)
      .then(res => res.json())
      .then(data => {
        document.getElementById(`suggestion-${id}`).innerHTML = 
          `<p>Suggested index: ${data.index}</p>`;
      });
  }
  </script>
</body>
</html>
