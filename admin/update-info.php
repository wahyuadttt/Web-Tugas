<?php
session_start();

// Error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../koneksi.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Ambil urutan dari URL (PRIMARY KEY sekarang adalah urutan)
$urutan = isset($_GET['urutan']) ? intval($_GET['urutan']) : 0;

// Ambil data info berdasarkan urutan
$sql = "SELECT * FROM info WHERE urutan = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $urutan);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $info = $result->fetch_assoc();
} else {
    echo "Data tidak ditemukan";
    exit;
}
$stmt->close();

// Proses update data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $urutan_baru = intval($_POST['urutan']);
        $judul = trim($_POST['judul']);
        $deskripsi = trim($_POST['deskripsi']);
        $kategori = $_POST['kategori'];
        
        // Validasi input
        if (empty($judul) || empty($deskripsi) || $urutan_baru <= 0) {
            throw new Exception("Semua field harus diisi dengan benar!");
        }
        
        // Jika urutan berubah, cek apakah urutan baru sudah digunakan
        if ($urutan_baru != $urutan) {
            $check_sql = "SELECT urutan FROM info WHERE urutan = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $urutan_baru);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                throw new Exception("Urutan $urutan_baru sudah digunakan! Pilih urutan yang lain.");
            }
            $check_stmt->close();
        }
        
        // Update ke database
        // Jika urutan berubah, kita perlu hapus lalu insert karena urutan adalah PRIMARY KEY
        if ($urutan_baru != $urutan) {
            // Hapus data lama
            $delete_sql = "DELETE FROM info WHERE urutan = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $urutan);
            $delete_stmt->execute();
            $delete_stmt->close();
            
            // Insert dengan urutan baru
            $insert_sql = "INSERT INTO info (urutan, Judul, deskripsi, kategori) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("isss", $urutan_baru, $judul, $deskripsi, $kategori);
            
            if (!$insert_stmt->execute()) {
                throw new Exception("Gagal mengupdate dengan urutan baru: " . $insert_stmt->error);
            }
            $insert_stmt->close();
        } else {
            // Update biasa jika urutan tidak berubah
            $update_sql = "UPDATE info SET Judul=?, deskripsi=?, kategori=? WHERE urutan=?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sssi", $judul, $deskripsi, $kategori, $urutan);
            
            if (!$update_stmt->execute()) {
                throw new Exception("Gagal mengupdate data: " . $update_stmt->error);
            }
            $update_stmt->close();
        }
        
        $message = "Info berhasil diperbarui!";
        // Refresh data info
        $info['urutan'] = $urutan_baru;
        $info['Judul'] = $judul;
        $info['deskripsi'] = $deskripsi;
        $info['kategori'] = $kategori;
        
        // Redirect setelah 2 detik
        header("refresh:2;url=dashboard-info.php");
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log("Update-info error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Info</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 20px auto;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        .form-group select {
            cursor: pointer;
            background-color: white;
        }
        .form-hint {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-back {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            background: #5a6268;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
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
        .info-icon-header {
            display: inline-block;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        .info-icon-header svg {
            width: 28px;
            height: 28px;
            color: white;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9em;
        }
        
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="info-icon-header">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                </svg>
            </div>
            <h1>✏️ Edit Info</h1>
            <p>Perbarui informasi</p>
        </header>

        <a href="dashboard-info.php" class="btn-back">← Kembali ke Dashboard Info</a>

        <div class="form-container">
            <?php if ($message): ?>
                <div class="alert alert-success">✅ <?= $message ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">❌ <?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="urutan">Urutan *</label>
                        <input type="number" id="urutan" name="urutan" min="1" value="<?= htmlspecialchars($info['urutan']) ?>" required>
                        <div class="form-hint">Urutan saat ini: <?= $info['urutan'] ?></div>
                    </div>

                    <div class="form-group">
                        <label for="kategori">Kategori *</label>
                        <select id="kategori" name="kategori" required>
                            <option value="Info" <?= $info['kategori'] == 'Info' ? 'selected' : '' ?>>📋 Info</option>
                            <option value="Link" <?= $info['kategori'] == 'Link' ? 'selected' : '' ?>>🔗 Link</option>
                        </select>
                    </div>
                </div>

                <div class="warning-box">
                    ⚠️ <strong>Perhatian:</strong> Jika mengubah nomor urutan, pastikan nomor urutan baru belum digunakan oleh info lain.
                </div>

                <div class="form-group">
                    <label for="judul">Judul *</label>
                    <input type="text" id="judul" name="judul" value="<?= htmlspecialchars($info['Judul']) ?>" required maxlength="255">
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi *</label>
                    <textarea id="deskripsi" name="deskripsi" required maxlength="255"><?= htmlspecialchars($info['deskripsi']) ?></textarea>
                    <div class="form-hint">Maksimal 255 karakter</div>
                </div>

                <button type="submit" class="btn-submit">💾 Perbarui Info</button>
            </form>
        </div>

        <footer>
            <p>Admin Mode - Edit Info</p>
        </footer>
    </div>
</body>
</html>
<?php $conn->close(); ?>