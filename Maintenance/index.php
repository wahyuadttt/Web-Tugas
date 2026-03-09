<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - Daftar Tugas B 2024</title>
    <link rel="stylesheet" href="maintenance.css">
</head>
<body>
    <div class="maintenance-container">
        <header class="maintenance-header">
            <h1>Daftar Tugas B 24</h1>
        </header>

        <main class="maintenance-content">
            <div class="maintenance-box">
                <h2>Mohon Maaf Web<br>Sedang Dalam Maintenance</h2>
                
                <p class="maintenance-description">
                    Web akan diperbaiki saat admin sudah mood
                </p>

                <div class="maintenance-input-section">
                    <p class="maintenance-input-label">Ketik 1 Untuk Protes Ke Admin</p>
                    <div class="maintenance-input-wrapper">
                        <input 
                            type="text" 
                            id="protestInput" 
                            class="maintenance-input" 
                            placeholder="Input..."
                        >
                        <button class="maintenance-submit-btn" onclick="handleProtest()">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <footer class="maintenance-footer">
            <p>&copy; 2024 Daftar Tugas B 24. Admin lagi cape.</p>
            <a href="https://www.instagram.com/wahyuadt__" target="_blank" class="social-icon" title="Follow us on Instagram">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
            </a>
        </footer>
    </div>

    <!-- Modal Pop-up -->
    <div id="protestModal" class="protest-modal">
        <div class="protest-modal-content">
            <button class="protest-modal-close" onclick="closeModal()">&times;</button>
            <div class="protest-modal-body">
                <p class="protest-modal-text">Tombolnya sebenarnya ga ada fungsinya sih</p>
                <div class="protest-modal-image">
                    <img src="uploads/monyet.png" alt="Meme Image">
                </div>
            </div>
        </div>
    </div>

    <script>
        function handleProtest() {
            const input = document.getElementById('protestInput');
            const value = input.value.trim();
            const modal = document.getElementById('protestModal');
            
            if (value !== '') {
                // Show modal for any input
                modal.style.display = 'flex';
                input.value = '';
            } else {
                alert('Silakan masukkan sesuatu untuk protes.');
            }
        }

        function closeModal() {
            const modal = document.getElementById('protestModal');
            modal.style.display = 'none';
        }

        // Enter key support
        document.getElementById('protestInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                handleProtest();
            }
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            const modal = document.getElementById('protestModal');
            if (e.target === modal) {
                closeModal();
            }
        });
    </script>
</body>
</html>