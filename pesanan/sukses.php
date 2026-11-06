<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('pembeli');

$kode    = $_GET['kode'] ?? '';
$id_user = (int) current_user()['id_user'];

$stmt = mysqli_prepare($koneksi, 'SELECT * FROM pesanan WHERE kode_pesanan = ? AND id_user = ?');
mysqli_stmt_bind_param($stmt, 'si', $kode, $id_user);
mysqli_stmt_execute($stmt);
$pesanan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$pesanan) {
    set_flash('danger', 'Pesanan tidak ditemukan.');
    redirect('pesanan/riwayat.php');
}

$stmt = mysqli_prepare($koneksi,
    'SELECT d.*, p.nama_produk FROM detail_pesanan d
     JOIN produk p ON p.id_produk = d.id_produk
     WHERE d.id_pesanan = ?');
mysqli_stmt_bind_param($stmt, 'i', $pesanan['id_pesanan']);
mysqli_stmt_execute($stmt);
$items = mysqli_stmt_get_result($stmt);

$page_title = 'Pesanan Berhasil';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm text-center">
            <div class="card-body p-5">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h3 class="mt-3">Pesanan Berhasil Dibuat!</h3>
                <p class="text-muted">Kode pesanan Anda:</p>
                <h4 class="text-primary"><?= e($pesanan['kode_pesanan']) ?></h4>
                <p>Status: <?= status_badge($pesanan['status']) ?></p>
            </div>
        </div>
        <div class="card shadow-sm mt-3">
            <div class="card-header bg-white fw-semibold">Rincian Pesanan</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Produk</th><th>Harga Satuan</th><th>Jumlah</th><th>Subtotal</th></tr>
                    </thead>
                    <tbody>
                    <?php while ($item = mysqli_fetch_assoc($items)): ?>
                        <tr>
                            <td><?= e($item['nama_produk']) ?></td>
                            <td><?= rupiah($item['harga_satuan']) ?></td>
                            <td><?= (int) $item['jumlah'] ?></td>
                            <td><?= rupiah($item['subtotal']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Total</td>
                            <td class="fw-bold text-primary"><?= rupiah($pesanan['total_harga']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-body">
                <p class="mb-1 fw-semibold">Alamat Pengiriman:</p>
                <p class="text-muted"><?= nl2br(e($pesanan['alamat_pengiriman'])) ?></p>
                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>/pesanan/riwayat.php" class="btn btn-primary">Lihat Riwayat Pesanan</a>
                    <a href="<?= BASE_URL ?>/produk/index.php" class="btn btn-outline-secondary">Belanja Lagi</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
