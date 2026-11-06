<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$user = current_user();
$kode = $_GET['kode'] ?? '';

$stmt = mysqli_prepare($koneksi, 'SELECT p.*, u.nama AS nama_pembeli FROM pesanan p JOIN users u ON u.id_user = p.id_user WHERE p.kode_pesanan = ?');
mysqli_stmt_bind_param($stmt, 's', $kode);
mysqli_stmt_execute($stmt);
$pesanan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$pesanan) {
    set_flash('danger', 'Pesanan tidak ditemukan.');
    redirect('index.php');
}

$stmt = mysqli_prepare($koneksi,
    'SELECT COUNT(*) AS milik FROM detail_pesanan d
     JOIN produk p ON p.id_produk = d.id_produk
     WHERE d.id_pesanan = ? AND p.id_penjual = ?');
mysqli_stmt_bind_param($stmt, 'ii', $pesanan['id_pesanan'], $user['id_user']);
mysqli_stmt_execute($stmt);
$ada_produk_penjual = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['milik'] > 0;

$berhak = $user['role'] === 'admin'
    || ((int) $pesanan['id_user'] === (int) $user['id_user'])
    || ($user['role'] === 'penjual' && $ada_produk_penjual);

if (!$berhak) {
    set_flash('danger', 'Anda tidak memiliki akses ke pesanan ini.');
    redirect('index.php');
}

if ($user['role'] === 'penjual') {
    $stmt = mysqli_prepare($koneksi,
        'SELECT d.*, pr.nama_produk FROM detail_pesanan d
         JOIN produk pr ON pr.id_produk = d.id_produk
         WHERE d.id_pesanan = ? AND pr.id_penjual = ?');
    mysqli_stmt_bind_param($stmt, 'ii', $pesanan['id_pesanan'], $user['id_user']);
} else {
    $stmt = mysqli_prepare($koneksi,
        'SELECT d.*, pr.nama_produk FROM detail_pesanan d
         JOIN produk pr ON pr.id_produk = d.id_produk
         WHERE d.id_pesanan = ?');
    mysqli_stmt_bind_param($stmt, 'i', $pesanan['id_pesanan']);
}
mysqli_stmt_execute($stmt);
$items = mysqli_stmt_get_result($stmt);

$page_title = 'Detail Pesanan ' . $pesanan['kode_pesanan'];
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap gap-2">
                    <div>
                        <h5 class="mb-1"><?= e($pesanan['kode_pesanan']) ?></h5>
                        <p class="text-muted mb-0">
                            <?= date('d F Y, H:i', strtotime($pesanan['tanggal_pesanan'])) ?> &middot;
                            Pembeli: <?= e($pesanan['nama_pembeli']) ?>
                        </p>
                    </div>
                    <div><?= status_badge($pesanan['status']) ?></div>
                </div>
                <hr>
                <p class="mb-1 fw-semibold">Alamat Pengiriman:</p>
                <p class="text-muted mb-0"><?= nl2br(e($pesanan['alamat_pengiriman'])) ?></p>
            </div>
        </div>
        <div class="card shadow-sm">
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
                            <td colspan="3" class="text-end fw-bold">Total Pesanan</td>
                            <td class="fw-bold text-primary"><?= rupiah($pesanan['total_harga']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <a href="javascript:history.back()" class="btn btn-outline-secondary mt-3">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
