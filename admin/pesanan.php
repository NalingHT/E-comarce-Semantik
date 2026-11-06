<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pesanan  = (int) ($_POST['id_pesanan'] ?? 0);
    $status_baru = $_POST['status'] ?? '';
    $status_valid = ['menunggu', 'dibayar', 'dikirim', 'selesai', 'batal'];

    if (in_array($status_baru, $status_valid, true)) {
        $stmt = mysqli_prepare($koneksi, 'UPDATE pesanan SET status = ? WHERE id_pesanan = ?');
        mysqli_stmt_bind_param($stmt, 'si', $status_baru, $id_pesanan);
        mysqli_stmt_execute($stmt);
        set_flash('success', 'Status pesanan diperbarui.');
    } else {
        set_flash('danger', 'Status tidak valid.');
    }
    redirect('admin/pesanan.php');
}

$pesanan_list = mysqli_query($koneksi,
    'SELECT p.*, u.nama AS nama_pembeli,
            (SELECT COUNT(*) FROM detail_pesanan d WHERE d.id_pesanan = p.id_pesanan) AS jumlah_item
     FROM pesanan p
     JOIN users u ON u.id_user = p.id_user
     ORDER BY p.tanggal_pesanan DESC');

$status_opsi = ['menunggu', 'dibayar', 'dikirim', 'selesai', 'batal'];

$page_title = 'Semua Pesanan';
require_once __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-4">Semua Pesanan</h4>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode Pesanan</th><th>Tanggal</th><th>Pembeli</th>
                    <th>Item</th><th>Total</th><th>Status</th><th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($pesanan_list) === 0): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pesanan.</td></tr>
            <?php endif; ?>
            <?php while ($pesanan = mysqli_fetch_assoc($pesanan_list)): ?>
                <tr>
                    <td class="fw-semibold"><?= e($pesanan['kode_pesanan']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($pesanan['tanggal_pesanan'])) ?></td>
                    <td><?= e($pesanan['nama_pembeli']) ?></td>
                    <td><?= (int) $pesanan['jumlah_item'] ?></td>
                    <td><?= rupiah($pesanan['total_harga']) ?></td>
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
