<?php
session_start();

/**
 * Inisialisasi daftar tugas
 */
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [
        ['nama' => 'Belajar PHP dasar', 'selesai' => false],
        ['nama' => 'Mengerjakan skripsi', 'selesai' => false],
        ['nama' => 'Olahraga sore', 'selesai' => false],
        ['nama' => 'Baca buku teknologi', 'selesai' => false]
    ];
}

$aksi = null; // untuk catat aksi terakhir

// --- Fungsi ---
function tambahTugas($nama) {
    global $aksi;
    if (!empty($nama)) {
        $_SESSION['tasks'][] = ['nama' => $nama, 'selesai' => false];
        $aksi = "Tambah tugas: $nama";
    }
}

function toggleTugas($index) {
    global $aksi;
    if (isset($_SESSION['tasks'][$index])) {
        $_SESSION['tasks'][$index]['selesai'] = !$_SESSION['tasks'][$index]['selesai'];
        $status = $_SESSION['tasks'][$index]['selesai'] ? "Selesai" : "Belum selesai";
        $aksi = "Ubah status tugas: " . $_SESSION['tasks'][$index]['nama'] . " → $status";
    }
}

function hapusTugas($index) {
    global $aksi;
    if (isset($_SESSION['tasks'][$index])) {
        $aksi = "Hapus tugas: " . $_SESSION['tasks'][$index]['nama'];
        array_splice($_SESSION['tasks'], $index, 1);
    }
}

function editTugas($index, $nama) {
    global $aksi;
    if (isset($_SESSION['tasks'][$index]) && !empty($nama)) {
        $aksi = "Edit tugas: " . $_SESSION['tasks'][$index]['nama'] . " → $nama";
        $_SESSION['tasks'][$index]['nama'] = $nama;
    }
}

// --- Aksi Form ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tugas'])) {
        tambahTugas($_POST['tugas']);
    } elseif (isset($_POST['toggle'])) {
        toggleTugas($_POST['toggle']);
    } elseif (isset($_POST['hapus'])) {
        hapusTugas($_POST['hapus']);
    } elseif (isset($_POST['edit_index']) && isset($_POST['edit_nama'])) {
        editTugas($_POST['edit_index'], $_POST['edit_nama']);
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?aksi=" . urlencode($aksi));
    exit;
}

// --- Render daftar tugas ---
function tampilkanDaftar($tasks) {
    foreach ($tasks as $i => $task) {
        $checked = $task['selesai'] ? 'checked' : '';
        $class = $task['selesai'] ? 'text-decoration-line-through text-muted' : '';

        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
        echo '<div class="d-flex align-items-center">';

        // Checkbox toggle
        echo '<form method="post" class="me-2">';
        echo '<input type="hidden" name="toggle" value="' . $i . '">';
        echo '<input type="checkbox" class="form-check-input" onchange="this.form.submit()" ' . $checked . '>';
        echo '</form>';

        // Nama tugas (atau form edit jika sedang edit)
        if (isset($_GET['edit']) && $_GET['edit'] == $i) {
            echo '<form method="post" class="d-flex">';
            echo '<input type="hidden" name="edit_index" value="' . $i . '">';
            echo '<input type="text" name="edit_nama" value="' . htmlspecialchars($task['nama']) . '" class="form-control me-2" required>';
            echo '<button type="submit" class="btn btn-primary btn-sm">Simpan</button>';
            echo '</form>';
        } else {
            echo '<span class="' . $class . '">' . htmlspecialchars($task['nama']) . '</span>';
        }

        echo '</div>';

        // Tombol Edit & Hapus
        if (!(isset($_GET['edit']) && $_GET['edit'] == $i)) {
            echo '<div>';
            echo '<a href="?edit=' . $i . '" class="btn btn-sm btn-warning me-2">Edit</a>';
            echo '<form method="post" style="display:inline">';
            echo '<input type="hidden" name="hapus" value="' . $i . '">';
            echo '<button type="submit" class="btn btn-sm btn-danger">Hapus</button>';
            echo '</form>';
            echo '</div>';
        }

        echo '</li>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Aplikasi To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="card-title text-center mb-4">📋 Aplikasi To-Do List</h1>

            <!-- Form Tambah Tugas -->
            <form method="post" class="input-group mb-3">
                <input type="text" name="tugas" class="form-control" placeholder="Tambah tugas baru..." required>
                <button class="btn btn-success" type="submit">Tambah</button>
            </form>

            <!-- Daftar Tugas -->
            <ul class="list-group">
                <?php tampilkanDaftar($_SESSION['tasks']); ?>
            </ul>
        </div>
    </div>
</div>

<!-- Script Debug Console -->
<script>
    // Ambil data tasks dari PHP
    let tasks = <?php echo json_encode($_SESSION['tasks']); ?>;
    console.log("📋 Daftar tugas saat ini:", tasks);

    // Ambil aksi terakhir dari parameter GET (kalau ada)
    <?php if (isset($_GET['aksi'])): ?>
        console.log("✅ Aksi terakhir:", "<?php echo $_GET['aksi']; ?>");
    <?php endif; ?>
</script>
</body>
</html>
