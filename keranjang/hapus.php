<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('pembeli');

$id_user      = (int) current_user()['id_user'];
$id_keranjang = (int) ($_GET['id'] ?? 0);

$stmt = mysqli_prepare($koneksi, 'DELETE FROM keranjang WHERE id_keranjang = ? AND id_user = ?');
mysqli_stmt_bind_param($stmt, 'ii', $id_keranjang, $id_user);
mysqli_stmt_execute($stmt);

set_flash('success', 'Item dihapus dari keranjang.');
redirect('keranjang/index.php');
