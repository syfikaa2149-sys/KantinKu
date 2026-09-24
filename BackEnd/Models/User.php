<?php
class User {
    private $db;

    public function __construct($database) { 
        $this->db = $database; 
    }

    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $this->logActivity($user['id'], "User '{$user['username']}' berhasil login");
            return $user;
        }
        return false;
    }

    public function register($username, $password, $nama, $noHp) {
        $chk = $this->db->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
        $chk->execute([':username' => $username]);
        if ($chk->fetch()) {
            throw new Exception("Username '{$username}' sudah digunakan.");
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, password, nama_lengkap, no_hp, role) VALUES (:u, :p, :n, :h, 'siswa')");
        return $stmt->execute([':u' => $username, ':p' => $hashed, ':n' => $nama, ':h' => $noHp]);
    }

    public function logActivity($userId, $aktivitas) {
        $stmt = $this->db->prepare("INSERT INTO activity_logs (user_id, aktivitas) VALUES (:u, :a)");
        return $stmt->execute([':u' => $userId, ':a' => $aktivitas]);
    }
}