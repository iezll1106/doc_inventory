<?php
// ----------------------------
// 0. Include database connection
// ----------------------------
require_once 'config.php';

// ----------------------------
// 1. Pagination Setup
// ----------------------------
$perPage = 10;                               // Items per page
$page    = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset  = ($page - 1) * $perPage;

// ----------------------------
// 2. Filter Setup
// ----------------------------
$q      = $_GET['q'] ?? '';
$office = $_GET['office'] ?? '';
$from   = $_GET['from'] ?? '';
$to     = $_GET['to'] ?? '';

$where  = [];
$params = [];

// Search by text
if ($q) {
    $where[] = "(title LIKE :q OR subject LIKE :q OR description LIKE :q OR remarks LIKE :q)";
    $params[':q'] = "%$q%";
}

// Exact office filter (from dropdown)
if ($office) {
    $where[] = "office = :office";
    $params[':office'] = $office;
}

// Date filters
if ($from) {
    $where[] = "doc_date >= :from";
    $params[':from'] = $from;
}
if ($to) {
    $where[] = "doc_date <= :to";
    $params[':to'] = $to;
}

// Build WHERE clause
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ----------------------------
// 3. Count Total Rows
// ----------------------------
$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM documents $where_sql");
foreach ($params as $k => $v) {
    $stmtTotal->bindValue($k, $v);
}
$stmtTotal->execute();
$total = (int)$stmtTotal->fetchColumn();

// ----------------------------
// 4. Fetch Paginated Rows
// ----------------------------
$stmt = $pdo->prepare("SELECT * FROM documents $where_sql ORDER BY uploaded_at DESC LIMIT :lim OFFSET :off");
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ----------------------------
// 5. Export Filtered Results
// ----------------------------
if (isset($_GET['export'])) {
    $stmtExport = $pdo->prepare("SELECT * FROM documents $where_sql ORDER BY uploaded_at DESC");
    foreach ($params as $k => $v) {
        $stmtExport->bindValue($k, $v);
    }
    $stmtExport->execute();
    $allRows = $stmtExport->fetchAll(PDO::FETCH_ASSOC);

    if ($_GET['export'] === 'csv') {
        exportCSV($allRows);
    } elseif ($_GET['export'] === 'xlsx') {
        exportExcel($allRows);
    }
}