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
    $where = "WHERE first_name LIKE :search OR last_name LIKE :search OR email LIKE :search";
    $params = ['search' => "%$search%"];
}

$stmt = $conn->prepare("SELECT * FROM users $where ORDER BY last_name, first_name");
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kasutajate haldus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../../../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h1>Kasutajate haldus</h1>
        
        <form method="get" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Otsi kasutajaid..." value="<?= htmlspecialchars($search) ?>">
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
                        <th>Nimi</th>
                        <th>E-post</th>
                        <th>Isikukood</th>
                        <th>Roll</th>
                        <th>Tegevused</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['personal_code']) ?></td>
                        <td><?= $user['is_staff'] ? 'Töötaja' : 'Kasutaja' ?></td>
                        <td>
                            <a href="manage.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-warning">Halda</a>
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