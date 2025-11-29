// Fungsi untuk menampilkan error
function showError(message) {
    const errorElement = document.getElementById('errorMessage');
    errorElement.textContent = message;
    errorElement.classList.add('show');
    
    setTimeout(() => {
        errorElement.classList.remove('show');
    }, 3000);
}

// Handle form submit
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const passwordInput = document.getElementById('password');
    const password = passwordInput.value;
    
    if (password === ADMIN_PASSWORD) {
        // Simpan status login ke variabel session
        window.adminLoggedIn = true;
        
        // Redirect ke dashboard
        window.location.href = 'dashboard.php';
    } else {
        showError('❌ Password salah! Silakan coba lagi.');
        passwordInput.value = '';
        passwordInput.focus();
    }
});

// Autofocus pada input password
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('password').focus();
});