<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/auth.php';

if (!isAdmin()) {
    header("Location: /raamatukogu/login.php");
    exit;
}

$search = $_GET['search'] ?? '';
$where = '';
$params = [];

if (!empty($search)) {
    $where = "WHERE b.title LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search";
    $params = ['search' => "%$search%"];
}

$loans = $conn->prepare("
    SELECT l.*, 
           u.first_name, u.last_name, 
           b.title, b.author
    FROM loans l
    JOIN users u ON l.user_id = u.user_id
    JOIN books b ON l.book_id = b.book_id
    $where
    ORDER BY l.reserved_date DESC
    LIMIT 100
")->execute($params)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laenutuste ajalugu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../../../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h1>Laenutuste ajalugu</h1>
        
        <form method="get" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Otsi laenutusi..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">Otsi</button>
                <?php if (!empty($search)): ?>
                <a href="history.php" class="btn btn-outline-secondary">Tühista otsing</a>
                <?php endif; ?>
            </div>
        </form>
        
        <?php if (empty($loans)): ?>
        <div class="alert alert-info">Laenutusi ei leitud</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Kasutaja</th>
                        <th>Raamat</th>
                        <th>Autor</th>
                        <th>Broneerimine</th>
                        <th>Laenutus</th>
                        <th>Tähtaeg</th>
                        <th>Tagastus</th>
                        <th>Staatus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loans as $loan): ?>
                    <tr>
                        <td><?= htmlspecialchars($loan['first_name']) . ' ' . htmlspecialchars($loan['last_name']) ?></td>
                        <td><?= htmlspecialchars($loan['title']) ?></td>
                        <td><?= htmlspecialchars($loan['author']) ?></td>
                        <td><?= date('d.m.Y H:i', strtotime($loan['reserved_date'])) ?></td>
                        <td><?= $loan['loan_date'] ? date('d.m.Y H:i', strtotime($loan['loan_date'])) : '-' ?></td>
                        <td><?= $loan['due_date'] ? date('d.m.Y', strtotime($loan['due_date'])) : '-' ?></td>
                        <td><?= $loan['return_date'] ? date('d.m.Y H:i', strtotime($loan['return_date'])) : '-' ?></td>
                        <td>
                            <?php 
                            $status_class = [
                                'reserved' => 'warning',
                                'borrowed' => 'primary',
                                'returned' => 'success',
                                'cancelled' => 'secondary'
                            ];
                            ?>
                            <span class="badge bg-<?= $status_class[$loan['status']] ?>">
                                <?= $loan['status'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include __DIR__ . '/../../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>