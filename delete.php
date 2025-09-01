<?php
require_once 'config.php';
require_once 'file_helper.php'; // use the helper

$id = $_GET['id'] ?? null;

$message = '';

if ($id) {
    // Fetch document to get filename
    $stmtSelect = $pdo->prepare("SELECT filename FROM documents WHERE id = :id");
    $stmtSelect->bindValue(':id', $id, PDO::PARAM_INT);
    $stmtSelect->execute();
    $doc = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if ($doc) {
        // Delete the uploaded file if exists
        if ($doc['filename']) {
            deleteFile($doc['filename']);
        }

        // Delete database record
        $stmtDelete = $pdo->prepare("DELETE FROM documents WHERE id = :id");
        $stmtDelete->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtDelete->execute();

        $message = "Document deleted successfully.";
    } else {
        $message = "Document not found.";
    }
} else {
    $message = "Invalid document ID.";
}

// Redirect back to index.php with alert
echo "<script>
    alert('". addslashes($message) ."');
    window.location.href = 'index.php';
</script>";
exit;