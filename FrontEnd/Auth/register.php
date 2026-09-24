<?php
session_start();

if (isset($_SESSION['user'])) { 
    header("Location: ../../index.php"); 
    exit; 
}

require_once __DIR__ . '/../../backend/config/Database.php';
require_once __DIR__ . '/../../backend/models/User.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $nama     = trim($_POST['nama_lengkap'] ?? '');
    $noHp     = trim($_POST['no_hp'] ?? '');

    if (!empty($username) && !empty($password) && !empty($nama)) {
        try {
            $db = (new Database())->getConnection();
            $userModel = new User($db);
            $userModel->register($username, $password, $nama, $noHp);
            $_SESSION['register_success'] = "Pendaftaran berhasil! Silakan login.";
            header("Location: login.php");
            exit;
        } catch (Exception $e) { 
            $error = $e->getMessage(); 
        }
    } else { 
        $error = "Harap lengkapi seluruh formulir!"; 
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - KantinKu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light d-flex align-items-center min-vh-100 py-4">

    <div class="container" style="max-width: 450px;">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-success m-0">KantinKu</h3>
                    <p class="text-muted small">Buat akun siswa baru</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small rounded-3 mb-3"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" placeholder="Ahmad Subagja" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="ahmad123" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nomor WhatsApp / HP</label>
                        <input type="text" name="no_hp" class="form-control" placeholder="081234567890">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Kata Sandi</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-food-primary w-100 fw-semibold py-2">Daftar Akun</button>
                </form>

                <div class="text-center mt-4">
                    <p class="small text-muted mb-0">Sudah punya akun? <a href="login.php" class="text-success text-decoration-none fw-semibold">Masuk di sini</a></p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>