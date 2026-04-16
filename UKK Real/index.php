<?php
$koneksi = mysqli_connect("localhost", "root", "", "ukk2026_todolist");

// tambah task
if (isset($_POST['add_task'])) {
    $task     = $_POST['task'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];

    if (!empty($task) && !empty($priority) && !empty($due_date)) {

        // mengambil urutan terakhir untuk menentukan posisi task baru
        $q = mysqli_query($koneksi, "SELECT MAX(sort_order) as max_order FROM task");
        $d = mysqli_fetch_assoc($q);
        $next = $d['max_order'] + 1;

        mysqli_query($koneksi, "INSERT INTO task (task, priority, due_date, status, sort_order) 
        VALUES ('$task', '$priority', '$due_date', '0', '$next')");

        echo "<script>alert('Task berhasil ditambahkan')</script>";
    } else {
        echo "<script>alert('Task gagal ditambahkan')</script>";
        header("location: index.php");
    }
}

// task selesai
if (isset($_GET['complete'])) {
    $id = $_GET['complete'];
    mysqli_query($koneksi, "UPDATE task SET status = '1' WHERE id = '$id'");
    echo "<script>
            alert('Task berhasil diselesaikan');
            window.location='index.php';
          </script>";
}

// hapus task
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($koneksi, "DELETE FROM task WHERE id = '$id'");
    echo "<script>
            alert('Task berhasil dihapus');
            window.location='index.php';
          </script>";
}

// tombol naik
if (isset($_GET['up'])) {
    $id = $_GET['up'];

    // mengambil data task yang dipilih
    $current = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT id, sort_order FROM task WHERE id='$id'"));

    // mencari task di atasnya
    $above = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT id, sort_order FROM task 
         WHERE sort_order < {$current['sort_order']}
         ORDER BY sort_order DESC LIMIT 1"));

    // jika ada task di atas maka tukar posisi
    if ($above) {
        mysqli_query($koneksi,
        "UPDATE task SET sort_order={$above['sort_order']} WHERE id={$current['id']}");
        mysqli_query($koneksi,
        "UPDATE task SET sort_order={$current['sort_order']} WHERE id={$above['id']}");
    }

    header("location:index.php");
}

// tombol turun
if (isset($_GET['down'])) {
    $id = $_GET['down'];
    // mengambil data task yang dipilih
    $current = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT id, sort_order FROM task WHERE id='$id'"));
    // mencari task di bawahnya
    $below = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT id, sort_order FROM task 
         WHERE sort_order > {$current['sort_order']}
         ORDER BY sort_order ASC LIMIT 1"));
    // jika ada task di bawah maka tukar posisi
    if ($below) {

        mysqli_query($koneksi,
        "UPDATE task SET sort_order={$below['sort_order']} WHERE id={$current['id']}");

        mysqli_query($koneksi,
        "UPDATE task SET sort_order={$current['sort_order']} WHERE id={$below['id']}");
    }

    header("location:index.php");
}

// menampilkan task
// diurutkan berdasarkan posisi manual
$result = mysqli_query($koneksi, "SELECT * FROM task ORDER BY sort_order ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Todo List | UKK RPL 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>

<body>
<div class="container mt-4">
    <h2 class="text-center">Aplikasi To Do List</h2>

    <form action="" method="post" class="border rounded bg-light p-2">
        <label class="form-label">Nama Task</label>
        <input type="text" name="task" class="form-control" placeholder="Masukan Task Baru" autocomplete="off" autofocus required>

        <label class="form-label">Prioritas</label>
        <select name="priority" class="form-control" required>
            <option value="">--Pilih Prioritas--</option>
            <option value="1">Low</option>
            <option value="2">Medium</option>
            <option value="3">High</option>
        </select>

        <label class="form-label">Tanggal</label>
        <input type="date" name="due_date" class="form-control" value="<?php echo date('Y-m-d') ?>" required>

        <button class="btn btn-primary w-100 mt-2" name="add_task">Tambah</button>
    </form>

    <hr>
    <hr>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>Task</th>
                <th>Priority</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                $no = 1;
                while ($row = mysqli_fetch_assoc($result)) {
            ?>
            <tr>
                <td><?php echo $no++ ?></td>
                <td><?php echo $row['task'] ?></td>
                <td><?php
                    if ($row['priority'] == 1) {
                        echo "Low";
                    } elseif ($row['priority'] == 2) {
                        echo "Medium";
                    } else {
                        echo "High";
                    }
                ?></td>
                <td><?php echo $row['due_date'] ?></td>
                <td><?php
                    if ($row['status'] == 0) {
                        echo "<span style='color: red;'>Belum Selesai</span>";
                    } else {
                        echo "<span style='color: green;'>Selesai</span>";
                    }
                ?></td>
                <td>

                    <!-- tombol naik -->
                    <a href="?up=<?php echo $row['id'] ?>" class="btn btn-secondary btn-sm">
                        ↑
                    </a>

                    <!-- tombol turun -->
                    <a href="?down=<?php echo $row['id'] ?>" class="btn btn-secondary btn-sm">
                        ↓
                    </a>

                    <?php if ($row['status'] == 0) { ?>
                        <a href="?complete=<?php echo $row['id'] ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-check"></i> Selesai
                        </a>
                    <?php } ?>

                    <a href="?delete=<?php echo $row['id'] ?>" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash"></i> Hapus
                    </a>

                </td>
            </tr>
            <?php
                }
            }
            ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>