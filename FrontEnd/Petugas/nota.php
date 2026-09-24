<?php
session_start();

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['petugas', 'admin'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/Database.php';

$db = (new Database())->getConnection();
$orderId = (int)($_GET['id'] ?? 0);

// Ambil data pesanan
$stmt = $db->prepare("
    SELECT o.*, u.nama_lengkap 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = :id 
    LIMIT 1
");
$stmt->execute([':id' => $orderId]);
$order = $stmt->fetch();

if (!$order) {
    die("Pesanan tidak ditemukan.");
}

// Ambil rincian produk
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
    <title>Struk Kasir - POS KantinKu</title>
    <style>
        @media print {
            @page { size: 80mm auto; margin: 0; }
            body { width: 76mm; margin: 2mm auto; font-family: 'Courier New', Courier, monospace; font-size: 9pt; }
            .no-print { display: none !important; }
        }
        body { width: 76mm; margin: 20px auto; font-family: 'Courier New', Courier, monospace; font-size: 9pt; color: #000; }
        .receipt-card { border: 1px dashed #333; padding: 10px; }
        .text-center { text-align: center; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .item-row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .btn-print { background: #16a34a; color: white; border: none; padding: 6px 12px; cursor: pointer; border-radius: 4px; margin-bottom: 10px; width: 100%; }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Struk</button>
    </div>

    <div class="receipt-card">
        <div class="text-center">
            <h3 style="margin: 0; font-size: 12pt;">KANTINKU DIGITAL</h3>
            <p style="margin: 2px 0;">Struk Kasir POS</p>
        </div>
        <div class="divider"></div>
        <div>No: <strong><?= htmlspecialchars($order['kode_pesanan']) ?></strong></div>
        <div>Pemesan: <?= htmlspecialchars($order['nama_lengkap']) ?></div>
        <div>Tgl: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
        <div class="divider"></div>

        <?php foreach ($details as $item): ?>
            <div class="item-row">
                <span><?= htmlspecialchars($item['nama_produk']) ?> (x<?= $item['jumlah'] ?>)</span>
                <span>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
            </div>
        <?php endforeach; ?>

        <div class="divider"></div>
        <div class="item-row" style="font-weight: bold;">
            <span>TOTAL:</span>
            <span>Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></span>
        </div>
        <div class="item-row">
            <span>Metode:</span>
            <span style="text-transform: uppercase;"><?= htmlspecialchars($order['metode_pembayaran']) ?></span>
        </div>
        <div class="item-row">
            <span>Status:</span>
            <span style="text-transform: uppercase;"><?= htmlspecialchars($order['status_pembayaran']) ?></span>
        </div>
        <div class="divider"></div>
        <div class="text-center">
            <p style="margin: 2px 0;">Terima Kasih!</p>
            <p style="margin: 0; font-size: 8pt;">Kantinku - Bebas Antre</p>
        </div>
    </div>

</body>
</html>