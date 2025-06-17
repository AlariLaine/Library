<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/auth.php';

if (!isAdmin()) {
    header("Location: /raamatukogu/login.php");
    exit;
}

$status_filter = $_GET['status'] ?? 'borrowed';

$valid_statuses = ['reserved', 'borrowed', 'overdue'];
if (!in_array($status_filter, $valid_statuses)) {
    $status_filter = 'borrowed';
}

$where = "WHERE l.status = :status";
$params = ['status' => $status_filter];

if ($status_filter === 'overdue') {
    $where = "WHERE l.status = 'borrowed' AND l.due_date < CURDATE()";
    $params = [];
}

$loans = $conn->prepare("
    SELECT l.*, 
           u.first_name, u.last_name, 
           b.title, b.author
    FROM loans l
    JOIN users u ON l.user_id = u.user_id
    JOIN books b ON l.book_id = b.book_id
    $where
    ORDER BY l.due_date ASC
")->execute($params)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laenutuste haldus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../../../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h1>Laenutuste haldus</h1>
        
        <div class="btn-group mb-4">
            <a href="?status=borrowed" class="btn btn-<?= $status_filter === 'borrowed' ? 'primary' : 'outline-primary' ?>">
                Aktiivsed laenutused
            </a>
            <a href="?status=reserved" class="btn btn-<?= $status_filter === 'reserved' ? 'warning' : 'outline-warning' ?>">
                Broneeringud
            </a>
            <a href="?status=overdue" class="btn btn-<?= $status_filter === 'overdue' ? 'danger' : 'outline-danger' ?>">
                Hilinenud tagastused
            </a>
        </div>
        
        <?php if (empty($loans)): ?>
        <div class="alert alert-info">
            <?= $status_filter === 'borrowed' ? 'Aktiivseid laenutusi ei leitud' : 
               ($status_filter === 'reserved' ? 'Broneeringuid ei leitud' : 'Hilinenud tagastusi ei leitud') ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Kasutaja</th>
                        <th>Raamat</th>
                        <th>Autor</th>
                        <th>Kuupäev</th>
                        <th>Tähtaeg</th>
                        <th>Tegevused</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loans as $loan): ?>
                    <tr>
                        <td><?= htmlspecialchars($loan['first_name']) . ' ' . htmlspecialchars($loan['last_name']) ?></td>
                        <td><?= htmlspecialchars($loan['title']) ?></td>
                        <td><?= htmlspecialchars($loan['author']) ?></td>
                        <td>
                            <?= $status_filter === 'reserved' ? 
                                date('d.m.Y H:i', strtotime($loan['reserved_date'])) : 
                                date('d.m.Y', strtotime($loan['loan_date'])) ?>
                        </td>
                        <td>
                            <?php if ($status_filter !== 'reserved'): ?>
                                <?= date('d.m.Y', strtotime($loan['due_date'])) ?>
                                <?php if (strtotime($loan['due_date']) < time()): ?>
                                    <span class="badge bg-danger">Hilinenud</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <?= date('d.m.Y', strtotime($loan['due_date'])) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($status_filter === 'reserved'): ?>
                                <a href="process.php?action=confirm&id=<?= $loan['loan_id'] ?>" class="btn btn-sm btn-success">Kinnita laenutus</a>
                                <a href="process.php?action=cancel&id=<?= $loan['loan_id'] ?>" class="btn btn-sm btn-danger">Tühista</a>
                            <?php else: ?>
                                <a href="process.php?action=return&id=<?= $loan['loan_id'] ?>" class="btn btn-sm btn-primary">Märgi tagastatuks</a>
                            <?php endif; ?>
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