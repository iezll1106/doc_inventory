<?php
// ----------------------------
// 0. Include dependencies
// ----------------------------
require_once 'query.php';        // Fetch filtered/paginated rows
require_once 'pagination.php';   // Render pagination links
require_once 'search_form.php';  // Render search/filter form
require_once 'export.php';       // Handle CSV/Excel export

// ----------------------------
// 1. Fetch distinct offices for dropdown
// ----------------------------
$offices = $pdo->query("
    SELECT DISTINCT office 
    FROM documents 
    WHERE office IS NOT NULL AND office <> '' 
    ORDER BY office
")->fetchAll(PDO::FETCH_COLUMN);

// ----------------------------
// 2. Highlight function
// ----------------------------
function highlight($text, $search) {
    if (!$search) return htmlspecialchars($text);
    $search = preg_quote($search, '/');
    return preg_replace("/($search)/i", '<mark>$1</mark>', htmlspecialchars($text));
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Document Management System</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- ---------------------------- -->
<!-- 3. Header with Logos and Title -->
<!-- ---------------------------- -->
<div class="header-bar">
    <!-- Left logo -->
    <div class="header-left">
        <img src="images/logo_province.png" alt="Left Logo" class="logo-left">
    </div>

    <!-- Center title -->
    <div class="header-center">
        <h1>OFFICE OF THE PROVINCIAL ADMINISTRATOR</h1>
        <h2>Document Management System</h2>
    </div>

    <!-- Right logo + buttons -->
    <div class="header-right">
        <img src="images/bp_logo.png" alt="Right Logo" class="logo-right">
        <div class="actions">
            <a href="form.php" class="button">+ Add Document</a>
            <a href="export.php?export=csv&<?= http_build_query($_GET) ?>" class="button">Export as CSV</a>
            <a href="export.php?export=xlsx&<?= http_build_query($_GET) ?>" class="button">Export as Excel</a>
        </div>
    </div>
</div>

<!-- ---------------------------- -->
<!-- 4. Search/Filter Form -->
<!-- ---------------------------- -->
<?php renderSearchForm($q, $office, $from, $to, $offices); ?>

<!-- ---------------------------- -->
<!-- 5. Document Table -->
<!-- ---------------------------- -->
<table>
<thead>
<tr>
    <th>ID</th>
    <th>Title</th>
    <th>Subject</th>
    <th>Description</th> <!-- New column -->
    <th>Remarks</th>     <!-- New column -->
    <th>Office</th>
    <th>Date</th>
    <th>File</th>
    <th>Uploaded At</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
<?php if (!empty($rows)): ?>
    <?php foreach ($rows as $r): ?>
    <tr>
        <td data-label="ID"><?= $r['id'] ?></td>
        <td data-label="Title"><?= highlight($r['title'], $q) ?></td>
        <td data-label="Subject"><?= highlight($r['subject'], $q) ?></td>
        <td data-label="Description"><?= highlight($r['description'], $q) ?></td>
        <td data-label="Remarks"><?= highlight($r['remarks'] ?? '', $q) ?></td>
        <td data-label="Office"><?= highlight($r['office'], $q) ?></td>
        <td data-label="Date"><?= $r['doc_date'] ?></td>
        <td data-label="File">
            <?php if ($r['filename']): ?>
                <a href="uploads/<?= htmlspecialchars($r['filename']) ?>" target="_blank" title="View Document">
                    <i class="fas fa-eye"></i>
                </a>
            <?php else: ?>
                —
            <?php endif; ?>
        </td>
        <td data-label="Uploaded At"><?= $r['uploaded_at'] ?></td>
        <td data-label="Actions">
            <a href="form.php?id=<?= $r['id'] ?>" title="Edit" class="action-icon">
                <i class="fas fa-edit"></i>
            </a>
            <button class="delete-btn action-icon" 
                    data-id="<?= $r['id'] ?>" 
                    data-title="<?= htmlspecialchars($r['title']) ?>" 
                    title="Delete <?= htmlspecialchars($r['title']) ?>">
                <i class="fas fa-trash-alt" style="color:#dc3545;"></i>
            </button>
        </td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
<tr><td colspan="10">No documents found.</td></tr>
<?php endif; ?>
</tbody>
</table>

<!-- ---------------------------- -->
<!-- 6. Pagination -->
<!-- ---------------------------- -->
<?php renderPagination($total, $perPage, $page, [
    'q' => $q,
    'office' => $office,
    'from' => $from,
    'to' => $to
]); ?>

<!-- ---------------------------- -->
<!-- 7. Delete Modal -->
<!-- ---------------------------- -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Confirm Deletion</h3>
        <p id="modal-text"></p>
        <div class="modal-actions">
            <button id="confirm-delete">Delete</button>
            <button id="cancel-delete">Cancel</button>
        </div>
    </div>
</div>

<!-- ---------------------------- -->
<!-- 8. Modal JS Logic -->
<!-- ---------------------------- -->
<script>
const modal = document.getElementById('deleteModal');
const modalText = document.getElementById('modal-text');
let deleteId = null;

// Open modal
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        deleteId = btn.dataset.id;
        modalText.textContent = `Are you sure you want to delete "${btn.dataset.title}"?`;
        modal.style.display = 'flex';
    });
});

// Close modal
document.querySelector('.close').addEventListener('click', () => modal.style.display = 'none');
document.getElementById('cancel-delete').addEventListener('click', () => modal.style.display = 'none');

// Confirm delete
document.getElementById('confirm-delete').addEventListener('click', () => {
    if (deleteId) window.location.href = `delete.php?id=${deleteId}`;
});
</script>

</body>
</html>