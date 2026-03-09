<?php
session_start();

// Proteksi admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

include '../koneksi.php';

// Ambil semua data info - DIURUTKAN BERDASARKAN URUTAN
$sql = "SELECT * FROM info ORDER BY urutan ASC";
$result = $conn->query($sql);

$infoList = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $infoList[] = $row;
    }
}

// Ambil tanggal hari ini untuk header
$currentDate = new DateTime();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard Info - Daftar Tugas B 2024</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="dashboard-info.css">
    <style>
        .kategori-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75em;
            font-weight: bold;
            margin-left: 8px;
        }
        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        .badge-link {
            background: #fff3cd;
            color: #856404;
        }
        .urutan-badge {
            display: inline-block;
            background: #e9ecef;
            color: #495057;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            margin-right: 8px;
        }
        .info-header-content {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <div class="container-new">
        <header class="header-new">
            <a href="dashboard.php" class="nav-left">Tugas</a>
            <h1 class="header-title">Daftar Tugas B 24</h1>
            <a href="dashboard-info.php" class="nav-right">Info</a>
        </header>

        <div class="main-layout">
            <!-- Sidebar dengan Action Buttons -->
            <aside class="calendar-sidebar">
                <div class="admin-actions">
                    <button onclick="window.location.href='create-info.php'" class="btn-add">
                        +
                    </button>
                    <button onclick="logout()" class="btn-logout-admin">
                        Logout
                    </button>
                </div>

                <div class="calendar-header">
                    <span class="calendar-day-label">
                        <?php 
                        $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        echo $hari[$currentDate->format('w')];
                        ?>
                    </span>
                    <h2 class="calendar-date"><?= $currentDate->format('d') ?>, <?= $currentDate->format('F Y') ?></h2>
                </div>

                <div class="info-sidebar-content">
                    <div class="info-sidebar-card">
                        <div class="info-sidebar-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                            </svg>
                        </div>
                        <h3>Info Dashboard</h3>
                        <p>Kelola informasi penting untuk kelas B 2024</p>
                    </div>
                    
                    <div class="navigation-links">
                        <a href="dashboard.php" class="nav-link">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 11l3 3L22 4"></path>
                                <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path>
                            </svg>
                            Lihat Tugas
                        </a>
                    </div>
                </div>
            </aside>

            <!-- Area Info -->
            <main class="tasks-area">
                <div class="tasks-grid">
                    <?php if (count($infoList) > 0): ?>
                        <?php foreach ($infoList as $info): ?>
                        <div class="info-card-admin">
                            <div class="task-card-header">
                                <div class="info-header-content">
                                    <span class="urutan-badge">#<?= htmlspecialchars($info['urutan']) ?></span>
                                    <div class="info-icon-inline">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                                        </svg>
                                    </div>
                                    <h3><?= htmlspecialchars($info['Judul']) ?></h3>
                                    <span class="kategori-badge badge-<?= strtolower($info['kategori']) ?>">
                                        <?= $info['kategori'] == 'Link' ? '🔗' : '📋' ?> <?= htmlspecialchars($info['kategori']) ?>
                                    </span>
                                </div>
                                <div class="task-admin-icons">
                                    <a href="update-info.php?urutan=<?= $info['urutan'] ?>" class="icon-edit" title="Edit">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </a>
                                    <a href="delete-info.php?urutan=<?= $info['urutan'] ?>" class="icon-delete" title="Hapus" onclick="return confirm('Yakin ingin menghapus info #<?= $info['urutan'] ?> - <?= htmlspecialchars($info['Judul']) ?>?')">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                            <p class="task-description"><?= htmlspecialchars($info['deskripsi']) ?></p>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">📋</div>
                            <h3>Belum ada info</h3>
                            <p>Klik tombol + untuk menambah info baru</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        function logout() {
            if (confirm('Yakin ingin logout?')) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>