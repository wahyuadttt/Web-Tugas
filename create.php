<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul = $_POST['judul'];
    $deadline_date = $_POST['deadline_date'];
    $deadline_time = $_POST['deadline_time'];
    $catatan = $_POST['catatan'];
    $kategori = $_POST['kategori'];
    
    $uploadedFiles = [];
    $uploadDir = "uploads/";
    
    // Buat folder uploads jika belum ada
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Proses upload multiple files
    if (!empty($_FILES['gambar']['name'][0])) {
        $fileCount = count($_FILES['gambar']['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['gambar']['error'][$i] == 0) {
                $fileName = $_FILES['gambar']['name'][$i];
                $fileTmpName = $_FILES['gambar']['tmp_name'][$i];
                $fileSize = $_FILES['gambar']['size'][$i];
                
                // Validasi file
                $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                if (in_array($fileExt, $allowedExt)) {
                    if ($fileSize < 5000000) { // 5MB
                        // Rename file untuk menghindari duplikasi
                        $newFileName = uniqid('', true) . "." . $fileExt;
                        $fileDestination = $uploadDir . $newFileName;
                        
                        if (move_uploaded_file($fileTmpName, $fileDestination)) {
                            $uploadedFiles[] = $fileDestination;
                        } else {
                            $error = "Gagal mengunggah file: " . $fileName;
                            break;
                        }
                    } else {
                        $error = "File $fileName terlalu besar (maksimal 5MB).";
                        break;
                    }
                } else {
                    $error = "Format file $fileName tidak diizinkan. Gunakan JPG, JPEG, PNG, atau GIF.";
                    break;
                }
            }
        }
    }
    
    // Gabungkan semua path gambar dengan koma
    $gambarString = implode(',', $uploadedFiles);
    
    // Insert ke database jika tidak ada error
    if (empty($error)) {
        $sql = "INSERT INTO tugas (judul, deadline_date, deadline_time, catatan, gambar, kategori) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $judul, $deadline_date, $deadline_time, $catatan, $gambarString, $kategori);
        
        if ($stmt->execute()) {
            $message = "Data berhasil ditambahkan dengan " . count($uploadedFiles) . " gambar!";
            // Redirect setelah 2 detik
            header("refresh:2;url=dashboard.php");
        } else {
            $error = "Error: " . $conn->error;
        }
        
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Tugas Baru</title>
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
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        /* Custom File Upload Styling */
        .file-upload-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        
        .file-upload-input {
            position: absolute;
            left: -9999px;
        }
        
        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border: 2px dashed #2196f3;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #1976d2;
            font-weight: 600;
        }
        
        .file-upload-label:hover {
            background: linear-gradient(135deg, #bbdefb 0%, #90caf9 100%);
            border-color: #1976d2;
        }
        
        .file-upload-icon {
            font-size: 1.5em;
            margin-right: 10px;
        }
        
        .file-preview {
            margin-top: 15px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
        }
        
        .file-preview-item {
            position: relative;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            aspect-ratio: 1;
        }
        
        .file-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .file-preview-item .remove-file {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #f44336;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            font-size: 0.8em;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .file-preview-item .remove-file:hover {
            background: #d32f2f;
            transform: scale(1.1);
        }
        
        .file-count {
            margin-top: 10px;
            color: #666;
            font-size: 0.9em;
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
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>➕ Tambah Tugas Baru</h1>
            <p>Isi form di bawah untuk menambah tugas baru</p>
        </header>

        <a href="dashboard.php" class="btn-back">← Kembali ke Dashboard</a>

        <div class="form-container">
            <?php if ($message): ?>
                <div class="alert alert-success"><?= $message ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="judul">Judul Tugas *</label>
                    <input type="text" id="judul" name="judul" placeholder="Contoh: Tugas Strukdat" required>
                </div>

                <div class="form-group">
                    <label for="deadline_date">Tanggal Deadline *</label>
                    <input type="date" id="deadline_date" name="deadline_date" required>
                </div>

                <div class="form-group">
                    <label for="deadline_time">Waktu Deadline *</label>
                    <input type="time" id="deadline_time" name="deadline_time" required>
                </div>

                <div class="form-group">
                    <label for="kategori">Kategori *</label>
                    <select id="kategori" name="kategori" required>
                        <option value="deadline">Deadline Pasti</option>
                        <option value="flexible">Kapan-Kapan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="catatan">Catatan (Opsional)</label>
                    <textarea id="catatan" name="catatan" placeholder="Tambahkan catatan jika perlu..."></textarea>
                </div>

                <div class="form-group">
                    <label>Upload Gambar (Opsional - Multiple)</label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="gambar" name="gambar[]" class="file-upload-input" accept="image/*" multiple onchange="previewFiles(this)">
                        <label for="gambar" class="file-upload-label">
                            <span class="file-upload-icon">📁</span>
                            <span>Pilih Gambar (Bisa lebih dari 1)</span>
                        </label>
                    </div>
                    <div id="filePreview" class="file-preview"></div>
                    <div id="fileCount" class="file-count"></div>
                    <small style="color: #666; display: block; margin-top: 8px;">Format: JPG, JPEG, PNG, GIF (Maksimal 5MB per file)</small>
                </div>

                <button type="submit" class="btn-submit">💾 Simpan Tugas</button>
            </form>
        </div>

        <footer>
            <p>Admin Mode - Tambah Tugas Baru</p>
        </footer>
    </div>
    
    <script>
        let selectedFiles = [];
        
        function previewFiles(input) {
            const preview = document.getElementById('filePreview');
            const fileCount = document.getElementById('fileCount');
            selectedFiles = Array.from(input.files);
            
            preview.innerHTML = '';
            
            if (selectedFiles.length > 0) {
                fileCount.textContent = `${selectedFiles.length} file dipilih`;
                
                selectedFiles.forEach((file, index) => {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'file-preview-item';
                        div.innerHTML = `
                            <img src="${e.target.result}" alt="Preview">
                            <button type="button" class="remove-file" onclick="removeFile(${index})">×</button>
                        `;
                        preview.appendChild(div);
                    }
                    
                    reader.readAsDataURL(file);
                });
            } else {
                fileCount.textContent = '';
            }
        }
        
        function removeFile(index) {
            selectedFiles.splice(index, 1);
            
            // Update input files
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            document.getElementById('gambar').files = dataTransfer.files;
            
            // Re-render preview
            previewFiles(document.getElementById('gambar'));
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>