<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/auth.php';

if (!isAdmin()) {
    header("Location: /raamatukogu/login.php");
    exit;
}

// Otsingufunktsioon
$search = $_GET['search'] ?? '';
$where = '';
$params = [];

if (!empty($search)) {
    $where = "WHERE title LIKE :search OR author LIKE :search OR isbn LIKE :search";
    $params = ['search' => "%$search%"];
}

// Raamatute päring
$stmt = $conn->prepare("SELECT * FROM books $where ORDER BY title");
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Raamatute haldus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../../../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Raamatute haldus</h1>
            <a href="add.php" class="btn btn-success">Lisa uus raamat</a>
        </div>
        
        <form method="get" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Otsi raamatuid..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">Otsi</button>
                <?php if (!empty($search)): ?>
                <a href="index.php" class="btn btn-outline-secondary">Tühista otsing</a>
                <?php endif; ?>
            </div>
        </form>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Pealkiri</th>
                        <th>Autor</th>
                        <th>ISBN</th>
                        <th>Eksemplarid</th>
                        <th>Tegevused</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?= htmlspecialchars($book['title']) ?></td>
                        <td><?= htmlspecialchars($book['author']) ?></td>
                        <td><?= htmlspecialchars($book['isbn']) ?></td>
                        <td><?= $book['available_copies'] ?> / <?= $book['total_copies'] ?></td>
                        <td>
                            <a href="edit.php?id=<?= $book['book_id'] ?>" class="btn btn-sm btn-warning">Muuda</a>
                            <a href="delete.php?id=<?= $book['book_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Kas olete kindel, et soovite selle raamatu kustutada?')">Kustuta</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php include __DIR__ . '/../../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>