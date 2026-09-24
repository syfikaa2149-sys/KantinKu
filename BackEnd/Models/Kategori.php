<?php
class Kategori {
    private $db;

    public function __construct($database) { 
        $this->db = $database; 
    }

    public function getAll() {
        $stmt = $this->db->query("
            SELECT k.*, COUNT(p.id) as jumlah_produk 
            FROM categories k 
            LEFT JOIN products p ON k.id = p.category_id 
            GROUP BY k.id 
            ORDER BY k.nama_kategori ASC
        ");
        return $stmt->fetchAll();
    }

    public function create($nama) {
        $stmt = $this->db->prepare("INSERT INTO categories (nama_kategori) VALUES (:nama)");
        return $stmt->execute([':nama' => trim($nama)]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}