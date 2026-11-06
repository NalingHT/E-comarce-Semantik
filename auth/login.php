<?php
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Email dan password wajib diisi.';
    } else {
        $stmt = mysqli_prepare($koneksi, 'SELECT id_user, nama, email, password, role FROM users WHERE email = ?');
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id_user' => (int) $user['id_user'],
                'nama'    => $user['nama'],
                'email'   => $user['email'],
                'role'    => $user['role'],
            ];
            set_flash('success', 'Selamat datang, ' . $user['nama'] . '!');
            redirect('index.php');
        }
        $errors[] = 'Email atau password salah.';
    }
}

$page_title = 'Login';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="card-title mb-4 text-center">Login</h4>
                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endforeach; ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required
                               value="<?= e($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Login</button>
                </form>
                <p class="text-center mt-3 mb-0">
                    Belum punya akun? <a href="<?= BASE_URL ?>/auth/register.php">Daftar di sini</a>
                </p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
