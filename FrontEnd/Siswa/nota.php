<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/Database.php';

$db = (new Database())->getConnection();
$orderId = (int)($_GET['id'] ?? 0);

// Ambil data pesanan milik siswa yang sedang login
$stmt = $db->prepare("
    SELECT * FROM orders 
    WHERE id = :id AND user_id = :uid 
    LIMIT 1
");
$stmt->execute([':id' => $orderId, ':uid' => $_SESSION['user']['id']]);
$order = $stmt->fetch();

if (!$order) {
    die("Tiket pesanan tidak ditemukan.");
}

$stmtDetail = $db->prepare("
    SELECT od.*, p.nama_produk 
    FROM order_details od 
    JOIN products p ON od.product_id = p.id 
    WHERE od.order_id = :id
");
$stmtDetail->execute([':id' => $orderId]);
$details = $stmtDetail->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Digital - KantinKu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light py-4">

    <div class="container" style="max-width: 420px;">
        <div class="card border-0 shadow-sm rounded-4 text-center p-4">
            <h4 class="fw-bold text-success m-0">Tiket Ambil Makanan</h4>
            <p class="text-muted small">Tunjukkan tiket ini ke loket kantin</p>
            
            <div class="bg-light border rounded-3 p-3 my-3">
                <span class="text-muted small d-block">Kode Pesanan</span>
                <h5 class="fw-bold text-dark m-0"><?= htmlspecialchars($order['kode_pesanan']) ?></h5>
            </div>

            <div class="text-start mb-3">
                <h6 class="fw-bold mb-2">Detail Menu:</h6>
                <ul class="list-group list-group-flush small">
                    <?php foreach ($details as $item): ?>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span><?= htmlspecialchars($item['nama_produk']) ?> (x<?= $item['jumlah'] ?>)</span>
                            <span class="fw-semibold">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="d-flex justify-content-between fw-bold fs-6 mb-3 border-top pt-2">
                <span>Total Bayar:</span>
                <span class="text-success">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></span>
            </div>

            <a href="index.php" class="btn btn-food-primary w-100 py-2 fw-semibold">Kembali ke Katalog</a>
        </div>
    </div>

</body>
</html>