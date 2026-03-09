<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'koneksi.php';

// Ambil semua data Info - DIURUTKAN BERDASARKAN URUTAN
$sql = "SELECT * FROM info ORDER BY urutan ASC";
$result = $conn->query($sql);

$infoList = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $infoList[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Info B 2024</title>
    <link rel="stylesheet" href="style-info.css">
    <style>
        /* Style tambahan untuk link */
        .Info-card.link-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .Info-card.link-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .Info-description.clickable {
            color: #667eea;
            text-decoration: underline;
            cursor: pointer;
            word-break: break-all;
        }
        .Info-description.clickable:hover {
            color: #764ba2;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        .empty-state h3 {
            font-size: 1.5em;
            margin-bottom: 10px;
        }
        .empty-state p {
            font-size: 1em;
        }
    </style>
</head>
<body>
    <div class="container-new">
        <header class="header-new">
            <a href="index.php" class="nav-left">Tugas</a>
            <a href="index.php" class="header-title">Daftar Tugas B 24</a>
            <a href="info.php" class="nav-right">Info</a>
        </header>

        <main class="Info-area">
            <div class="Info-grid">
                <?php if (count($infoList) > 0): ?>
                    <?php foreach ($infoList as $info): ?>
                    <div class="Info-card <?= $info['kategori'] == 'Link' ? 'link-card' : '' ?>" 
                         <?= $info['kategori'] == 'Link' ? 'onclick="window.open(\'' . htmlspecialchars($info['deskripsi'], ENT_QUOTES) . '\', \'_blank\')"' : '' ?>>
                        <h3 class="Info-title">
                            <?= htmlspecialchars($info['Judul']) ?>
                        </h3>
                        <p class="Info-description <?= $info['kategori'] == 'Link' ? 'clickable' : '' ?>">
                            <?= htmlspecialchars($info['deskripsi']) ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                        </svg>
                        <h3>Belum ada informasi</h3>
                        <p>Informasi akan ditampilkan di sini</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <footer class="footer-new">
            <button onclick="window.location.href='login.php'" class="login-btn">
                Login Admin
            </button>
            <a href="https://www.instagram.com/wahyuadt__" target="_blank" class="social-icon" title="Follow us on Instagram">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
            </a>
        </footer>
    </div>
</body>
</html>
<?php $conn->close(); ?>