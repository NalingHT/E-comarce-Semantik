<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($string) {
    return htmlspecialchars((string) $string, ENT_QUOTES, 'UTF-8');
}

function rupiah($angka) {
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

function redirect($path) {
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function is_logged_in() {
    return isset($_SESSION['user']);
}

function cart_count($koneksi) {
    if (!is_logged_in()) {
        return 0;
    }
    $id_user = (int) $_SESSION['user']['id_user'];
    $stmt = mysqli_prepare($koneksi, 'SELECT COALESCE(SUM(jumlah), 0) AS total FROM keranjang WHERE id_user = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id_user);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    return (int) $row['total'];
}

function produk_gambar_url($gambar) {
    if (!$gambar) {
        return BASE_URL . '/assets/img/no-image.svg';
    }
    if (is_file(UPLOAD_DIR . $gambar)) {
        return BASE_URL . '/assets/uploads/' . rawurlencode($gambar);
    }
    if (is_file(__DIR__ . '/../assets/img/produk/' . $gambar)) {
        return BASE_URL . '/assets/img/produk/' . rawurlencode($gambar);
    }
    return BASE_URL . '/assets/img/no-image.svg';
}

function status_badge($status) {
    $map = [
        'menunggu' => 'warning',
        'dibayar'  => 'info',
        'dikirim'  => 'primary',
        'selesai'  => 'success',
        'batal'    => 'danger',
    ];
    $color = $map[$status] ?? 'secondary';
    return '<span class="badge text-bg-' . $color . '">' . e(ucfirst($status)) . '</span>';
}
