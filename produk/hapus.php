<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
require_role(['penjual', 'admin']);

$user = current_user();
$id   = (int) ($_GET['id'] ?? 0);

$stmt = mysqli_prepare($koneksi, 'SELECT * FROM produk WHERE id_produk = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$produk = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$produk) {
    set_flash('danger', 'Produk tidak ditemukan.');
    redirect('produk/saya.php');
}

if ($user['role'] !== 'admin' && (int) $produk['id_penjual'] !== (int) $user['id_user']) {
    set_flash('danger', 'Anda hanya dapat menghapus produk milik sendiri.');
    redirect('produk/saya.php');
}

$stmt = mysqli_prepare($koneksi, 'DELETE FROM produk WHERE id_produk = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);

if (mysqli_stmt_execute($stmt)) {
    hapus_gambar($produk['gambar']);
    set_flash('success', 'Produk berhasil dihapus.');
} else {
    set_flash('danger', 'Produk tidak dapat dihapus karena sudah ada dalam pesanan. Kosongkan stoknya saja.');
}

redirect('produk/saya.php');
