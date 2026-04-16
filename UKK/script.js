// Ambil semua elemen kartu tugas dan area drop zone
const draggables = document.querySelectorAll('.draggable');
const droppables = document.querySelectorAll('.droppable');

// Daftarkan event drag ke setiap kartu tugas
draggables.forEach(draggable => {
    // Saat mulai ditarik — tambahkan class 'dragging' untuk styling
    draggable.addEventListener('dragstart', () => {
        draggable.classList.add('dragging');
    });

    // Saat dilepas — update status di DB via AJAX tanpa reload halaman
    draggable.addEventListener('dragend', () => {
        draggable.classList.remove('dragging');

        const id        = draggable.getAttribute('data-id');   // ID tugas dari data-id HTML
        const newStatus = draggable.parentElement.getAttribute('data-status'); // Kolom tujuan

        updateColumnCounts(); // Perbarui angka jumlah tugas di setiap kolom

        // Kirim status baru ke server pakai Fetch API (AJAX — tanpa reload halaman)
        const formData = new FormData();
        formData.append('action', 'update_status');
        formData.append('id', id);
        formData.append('status', newStatus);

        fetch('index.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(() => {
                // Sesuaikan tampilan kartu berdasarkan kolom tujuannya
                const textElement = draggable.querySelector('.card-text');

                if (newStatus === 'selesai') {
                    draggable.style.opacity = '0.75';
                    draggable.classList.add('text-muted');
                    textElement.classList.add('text-decoration-line-through');
                } else {
                    draggable.style.opacity = '1';
                    draggable.classList.remove('text-muted');
                    textElement.classList.remove('text-decoration-line-through');
                }
            })
            .catch(err => console.error('Gagal update status:', err));
    });
});

// Daftarkan event drop ke setiap kolom
droppables.forEach(droppable => {
    // dragover — terus dipanggil selama kartu ditarik di atas kolom ini
    droppable.addEventListener('dragover', e => {
        e.preventDefault(); // Wajib dicegah defaultnya biar elemen bisa menerima drop
        droppable.classList.add('drag-over');

        // Pindahkan kartu yang sedang ditarik ke dalam kolom ini secara visual
        const draggingItem = document.querySelector('.dragging');
        if (draggingItem) {
            droppable.appendChild(draggingItem);
        }
    });

    // Saat kartu keluar dari area kolom ini
    droppable.addEventListener('dragleave', () => {
        droppable.classList.remove('drag-over');
    });

    // Saat kartu berhasil dilepas di kolom ini
    droppable.addEventListener('drop', () => {
        droppable.classList.remove('drag-over');
    });
});

// Hitung ulang jumlah kartu di tiap kolom dan update badge angkanya
function updateColumnCounts() {
    document.querySelectorAll('.card-column').forEach(column => {
        const count = column.querySelectorAll('.draggable').length;
        const badge = column.querySelector('span.badge');
        if (badge) {
            badge.innerText = count;
        }
    });
}
