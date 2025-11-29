<?php
session_start();

// Inisialisasi variable di awal
$message = '';
$error = '';

// Proteksi admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

include 'koneksi.php';

// Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Ambil data tugas untuk mendapatkan path gambar
    $sql = "SELECT gambar FROM tugas WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $tugas = $result->fetch_assoc();
        
        // Hapus file gambar jika ada
        if (!empty($tugas['gambar']) && file_exists($tugas['gambar'])) {
            unlink($tugas['gambar']);
        }
        
        // Hapus data dari database
        $sqlDelete = "DELETE FROM tugas WHERE id = ?";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->bind_param("i", $id);
        
        if ($stmtDelete->execute()) {
            $message = "Data berhasil dihapus!";
        } else {
            $error = "Error hapus data: " . $conn->error;
        }
        
        $stmtDelete->close();
    } else {
        $error = "Data tidak ditemukan";
    }
    
    $stmt->close();
} else {
    $error = "ID tidak valid";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Tugas</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .result-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 20px auto;
            text-align: center;
        }
        .alert {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 1.1em;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .btn-back {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            transition: all 0.3s ease;
            font-weight: bold;
        }
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(102, 126, 234, 0.4);
        }
        .icon {
            font-size: 4em;
            margin-bottom: 20px;
        }
    </style>
    <?php if (!empty($message)): ?>
    <meta http-equiv="refresh" content="3;url=dashboard.php">
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <header>
            <h1>🗑️ Hapus Tugas</h1>
            <p>Proses penghapusan data</p>
        </header>

        <div class="result-container">
            <?php if (!empty($message)): ?>
                <div class="icon">✅</div>
                <div class="alert alert-success">
                    <?= htmlspecialchars($message) ?><br>
                    <small>Anda akan diarahkan ke dashboard dalam 3 detik...</small>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="icon">❌</div>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <a href="dashboard.php" class="btn-back">← Kembali ke Dashboard</a>
        </div>

        <footer>
            <p>Admin Mode - Hapus Tugas</p>
        </footer>
    </div>
</body>
</html>