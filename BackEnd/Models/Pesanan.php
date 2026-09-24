<?php
class Pesanan {
    private $db;

    public function __construct($database) { 
        $this->db = $database; 
    }

    public function createOrder($userId, array $cartItems, $metode = 'tunai') {
        if (empty($cartItems)) {
            throw new Exception("Keranjang belanja kosong.");
        }

        $statusBayar = ($metode === 'qris' || $metode === 'e_wallet') ? 'lunas' : 'belum_bayar';

        try {
            $this->db->beginTransaction();

            $totalHarga = 0;
            $itemsToProcess = [];

            foreach ($cartItems as $item) {
                $stmt = $this->db->prepare("SELECT id, nama_produk, harga, stok FROM products WHERE id = :id FOR UPDATE");
                $stmt->execute([':id' => (int)$item['product_id']]);
                $prod = $stmt->fetch();

                if (!$prod || $prod['stok'] < (int)$item['jumlah']) {
                    throw new Exception("Stok produk '" . ($prod['nama_produk'] ?? 'Item') . "' tidak mencukupi.");
                }

                $subtotal = $prod['harga'] * (int)$item['jumlah'];
                $totalHarga += $subtotal;
                $itemsToProcess[] = [
                    'id'    => $prod['id'],
                    'qty'   => (int)$item['jumlah'],
                    'harga' => $prod['harga'],
                    'sub'   => $subtotal
                ];
            }

            $kodePesanan = 'KTK-' . date('YmdHis') . '-' . rand(100, 999);

            $insO = $this->db->prepare("INSERT INTO orders (user_id, kode_pesanan, total_harga, metode_pembayaran, status_pembayaran, status) VALUES (?, ?, ?, ?, ?, 'menunggu')");
            $insO->execute([$userId, $kodePesanan, $totalHarga, $metode, $statusBayar]);
            $orderId = $this->db->lastInsertId();

            $insD = $this->db->prepare("INSERT INTO order_details (order_id, product_id, jumlah, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)");
            $updS = $this->db->prepare("UPDATE products SET stok = stok - ? WHERE id = ?");

            foreach ($itemsToProcess as $it) {
                $insD->execute([$orderId, $it['id'], $it['qty'], $it['harga'], $it['sub']]);
                $updS->execute([$it['qty'], $it['id']]);
            }

            $this->db->commit();
            return ['order_id' => $orderId, 'kode_pesanan' => $kodePesanan, 'total' => $totalHarga];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}