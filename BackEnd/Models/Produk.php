<?php
class Produk {
    private $db;

    public function __construct($database) { 
        $this->db = $database; 
    }

    public function getAll() {
        $sql = "SELECT p.*, k.nama_kategori 
                FROM products p 
                JOIN categories k ON p.category_id = k.id 
                ORDER BY p.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function create($catId, $nama, $harga, $stok, $deskripsi = '', $foto = null) {
        $stmt = $this->db->prepare("INSERT INTO products (category_id, nama_produk, harga, stok, deskripsi, foto) VALUES (:c, :n, :h, :s, :d, :f)");
        return $stmt->execute([
            ':c' => $catId,
            ':n' => trim($nama),
            ':h' => $harga,
            ':s' => $stok,
            ':d' => trim($deskripsi),
            ':f' => $foto
        ]);
    }

    public function updateStok($id, $qtyChange) {
        $stmt = $this->db->prepare("UPDATE products SET stok = stok + :qty WHERE id = :id AND (stok + :qty) >= 0");
        return $stmt->execute([':qty' => $qtyChange, ':id' => $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}