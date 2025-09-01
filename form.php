<?php
// ----------------------------
// 1. Include dependencies
// ----------------------------
require_once 'config.php'; // DB connection

// ----------------------------
// 2. Helper function: sanitize input
// ----------------------------
function safeInput($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

// ----------------------------
// 3. Initialize variables
// ----------------------------
$id          = $_GET['id'] ?? null;
$title       = '';
$subject     = '';
$office      = '';
$date        = '';
$description = '';
$remarks     = '';
$filename    = '';
$message     = '';

// ----------------------------
// 4. If editing an existing record, fetch it
// ----------------------------
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($doc) {
        $title       = $doc['title'];
        $subject     = $doc['subject'];
        $description = $doc['description'];
        $remarks     = $doc['remarks'] ?? '';
        $office      = $doc['office'];
        $date        = $doc['doc_date'];
        $filename    = $doc['filename'];
    }
}

// ----------------------------
// 5. Handle form submission (Add or Update)
// ----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = safeInput($_POST['title'] ?? '');
    $subject     = safeInput($_POST['subject'] ?? '');
    $description = safeInput($_POST['description'] ?? '');
    $remarks     = safeInput($_POST['remarks'] ?? '');
    $office      = safeInput($_POST['office'] ?? '');
    $date        = safeInput($_POST['doc_date'] ?? '');

    // File upload handling
    if (!empty($_FILES['filename']['name'])) {
        $uploadDir  = "uploads/";
        $filename   = basename($_FILES['filename']['name']);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['filename']['tmp_name'], $targetPath)) {
            $message = "File uploaded successfully.";
        } else {
            $message = "File upload failed.";
        }
    }

    // If editing, update the record
    if ($id) {
        $stmt = $pdo->prepare("UPDATE documents 
                               SET title = :title, subject = :subject, description = :description,
                                   remarks = :remarks, office = :office, doc_date = :doc_date, filename = :filename
                               WHERE id = :id");
        $stmt->execute([
            ':title'       => $title,
            ':subject'     => $subject,
            ':description' => $description,
            ':remarks'     => $remarks,
            ':office'      => $office,
            ':doc_date'    => $date,
            ':filename'    => $filename,
            ':id'          => $id
        ]);
        $message = "Document updated successfully!";
    } 
    // If adding new
    else {
        $stmt = $pdo->prepare("INSERT INTO documents (title, subject, description, remarks, office, doc_date, filename) 
                               VALUES (:title, :subject, :description, :remarks, :office, :doc_date, :filename)");
        $stmt->execute([
            ':title'       => $title,
            ':subject'     => $subject,
            ':description' => $description,
            ':remarks'     => $remarks,
            ':office'      => $office,
            ':doc_date'    => $date,
            ':filename'    => $filename
        ]);
        $message = "Document added successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Edit Document' : 'Add Document' ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- ----------------------------
         6. Form layout
    ---------------------------- -->
    <div class="form-container">
        <h2><?= $id ? 'Edit Document' : 'Add Document' ?></h2>

        <?php if ($message): ?>
            <p class="message"><?= $message ?></p>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label>Title:</label>
            <input type="text" name="title" value="<?= htmlspecialchars($title) ?>" required>

            <label>Subject:</label>
            <input type="text" name="subject" value="<?= htmlspecialchars($subject) ?>" required>
            
            <label>Description:</label>
            <input type="text" name="description" value="<?= htmlspecialchars($description) ?>" required>

            <label>Remarks:</label>
            <input type="text" name="remarks" value="<?= htmlspecialchars($remarks) ?>">

            <label>Office:</label>
            <input type="text" name="office" value="<?= htmlspecialchars($office) ?>" required>

            <label>Date:</label>
            <input type="date" name="doc_date" value="<?= htmlspecialchars($date) ?>" required>

            <label>File:</label>
            <?php if ($filename): ?>
                <p>Current: <a href="uploads/<?= htmlspecialchars($filename) ?>" target="_blank"><?= htmlspecialchars($filename) ?></a></p>
            <?php endif; ?>
            <input type="file" name="filename">

            <button type="submit"><?= $id ? 'Update' : 'Add' ?> Document</button>
            <a href="index.php" class="button">Back</a>
        </form>
    </div>
</body>
</html>