<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/auth.php';

if (!isAdmin()) {
    header("Location: /raamatukogu/login.php");
    exit;
}

$book_id = $_GET['id'] ?? 0;
$book = $conn->prepare("SELECT * FROM books WHERE book_id = ?")->execute([$book_id])->fetch();

if (!$book) {
    $_SESSION['error'] = 'Raamatut ei leitud';
    header("Location: index.php");
    exit;
}

// Kontrollime, kas raamat on laenutatud
$loaned = $conn->prepare("SELECT COUNT(*) FROM loans WHERE book_id = ? AND status = 'borrowed'")
    ->execute([$book_id])
    ->fetchColumn();

if ($loaned > 0) {
    $_SESSION['error'] = 'Ei saa kustutada - raamat on laenutatud';
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        
        // Kustutame laenutuste ajaloo
        $conn->prepare("DELETE FROM loans WHERE book_id = ?")->execute([$book_id]);
        
        // Kustutame raamatu
        $conn->prepare("DELETE FROM books WHERE book_id = ?")->execute([$book_id]);
        
        $conn->commit();
        
        $_SESSION['success'] = 'Raamat kustutati edukalt!';
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = 'Viga raamatu kustutamisel: ' . $e->getMessage();
    }
    
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kustuta raamat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../../../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h1>Kustuta raamat</h1>
        
        <div class="alert alert-warning">
            <p>Kas olete kindel, et soovite kustutada järgmist raamatut?</p>
            <h4><?= htmlspecialchars($book['title']) ?></h4>
            <p>Autor: <?= htmlspecialchars($book['author']) ?></p>
            <p>ISBN: <?= htmlspecialchars($book['isbn']) ?></p>
        </div>
        
        <form method="post">
            <button type="submit" class="btn btn-danger">Kinnita kustutamine</button>
            <a href="index.php" class="btn btn-secondary">Tagasi</a>
        </form>
    </div>
    
    <?php include __DIR__ . '/../../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>