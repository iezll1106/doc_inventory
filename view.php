<?php
include 'db.php';

// Build filters
$where = [];
$params = [];

if (!empty($_GET['office'])) {
    $where[] = "office = ?";
    $params[] = $_GET['office'];
}

if (!empty($_GET['date_from']) && !empty($_GET['date_to'])) {
    $where[] = "doc_date BETWEEN ? AND ?";
    $params[] = $_GET['date_from'];
    $params[] = $_GET['date_to'];
}

if (!empty($_GET['keyword'])) {
    $where[] = "(title LIKE ? OR subject LIKE ? OR description LIKE ?)";
    $kw = "%" . $_GET['keyword'] . "%";
    $params[] = $kw;
    $params[] = $kw;
    $params[] = $kw;
}

$sql = "SELECT * FROM documents";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$stmt = $conn->prepare($sql);
if ($params) {
    // bind params dynamically
    $types = str_repeat("s", count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);

// Keep query string for export links
$query = http_build_query($_GET);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Document Inventory</title>
    <style>
        .export-buttons { margin: 15px 0; }
        .export-buttons a {
            display: inline-block;
            padding: 8px 14px;
            margin-right: 10px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        .export-buttons a:hover { background: #0056b3; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f2f2f2; }
        form.filter-form { margin-bottom: 15px; }
    </style>
</head>
<body>

<h2>📂 Document Inventory</h2>

<!-- Filter Form -->
<form method="get" class="filter-form">
    <label>Office:
        <input type="text" name="office" value="<?= htmlspecialchars($_GET['office'] ?? '') ?>">
    </label>
    <label>Date From:
        <input type="date" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
    </label>
    <label>Date To:
        <input type="date" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
    </label>
    <label>Keyword:
        <input type="text" name="keyword" value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
    </label>
    <button type="submit">🔍 Filter</button>
    <a href="view.php">Reset</a>
</form>

<!-- Export Buttons -->
<div class="export-buttons">
    <a href="export.php?type=csv&<?= $query ?>">⬇ Export to CSV</a>
    <a href="export.php?type=excel&<?= $query ?>">⬇ Export to Excel</a>
</div>

<!-- Table of Documents -->
<table>
    <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Subject</th>
        <th>Office</th>
        <th>Date</th>
        <th>Description</th>
        <th>File</th>
        <th>Uploaded At</th>
    </tr>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['id']) ?></td>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= htmlspecialchars($r['subject']) ?></td>
            <td><?= htmlspecialchars($r['office']) ?></td>
            <td><?= htmlspecialchars($r['doc_date']) ?></td>
            <td><?= htmlspecialchars($r['description']) ?></td>
            <td><?= htmlspecialchars($r['filename']) ?></td>
            <td><?= htmlspecialchars($r['uploaded_at']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>