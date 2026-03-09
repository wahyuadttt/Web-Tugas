<?php
session_start();

// Error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inisialisasi variable di awal
$message = '';
$error = '';

// Proteksi admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

include '../koneksi.php';

// Ambil urutan dari URL (PRIMARY KEY sekarang adalah urutan)
$urutan = isset($_GET['urutan']) ? intval($_GET['urutan']) : 0;

if ($urutan > 0) {
    // Cek apakah data info ada
    $sql = "SELECT * FROM info WHERE urutan = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $urutan);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $info_data = $result->fetch_assoc();
        
        // Hapus data dari database
        $sqlDelete = "DELETE FROM info WHERE urutan = ?";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->bind_param("i", $urutan);
        
        if ($stmtDelete->execute()) {
            $message = "Info dengan urutan $urutan berhasil dihapus!";
        } else {
            $error = "Error hapus data: " . $conn->error;
        }
        
        $stmtDelete->close();
    } else {
        $error = "Data dengan urutan $urutan tidak ditemukan";
    }
    
    $stmt->close();
} else {
    $error = "Urutan tidak valid";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Info</title>
    <link rel="stylesheet" href="../style.css">
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
        .info-icon-header {
            display: inline-block;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            border-radius: 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        .info-icon-header svg {
            width: 32px;
            height: 32px;
            color: white;
        }
        .info-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }
        .info-details p {
            margin: 8px 0;
            font-size: 0.95em;
        }
        .info-details strong {
            color: #495057;
        }
    </style>
    <?php if (!empty($message)): ?>
    <meta http-equiv="refresh" content="3;url=dashboard-info.php">
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <header>
            <div class="info-icon-header">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
            </div>
            <h1>🗑️ Hapus Info</h1>
            <p>Proses penghapusan data informasi</p>
        </header>

        <div class="result-container">
            <?php if (!empty($message)): ?>
                <div class="icon">✅</div>
                <div class="alert alert-success">
                    <?= htmlspecialchars($message) ?><br>
                    <small>Anda akan diarahkan ke dashboard info dalam 3 detik...</small>
                </div>
                
                <?php if (isset($info_data)): ?>
                <div class="info-details">
                    <p><strong>Data yang dihapus:</strong></p>
                    <p><strong>Urutan:</strong> <?= htmlspecialchars($info_data['urutan']) ?></p>
                    <p><strong>Kategori:</strong> <?= htmlspecialchars($info_data['kategori']) ?></p>
                    <p><strong>Judul:</strong> <?= htmlspecialchars($info_data['Judul']) ?></p>
                    <p><strong>Deskripsi:</strong> <?= htmlspecialchars($info_data['deskripsi']) ?></p>
                </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="icon">❌</div>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <a href="dashboard-info.php" class="btn-back">← Kembali ke Dashboard Info</a>
        </div>

        <footer>
            <p>Admin Mode - Hapus Info</p>
        </footer>
    </div>
</body>
</html>