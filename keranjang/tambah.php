<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('pembeli');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('produk/index.php');
}

$id_user   = (int) current_user()['id_user'];
$id_produk = (int) ($_POST['id_produk'] ?? 0);
$jumlah    = (int) ($_POST['jumlah'] ?? 1);

if ($jumlah < 1) {
    $jumlah = 1;
}

$stmt = mysqli_prepare($koneksi, 'SELECT * FROM produk WHERE id_produk = ?');
mysqli_stmt_bind_param($stmt, 'i', $id_produk);
mysqli_stmt_execute($stmt);
$produk = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$produk) {
    set_flash('danger', 'Produk tidak ditemukan.');
    redirect('produk/index.php');
}

if ((int) $produk['id_penjual'] === $id_user) {
    set_flash('danger', 'Anda tidak dapat membeli produk sendiri.');
    redirect('produk/detail.php?id=' . $id_produk);
}

if ((int) $produk['stok'] <= 0) {
    set_flash('danger', 'Stok produk habis.');
    redirect('produk/detail.php?id=' . $id_produk);
}

$stmt = mysqli_prepare($koneksi, 'SELECT jumlah FROM keranjang WHERE id_user = ? AND id_produk = ?');
mysqli_stmt_bind_param($stmt, 'ii', $id_user, $id_produk);
mysqli_stmt_execute($stmt);
$item = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$jumlah_di_keranjang = $item ? (int) $item['jumlah'] : 0;

if ($jumlah_di_keranjang + $jumlah > (int) $produk['stok']) {
    set_flash('danger', 'Jumlah melebihi stok tersedia (stok: ' . $produk['stok'] . ', sudah di keranjang: ' . $jumlah_di_keranjang . ').');
    redirect('produk/detail.php?id=' . $id_produk);
}

$stmt = mysqli_prepare($koneksi,
    'INSERT INTO keranjang (id_user, id_produk, jumlah) VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE jumlah = jumlah + VALUES(jumlah)');
mysqli_stmt_bind_param($stmt, 'iii', $id_user, $id_produk, $jumlah);
mysqli_stmt_execute($stmt);

set_flash('success', 'Produk ditambahkan ke keranjang.');
redirect('keranjang/index.php');
