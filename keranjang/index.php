<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('pembeli');

$id_user = (int) current_user()['id_user'];

$stmt = mysqli_prepare($koneksi,
    'SELECT k.id_keranjang, k.jumlah, p.id_produk, p.nama_produk, p.harga, p.stok, p.gambar,
            (k.jumlah * p.harga) AS subtotal
     FROM keranjang k
     JOIN produk p ON p.id_produk = k.id_produk
     WHERE k.id_user = ?
     ORDER BY k.created_at DESC');
mysqli_stmt_bind_param($stmt, 'i', $id_user);
mysqli_stmt_execute($stmt);
$items = mysqli_stmt_get_result($stmt);

$total = 0;
$page_title = 'Keranjang Belanja';
require_once __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-4">Keranjang Belanja</h4>

<?php if (mysqli_num_rows($items) === 0): ?>
    <div class="alert alert-info">
        Keranjang masih kosong. <a href="<?= BASE_URL ?>/produk/index.php">Mulai belanja sekarang</a>.
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Produk</th><th>Harga</th><th style="width: 170px;">Jumlah</th>
                        <th>Subtotal</th><th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($item = mysqli_fetch_assoc($items)): $total += (float) $item['subtotal']; ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= produk_gambar_url($item['gambar']) ?>"
                                     width="60" height="45" class="rounded" style="object-fit: cover;" alt="">
                                <a href="<?= BASE_URL ?>/produk/detail.php?id=<?= $item['id_produk'] ?>" class="text-decoration-none">
                                    <?= e($item['nama_produk']) ?>
                                </a>
                            </div>
                            <small class="text-muted">Stok tersedia: <?= (int) $item['stok'] ?></small>
                        </td>
                        <td><?= rupiah($item['harga']) ?></td>
                        <td>
                            <form action="<?= BASE_URL ?>/keranjang/update.php" method="post" class="d-flex gap-1">
                                <input type="hidden" name="id_keranjang" value="<?= $item['id_keranjang'] ?>">
                                <input type="number" name="jumlah" class="form-control form-control-sm text-center"
                                       value="<?= (int) $item['jumlah'] ?>" min="1" max="<?= (int) $item['stok'] ?>" style="width: 70px;">
                                <button type="submit" class="btn btn-outline-primary btn-sm" title="Perbarui">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                            </form>
                        </td>
                        <td class="fw-semibold"><?= rupiah($item['subtotal']) ?></td>
                        <td class="text-end">
                            <a href="<?= BASE_URL ?>/keranjang/hapus.php?id=<?= $item['id_keranjang'] ?>"
                               class="btn btn-outline-danger btn-sm"
                               onclick="return confirm('Hapus item ini dari keranjang?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Total</td>
                        <td class="fw-bold text-primary fs-5"><?= rupiah($total) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-between mt-3">
        <a href="<?= BASE_URL ?>/produk/index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Lanjut Belanja
        </a>
        <a href="<?= BASE_URL ?>/pesanan/checkout.php" class="btn btn-primary">
            Checkout <i class="bi bi-arrow-right"></i>
        </a>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
