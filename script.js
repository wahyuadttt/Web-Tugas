// ========================================
// LOCALSTORAGE CHECKLIST SYSTEM
// ========================================

const STORAGE_KEY = 'taskChecklist_B2024';

// Load checklist status saat halaman dimuat
function loadChecklistStatus() {
    const savedData = localStorage.getItem(STORAGE_KEY);
    const checklist = savedData ? JSON.parse(savedData) : {};
    
    // Apply status ke semua task cards
    Object.keys(checklist).forEach(taskId => {
        const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
        const checkbox = document.getElementById(`check-${taskId}`);
        
        if (taskCard && checkbox && checklist[taskId]) {
            checkbox.checked = true;
            taskCard.classList.add('completed');
        }
    });
    
    // Update task count setelah load
    updateTaskCount();
}

// Toggle task completion
function toggleTaskComplete(taskId) {
    const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
    const checkbox = document.getElementById(`check-${taskId}`);
    
    // Get current checklist from localStorage
    const savedData = localStorage.getItem(STORAGE_KEY);
    const checklist = savedData ? JSON.parse(savedData) : {};
    
    if (checkbox.checked) {
        // Mark as completed
        taskCard.classList.add('completed');
        checklist[taskId] = true;
    } else {
        // Mark as incomplete
        taskCard.classList.remove('completed');
        delete checklist[taskId];
    }
    
    // Save to localStorage
    localStorage.setItem(STORAGE_KEY, JSON.stringify(checklist));
    
    // Update task count
    updateTaskCount();
}

// ========================================
// MODAL DETAIL SYSTEM
// ========================================

function openTaskDetail(taskId) {
    const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
    const modal = document.getElementById('taskModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    
    // Ambil data dari kartu tugas
    const title = taskCard.querySelector('h3').textContent;
    const description = taskCard.querySelector('.task-description').textContent;
    const dateMeta = taskCard.querySelector('.task-meta').innerHTML;
    const hiddenData = taskCard.querySelector('.task-hidden-data .task-full-info');
    
    // Set judul modal
    modalTitle.textContent = title;
    
    // Set isi modal
    let modalContent = `
        <div class="modal-description">${description}</div>
        <div class="modal-meta">${dateMeta}</div>
    `;
    
    // Tambahkan data tersembunyi (gambar dan waktu)
    if (hiddenData) {
        modalContent += hiddenData.innerHTML;
    }
    
    modalBody.innerHTML = modalContent;
    
    // Tampilkan modal
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeTaskDetail() {
    const modal = document.getElementById('taskModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// ========================================
// TASK STATISTICS
// ========================================

function updateTaskCount() {
    const totalTasks = document.querySelectorAll('.task-card[data-task-id]').length;
    const completedTasks = document.querySelectorAll('.task-card.completed').length;
    const remainingTasks = totalTasks - completedTasks;
    
    console.log(`📊 Total Tugas: ${totalTasks}`);
    console.log(`✅ Selesai: ${completedTasks}`);
    console.log(`⏳ Tersisa: ${remainingTasks}`);
    
    return { totalTasks, completedTasks, remainingTasks };
}

// ========================================
// EVENT LISTENERS & INITIALIZATION
// ========================================

// Tutup modal ketika klik di luar konten
window.onclick = function(event) {
    const modal = document.getElementById('taskModal');
    if (modal && event.target == modal) {
        closeTaskDetail();
    }
}

// Tutup modal dengan tombol ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeTaskDetail();
    }
});

// Smooth scroll untuk anchor links
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Inisialisasi saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Daftar Tugas B 2024 - Loaded!');
    
    // Load checklist status dari localStorage
    loadChecklistStatus();
    
    // Initialize smooth scroll
    initSmoothScroll();
    
    // Log initial task count
    updateTaskCount();
});