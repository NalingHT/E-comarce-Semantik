<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['penjual', 'admin']);

$user = current_user();

if ($user['role'] === 'admin') {
    $stmt = mysqli_prepare($koneksi,
        'SELECT p.*, k.nama_kategori, u.nama AS nama_penjual
         FROM produk p
         JOIN kategori k ON k.id_kategori = p.id_kategori
         JOIN users u ON u.id_user = p.id_penjual
         ORDER BY p.created_at DESC');
} else {
    $stmt = mysqli_prepare($koneksi,
        'SELECT p.*, k.nama_kategori, u.nama AS nama_penjual
         FROM produk p
         JOIN kategori k ON k.id_kategori = p.id_kategori
         JOIN users u ON u.id_user = p.id_penjual
         WHERE p.id_penjual = ?
         ORDER BY p.created_at DESC');
    mysqli_stmt_bind_param($stmt, 'i', $user['id_user']);
}
mysqli_stmt_execute($stmt);
$produk_list = mysqli_stmt_get_result($stmt);

$page_title = $user['role'] === 'admin' ? 'Semua Produk' : 'Produk Saya';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><?= e($page_title) ?></h4>
    <a href="<?= BASE_URL ?>/produk/tambah.php" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Produk
    </a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Gambar</th><th>Nama Produk</th><th>Kategori</th>
                    <?php if ($user['role'] === 'admin'): ?><th>Penjual</th><?php endif; ?>
                    <th>Harga</th><th>Stok</th><th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($produk_list) === 0): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada produk.</td></tr>
            <?php endif; ?>
            <?php while ($produk = mysqli_fetch_assoc($produk_list)): ?>
                <tr>
                    <td>
                        <img src="<?= produk_gambar_url($produk['gambar']) ?>"
                             width="60" height="45" class="rounded" style="object-fit: cover;" alt="">
                    </td>
                    <td><?= e($produk['nama_produk']) ?></td>
                    <td><?= e($produk['nama_kategori']) ?></td>
                    <?php if ($user['role'] === 'admin'): ?><td><?= e($produk['nama_penjual']) ?></td><?php endif; ?>
                    <td><?= rupiah($produk['harga']) ?></td>
                    <td><?= (int) $produk['stok'] ?></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= BASE_URL ?>/produk/edit.php?id=<?= $produk['id_produk'] ?>" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/produk/hapus.php?id=<?= $produk['id_produk'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Yakin ingin menghapus produk ini?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
