<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
    <title>Daftar Tugas B 2024</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container-new">
        <header class="header-new">
            <h1>Daftar Tugas B 24</h1>
        </header>

        <div class="main-layout">
            <!-- Sidebar Kalender -->
            <aside class="calendar-sidebar">
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
                    <div class="task-card" data-task-id="<?= $tugas['id'] ?>">
                        <div class="task-card-header">
                            <div class="task-title-wrapper">
                                <input type="checkbox" class="task-checkbox-input" id="check-<?= $tugas['id'] ?>" onchange="toggleTaskComplete(<?= $tugas['id'] ?>)">
                                <label for="check-<?= $tugas['id'] ?>" class="task-checkbox-label"></label>
                                <h3><?= htmlspecialchars($tugas['judul']) ?></h3>
                            </div>
                            <div class="task-icons">
                                <span class="task-arrow" onclick="openTaskDetail(<?= $tugas['id'] ?>)">→</span>
                            </div>
                        </div>
                        <p class="task-description"><?= htmlspecialchars($tugas['catatan'] ?? 'Master the language powering the modern web.') ?></p>
                        <div class="task-meta">
                            <span class="task-date-label">
                                <?php 
                                $taskDate = new DateTime($tugas['deadline_date']);
                                echo ($taskDate < new DateTime()) ? 'Due Date' : 'Due date';
                                ?>:
                            </span>
                            <span class="task-date-value"><?= date('d-m-Y', strtotime($tugas['deadline_date'])) ?></span>
                        </div>
                        
                        <!-- Hidden data untuk modal -->
                        <div class="task-hidden-data" style="display: none;">
                            <div class="task-full-info">
                                <p class="task-time-detail">⏰ Deadline: <?= date('H:i', strtotime($tugas['deadline_time'])) ?></p>
                                <?php if (!empty($tugas['gambar'])): 
                                    $gambarArray = explode(',', $tugas['gambar']);
                                    foreach ($gambarArray as $gambar): 
                                        $gambar = trim($gambar);
                                        if (!empty($gambar)):
                                ?>
                                <div class="task-image-data">
                                    <img src="<?= htmlspecialchars($gambar) ?>" alt="Gambar Tugas">
                                </div>
                                <?php 
                                        endif;
                                    endforeach;
                                endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php foreach ($tugasFlexible as $tugas): ?>
                    <div class="task-card flexible" data-task-id="<?= $tugas['id'] ?>">
                        <div class="task-card-header">
                            <div class="task-title-wrapper">
                                <input type="checkbox" class="task-checkbox-input" id="check-<?= $tugas['id'] ?>" onchange="toggleTaskComplete(<?= $tugas['id'] ?>)">
                                <label for="check-<?= $tugas['id'] ?>" class="task-checkbox-label"></label>
                                <h3><?= htmlspecialchars($tugas['judul']) ?></h3>
                            </div>
                            <div class="task-icons">
                                <span class="task-arrow" onclick="openTaskDetail(<?= $tugas['id'] ?>)">→</span>
                            </div>
                        </div>
                        <p class="task-description"><?= htmlspecialchars($tugas['catatan'] ?? 'Tugas fleksibel') ?></p>
                        <div class="task-meta">
                            <span class="task-date-label">Kapan-Kapan</span>
                        </div>
                        
                        <!-- Hidden data untuk modal -->
                        <div class="task-hidden-data" style="display: none;">
                            <div class="task-full-info">
                                <?php if (!empty($tugas['gambar'])): 
                                    $gambarArray = explode(',', $tugas['gambar']);
                                    foreach ($gambarArray as $gambar): 
                                        $gambar = trim($gambar);
                                        if (!empty($gambar)):
                                ?>
                                <div class="task-image-data">
                                    <img src="<?= htmlspecialchars($gambar) ?>" alt="Gambar Tugas">
                                </div>
                                <?php 
                                        endif;
                                    endforeach;
                                endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </main>
            
            <!-- Modal untuk Detail Tugas -->
            <div id="taskModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 id="modalTitle">Detail Tugas</h2>
                        <span class="modal-close" onclick="closeTaskDetail()">&times;</span>
                    </div>
                    <div class="modal-body" id="modalBody">
                        <!-- Content akan diisi oleh JavaScript -->
                    </div>
                </div>
            </div>
        </div>

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
    
    <script src="script.js"></script>
</body>
</html>
<?php $conn->close(); ?>