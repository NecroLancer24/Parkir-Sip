const toggleBtn = document.getElementById('theme-toggle');

function updateButtonText() {
    const isDark = document.documentElement.classList.contains('dark');
    toggleBtn.textContent = isDark ? 'Toggle Light Mode' : 'Toggle Dark Mode';
}

// Cek preferensi tersimpan, kalau tidak ada pakai preferensi sistem
if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
}

updateButtonText(); // set teks awal sesuai state saat load

toggleBtn?.addEventListener('click', () => {
    document.documentElement.classList.toggle('dark');
    localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    updateButtonText(); // update teks setiap kali di-toggle
});
