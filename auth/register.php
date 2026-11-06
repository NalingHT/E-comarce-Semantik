<?php
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';
    $no_hp    = trim($_POST['no_hp'] ?? '');
    $alamat   = trim($_POST['alamat'] ?? '');
    $role     = $_POST['role'] ?? 'pembeli';

    if ($nama === '' || $email === '' || $password === '') {
        $errors[] = 'Nama, email, dan password wajib diisi.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }
    if ($password !== $konfirmasi) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }
    if (!in_array($role, ['pembeli', 'penjual'], true)) {
        $role = 'pembeli';
    }

    if (!$errors) {
        $stmt = mysqli_prepare($koneksi, 'SELECT id_user FROM users WHERE email = ?');
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
            $errors[] = 'Email sudah terdaftar. Silakan gunakan email lain.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($koneksi, 'INSERT INTO users (nama, email, password, no_hp, alamat, role) VALUES (?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'ssssss', $nama, $email, $hash, $no_hp, $alamat, $role);
        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', 'Registrasi berhasil! Silakan login.');
            redirect('auth/login.php');
        }
        $errors[] = 'Registrasi gagal. Silakan coba lagi.';
    }
}

$page_title = 'Registrasi';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="card-title mb-4 text-center">Registrasi Akun</h4>
                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endforeach; ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" required
                               value="<?= e($_POST['nama'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required
                               value="<?= e($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="konfirmasi" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. HP (opsional)</label>
                        <input type="text" name="no_hp" class="form-control"
                               value="<?= e($_POST['no_hp'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat (opsional)</label>
                        <textarea name="alamat" class="form-control" rows="2"><?= e($_POST['alamat'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Daftar sebagai</label>
                        <select name="role" class="form-select">
                            <option value="pembeli" <?= ($_POST['role'] ?? '') === 'pembeli' ? 'selected' : '' ?>>Pembeli</option>
                            <option value="penjual" <?= ($_POST['role'] ?? '') === 'penjual' ? 'selected' : '' ?>>Penjual</option>
                        </select>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Daftar</button>
                </form>
                <p class="text-center mt-3 mb-0">
                    Sudah punya akun? <a href="<?= BASE_URL ?>/auth/login.php">Login di sini</a>
                </p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
