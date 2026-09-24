<?php
class Laporan {
    private $db;

    public function __construct($database) { 
        $this->db = $database; 
    }

    public function getRingkasan($startDate = null, $endDate = null) {
        $where = "WHERE o.status != 'batal'";
        $params = [];

        if (!empty($startDate) && !empty($endDate)) {
            $where .= " AND DATE(o.created_at) BETWEEN :start AND :end";
            $params = [':start' => $startDate, ':end' => $endDate];
        }

        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT o.id) as total_transaksi,
                IFNULL(SUM(CASE WHEN o.status_pembayaran = 'lunas' THEN o.total_harga ELSE 0 END), 0) as total_pendapatan,
                IFNULL(SUM(od.jumlah), 0) as total_produk_terjual
            FROM orders o
            LEFT JOIN order_details od ON o.id = od.order_id
            {$where}
        ");
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function getProdukTerlaris($limit = 5) {
        $stmt = $this->db->prepare("
            SELECT p.nama_produk, SUM(od.jumlah) as total_terjual 
            FROM order_details od 
            JOIN products p ON od.product_id = p.id 
            JOIN orders o ON od.order_id = o.id 
            WHERE o.status != 'batal' AND o.status_pembayaran = 'lunas'
            GROUP BY od.product_id 
            ORDER BY total_terjual DESC 
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}