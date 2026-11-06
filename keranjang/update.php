<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('pembeli');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('keranjang/index.php');
}

$id_user      = (int) current_user()['id_user'];
$id_keranjang = (int) ($_POST['id_keranjang'] ?? 0);
$jumlah       = (int) ($_POST['jumlah'] ?? 1);

$stmt = mysqli_prepare($koneksi,
    'SELECT k.id_keranjang, p.stok FROM keranjang k
     JOIN produk p ON p.id_produk = k.id_produk
     WHERE k.id_keranjang = ? AND k.id_user = ?');
mysqli_stmt_bind_param($stmt, 'ii', $id_keranjang, $id_user);
mysqli_stmt_execute($stmt);
$item = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$item) {
    set_flash('danger', 'Item keranjang tidak ditemukan.');
    redirect('keranjang/index.php');
}

if ($jumlah < 1) {
    $stmt = mysqli_prepare($koneksi, 'DELETE FROM keranjang WHERE id_keranjang = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id_keranjang);
    mysqli_stmt_execute($stmt);
    set_flash('success', 'Item dihapus dari keranjang.');
    redirect('keranjang/index.php');
}

if ($jumlah > (int) $item['stok']) {
    set_flash('danger', 'Jumlah melebihi stok tersedia (' . $item['stok'] . ').');
    redirect('keranjang/index.php');
}

$stmt = mysqli_prepare($koneksi, 'UPDATE keranjang SET jumlah = ? WHERE id_keranjang = ?');
mysqli_stmt_bind_param($stmt, 'ii', $jumlah, $id_keranjang);
mysqli_stmt_execute($stmt);

set_flash('success', 'Keranjang diperbarui.');
redirect('keranjang/index.php');
