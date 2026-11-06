<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/functions.php';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($page_title) ? e($page_title) . ' - ' : '' ?>TokoKita Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/index.php">
            <i class="bi bi-shop"></i> TokoKita
        </a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <form class="d-flex mx-lg-auto my-2 my-lg-0" style="max-width: 420px; width: 100%;"
                  action="<?= BASE_URL ?>/produk/index.php" method="get">
                <input class="form-control me-2" type="search" name="q" placeholder="Cari produk..."
                       value="<?= e($_GET['q'] ?? '') ?>">
                <button class="btn btn-light" type="submit"><i class="bi bi-search"></i></button>
            </form>
            <ul class="navbar-nav ms-lg-3 align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/produk/index.php">Produk</a>
                </li>
                <?php if ($user && $user['role'] === 'pembeli'): ?>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="<?= BASE_URL ?>/keranjang/index.php">
                            <i class="bi bi-cart3"></i> Keranjang
                            <?php $count = cart_count($koneksi); if ($count > 0): ?>
                                <span class="badge rounded-pill text-bg-danger"><?= $count ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ($user): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= e($user['nama']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if ($user['role'] === 'pembeli'): ?>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/pesanan/riwayat.php">Riwayat Pesanan</a></li>
                            <?php endif; ?>
                            <?php if ($user['role'] === 'penjual'): ?>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/produk/saya.php">Produk Saya</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/produk/tambah.php">Tambah Produk</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/pesanan/masuk.php">Pesanan Masuk</a></li>
                            <?php endif; ?>
                            <?php if ($user['role'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/index.php">Dashboard Admin</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/kategori.php">Kelola Kategori</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/pengguna.php">Kelola Pengguna</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/pesanan.php">Semua Pesanan</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/auth/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/auth/login.php">Login</a></li>
                    <li class="nav-item"><a class="btn btn-light btn-sm ms-lg-2" href="<?= BASE_URL ?>/auth/register.php">Daftar</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4 flex-grow-1">
<?php $flash = get_flash(); if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
