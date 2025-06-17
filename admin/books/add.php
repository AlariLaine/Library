<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/auth.php';

if (!isAdmin()) {
    header("Location: /raamatukogu/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn = trim($_POST['isbn']);
    $copies = (int)$_POST['copies'];
    
    // Valideerimine
    $errors = [];
    if (empty($title)) $errors[] = 'Pealkiri on kohustuslik';
    if (empty($author)) $errors[] = 'Autor on kohustuslik';
    if (empty($isbn)) $errors[] = 'ISBN on kohustuslik';
    if ($copies < 1) $errors[] = 'Eksemplaride arv peab olema positiivne';
    
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO books (title, author, isbn, total_copies, available_copies) 
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $author, $isbn, $copies, $copies]);
            
            $_SESSION['success'] = 'Raamat lisati edukalt!';
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = 'Sellise ISBN-iga raamat on juba olemas';
            } else {
                $errors[] = 'Andmebaasi viga: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lisa uus raamat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../../../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h1>Lisa uus raamat</h1>
        
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <form method="post" class="mt-4">
            <div class="mb-3">
                <label for="title" class="form-label">Pealkiri *</label>
                <input type="text" class="form-control" id="title" name="title" required 
                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="author" class="form-label">Autor *</label>
                <input type="text" class="form-control" id="author" name="author" required
                       value="<?= htmlspecialchars($_POST['author'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="isbn" class="form-label">ISBN *</label>
                <input type="text" class="form-control" id="isbn" name="isbn" required
                       value="<?= htmlspecialchars($_POST['isbn'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="copies" class="form-label">Eksemplaride arv *</label>
                <input type="number" class="form-control" id="copies" name="copies" min="1" required
                       value="<?= htmlspecialchars($_POST['copies'] ?? 1) ?>">
            </div>
            
            <button type="submit" class="btn btn-primary">Lisa raamat</button>
            <a href="index.php" class="btn btn-secondary">Tagasi</a>
        </form>
    </div>
    
    <?php include __DIR__ . '/../../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>