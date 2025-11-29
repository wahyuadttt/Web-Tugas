<?php
session_start();

// Proteksi admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

include 'koneksi.php';

// Ambil semua data tugas
$sql = "SELECT * FROM tugas ORDER BY deadline_date ASC, deadline_time ASC";
$result = $conn->query($sql);

$tugasDeadline = [];
$tugasFlexible = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if ($row['kategori'] == 'flexible') {
            $tugasFlexible[] = $row;
        } else {
            $tugasDeadline[] = $row;
        }
    }
}

// Ambil tanggal hari ini untuk kalender
$currentDate = new DateTime();
$selectedDate = isset($_GET['date']) ? $_GET['date'] : $currentDate->format('Y-m-d');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Daftar Tugas B 2024</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="container-new">
        <header class="header-new">
            <h1>Daftar Tugas B 24</h1>
        </header>

        <div class="main-layout">
            <!-- Sidebar Kalender -->
            <aside class="calendar-sidebar">
                <div class="admin-actions">
                    <button onclick="window.location.href='create.php'" class="btn-add">
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
                        $dateObj = new DateTime($selectedDate);
                        echo $hari[$dateObj->format('w')];
                        ?>
                    </span>
                    <h2 class="calendar-date"><?= $dateObj->format('d') ?>, <?= $dateObj->format('F Y') ?></h2>
                </div>

                <div class="calendar-grid">
                    <div class="calendar-weekdays">
                        <span>Sen</span>
                        <span>Sel</span>
                        <span>Rab</span>
                        <span>Kam</span>
                        <span>Jum</span>
                        <span>Sab</span>
                        <span>Min</span>
                    </div>
                    
                    <div class="calendar-days">
                        <?php
                        $month = $dateObj->format('m');
                        $year = $dateObj->format('Y');
                        $firstDay = new DateTime("$year-$month-01");
                        $lastDay = new DateTime($firstDay->format('Y-m-t'));
                        
                        // Hitung hari pertama (1 = Monday, 7 = Sunday)
                        $startDay = $firstDay->format('N');
                        
                        // Tanggal dari bulan sebelumnya
                        $prevMonth = clone $firstDay;
                        $prevMonth->modify('-1 day');
                        for ($i = $startDay - 1; $i > 0; $i--) {
                            $day = clone $prevMonth;
                            $day->modify("-" . ($i - 1) . " days");
                            echo '<span class="calendar-day other-month">' . $day->format('d') . '</span>';
                        }
                        
                        // Tanggal bulan ini
                        $current = clone $firstDay;
                        $today = new DateTime();
                        while ($current <= $lastDay) {
                            $classes = ['calendar-day'];
                            if ($current->format('Y-m-d') == $today->format('Y-m-d')) {
                                $classes[] = 'today';
                            }
                            if ($current->format('Y-m-d') == $selectedDate) {
                                $classes[] = 'selected';
                            }
                            
                            // Cek apakah ada tugas di tanggal ini
                            $hasTask = false;
                            foreach ($tugasDeadline as $tugas) {
                                if ($tugas['deadline_date'] == $current->format('Y-m-d')) {
                                    $hasTask = true;
                                    break;
                                }
                            }
                            if ($hasTask) {
                                $classes[] = 'has-task';
                            }
                            
                            echo '<span class="' . implode(' ', $classes) . '" data-date="' . $current->format('Y-m-d') . '">' . $current->format('d') . '</span>';
                            $current->modify('+1 day');
                        }
                        
                        // Tanggal bulan berikutnya untuk melengkapi grid
                        $remainingDays = 42 - ($startDay - 1 + $lastDay->format('d'));
                        $nextMonth = clone $lastDay;
                        $nextMonth->modify('+1 day');
                        for ($i = 0; $i < $remainingDays; $i++) {
                            $day = clone $nextMonth;
                            $day->modify("+$i days");
                            echo '<span class="calendar-day other-month">' . $day->format('d') . '</span>';
                        }
                        ?>
                    </div>
                </div>
            </aside>

            <!-- Area Tugas -->
            <main class="tasks-area">
                <div class="tasks-grid">
                    <?php foreach ($tugasDeadline as $tugas): ?>
                    <div class="task-card-admin">
                        <div class="task-card-header">
                            <h3><?= htmlspecialchars($tugas['judul']) ?></h3>
                            <div class="task-admin-icons">
                                <a href="update.php?id=<?= $tugas['id'] ?>" class="icon-edit" title="Edit">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </a>
                                <a href="delete.php?id=<?= $tugas['id'] ?>" class="icon-delete" title="Hapus" onclick="return confirm('Yakin ingin menghapus tugas ini?')">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        <p class="task-description"><?= htmlspecialchars($tugas['catatan'] ?? 'Master the language powering the modern web.') ?></p>
                        <div class="task-meta">
                            <span class="task-date-label">
                                <?php 
                                $taskDate = new DateTime($tugas['deadline_date']);
                                echo ($taskDate < new DateTime()) ? 'Due Date' : 'Start date';
                                ?>:
                            </span>
                            <span class="task-date-value"><?= date('d-m-Y', strtotime($tugas['deadline_date'])) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php foreach ($tugasFlexible as $tugas): ?>
                    <div class="task-card-admin flexible">
                        <div class="task-card-header">
                            <h3><?= htmlspecialchars($tugas['judul']) ?></h3>
                            <div class="task-admin-icons">
                                <a href="update.php?id=<?= $tugas['id'] ?>" class="icon-edit" title="Edit">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </a>
                                <a href="delete.php?id=<?= $tugas['id'] ?>" class="icon-delete" title="Hapus" onclick="return confirm('Yakin ingin menghapus tugas ini?')">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        <p class="task-description"><?= htmlspecialchars($tugas['catatan'] ?? 'Tugas fleksibel') ?></p>
                        <div class="task-meta">
                            <span class="task-date-label">Flexible</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
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