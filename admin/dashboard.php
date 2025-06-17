<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if (!isAdmin()) {
    header("Location: /raamatukogu/login.php");
    exit;
}

// Statistika kogumine
$stats = [
    'total_books' => $conn->query("SELECT COUNT(*) FROM books")->fetchColumn(),
    'total_users' => $conn->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'active_loans' => $conn->query("SELECT COUNT(*) FROM loans WHERE status = 'borrowed'")->fetchColumn(),
    'overdue_loans' => $conn->query("SELECT COUNT(*) FROM loans WHERE status = 'borrowed' AND due_date < NOW()")->fetchColumn()
];
?>

<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administraatori juhtpaneel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h1>Juhtpaneel</h1>
        
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Raamatud kokku</h5>
                        <p class="card-text display-4"><?= $stats['total_books'] ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Kasutajad</h5>
                        <p class="card-text display-4"><?= $stats['total_users'] ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Aktiivsed laenutused</h5>
                        <p class="card-text display-4"><?= $stats['active_loans'] ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Hilinenud tagastused</h5>
                        <p class="card-text display-4"><?= $stats['overdue_loans'] ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="books/index.php" class="btn btn-outline-primary me-2">Raamatute haldus</a>
            <a href="users/index.php" class="btn btn-outline-secondary me-2">Kasutajate haldus</a>
            <a href="loans/index.php" class="btn btn-outline-dark">Laenutuste haldus</a>
        </div>
    </div>
    
    <?php include __DIR__ . '/../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>