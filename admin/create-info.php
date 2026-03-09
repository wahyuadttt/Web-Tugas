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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $urutan = intval($_POST['urutan']);
        $judul = trim($_POST['judul']);
        $deskripsi = trim($_POST['deskripsi']);
        $kategori = $_POST['kategori'];
        
        // Validasi input
        if (empty($judul) || empty($deskripsi) || $urutan <= 0) {
            throw new Exception("Semua field harus diisi dengan benar!");
        }
        
        // Cek apakah urutan sudah ada
        $check_sql = "SELECT urutan FROM info WHERE urutan = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $urutan);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            throw new Exception("Urutan $urutan sudah digunakan! Pilih urutan yang lain.");
        }
        $check_stmt->close();
        
        // Insert ke database
        $sql = "INSERT INTO info (urutan, Judul, deskripsi, kategori) VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare statement gagal: " . $conn->error);
        }
        
        $stmt->bind_param("isss", $urutan, $judul, $deskripsi, $kategori);
        
        if ($stmt->execute()) {
            $message = "Info berhasil ditambahkan!";
            // Redirect setelah 2 detik
            header("refresh:2;url=dashboard-info.php");
        } else {
            throw new Exception("Execute gagal: " . $stmt->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log("Create-info error: " . $e->getMessage());
    }
}

// Ambil urutan terakhir untuk saran
$last_urutan = 0;
$last_sql = "SELECT MAX(urutan) as max_urutan FROM info";
$last_result = $conn->query($last_sql);
if ($last_result && $last_result->num_rows > 0) {
    $row = $last_result->fetch_assoc();
    $last_urutan = $row['max_urutan'] ? $row['max_urutan'] : 0;
}
$suggested_urutan = $last_urutan + 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Info Baru</title>
    <link rel="stylesheet" href="style.css">
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .kategori-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
            margin-left: 5px;
        }
        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        .badge-link {
            background: #fff3cd;
            color: #856404;
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
            <h1>➕ Tambah Info Baru</h1>
            <p>Isi form di bawah untuk menambah informasi baru</p>
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
                        <input type="number" id="urutan" name="urutan" min="1" value="<?= $suggested_urutan ?>" placeholder="Nomor urutan" required>
                        <div class="form-hint">Urutan terakhir: <?= $last_urutan ?></div>
                    </div>

                    <div class="form-group">
                        <label for="kategori">Kategori *</label>
                        <select id="kategori" name="kategori" required>
                            <option value="Info" selected>📋 Info</option>
                            <option value="Link">🔗 Link</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="judul">Judul *</label>
                    <input type="text" id="judul" name="judul" placeholder="Contoh: Pengumuman Penting" required maxlength="255">
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi *</label>
                    <textarea id="deskripsi" name="deskripsi" placeholder="Tulis deskripsi atau informasi lengkap di sini..." required maxlength="255"></textarea>
                    <div class="form-hint">Maksimal 255 karakter</div>
                </div>

                <button type="submit" class="btn-submit">💾 Simpan Info</button>
            </form>
        </div>

        <footer>
            <p>Admin Mode - Tambah Info Baru</p>
        </footer>
    </div>
</body>
</html>
<?php $conn->close(); ?>