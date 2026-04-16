# 📚 Panduan Sintaks Lengkap To-Do List (PHP, Bootstrap, JS, CSS)

Halo! Panduan ini khusus dibuat untuk membedah sintaks **baris demi baris**, mempelajari apa arti setiap baris kodenya (PHP, Bootstrap, JavaScript, dan CSS), dan **bagaimana kode tersebut saling berhubungan satu sama lain**.

---

## 🐘 1. PHP Native (`index.php`)

PHP adalah bahasa pemrograman yang berjalan di **server** (bukan di browser). Tugasnya merakit HTML dan mengurus data sebelum dikirim ke layar kamu. Bayangkan PHP sebagai "dapur" restoran — user hanya melihat makanan yang sudah jadi (HTML), bukan proses masaknya.

---

### A. Koneksi ke Database

```php
$db = mysqli_connect('localhost', 'root', '', 'lat_todo') or die("Koneksi ke database gagal.");
```

| Bagian | Arti |
|---|---|
| `$db` | Variabel penampung "jalur" ke database. Nama `$db` bebas, tapi konvensinya pakai nama pendek. |
| `mysqli_connect(...)` | Fungsi bawaan PHP untuk membuka koneksi ke MySQL. Urutan parameternya **tidak boleh ditukar**. |
| `'localhost'` | Alamat server database. `localhost` artinya "di komputer yang sama". |
| `'root'` | Username MySQL. Default XAMPP pakai `root`. |
| `''` | Password MySQL. Kosong karena default XAMPP tidak ada password. |
| `'lat_todo'` | Nama database yang dipakai. |
| `or die(...)` | Kalau koneksi **gagal**, PHP langsung berhenti dan menampilkan pesan error ini. |

**Kenapa perlu ini?** Semua operasi baca/tulis data (SELECT, INSERT, UPDATE, DELETE) butuh jalur ini. Makanya `$db` dipakai berulang-ulang di baris-baris selanjutnya.

---

### B. Fungsi Alert (Pesan Notifikasi)

```php
function alert($type, $icon, $txt)
{
    return "
    <div class='alert alert-$type alert-dismissible fade show shadow-sm text-center'>
        <i class='fa-solid fa-$icon me-2'></i> $txt
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
}
```

| Bagian | Arti |
|---|---|
| `function alert(...)` | Membuat fungsi buatan sendiri bernama `alert`. Fungsi ini bisa dipanggil berkali-kali. |
| `$type, $icon, $txt` | **Parameter** fungsi — nilai yang dikirim saat fungsi dipanggil. |
| `return "..."` | Fungsi ini menghasilkan (mengembalikan) teks HTML berbentuk string. |
| `alert-$type` | PHP menyisipkan nilai `$type` langsung ke dalam teks HTML. Ini namanya **string interpolation**. Misalnya kalau `$type = 'success'` maka hasilnya `alert-success`. |
| `fa-$icon` | Sama seperti di atas, nama icon Font Awesome disisipkan dinamis. |

**Contoh pemanggilan:**
```php
alert('success', 'circle-check', 'Tugas berhasil ditambahkan.')
// Menghasilkan: <div class='alert alert-success ...'>
```

---

### C. Menangani Update Status (Lewat JavaScript)

```php
if (isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $id = (int) $_POST['id'];
    $status = mysqli_real_escape_string($db, $_POST['status']);
    mysqli_query($db, "UPDATE tasks SET status='$status' WHERE id=$id");
    exit();
}
```

| Bagian | Arti |
|---|---|
| `isset($_POST['action'])` | Mengecek apakah ada data POST bernama `action` yang dikirim. |
| `$_POST['action'] == 'update_status'` | Memastikan nilainya adalah `'update_status'` (bukan form tambah tugas). |
| `(int) $_POST['id']` | Mengubah paksa nilai `id` menjadi angka bulat (integer). Ini keamanan — mencegah karakter berbahaya masuk. |
| `mysqli_real_escape_string($db, ...)` | Membersihkan teks dari karakter berbahaya seperti tanda kutip yang bisa merusak query SQL (SQL Injection). |
| `"UPDATE tasks SET status='$status' WHERE id=$id"` | Perintah SQL untuk mengubah kolom `status` di tabel `tasks` pada baris dengan `id` tertentu. |
| `exit()` | Menghentikan eksekusi PHP saat ini juga. Penting agar PHP tidak mengirim balik halaman HTML penuh ke JavaScript. |

**Alur kerjanya:** JavaScript mengirim data via `fetch()` → PHP menerima di sini → update database → selesai.

---

### D. Tambah Tugas Baru (Form Submit)

```php
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
```

| Bagian | Arti |
|---|---|
| `isset($_POST['submit'])` | Mengecek apakah tombol submit (dengan `name="submit"`) sudah ditekan. |
| `$_POST['task_input']` | Mengambil teks dari input HTML `<input name="task_input">`. |
| `$_POST['tanggal']` | Mengambil nilai dari `<input name="tanggal">`. |
| `$_POST['priority']` | Mengambil nilai dari `<select name="priority">`. |
| `!empty($task)` | `!empty` artinya "tidak kosong". Tanda `!` membalik kondisi. |
| `&&` | Operator "DAN" — semua kondisi harus terpenuhi. |
| `"INSERT INTO tasks (...) VALUES (...)"` | Perintah SQL untuk **menambah baris baru** ke tabel `tasks`. |
| `header('location:index.php?notif=added')` | Perintah PHP untuk redirect (pindah halaman) ke `index.php` dengan parameter `notif=added`. |
| `exit()` | Wajib dipanggil setelah `header()` agar kode di bawahnya tidak ikut jalan. |

---

### E. Hapus Tugas

```php
if (isset($_GET['del_task'])) {
    $id = (int) $_GET['del_task'];
    mysqli_query($db, "DELETE FROM tasks WHERE id=$id");
    header('location:index.php?notif=deleted');
    exit();
}
```

| Bagian | Arti |
|---|---|
| `$_GET['del_task']` | Mengambil nilai dari URL. Contoh: `index.php?del_task=5` → `$_GET['del_task']` bernilai `5`. |
| `"DELETE FROM tasks WHERE id=$id"` | Perintah SQL untuk menghapus baris dengan `id` tertentu dari tabel `tasks`. |

**Perbedaan `$_POST` vs `$_GET`:**
- `$_POST` → data dikirim lewat **body** (tidak terlihat di URL). Dipakai untuk form.
- `$_GET` → data dikirim lewat **URL** (terlihat). Dipakai untuk link/tombol hapus.

---

### F. Notifikasi dari URL

```php
$notif = $_GET['notif'] ?? '';
if ($notif == 'added')
    $msg = alert('success', 'circle-check', 'Tugas berhasil ditambahkan.');
if ($notif == 'deleted')
    $msg = alert('warning', 'trash-can', 'Tugas berhasil dihapus.');
```

| Bagian | Arti |
|---|---|
| `$_GET['notif'] ?? ''` | Mengambil nilai `notif` dari URL. Tanda `??` adalah **null coalescing operator** — kalau `notif` tidak ada di URL, nilai defaultnya adalah string kosong `''`. |
| `$msg = alert(...)` | Menyimpan HTML notifikasi ke variabel `$msg` untuk dicetak nanti di HTML. |

---

### G. Ambil Semua Data Tugas

```php
$tasks = [];
$tasks_query = mysqli_query($db, "SELECT * FROM tasks ORDER BY id DESC");
while ($row = mysqli_fetch_array($tasks_query)) {
    $row['status'] = $row['status'] ?: 'belum';
    $tasks[] = $row;
}
```

| Bagian | Arti |
|---|---|
| `$tasks = []` | Membuat array kosong sebagai wadah data. |
| `"SELECT * FROM tasks ORDER BY id DESC"` | Ambil **semua kolom** (`*`) dari tabel `tasks`, diurutkan dari yang paling baru (`DESC` = descending / menurun). |
| `while ($row = mysqli_fetch_array(...))` | Loop yang terus berjalan selama masih ada baris data. Setiap iterasi, `$row` berisi satu baris data sebagai array. |
| `$row['status'] ?: 'belum'` | Kalau kolom `status` kosong/null, isi dengan string `'belum'`. Tanda `?:` adalah **Elvis operator**. |
| `$tasks[] = $row` | Menambahkan `$row` ke dalam array `$tasks`. Tanda `[]` tanpa index = tambah ke akhir array. |

---

### H. Mencetak Kolom Kanban

```php
$columns = [
    'belum' => ['Belum Dimulai', 'list-ul', 'text-secondary', 'bg-white text-dark', ''],
    'proses' => ['Proses', 'spinner fa-spin-pulse', 'text-primary', 'bg-white text-primary', '#e3f2fd'],
    'selesai' => ['Selesai', 'circle-check', 'text-success', 'bg-white text-success', '#e8f5e9']
];

foreach ($columns as $slug => $meta):
    $filtered_tasks = array_filter($tasks, fn($t) => strtolower($t['status']) == $slug);
```

| Bagian | Arti |
|---|---|
| `$columns = [...]` | Array asosiatif (key → value). Key-nya adalah nama status (`belum`, `proses`, `selesai`), valuenya array berisi konfigurasi tampilan kolom. |
| `foreach ($columns as $slug => $meta)` | Loop setiap kolom. `$slug` = key (nama status), `$meta` = array konfigurasinya. |
| `array_filter(...)` | Menyaring array `$tasks`, hanya ambil tugas yang statusnya cocok dengan `$slug`. |
| `fn($t) => ...` | **Arrow function** (fungsi singkat) yang tersedia sejak PHP 7.4. |
| `strtolower(...)` | Mengubah teks menjadi huruf kecil semua, agar perbandingan tidak case-sensitive. |

---

### I. Mencetak Kartu Tugas

```php
foreach ($filtered_tasks as $item):
    $priority_class = $item['priority'] == 'High' ? 'danger' : ($item['priority'] == 'Medium' ? 'warning' : 'success');
    $is_finished = ($slug == 'selesai');
```

| Bagian | Arti |
|---|---|
| `foreach ($filtered_tasks as $item)` | Loop setiap tugas di kolom ini. `$item` berisi satu tugas (array). |
| `... ? 'danger' : (... ? 'warning' : 'success')` | **Ternary operator** bersarang. Singkatnya: kalau High → `danger`, kalau Medium → `warning`, selain itu → `success`. |
| `$is_finished = ($slug == 'selesai')` | Variabel boolean: `true` kalau di kolom selesai, `false` kalau tidak. |

---
---

## 🅱️ 2. Bootstrap 5 (Class-class HTML)

Bootstrap adalah **framework CSS** yang menyediakan ratusan class siap pakai. Kamu hanya perlu menambahkan nama class yang benar ke elemen HTML, dan tampilan otomatis terbentuk.

---

### A. Layout & Posisi

#### `container` dan `container-fluid`
```html
<body class="container px-3 bg-light mt-4">
<div class="container-fluid p-0">
```
| Class | Arti |
|---|---|
| `container` | Membatasi lebar konten agar tidak molor ke pinggir layar. Lebar maksimalnya berubah sesuai ukuran layar. |
| `container-fluid` | Lebar penuh 100% layar, tanpa batas. |
| `px-3` | **Padding** kiri-kanan sebesar level 3 (sekitar 16px). `p` = padding, `x` = kiri & kanan. |
| `mt-4` | **Margin** atas sebesar level 4 (sekitar 24px). `m` = margin, `t` = top. |

#### Sistem Spasi Bootstrap
Bootstrap menggunakan skala 0–5 untuk spasi:

| Kode | Arti |
|---|---|
| `m` / `p` | margin / padding |
| `t` / `b` / `s` / `e` / `x` / `y` | top / bottom / start(kiri) / end(kanan) / kiri+kanan / atas+bawah |
| `0`–`5` | Ukuran: 0=0px, 1=4px, 2=8px, 3=16px, 4=24px, 5=48px |

Contoh: `mb-2` = margin-bottom 8px, `py-4` = padding atas+bawah 24px.

---

#### Flexbox di Bootstrap
```html
<div class="d-flex justify-content-between align-items-center mb-2">
```
| Class | Arti |
|---|---|
| `d-flex` | Mengaktifkan Flexbox. Elemen di dalamnya akan berjajar **horizontal** secara default. |
| `justify-content-between` | Mendistribusikan item ke ujung kiri dan kanan (ada jarak di tengah). |
| `align-items-center` | Menyelaraskan item secara **vertikal** ke tengah. |
| `gap-2` | Jarak antar elemen flex sebesar level 2 (8px). |
| `gap-4` | Jarak antar elemen flex sebesar level 4 (24px). |

---

### B. Komponen Bootstrap

#### Button
```html
<button class="btn btn-dark shadow-sm rounded-pill px-4">
```
| Class | Arti |
|---|---|
| `btn` | Class wajib untuk semua tombol Bootstrap. Tanpa ini, styling tidak muncul. |
| `btn-dark` | Warna tombol hitam. Variannya: `btn-primary` (biru), `btn-danger` (merah), `btn-success` (hijau), dll. |
| `shadow-sm` | Bayangan kecil di bawah tombol agar terlihat "mengambang". |
| `rounded-pill` | Tepi tombol sangat melengkung (seperti kapsul). |
| `px-4` | Padding kiri-kanan level 4 agar tombol tidak terlalu sempit. |

#### Badge (Label kecil)
```html
<span class="badge bg-white text-dark shadow-sm rounded-pill px-2">
```
| Class | Arti |
|---|---|
| `badge` | Menjadikan elemen sebagai "label" kecil. |
| `text-bg-danger` | Warna teks dan background sekaligus — merah untuk `danger`, kuning untuk `warning`, hijau untuk `success`. |
| `fw-semibold` | Ketebalan font semi-bold (agak tebal). `fw` = font-weight. |

#### Card
```html
<div class="card border-0 shadow-sm rounded-2">
    <div class="card-body p-2">
        <p class="card-text fw-semibold mb-2">
```
| Class | Arti |
|---|---|
| `card` | Komponen kotak berisi konten dengan border dan background putih. |
| `border-0` | Menghilangkan border default card. |
| `shadow-sm` | Bayangan kecil agar terlihat melayang. |
| `rounded-2` | Sudut sedikit melengkung (level 2). |
| `card-body` | Area dalam card yang punya padding. |
| `card-text` | Paragraf teks di dalam card. |

---

### C. Modal (Popup)

```html
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
            <div class="modal-body p-4">
            <div class="modal-footer border-0">
```

| Class / Atribut | Arti |
|---|---|
| `modal` | Menandai elemen ini sebagai komponen modal Bootstrap. |
| `fade` | Efek animasi muncul/hilang secara bertahap (fade in/out). |
| `id="addTaskModal"` | ID unik modal. ID ini dipakai oleh tombol pemicu (`data-bs-target="#addTaskModal"`). |
| `tabindex="-1"` | Mencegah modal mendapat fokus keyboard saat tersembunyi. |
| `modal-dialog` | Wrapper untuk mengatur posisi dialog. |
| `modal-dialog-centered` | Membuat modal muncul di tengah layar secara vertikal. |
| `modal-content` | Kotak putih yang berisi konten modal. |
| `modal-header` | Area judul di atas modal. |
| `modal-body` | Area konten utama modal. |
| `modal-footer` | Area tombol di bawah modal. |

**Cara memicu modal dari tombol:**
```html
<button data-bs-toggle="modal" data-bs-target="#addTaskModal">
```
- `data-bs-toggle="modal"` → memberitahu Bootstrap bahwa klik tombol ini akan membuka modal.
- `data-bs-target="#addTaskModal"` → ID modal yang akan dibuka.

---

### D. Form

```html
<input type="text" class="form-control form-control-lg bg-light" name="task_input" required>
<select name="priority" class="form-select bg-light" required>
<input type="date" class="form-control bg-light" name="tanggal" required>
```

| Class / Atribut | Arti |
|---|---|
| `form-control` | Styling Bootstrap untuk `<input>` dan `<textarea>`. Membuat kotak input terlihat rapi dengan border dan padding. |
| `form-control-lg` | Ukuran input lebih besar dari default. |
| `form-select` | Styling Bootstrap untuk `<select>` (dropdown). |
| `bg-light` | Background abu-abu muda. |
| `required` | Atribut HTML — input ini wajib diisi sebelum form bisa disubmit. |
| `name="..."` | Nama input, dipakai PHP untuk mengambil nilainya via `$_POST['nama']`. |

---

### E. Teks & Warna

| Class | Arti |
|---|---|
| `text-secondary` | Warna teks abu-abu (secondary). |
| `text-primary` | Warna teks biru (primary). |
| `text-success` | Warna teks hijau. |
| `text-danger` | Warna teks merah. |
| `text-muted` | Warna teks abu-abu pudar (untuk teks yang tidak terlalu penting). |
| `fw-semibold` | Font weight semi-bold. |
| `fw-bold` | Font weight bold. |
| `fs-5` / `fs-6` | Ukuran font. `fs-5` lebih besar dari `fs-6`. |
| `small` | Ukuran teks sedikit lebih kecil. |
| `text-decoration-line-through` | Teks dengan garis coret di tengahnya (strikethrough). |

---

### F. Overflow & Display

```html
<main class="d-flex gap-4 pb-4 overflow-x-auto align-items-start">
```

| Class | Arti |
|---|---|
| `overflow-x-auto` | Kalau konten melebihi lebar layar, tampilkan scrollbar horizontal. Penting untuk layout kanban di layar kecil. |
| `align-items-start` | Item flex disejajarkan ke atas (bukan tengah atau bawah). |
| `pb-4` | Padding bawah level 4. |
| `flex-grow-1` | Elemen ini mengambil sisa ruang yang tersedia secara horizontal/vertikal. |
| `flex-column` | Ubah arah flex menjadi vertikal (atas ke bawah). |

---
---

## ⚡ 3. JavaScript (`script.js`)

JavaScript berjalan di **browser** (bukan server). Tugasnya membuat halaman interaktif — merespons klik, drag, drop, dan berkomunikasi dengan server tanpa reload halaman.

---

### A. Mengambil Elemen HTML

```javascript
const draggables = document.querySelectorAll('.draggable');
const droppables = document.querySelectorAll('.droppable');
```

| Bagian | Arti |
|---|---|
| `const` | Deklarasi variabel yang nilainya **tidak bisa diubah** (constant). |
| `document` | Objek bawaan browser yang merepresentasikan seluruh halaman HTML. |
| `querySelectorAll('.draggable')` | Mencari **semua** elemen yang punya class `draggable`. Hasilnya adalah NodeList (mirip array). |
| `querySelector(...)` | Tanpa `All` — mencari **satu** elemen pertama yang cocok saja. |

**Analoginya:** Bayangkan `document` adalah buku HTML. `querySelectorAll` seperti `Ctrl+F` yang menandai semua hasil pencarian.

---

### B. Loop dan Event Listener

```javascript
draggables.forEach(draggable => {
    draggable.addEventListener('dragstart', () => {
        draggable.classList.add('dragging');
    });
});
```

| Bagian | Arti |
|---|---|
| `.forEach(...)` | Melakukan sesuatu pada **setiap** elemen dalam koleksi, satu per satu. |
| `draggable => { ... }` | **Arrow function** — cara singkat menulis fungsi. `draggable` adalah parameter (elemen saat ini). |
| `addEventListener('dragstart', ...)` | Memasang "pendengar" pada elemen. Kalau event `dragstart` terjadi (user mulai menarik elemen), jalankan fungsi di dalamnya. |
| `classList.add('dragging')` | Menambahkan class CSS `dragging` ke elemen. Ini yang memicu efek visual di CSS. |

**Event-event Drag & Drop:**
| Event | Kapan terjadi |
|---|---|
| `dragstart` | Saat user mulai menarik elemen |
| `dragend` | Saat user melepaskan elemen (di mana saja) |
| `dragover` | Saat elemen yang ditarik melayang di atas zona drop |
| `dragleave` | Saat elemen yang ditarik meninggalkan zona drop |
| `drop` | Saat elemen dilepaskan tepat di zona drop |

---

### C. Logika Saat Melepas (dragend)

```javascript
draggable.addEventListener('dragend', () => {
    draggable.classList.remove('dragging');
    const id = draggable.getAttribute('data-id');
    const newStatus = draggable.parentElement.getAttribute('data-status');
    updateColumnCounts();
    // ... kirim ke server
});
```

| Bagian | Arti |
|---|---|
| `classList.remove('dragging')` | Menghapus class `dragging` — efek visual kembali normal. |
| `getAttribute('data-id')` | Mengambil nilai atribut HTML kustom `data-id`. Di HTML ada `<div data-id="5">` → hasilnya `"5"`. |
| `draggable.parentElement` | Mengakses elemen **induk** dari kartu yang baru dilepas (yaitu elemen `.droppable` tempat kartu mendarat). |
| `getAttribute('data-status')` | Mengambil status kolom tempat kartu dilepas. Di HTML ada `<div data-status="proses">` → hasilnya `"proses"`. |

---

### D. Zona Drop (droppable)

```javascript
droppables.forEach(droppable => {
    droppable.addEventListener('dragover', e => {
        e.preventDefault();
        droppable.classList.add('drag-over');
        const draggingItem = document.querySelector('.dragging');
        if (draggingItem) {
            droppable.appendChild(draggingItem);
        }
    });
});
```

| Bagian | Arti |
|---|---|
| `e.preventDefault()` | Mencegah perilaku default browser. Secara default, browser **melarang** menjatuhkan elemen di sembarang tempat. Baris ini wajib agar `drop` bisa terjadi. |
| `droppable.classList.add('drag-over')` | Menambahkan class `drag-over` untuk efek visual (background berubah warna saat kartu melayang di atasnya). |
| `document.querySelector('.dragging')` | Mencari elemen yang **sedang** ditarik (yang punya class `dragging`). |
| `droppable.appendChild(draggingItem)` | Memindahkan kartu yang ditarik menjadi **anak** dari zona drop ini. Inilah yang membuat kartu "pindah kolom" secara visual. |

---

### E. Fetch API (Komunikasi Tanpa Reload)

```javascript
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
    // update tampilan
})
.catch(err => console.error('Gagal update status:', err));
```

**Fetch adalah cara JavaScript mengirim/menerima data dari server tanpa me-reload halaman.** Ini sering disebut AJAX.

| Bagian | Arti |
|---|---|
| `new FormData()` | Membuat objek "paket data" yang akan dikirim ke server. Mirip seperti mengisi formulir kosong. |
| `formData.append('action', 'update_status')` | Menambahkan field ke paket data. Ini seperti `<input name="action" value="update_status">`. |
| `fetch('index.php', { method: 'POST', body: formData })` | Mengirim paket data ke `index.php` menggunakan metode POST. |
| `.then(response => response.text())` | Setelah server merespons, ambil isinya sebagai teks. `.then()` artinya "kalau berhasil, lakukan ini". |
| `.then(() => { ... })` | `.then()` kedua — jalankan kode ini setelah respons teks diterima. |
| `.catch(err => console.error(...))` | Kalau ada error, cetak ke konsol browser. `.catch()` = penangkap error. |

**Alur lengkap:**
```
User drag kartu → dragend terjadi → JavaScript kirim fetch ke index.php
→ PHP terima, update database → PHP kirim respons
→ JavaScript terima respons → update tampilan kartu
```

---

### F. Update Tampilan Kartu

```javascript
.then(() => {
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
```

| Bagian | Arti |
|---|---|
| `draggable.querySelector('.card-text')` | Mencari elemen dengan class `card-text` yang ada **di dalam** elemen `draggable`. |
| `draggable.style.opacity = '0.75'` | Mengubah CSS opacity langsung via JavaScript. |
| `classList.add('text-muted')` | Menambahkan class Bootstrap `text-muted` agar teks berubah abu-abu. |
| `classList.add('text-decoration-line-through')` | Menambahkan class Bootstrap untuk efek teks dicoret. |
| `=== 'selesai'` | Perbandingan **strict equality** — nilai DAN tipenya harus sama persis. `===` lebih aman dari `==`. |

---

### G. Update Jumlah Badge Kolom

```javascript
function updateColumnCounts() {
    document.querySelectorAll('.kanban-column').forEach(column => {
        const count = column.querySelectorAll('.draggable').length;
        const badge = column.querySelector('span.badge');
        if (badge) {
            badge.innerText = count;
        }
    });
}
```

| Bagian | Arti |
|---|---|
| `function updateColumnCounts()` | Mendefinisikan fungsi yang bisa dipanggil kapan saja. |
| `column.querySelectorAll('.draggable').length` | Menghitung berapa banyak kartu (`.draggable`) yang ada di dalam kolom ini. `.length` = jumlah elemen. |
| `column.querySelector('span.badge')` | Mencari elemen `<span>` dengan class `badge` di dalam kolom. |
| `badge.innerText = count` | Mengganti isi teks badge dengan angka baru. |

---
---

## 🎨 4. CSS Kustom (`style.css`)

CSS kustom dipakai untuk hal-hal yang **tidak bisa atau sulit** dilakukan Bootstrap.

---

### A. Kolom Kanban

```css
.kanban-column {
    background-color: #ebecf0;
    min-width: 320px;
    max-width: 320px;
}
```

| Property | Arti |
|---|---|
| `background-color: #ebecf0` | Warna latar kolom (abu-abu muda). `#ebecf0` adalah kode warna **hex** (6 digit heksadesimal). |
| `min-width: 320px` | Lebar minimum kolom adalah 320px. Tidak boleh lebih kecil dari ini. |
| `max-width: 320px` | Lebar maksimum kolom adalah 320px. Tidak boleh lebih besar dari ini. |

**Kenapa keduanya diset sama?** Agar lebar kolom **selalu tetap 320px** — tidak mengecil atau membesar mengikuti konten. Ini yang Bootstrap tidak bisa lakukan dengan mudah karena Bootstrap pakai sistem persentase.

---

### B. Kartu yang Bisa Ditarik

```css
.card.draggable {
    cursor: grab;
    transition: box-shadow 0.2s, transform 0.1s;
}

.card.draggable:active {
    cursor: grabbing;
}
```

| Bagian | Arti |
|---|---|
| `.card.draggable` | Selector yang menargetkan elemen yang punya **dua class sekaligus**: `card` DAN `draggable`. |
| `cursor: grab` | Mengubah kursor mouse menjadi ikon "tangan menggenggam terbuka" saat hover. |
| `cursor: grabbing` | Mengubah kursor menjadi "tangan menggenggam tertutup" saat diklik/ditahan. `:active` = saat sedang diklik. |
| `transition: box-shadow 0.2s, transform 0.1s` | Membuat perubahan `box-shadow` berlangsung dalam 0.2 detik, dan perubahan `transform` dalam 0.1 detik. Tanpa ini, perubahan akan tiba-tiba tanpa animasi. |

---

### C. Efek Visual Saat Sedang Ditarik

```css
.card.draggable.dragging {
    opacity: 0.5;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2) !important;
    transform: scale(1.02);
}
```

Class `.dragging` ini **ditambahkan oleh JavaScript** saat kartu mulai ditarik, dan dihapus saat dilepas.

| Property | Arti |
|---|---|
| `opacity: 0.5` | Membuat kartu setengah transparan (50% tembus pandang). |
| `box-shadow: 0 5px 15px rgba(0,0,0,0.2)` | Bayangan yang lebih kuat. Format: `horizontal vertical blur warna`. `rgba(0,0,0,0.2)` = hitam 20% transparan. |
| `!important` | Memaksa property ini menang dari style lain yang mungkin bertentangan. |
| `transform: scale(1.02)` | Membesarkan kartu 2% dari ukuran asli — efek "mengambang" saat ditarik. |

---

### D. Area Drop Zone

```css
.droppable {
    min-height: 100px;
    transition: background-color 0.2s;
}

.droppable.drag-over {
    background-color: #dcdfe5 !important;
}
```

| Bagian | Arti |
|---|---|
| `min-height: 100px` | Area drop memiliki tinggi minimum 100px, agar kolom yang kosong tetap punya ruang untuk menjatuhkan kartu. |
| `transition: background-color 0.2s` | Perubahan warna background terjadi secara gradual dalam 0.2 detik. |
| `.droppable.drag-over` | Style khusus yang aktif saat class `drag-over` ditambahkan JavaScript. |
| `background-color: #dcdfe5 !important` | Background berubah lebih gelap — sinyal visual bahwa "kamu bisa melepaskan kartu di sini". |

---
---

## 🔗 5. Bagaimana Semua Saling Terhubung

```
┌─────────────┐     Kirim Form (POST)      ┌─────────────┐
│   Browser   │ ─────────────────────────► │  PHP Server  │
│   (HTML)    │ ◄───────────── Kirim HTML ─ │  (index.php) │
└─────────────┘                            └──────┬──────┘
       │                                          │
       │ JS menangkap                      SELECT/INSERT/
       │ event drag/drop                   UPDATE/DELETE
       │                                          │
       ▼                                          ▼
┌─────────────┐   fetch() tanpa reload    ┌─────────────┐
│ JavaScript  │ ─────────────────────────► │   MySQL DB   │
│ (script.js) │ ◄─── Respons singkat ───── │  (lat_todo)  │
└─────────────┘                            └─────────────┘
       │
       │ classList.add/remove
       ▼
┌─────────────┐
│  CSS Kustom │ → Visual berubah sesuai class
│  Bootstrap  │ → Tampilan awal dari class HTML
└─────────────┘
```

### Alur Lengkap "Drag Kartu ke Kolom Lain":

1. **HTML** merender kartu dengan `draggable="true"` dan `data-id="5"`.
2. **JavaScript** memasang listener `dragstart` → saat kartu ditarik, tambah class `.dragging`.
3. **CSS** melihat class `.dragging` → kartu jadi transparan dan sedikit membesar.
4. **JavaScript** listener `dragover` → `preventDefault()` + tambah class `.drag-over` ke kolom tujuan.
5. **CSS** melihat class `.drag-over` → background kolom berubah warna.
6. **JavaScript** `appendChild` → kartu berpindah secara visual ke kolom baru.
7. **JavaScript** listener `dragend` → hapus class `.dragging`, ambil `data-id` dan status baru.
8. **JavaScript** `fetch()` → kirim `id` dan status baru ke `index.php` tanpa reload.
9. **PHP** menerima request, jalankan `UPDATE tasks SET status=... WHERE id=...` ke MySQL.
10. **JavaScript** menerima respons → update tampilan kartu (opacity, strikethrough, dll).
11. **JavaScript** `updateColumnCounts()` → update angka badge di setiap kolom.

---

> **Inilah harmoni murni Web Programming:** HTML membentuk raga, CSS merias busana, JavaScript menyuntikkan nyawa interaksi, dan PHP/MySQL menjaga memori permanen di database. 🚀
