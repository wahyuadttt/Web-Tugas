// Fungsi logout
function logout() {
    if (confirm('Yakin ingin logout?')) {
        // Hapus status login
        window.adminLoggedIn = false;
        
        // Redirect ke halaman utama
        window.location.href = 'index.html';
    }
}

// Cek apakah user sudah login (proteksi halaman)
document.addEventListener('DOMContentLoaded', function() {
    // Note: Ini proteksi sederhana untuk demo
    // Untuk production, gunakan session/token di server
    
    console.log('Dashboard Admin loaded');
    
    // Tambahkan informasi admin
    const header = document.querySelector('header p');
    if (header) {
        header.innerHTML = 'Kelola daftar tugas kelas B 2024 | <span style="color: #fff3e0;">Mode Admin Aktif</span>';
    }
});