<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('pembeli');

$id_user = (int) current_user()['id_user'];

$stmt = mysqli_prepare($koneksi,
    'SELECT * FROM pesanan WHERE id_user = ? ORDER BY tanggal_pesanan DESC');
mysqli_stmt_bind_param($stmt, 'i', $id_user);
mysqli_stmt_execute($stmt);
$pesanan_list = mysqli_stmt_get_result($stmt);

$page_title = 'Riwayat Pesanan';
require_once __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-4">Riwayat Pesanan</h4>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode Pesanan</th><th>Tanggal</th><th>Total</th><th>Status</th><th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($pesanan_list) === 0): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">
                    Belum ada pesanan. <a href="<?= BASE_URL ?>/produk/index.php">Mulai belanja</a>.
                </td></tr>
            <?php endif; ?>
            <?php while ($pesanan = mysqli_fetch_assoc($pesanan_list)): ?>
                <tr>
                    <td class="fw-semibold"><?= e($pesanan['kode_pesanan']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($pesanan['tanggal_pesanan'])) ?></td>
                    <td><?= rupiah($pesanan['total_harga']) ?></td>
                    <td><?= status_badge($pesanan['status']) ?></td>
                    <td class="text-end">
                        <a href="<?= BASE_URL ?>/pesanan/detail.php?kode=<?= urlencode($pesanan['kode_pesanan']) ?>"
                           class="btn btn-outline-primary btn-sm">Detail</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
