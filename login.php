<?php
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

include 'koneksi.php';

// Proses login
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validasi input tidak kosong
    if (empty($username) || empty($password)) {
        $error = '❌ Username dan password harus diisi!';
    } else {
        // Ambil data admin dari database
        $sql = "SELECT * FROM admin WHERE username = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            
            // Verifikasi password dengan password_verify
            if ($password === $admin['password']) {
                // Login berhasil
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                
                header('Location: dashboard.php');
                exit;
            } else {
                $error = '❌ Password salah! Silakan coba lagi.';
            }
        } else {
            $error = '❌ Username tidak ditemukan!';
        }
        
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Daftar Tugas</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 Admin Login</h1>
            <p>Masukkan username dan password untuk mengakses dashboard</p>
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required>
            </div>

            <?php if (!empty($error)): ?>
            <div class="error-message show">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <button type="submit" class="login-button">Login</button>

            <button type="button" class="back-button" onclick="window.location.href='index.php'">
                ← Kembali ke Halaman Utama
            </button>
        </form>
    </div>
</body>
</html>