<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'query.php';

if (isset($_GET['export'])) {
    $stmtExport = $pdo->prepare("SELECT * FROM documents $where_sql ORDER BY uploaded_at DESC");
    foreach ($params as $k => $v) $stmtExport->bindValue($k, $v);
    $stmtExport->execute();
    $allRows = $stmtExport->fetchAll(PDO::FETCH_ASSOC);

    if ($_GET['export'] == 'csv') exportCSV($allRows);
    if ($_GET['export'] == 'xlsx') exportExcel($allRows);
}