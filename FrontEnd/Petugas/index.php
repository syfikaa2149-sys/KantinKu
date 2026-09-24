<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'petugas') {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/Database.php';
require_once __DIR__ . '/../../backend/models/Pesanan.php';

$db = (new Database())->getConnection();
$pesananModel = new Pesanan($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $orderId = (int)($_POST['order_id'] ?? 0);

    if ($action === 'update_status' && isset($_POST['status'])) {
        $stmt = $db->prepare("UPDATE orders SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $_POST['status'], ':id' => $orderId]);
    } elseif ($action === 'update_payment_status' && isset($_POST['status_pembayaran'])) {
        $stmt = $db->prepare("UPDATE orders SET status_pembayaran = :status_bayar WHERE id = :id");
        $stmt->execute([':status_bayar' => $_POST['status_pembayaran'], ':id' => $orderId]);
    }

    header("Location: index.php");
    exit;
}

// Ambil daftar seluruh pesanan
$query = "SELECT o.*, u.nama_lengkap FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC";
$orders = $db->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Petugas Kasir POS - KantinKu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">KantinKu - Kasir POS</a>
            <div class="d-flex align-items-center gap-3">
                <span class="small text-white">Kasir: <?= htmlspecialchars($_SESSION['user']['nama_lengkap']) ?></span>
                <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold m-0">Antrean Pesanan Masuk</h4>
                <p class="text-muted small m-0">Kelola verifikasi bayar dan status hidangan dapur</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Pesanan</th>
                            <th>Pemesan</th>
                            <th>Total</th>
                            <th>Metode</th>
                            <th>Status Bayar</th>
                            <th>Status Pesanan</th>
                            <th class="text-center">Aksi Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada pesanan masuk.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td class="fw-bold text-success"><?= htmlspecialchars($o['kode_pesanan']) ?></td>
                                    <td><?= htmlspecialchars($o['nama_lengkap']) ?></td>
                                    <td class="fw-semibold">Rp <?= number_format($o['total_harga'], 0, ',', '.') ?></td>
                                    <td><span class="badge bg-secondary text-uppercase"><?= htmlspecialchars($o['metode_pembayaran']) ?></span></td>
                                    <td>
                                        <form method="POST" action="index.php" class="d-inline">
                                            <input type="hidden" name="action" value="update_payment_status">
                                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                            <select name="status_pembayaran" class="form-select form-select-sm fw-semibold <?= $o['status_pembayaran'] === 'lunas' ? 'text-success border-success' : 'text-danger border-danger' ?>" onchange="this.form.submit()">
                                                <option value="belum_bayar" <?= $o['status_pembayaran'] === 'belum_bayar' ? 'selected' : '' ?>>Belum Bayar</option>
                                                <option value="lunas" <?= $o['status_pembayaran'] === 'lunas' ? 'selected' : '' ?>>LUNAS</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" action="index.php" class="d-inline">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                            <select name="status" class="form-select form-select-sm fw-semibold" onchange="this.form.submit()">
                                                <option value="menunggu" <?= $o['status'] === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                                                <option value="diproses" <?= $o['status'] === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                                                <option value="siap" <?= $o['status'] === 'siap' ? 'selected' : '' ?>>Siap Diambil</option>
                                                <option value="selesai" <?= $o['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                                <option value="batal" <?= $o['status'] === 'batal' ? 'selected' : '' ?>>Batal</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <a href="nota.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">🖨️ Struk</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>