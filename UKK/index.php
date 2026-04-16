<?php

// Koneksi ke database
try {
    $db = mysqli_connect('localhost', 'root', '', 'lat_todo');
} catch (mysqli_sql_exception $e) {
    die("Koneksi ke database gagal... hehe");
}

// Fungsi buat nampilin pesan alert
function alert($type, $icon, $txt)
{
    return "
    <div class='alert alert-$type alert-dismissible fade show shadow-sm text-center'>
        <i class='fa-solid fa-$icon me-2'></i> $txt
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
}

$msg = "";

// Update status tugas lewat background
if (isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $id = (int) $_POST['id'];
    $status = mysqli_real_escape_string($db, $_POST['status']);
    mysqli_query($db, "UPDATE tasks SET status='$status' WHERE id=$id");
    exit();
}

// Tambah tugas baru
if (isset($_POST['submit'])) {
    $task = mysqli_real_escape_string($db, $_POST['task_input']);
    $tgl = $_POST['tanggal'];
    $prio = $_POST['priority'];

    if (!empty($task) && !empty($tgl) && !empty($prio)) {
        mysqli_query($db, "INSERT INTO tasks (task, tanggal, priority) VALUES ('$task', '$tgl', '$prio')");
        header('location:index.php?notif=added');
        exit();
    } else {
        $msg = alert('danger', 'triangle-exclamation', 'Harap isi semua kolom tugas.');
    }
}

// Hapus tugas
if (isset($_GET['del_task'])) {
    $id = (int) $_GET['del_task'];
    mysqli_query($db, "DELETE FROM tasks WHERE id=$id");
    header('location:index.php?notif=deleted');
    exit();
}

// Ambil data tugas dan notifikasi
$notif = $_GET['notif'] ?? '';
if ($notif == 'added')
    $msg = alert('success', 'circle-check', 'Tugas berhasil ditambahkan.');
if ($notif == 'deleted')
    $msg = alert('warning', 'trash-can', 'Tugas berhasil dihapus.');

$tasks = [];
$tasks_query = mysqli_query($db, "SELECT * FROM tasks ORDER BY id DESC");
while ($row = mysqli_fetch_array($tasks_query)) {
    $row['status'] = $row['status'] ?: 'belum';
    $tasks[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FIQAR | To Do List</title>
    <!-- Ambil Bootstrap dan Icon -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="container px-3 bg-light mt-5">
    <!-- Bagian notifikasi -->
    <div class="container-fluid p-0">
        <?= $msg ?>
    </div>

    <!-- Bagian judul daftar tugas -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h3 class="mb-0">To Do List</h3>
        <button type="button" class="btn btn-dark shadow-sm rounded-pill " data-bs-toggle="modal"
            data-bs-target="#addTaskModal">
            <i class="fa-solid fa-plus"></i> Add
        </button>
    </div>

    <!-- Area papan to do list -->
    <div class="d-flex gap-4 pb-4 overflow-x-auto align-items-start">
        <?php
        $columns = [
            'belum' => ['Belum Dikerjakan', 'list-ul', 'text-secondary', 'bg-white text-dark', ''],
            'proses' => ['Proses', 'spinner fa-spin-pulse', 'text-primary', 'bg-white text-primary', '#e3f2fd'],
            'selesai' => ['Selesai', 'circle-check', 'text-success', 'bg-white text-success', '#e8f5e9']
        ];

        foreach ($columns as $slug => $meta):
            $filtered_tasks = array_filter($tasks, fn($t) => strtolower($t['status']) == $slug);
            ?>
            <!-- Kolom status: <?= $meta[0] ?> -->

            <section class="card-column rounded-3 p-3 d-flex flex-column gap-2 shadow-sm"
                style="background-color: <?= $meta[4] ?: '#fff' ?>;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="fw-semibold <?= $meta[2] ?> mb-0 fs-5">
                        <i class="fa-solid fa-<?= $meta[1] ?> me-2"></i><?= $meta[0] ?>
                    </h5>
                    <span class="badge <?= $meta[3] ?> shadow-sm rounded-pill px-2"><?= count($filtered_tasks) ?></span>
                </div>

                <div class="droppable flex-grow-1 p-1 rounded-2 d-flex flex-column gap-2" data-status="<?= $slug ?>">
                    <?php foreach ($filtered_tasks as $item):
                        $priority_class = $item['priority'] == 'High' ? 'danger' : ($item['priority'] == 'Medium' ? 'warning' : 'success');
                        $is_finished = ($slug == 'selesai');
                        ?>
                        <!-- Task Card -->
                        <div class="card draggable w-100 border-0 shadow-sm rounded-2 <?= $is_finished ? 'text-muted' : '' ?>"
                            style="<?= $is_finished ? 'opacity:0.75;' : '' ?>" draggable="true" data-id="<?= $item['id'] ?>">
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span
                                        class="badge text-bg-<?= $priority_class ?> fw-semibold shadow-sm"><?= $item['priority'] ?></span>
                                    <a href="index.php?del_task=<?= $item['id'] ?>" class="text-danger p-1"
                                        onclick="return confirm('Hapus tugas?');">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </div>
                                <p
                                    class="card-text fw-semibold mb-2 <?= $is_finished ? 'text-decoration-line-through' : 'text-dark' ?> fs-6">
                                    <?= htmlspecialchars($item['task']) ?>
                                </p>
                                <div class="text-secondary small fw-semibold">
                                    <i
                                        class="fa-regular fa-calendar-days me-2"></i><?= date('d M Y', strtotime($item['tanggal'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>

    <!-- Popup Tambah Tugas -->
    <div class="modal fade" id="addTaskModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="POST">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold">Buat Tugas Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Nama Tugas</label>
                            <input type="text" class="form-control form-control-lg bg-light"
                                placeholder="Apa tugas Anda?" name="task_input" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-semibold">Tenggat Waktu</label>
                                <input type="date" class="form-control bg-light" name="tanggal" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-semibold">Prioritas</label>
                                <select name="priority" class="form-select bg-light" required>
                                    <option value="" disabled selected>Pilih...</option>
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary rounded-pill"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="submit" class="btn btn-dark px-4 rounded-pill">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- File Javascript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>

</html>