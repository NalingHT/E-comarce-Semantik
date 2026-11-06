<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('penjual');

$id_penjual = (int) current_user()['id_user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pesanan   = (int) ($_POST['id_pesanan'] ?? 0);
    $status_baru  = $_POST['status'] ?? '';
    $status_valid = ['menunggu', 'dibayar', 'dikirim', 'selesai', 'batal'];

    if (!in_array($status_baru, $status_valid, true)) {
        set_flash('danger', 'Status tidak valid.');
        redirect('pesanan/masuk.php');
    }

    $stmt = mysqli_prepare($koneksi,
        'SELECT COUNT(*) AS milik FROM detail_pesanan d
         JOIN produk p ON p.id_produk = d.id_produk
         WHERE d.id_pesanan = ? AND p.id_penjual = ?');
    mysqli_stmt_bind_param($stmt, 'ii', $id_pesanan, $id_penjual);
    mysqli_stmt_execute($stmt);

    if ((int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['milik'] === 0) {
        set_flash('danger', 'Anda tidak berhak mengubah pesanan ini.');
        redirect('pesanan/masuk.php');
    }

    $stmt = mysqli_prepare($koneksi, 'UPDATE pesanan SET status = ? WHERE id_pesanan = ?');
    mysqli_stmt_bind_param($stmt, 'si', $status_baru, $id_pesanan);
    mysqli_stmt_execute($stmt);

    set_flash('success', 'Status pesanan diperbarui.');
    redirect('pesanan/masuk.php');
}

$stmt = mysqli_prepare($koneksi,
    'SELECT p.id_pesanan, p.kode_pesanan, p.tanggal_pesanan, p.status, u.nama AS nama_pembeli,
            SUM(d.subtotal) AS total_penjual, COUNT(d.id_detail) AS jumlah_item
     FROM pesanan p
     JOIN users u ON u.id_user = p.id_user
     JOIN detail_pesanan d ON d.id_pesanan = p.id_pesanan
     JOIN produk pr ON pr.id_produk = d.id_produk
     WHERE pr.id_penjual = ?
     GROUP BY p.id_pesanan
     ORDER BY p.tanggal_pesanan DESC');
mysqli_stmt_bind_param($stmt, 'i', $id_penjual);
mysqli_stmt_execute($stmt);
$pesanan_list = mysqli_stmt_get_result($stmt);

$status_opsi = ['menunggu', 'dibayar', 'dikirim', 'selesai', 'batal'];

$page_title = 'Pesanan Masuk';
require_once __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-4">Pesanan Masuk</h4>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode Pesanan</th><th>Tanggal</th><th>Pembeli</th>
                    <th>Item Saya</th><th>Subtotal Saya</th><th>Status</th><th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($pesanan_list) === 0): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pesanan masuk.</td></tr>
            <?php endif; ?>
            <?php while ($pesanan = mysqli_fetch_assoc($pesanan_list)): ?>
                <tr>
                    <td class="fw-semibold"><?= e($pesanan['kode_pesanan']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($pesanan['tanggal_pesanan'])) ?></td>
                    <td><?= e($pesanan['nama_pembeli']) ?></td>
                    <td><?= (int) $pesanan['jumlah_item'] ?> item</td>
                    <td><?= rupiah($pesanan['total_penjual']) ?></td>
                    <td>
                        <form method="post" class="d-flex gap-1">
                            <input type="hidden" name="id_pesanan" value="<?= $pesanan['id_pesanan'] ?>">
                            <select name="status" class="form-select form-select-sm" style="width: 120px;">
                                <?php foreach ($status_opsi as $opsi): ?>
                                    <option value="<?= $opsi ?>" <?= $pesanan['status'] === $opsi ? 'selected' : '' ?>>
                                        <?= ucfirst($opsi) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                        </form>
                    </td>
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
