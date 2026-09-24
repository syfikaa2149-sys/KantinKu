<?php
session_start();

if (!isset($_SESSION['user']) \vert{}\vert{}$_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/Database.php';
require_once __DIR__ . '/../../backend/models/Produk.php';
require_once __DIR__ . '/../../backend/models/Pesanan.php';

$db = (new Database())->getConnection();
$produkModel = new Produk($db);
$pesananModel = new Pesanan($db);

$message = '';$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkout') {$metode = $_POST['metode_pembayaran'] ?? 'tunai';$cartItems = [];

    if (isset($_POST['products']) && is_array($_POST['products'])) {
        foreach ($_POST['products'] as $prodId =>$qty) {
            if ((int)$qty > 0) {$cartItems[] = [
                    'product_id' => (int)$prodId,
                    'jumlah' => (int)$qty
                ];
            }
        }
    }

    if (!empty($cartItems)) {
        try {
            $res =$pesananModel->createOrder($_SESSION['user']['id'],$cartItems, $metode);$message = "Pesanan berhasil dibuat dengan Kode: " . $res['kode_pesanan'];
        } catch (Exception $e) {
            $error =$e->getMessage();
        }
    } else {
        $error = "Keranjang belanja Anda kosong!";
    }
}

$produkList =$produkModel->getAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Siswa - KantinKu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-success" href="#">KantinKu - Siswa</a>
            <div class="d-flex align-items-center gap-3">
                <span class="small fw-semibold text-muted">Halo, <?= htmlspecialchars($_SESSION['user']['nama_lengkap']) ?></span>
                <a href="../auth/logout.php" class="btn btn-outline-danger btn-sm">Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Katalog Menu -->
            <div class="col-lg-8">
                <h4 class="fw-bold mb-3">Pilih Menu Kantin</h4>
                <div class="row row-cols-1 row-cols-md-2 g-3">
                    <?php foreach ($produkList as$p): ?>
                        <div class="col">
                            <div class="card card-product h-100 border-0 shadow-sm rounded-4 p-3">
                                <span class="badge bg-success-subtle text-success mb-2 me-auto"><?= htmlspecialchars($p['nama_kategori']) ?></span>
                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($p['nama_produk']) ?></h6>
                                <p class="text-muted small mb-2"><?= htmlspecialchars($p['deskripsi'] ?? '') ?></p>
                                <div class="d-flex align-items-center justify-content-between mt-auto pt-2 border-top">
                                    <span class="fw-bold text-success">Rp <?= number_format($p['harga'], 0, ',', '.') ?></span>
                                    <button class="btn btn-sm btn-food-primary rounded-pill px-3 add-to-cart" 
                                            data-id="<?= $p['id'] ?>" 
                                            data-nama="<?= htmlspecialchars($p['nama_produk']) ?>" 
                                            data-harga="<?= $p['harga'] ?>">
                                        + Tambah
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Ringkasan Keranjang -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 80px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Keranjang Saya</h5>
                        <div id="cart-list" class="mb-3">
                            <p class="text-muted small text-center py-3">Belum ada item dipilih.</p>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold mb-3">
                            <span>Total:</span>
                            <span id="cart-total" class="text-success">Rp 0</span>
                        </div>
                        <button class="btn btn-food-primary w-100 py-2 fw-semibold" id="btn-open-checkout" data-bs-toggle="modal" data-bs-target="#paymentModal" disabled>
                            Lanjut Pembayaran
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pembayaran -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark">Pilih Cara Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="index.php" method="POST" id="checkout-form">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="checkout">
                        <input type="hidden" name="metode_pembayaran" id="hidden-metode-pembayaran" value="tunai">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Metode Pembayaran</label>
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-success text-start active btn-payment" data-method="tunai">
                                    💵 Tunai di Loket Kantin (Bayar saat Ambil)
                                </button>
                                <button type="button" class="btn btn-outline-success text-start btn-payment" data-method="qris">
                                    📱 QRIS Digital (Konfirmasi Otomatis)
                                </button>
                            </div>
                        </div>
                        <div id="cart-form-inputs"></div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="submit" class="btn btn-food-primary w-100 py-2 fw-bold" id="btn-checkout">
                            Konfirmasi & Buat Pesanan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const cart = {};

        document.querySelectorAll('.add-to-cart').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const nama = this.dataset.nama;
                const harga = parseInt(this.dataset.harga);

                if (cart[id]) {
                    cart[id].qty += 1;
                } else {
                    cart[id] = { nama, harga, qty: 1 };
                }
                renderCart();
            });
        });

        function renderCart() {
            const cartList = document.getElementById('cart-list');
            const cartInputs = document.getElementById('cart-form-inputs');
            const totalEl = document.getElementById('cart-total');
            const btnCheckout = document.getElementById('btn-open-checkout');

            cartList.innerHTML = '';
            cartInputs.innerHTML = '';
            let total = 0;
            let itemCount = 0;

            for (const id in cart) {
                const item = cart[id];
                const subtotal = item.harga * item.qty;
                total += subtotal;
                itemCount += item.qty;

                cartList.innerHTML += `
                    <div class="d-flex justify-content-between align-items-center mb-2 small">
                        <div>
                            <strong>${item.nama}</strong><br>
                            <span class="text-muted">${item.qty} x Rp ${item.harga.toLocaleString('id-ID')}</span>
                        </div>
                        <span class="fw-semibold">Rp ${subtotal.toLocaleString('id-ID')}</span>
                    </div>
                `;

                cartInputs.innerHTML += `<input type="hidden" name="products[${id}]" value="${item.qty}">`;
            }

            totalEl.innerText = `Rp ${total.toLocaleString('id-ID')}`;
            btnCheckout.disabled = itemCount === 0;
            if (itemCount === 0) cartList.innerHTML = '<p class="text-muted small text-center py-3">Belum ada item dipilih.</p>';
        }

        document.querySelectorAll('.btn-payment').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.btn-payment').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('hidden-metode-pembayaran').value = this.dataset.method;
            });
        });
    </script>
</body>
</html>