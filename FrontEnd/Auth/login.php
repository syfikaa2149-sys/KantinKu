<?php
session_start();

if (isset($_SESSION['user'])) {
    $r = $_SESSION['user']['role'];
    if ($r === 'admin') header("Location: ../admin/index.php");
    elseif ($r === 'petugas') header("Location: ../petugas/index.php");
    else header("Location: ../siswa/index.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/Database.php';
require_once __DIR__ . '/../../backend/models/User.php';

$error = '';
$success = $_SESSION['register_success'] ?? '';
unset($_SESSION['register_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        $db = (new Database())->getConnection();
        $userModel = new User($db);
        $user = $userModel->login($username, $password);

        if ($user) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'           => $user['id'],
                'username'     => $user['username'],
                'nama_lengkap' => $user['nama_lengkap'],
                'role'         => $user['role']
            ];

            if ($user['role'] === 'admin') header("Location: ../admin/index.php");
            elseif ($user['role'] === 'petugas') header("Location: ../petugas/index.php");
            else header("Location: ../siswa/index.php");
            exit;
        } else {
            $error = "Username atau kata sandi tidak valid.";
        }
    } else {
        $error = "Harap masukkan username dan kata sandi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - KantinKu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light d-flex align-items-center min-vh-100 py-4">

    <div class="container" style="max-width: 400px;">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-success m-0">KantinKu</h3>
                    <p class="text-muted small">Silakan masuk ke akun Anda</p>
                </div>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success py-2 small rounded-3 mb-3"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small rounded-3 mb-3"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Kata Sandi</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-food-primary w-100 fw-semibold py-2">Masuk</button>
                </form>

                <div class="text-center mt-4">
                    <p class="small text-muted mb-0">Belum punya akun? <a href="register.php" class="text-success text-decoration-none fw-semibold">Daftar Akun Siswa</a></p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>