<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$total_pengguna = (int) mysqli_fetch_assoc(mysqli_query($koneksi, 'SELECT COUNT(*) AS t FROM users'))['t'];
$total_produk   = (int) mysqli_fetch_assoc(mysqli_query($koneksi, 'SELECT COUNT(*) AS t FROM produk'))['t'];
$total_kategori = (int) mysqli_fetch_assoc(mysqli_query($koneksi, 'SELECT COUNT(*) AS t FROM kategori'))['t'];
$total_pesanan  = (int) mysqli_fetch_assoc(mysqli_query($koneksi, 'SELECT COUNT(*) AS t FROM pesanan'))['t'];

$page_title = 'Dashboard Admin';
require_once __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-4">Dashboard Admin</h4>
<div class="row g-3">
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm text-center border-primary">
            <div class="card-body">
                <i class="bi bi-people fs-1 text-primary"></i>
                <h3 class="mb-0"><?= $total_pengguna ?></h3>
                <p class="text-muted mb-2">Pengguna</p>
                <a href="<?= BASE_URL ?>/admin/pengguna.php" class="btn btn-outline-primary btn-sm">Kelola</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm text-center border-success">
            <div class="card-body">
                <i class="bi bi-box-seam fs-1 text-success"></i>
                <h3 class="mb-0"><?= $total_produk ?></h3>
                <p class="text-muted mb-2">Produk</p>
                <a href="<?= BASE_URL ?>/produk/saya.php" class="btn btn-outline-success btn-sm">Kelola</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm text-center border-warning">
            <div class="card-body">
                <i class="bi bi-tags fs-1 text-warning"></i>
                <h3 class="mb-0"><?= $total_kategori ?></h3>
                <p class="text-muted mb-2">Kategori</p>
                <a href="<?= BASE_URL ?>/admin/kategori.php" class="btn btn-outline-warning btn-sm">Kelola</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm text-center border-info">
            <div class="card-body">
                <i class="bi bi-receipt fs-1 text-info"></i>
                <h3 class="mb-0"><?= $total_pesanan ?></h3>
                <p class="text-muted mb-2">Pesanan</p>
                <a href="<?= BASE_URL ?>/admin/pesanan.php" class="btn btn-outline-info btn-sm">Lihat</a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
