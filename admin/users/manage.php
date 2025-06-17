<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/auth.php';

if (!isAdmin()) {
    header("Location: /raamatukogu/login.php");
    exit;
}

$user_id = $_GET['id'] ?? 0;
$user = $conn->prepare("SELECT * FROM users WHERE user_id = ?")->execute([$user_id])->fetch();

if (!$user) {
    $_SESSION['error'] = 'Kasutajat ei leitud';
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_staff = isset($_POST['is_staff']) ? 1 : 0;
    
    try {
        $conn->prepare("UPDATE users SET is_staff = ? WHERE user_id = ?")
            ->execute([$is_staff, $user_id]);
        
        $_SESSION['success'] = 'Kasutaja andmed uuendati edukalt!';
        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        $error = 'Andmebaasi viga: ' . $e->getMessage();
    }
}

// Kasutaja laenutuste ajalugu
$loans = $conn->prepare("
    SELECT l.*, b.title 
    FROM loans l
    JOIN books b ON l.book_id = b.book_id
    WHERE l.user_id = ?
    ORDER BY l.reserved_date DESC
")->execute([$user_id])->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kasutaja haldamine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../../../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h1>Kasutaja haldamine</h1>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                <p class="card-text">
                    <strong>E-post:</strong> <?= htmlspecialchars($user['email']) ?><br>
                    <strong>Isikukood:</strong> <?= htmlspecialchars($user['personal_code']) ?><br>
                    <strong>Registreerunud:</strong> <?= date('d.m.Y H:i', strtotime($user['created_at'])) ?>
                </p>
                
                <form method="post">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_staff" name="is_staff" 
                               <?= $user['is_staff'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_staff">Töötaja õigused</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Salvesta muudatused</button>
                    <a href="index.php" class="btn btn-secondary">Tagasi</a>
                </form>
            </div>
        </div>
        
        <h2 class="mt-4">Laenutuste ajalugu</h2>
        
        <?php if (empty($loans)): ?>
        <div class="alert alert-info">Kasutajal pole laenutusi</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Raamat</th>
                        <th>Broneerimise kuupäev</th>
                        <th>Laenutuse kuupäev</th>
                        <th>Tähtaeg</th>
                        <th>Tagastamise kuupäev</th>
                        <th>Staatus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loans as $loan): ?>
                    <tr>
                        <td><?= htmlspecialchars($loan['title']) ?></td>
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