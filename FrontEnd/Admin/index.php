<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/Database.php';
require_once __DIR__ . '/../../backend/models/Laporan.php';
require_once __DIR__ . '/../../backend/models/Produk.php';

$db = (new Database())->getConnection();
$laporanModel = new Laporan($db);
$produkModel  = new Produk($db);

$ringkasan     = $laporanModel->getRingkasan();
$topProducts   = $laporanModel->getProdukTerlaris(5);
$produkList    = $produkModel->getAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - KantinKu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-success" href="#">KantinKu - Admin</a>
            <div class="d-flex align-items-center gap-3">
                <span class="small text-white">Admin: <?= htmlspecialchars($_SESSION['user']['nama_lengkap']) ?></span>
                <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold m-0">Ringkasan Eksekutif</h4>
            <a href="laporan.php" class="btn btn-success fw-semibold">📊 Cetak Laporan Omzet</a>
        </div>

        <!-- Metric Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <span class="text-muted small fw-semibold">Total Pendapatan (Lunas)</span>
                    <h3 class="fw-bold text-success m-0 mt-2">Rp <?= number_format($ringkasan['total_pendapatan'], 0, ',', '.') ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <span class="text-muted small fw-semibold">Total Transaksi</span>
                    <h3 class="fw-bold text-dark m-0 mt-2"><?= number_format($ringkasan['total_transaksi']) ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <span class="text-muted small fw-semibold">Produk Terjual</span>
                    <h3 class="fw-bold text-warning m-0 mt-2"><?= number_format($ringkasan['total_produk_terjual']) ?> Porsi</h3>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Menu Terlaris -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h5 class="fw-bold mb-3">🔥 5 Menu Terlaris</h5>
                    <ul class="list-group list-group-flush">
                        <?php if (empty($topProducts)): ?>
                            <li class="list-group-item text-muted text-center py-3">Belum ada data penjualan.</li>
                        <?php else: ?>
                            <?php foreach ($topProducts as $tp): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><?= htmlspecialchars($tp['nama_produk']) ?></span>
                                    <span class="badge bg-success rounded-pill"><?= $tp['total_terjual'] ?> Porsi</span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Katalog Produk -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h5 class="fw-bold mb-3">📦 Katalog Produk Saat Ini</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Menu</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produkList as $p): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($p['nama_produk']) ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['nama_kategori']) ?></span></td>
                                        <td>Rp <?= number_format($p['harga'], 0, ',', '.') ?></td>
                                        <td><span class="badge <?= $p['stok'] > 5 ? 'bg-success' : 'bg-danger' ?>"><?= $p['stok'] ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>