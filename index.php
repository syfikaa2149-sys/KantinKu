<?php
session_start();
require_once __DIR__ . '/backend/config/Database.php';
require_once __DIR__ . '/backend/models/Kategori.php';
require_once __DIR__ . '/backend/models/Produk.php';

// Gateway Router: auto-redirect jika sudah memiliki session aktif
if (isset($_SESSION['user'])) {
    $r = $_SESSION['user']['role'];
    if ($r === 'admin') { header("Location: frontend/admin/index.php"); exit; }
    if ($r === 'petugas') { header("Location: frontend/petugas/index.php"); exit; }
    if ($r === 'siswa') { header("Location: frontend/siswa/index.php"); exit; }
}

$db = (new Database())->getConnection();
$kategoriModel = new Kategori($db);
$produkModel   = new Produk($db);

$kategoriList = $kategoriModel->getAll();
$produkList   = $produkModel->getAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KantinKu - E-Kantin Sekolah</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="frontend/assets/css/style.css">
</head>
<body>

    <!-- Navbar Header -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-success" href="index.php">KantinKu</a>
            <div class="d-flex align-items-center gap-2">
                <a href="frontend/auth/login.php" class="btn btn-outline-success btn-sm px-3 fw-semibold">Masuk</a>
                <a href="frontend/auth/register.php" class="btn btn-success btn-sm px-3 fw-semibold">Daftar</a>
            </div>
        </div>
    </nav>

    <!-- Hero Banner -->
    <section class="bg-success text-white py-5">
        <div class="container py-4 text-center">
            <h1 class="fw-bold display-5 mb-3">Selamat Datang di KantinKu</h1>
            <p class="lead mb-4">Pesan menu makanan dan minuman kantin favoritmu secara praktis, bebas antre, dan cepat!</p>
            <a href="frontend/auth/login.php" class="btn btn-warning btn-lg fw-semibold px-4 rounded-pill">Pesan Sekarang</a>
        </div>
    </section>

    <!-- Etalase Menu Dinamis -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold m-0">Menu Hari Ini</h3>
                    <p class="text-muted small m-0"><?= count($produkList) ?> Varian Menu Tersedia</p>
                </div>
            </div>

            <?php if (empty($produkList)): ?>
                <div class="alert alert-info text-center py-4 rounded-4">
                    Belum ada menu yang tersedia saat ini.
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                    <?php foreach ($produkList as $p): ?>
                        <div class="col">
                            <div class="card card-product h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                                <div class="bg-secondary-subtle d-flex align-items-center justify-content-center" style="height: 160px;">
                                    <?php if (!empty($p['foto'])): ?>
                                        <img src="frontend/assets/uploads/<?= htmlspecialchars($p['foto']) ?>" class="w-100 h-100 object-fit-cover" alt="<?= htmlspecialchars($p['nama_produk']) ?>">
                                    <?php else: ?>
                                        <span class="text-muted small">Tanpa Gambar</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body d-flex flex-column p-3">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill mb-2 w-auto me-auto small">
                                        <?= htmlspecialchars($p['nama_kategori']) ?>
                                    </span>
                                    <h6 class="card-title fw-bold text-dark mb-1"><?= htmlspecialchars($p['nama_produk']) ?></h6>
                                    <p class="card-text text-muted small mb-3 flex-grow-1"><?= htmlspecialchars($p['deskripsi'] ?? '') ?></p>
                                    <div class="d-flex align-items-center justify-content-between mt-auto">
                                        <span class="fw-bold text-success">Rp <?= number_format($p['harga'], 0, ',', '.') ?></span>
                                        <span class="badge bg-light text-dark border small">Stok: <?= $p['stok'] ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-top py-4 text-center text-muted small">
        <div class="container">
            <p class="m-0">&copy; 2026 KantinKu. Seluruh hak cipta dilindungi.</p>
        </div>
    </footer>

</body>
</html>