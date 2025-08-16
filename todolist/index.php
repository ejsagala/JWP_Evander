<?php
session_start();

/* =========================================
   Inisialisasi & Normalisasi Data Session
   ========================================= */
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

/**
 * Pastikan setiap task punya key 'name' dan 'status'.
 * Juga migrasi dari data lama yang mungkin pakai 'nama'.
 */
function normalizeTasks(): void {
    foreach ($_SESSION['tasks'] as $k => $t) {
        // migrasi key 'nama' -> 'name'
        if (!isset($t['name']) && isset($t['nama'])) {
            $t['name'] = $t['nama'];
            unset($t['nama']);
        }
        // default status = false
        if (!isset($t['status'])) {
            $t['status'] = false;
        }
        // pastikan 'name' ada
        if (!isset($t['name'])) {
            $t['name'] = '';
        }
        $_SESSION['tasks'][$k] = $t;
    }
}
normalizeTasks();

/* ======================
   Handlers (POST only)
   ====================== */

// Tambah todo
if (isset($_POST['task_add'])) {
    $task = trim($_POST['task_add']);
    if ($task !== '') {
        $_SESSION['tasks'][] = ['name' => $task, 'status' => false];
    }
    header('Location: index.php'); exit;
}

// Toggle status
if (isset($_POST['toggle_status'])) {
    $i = (int) $_POST['toggle_status'];
    if (isset($_SESSION['tasks'][$i])) {
        $_SESSION['tasks'][$i]['status'] = !$_SESSION['tasks'][$i]['status'];
    }
    header('Location: index.php'); exit;
}

// Edit todo
if (isset($_POST['edit_task'], $_POST['edit_index'])) {
    $i = (int) $_POST['edit_index'];
    $new = trim($_POST['edit_task']);
    if ($new !== '' && isset($_SESSION['tasks'][$i])) {
        $_SESSION['tasks'][$i]['name'] = $new;
    }
    header('Location: index.php'); exit;
}

// Hapus todo
if (isset($_POST['delete'])) {
    $i = (int) $_POST['delete'];
    if (isset($_SESSION['tasks'][$i])) {
        unset($_SESSION['tasks'][$i]);
        $_SESSION['tasks'] = array_values($_SESSION['tasks']); // reindex
    }
    header('Location: index.php'); exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Aplikasi To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow p-4">
        <h2 class="text-center mb-4">📝To-Do List</h2>

        <!-- Form Tambah -->
        <form method="post" class="d-flex mb-3">
            <input type="text" name="task_add" class="form-control me-2" placeholder="Masukkan tugas baru..." required>
            <button type="submit" class="btn btn-primary">Tambah</button>
        </form>

        <!-- Daftar Todo -->
        <ul class="list-group">
            <?php foreach ($_SESSION['tasks'] as $index => $task): 
                // aman dari data lama
                $name   = htmlspecialchars($task['name'] ?? '', ENT_QUOTES, 'UTF-8');
                $done   = !empty($task['status']);
                $checked= $done ? 'checked' : '';
                $badge  = $done
                    ? '<span class="badge bg-success ms-2">✔ Sudah Selesai</span>'
                    : '<span class="badge bg-warning text-dark ms-2">⌛ Belum Selesai</span>';
            ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <form method="post" class="d-flex align-items-center w-100">
                        <!-- Checkbox Status -->
                        <input type="checkbox"
                               name="toggle_status"
                               value="<?= $index ?>"
                               class="form-check-input me-2"
                               onchange="this.form.submit()" <?= $checked ?>>

                        <!-- Teks todo -->
                        <span class="task-text flex-grow-1 <?= $done ? 'text-decoration-line-through text-muted' : '' ?>"
                              id="task-text-<?= $index ?>">
                            <?= $name ?>
                        </span>

                        <!-- Input edit (hidden + disabled saat tidak edit) -->
                        <input type="text"
                               name="edit_task"
                               value="<?= $name ?>"
                               class="form-control d-none flex-grow-1"
                               id="task-input-<?= $index ?>"
                               disabled
                               onblur="this.form.submit()"
                               onkeydown="if(event.key==='Enter'){this.form.submit();}">
                        <input type="hidden" name="edit_index" value="<?= $index ?>">

                        <!-- Status badge -->
                        <?= $badge ?>

                        <!-- Tombol Edit -->
                        <button type="button"
                                class="btn btn-warning btn-sm ms-2"
                                onclick="enableEdit(<?= $index ?>)">
                            Edit
                        </button>

                        <!-- Tombol Hapus -->
                        <button type="submit"
                                name="delete"
                                value="<?= $index ?>"
                                class="btn btn-danger btn-sm ms-2">
                            Hapus
                        </button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<!-- Script Edit Inline -->
<script>
function enableEdit(index) {
    const textEl  = document.getElementById('task-text-' + index);
    const inputEl = document.getElementById('task-input-' + index);

    textEl.classList.add('d-none');          // sembunyikan teks
    inputEl.classList.remove('d-none');      // tampilkan input
    inputEl.disabled = false;                // aktifkan input
    inputEl.focus();                         // fokus & select
    inputEl.select();
}
</script>
</body>
</html>
