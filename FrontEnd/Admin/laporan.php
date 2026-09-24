<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/Database.php';
require_once __DIR__ . '/../../backend/models/Laporan.php';

$db = (new Database())->getConnection();
$laporanModel = new Laporan($db);

$startDate = $_GET['start_date'] ?? null;
$endDate   = $_GET['end_date'] ?? null;

$ringkasan = $laporanModel->getRingkasan($startDate, $endDate);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Omzet - KantinKu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-light py-4">

    <div class="container" style="max-width: 700px;">
        <div class="no-print mb-3 d-flex justify-content-between align-items-center">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">&larr; Kembali ke Dashboard</a>
            <button onclick="window.print()" class="btn btn-success btn-sm">🖨️ Cetak Laporan</button>
        </div>

        <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
            <div class="text-center border-bottom pb-3 mb-3">
                <h3 class="fw-bold text-success m-0">KANTINKU DIGITAL</h3>
                <p class="text-muted small m-0">Laporan Rekapitulasi Pendapatan Kantin</p>
            </div>

            <!-- Filter Tanggal -->
            <form method="GET" action="laporan.php" class="row g-2 no-print mb-4">
                <div class="col-md-5">
                    <input type="date" name="start_date" class="form-control form-control-sm" value="<?= htmlspecialchars($startDate ?? '') ?>">
                </div>
                <div class="col-md-5">
                    <input type="date" name="end_date" class="form-control form-control-sm" value="<?= htmlspecialchars($endDate ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                </div>
            </form>

            <table class="table table-bordered mb-4">
                <tbody>
                    <tr>
                        <th class="bg-light" style="width: 50%;">Total Transaksi Sukses</th>
                        <td class="fw-bold"><?= number_format($ringkasan['total_transaksi']) ?> Transaksi</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Total Item Terjual</th>
                        <td class="fw-bold"><?= number_format($ringkasan['total_produk_terjual']) ?> Porsi</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Total Omzet / Pendapatan</th>
                        <td class="fw-bold text-success fs-5">Rp <?= number_format($ringkasan['total_pendapatan'], 0, ',', '.') ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="text-end text-muted small mt-4">
                <p class="mb-0">Dicetak pada: <?= date('d/m/Y H:i') ?></p>
                <p class="mb-0">Administrator: <?= htmlspecialchars($_SESSION['user']['nama_lengkap']) ?></p>
            </div>
        </div>
    </div>

</body>
</html>