const draggables = document.querySelectorAll('.draggable');
const droppables = document.querySelectorAll('.droppable');

// Fungsi utama drag and drop
draggables.forEach(draggable => {
    // Mulai tarik
    draggable.addEventListener('dragstart', () => {
        draggable.classList.add('dragging');
    });

    // Selesai tarik
    draggable.addEventListener('dragend', () => {
        draggable.classList.remove('dragging');

        const id = draggable.getAttribute('data-id');
        const newStatus = draggable.parentElement.getAttribute('data-status');

        // Update jumlah tugas di kolom
        updateColumnCounts();

        // Simpan status baru ke database
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
                // Ubah tampilan kartu sesuai kolom
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

// Area drop zone tugas
droppables.forEach(droppable => {
    droppable.addEventListener('dragover', e => {
        e.preventDefault(); // Wajib supaya bisa lepas
        droppable.classList.add('drag-over');

        const draggingItem = document.querySelector('.dragging');
        if (draggingItem) {
            droppable.appendChild(draggingItem);
        }
    });

    droppable.addEventListener('dragleave', () => {
        droppable.classList.remove('drag-over');
    });

    droppable.addEventListener('drop', () => {
        droppable.classList.remove('drag-over');
    });
});

// Fungsi bantu tampilan
function updateColumnCounts() {
    document.querySelectorAll('.card-column').forEach(column => {
        const count = column.querySelectorAll('.draggable').length;
        const badge = column.querySelector('span.badge');
        if (badge) {
            badge.innerText = count;
        }
    });
}
