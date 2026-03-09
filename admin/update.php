<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data tugas berdasarkan ID
$sql = "SELECT * FROM tugas WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $tugas = $result->fetch_assoc();
} else {
    echo "Data tidak ditemukan";
    exit;
}
$stmt->close();

// Proses update data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul = $_POST['judul'];
    $deadline_date = $_POST['deadline_date'];
    $deadline_time = $_POST['deadline_time'];
    $catatan = $_POST['catatan'];
    $kategori = $_POST['kategori'];
    
    // Ambil gambar lama yang masih dipertahankan
    $existingImages = isset($_POST['existing_images']) ? $_POST['existing_images'] : [];
    $uploadedFiles = $existingImages;
    $uploadDir = "../uploads/";
    
    // Buat folder uploads jika belum ada
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Hapus gambar yang tidak dicentang
    if (!empty($tugas['gambar'])) {
        $oldImages = explode(',', $tugas['gambar']);
        foreach ($oldImages as $oldImage) {
            $oldImage = trim($oldImage);
            if (!in_array($oldImage, $existingImages) && !empty($oldImage) && file_exists($oldImage)) {
                unlink($oldImage);
            }
        }
    }
    
    // Proses upload file baru jika ada
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
    
    // Update ke database jika tidak ada error
    if (empty($error)) {
        $sql = "UPDATE tugas SET judul=?, deadline_date=?, deadline_time=?, catatan=?, gambar=?, kategori=? WHERE id=?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $judul, $deadline_date, $deadline_time, $catatan, $gambarString, $kategori, $id);
        
        if ($stmt->execute()) {
            $message = "Data berhasil diperbarui!";
            // Refresh data tugas
            $tugas['judul'] = $judul;
            $tugas['deadline_date'] = $deadline_date;
            $tugas['deadline_time'] = $deadline_time;
            $tugas['catatan'] = $catatan;
            $tugas['gambar'] = $gambarString;
            $tugas['kategori'] = $kategori;
            
            // Redirect setelah 2 detik
            header("refresh:2;url=dashboard.php");
        } else {
            $error = "Error update data: " . $conn->error;
        }
        
        $stmt->close();
    }
}

// Parse existing images
$existingImages = [];
if (!empty($tugas['gambar'])) {
    $existingImages = array_map('trim', explode(',', $tugas['gambar']));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tugas</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        /* Override untuk memastikan scroll halaman berfungsi */
        html, body {
            overflow-y: auto !important;
            height: auto !important;
            min-height: 100vh;
        }
        
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
            min-height: 120px;
            line-height: 1.6;
            white-space: pre-wrap; /* Mempertahankan spasi dan line breaks */
        }
        
        /* Existing Images Management */
        .existing-images {
            margin-top: 15px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 15px;
        }
        
        .existing-image-item {
            position: relative;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            aspect-ratio: 1;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .existing-image-item:hover {
            border-color: #667eea;
        }
        
        .existing-image-item.marked-delete {
            border-color: #f44336;
            opacity: 0.5;
        }
        
        .existing-image-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .existing-image-item .delete-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(244, 67, 54, 0.8);
            color: white;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 2em;
            font-weight: bold;
        }
        
        .existing-image-item.marked-delete .delete-overlay {
            display: flex;
        }
        
        .existing-image-checkbox {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 20px;
            height: 20px;
            z-index: 10;
        }
        
        /* Custom File Upload Styling */
        .file-upload-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
            margin-top: 15px;
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
            border: 2px solid #4caf50;
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
        
        .section-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            display: block;
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
            <h1>✏️ Edit Tugas</h1>
            <p>Perbarui informasi tugas</p>
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
                    <input type="text" id="judul" name="judul" value="<?= htmlspecialchars($tugas['judul']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="deadline_date">Tanggal Deadline *</label>
                    <input type="date" id="deadline_date" name="deadline_date" value="<?= $tugas['deadline_date'] ?>" required>
                </div>

                <div class="form-group">
                    <label for="deadline_time">Waktu Deadline *</label>
                    <input type="time" id="deadline_time" name="deadline_time" value="<?= $tugas['deadline_time'] ?>" required>
                </div>

                <div class="form-group">
                    <label for="kategori">Kategori *</label>
                    <select id="kategori" name="kategori" required>
                        <option value="deadline" <?= $tugas['kategori'] == 'deadline' ? 'selected' : '' ?>>Deadline Pasti</option>
                        <option value="flexible" <?= $tugas['kategori'] == 'flexible' ? 'selected' : '' ?>>Kapan-Kapan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="catatan">Catatan (Opsional)</label>
                    <textarea id="catatan" name="catatan"><?= htmlspecialchars($tugas['catatan']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Kelola Gambar</label>
                    
                    <?php if (!empty($existingImages) && $existingImages[0] != ''): ?>
                        <span class="section-title">📷 Gambar Saat Ini (Klik untuk hapus):</span>
                        <div class="existing-images">
                            <?php foreach ($existingImages as $index => $image): ?>
                                <?php if (!empty($image)): ?>
                                <div class="existing-image-item" onclick="toggleImageDelete(this, '<?= htmlspecialchars($image) ?>')">
                                    <input type="checkbox" name="existing_images[]" value="<?= htmlspecialchars($image) ?>" class="existing-image-checkbox" checked onclick="event.stopPropagation()">
                                    <img src="<?= htmlspecialchars($image) ?>" alt="Image <?= $index + 1 ?>">
                                    <div class="delete-overlay">🗑️</div>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <small style="color: #666; display: block; margin-top: 8px;">💡 Uncheck gambar untuk menghapus</small>
                    <?php endif; ?>
                    
                    <span class="section-title" style="margin-top: 20px;">➕ Tambah Gambar Baru:</span>
                    <div class="file-upload-wrapper">
                        <input type="file" id="gambar" name="gambar[]" class="file-upload-input" accept="image/*" multiple onchange="previewFiles(this)">
                        <label for="gambar" class="file-upload-label">
                            <span class="file-upload-icon">📁</span>
                            <span>Pilih Gambar Tambahan (Opsional)</span>
                        </label>
                    </div>
                    <div id="filePreview" class="file-preview"></div>
                    <div id="fileCount" class="file-count"></div>
                    <small style="color: #666; display: block; margin-top: 8px;">Format: JPG, JPEG, PNG, GIF (Maksimal 5MB per file)</small>
                </div>

                <button type="submit" class="btn-submit">💾 Perbarui Tugas</button>
            </form>
        </div>

        <footer>
            <p>Admin Mode - Edit Tugas</p>
        </footer>
    </div>
    
    <script>
        let selectedFiles = [];
        
        function toggleImageDelete(element, imagePath) {
            const checkbox = element.querySelector('.existing-image-checkbox');
            checkbox.checked = !checkbox.checked;
            
            if (checkbox.checked) {
                element.classList.remove('marked-delete');
            } else {
                element.classList.add('marked-delete');
            }
        }
        
        function previewFiles(input) {
            const preview = document.getElementById('filePreview');
            const fileCount = document.getElementById('fileCount');
            selectedFiles = Array.from(input.files);
            
            preview.innerHTML = '';
            
            if (selectedFiles.length > 0) {
                fileCount.textContent = `${selectedFiles.length} gambar baru akan ditambahkan`;
                
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