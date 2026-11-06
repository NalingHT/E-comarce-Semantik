<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('pembeli');

$user    = current_user();
$id_user = (int) $user['id_user'];

$stmt = mysqli_prepare($koneksi,
    'SELECT k.id_keranjang, k.jumlah, p.id_produk, p.nama_produk, p.harga, p.stok,
            (k.jumlah * p.harga) AS subtotal
     FROM keranjang k
     JOIN produk p ON p.id_produk = k.id_produk
     WHERE k.id_user = ?');
mysqli_stmt_bind_param($stmt, 'i', $id_user);
mysqli_stmt_execute($stmt);
$items = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

if (!$items) {
    set_flash('warning', 'Keranjang Anda kosong.');
    redirect('keranjang/index.php');
}

$total = array_sum(array_column($items, 'subtotal'));

$stmt = mysqli_prepare($koneksi, 'SELECT alamat FROM users WHERE id_user = ?');
mysqli_stmt_bind_param($stmt, 'i', $id_user);
mysqli_stmt_execute($stmt);
$profil = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alamat = trim($_POST['alamat'] ?? '');

    if ($alamat === '') {
        set_flash('danger', 'Alamat pengiriman wajib diisi.');
        redirect('pesanan/checkout.php');
    }

    mysqli_begin_transaction($koneksi);
    try {
        $stmt = mysqli_prepare($koneksi,
            "SELECT CONCAT('ORD-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(COUNT(*) + 1, 4, '0')) AS kode
             FROM pesanan WHERE DATE(tanggal_pesanan) = CURDATE() FOR UPDATE");
        mysqli_stmt_execute($stmt);
        $kode_pesanan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['kode'];

        $stmt = mysqli_prepare($koneksi,
            'INSERT INTO pesanan (kode_pesanan, id_user, tanggal_pesanan, total_harga, alamat_pengiriman, status)
             VALUES (?, ?, NOW(), 0, ?, \'menunggu\')');
        mysqli_stmt_bind_param($stmt, 'sis', $kode_pesanan, $id_user, $alamat);
        mysqli_stmt_execute($stmt);
        $id_pesanan = (int) mysqli_insert_id($koneksi);

        $total_harga = 0;
        foreach ($items as $item) {
            $stmt = mysqli_prepare($koneksi,
                'UPDATE produk SET stok = stok - ? WHERE id_produk = ? AND stok >= ?');
            mysqli_stmt_bind_param($stmt, 'iii', $item['jumlah'], $item['id_produk'], $item['jumlah']);
            mysqli_stmt_execute($stmt);
            if (mysqli_stmt_affected_rows($stmt) === 0) {
                throw new Exception('Stok produk "' . $item['nama_produk'] . '" tidak mencukupi.');
            }

            $subtotal = (float) $item['harga'] * (int) $item['jumlah'];
            $total_harga += $subtotal;

            $stmt = mysqli_prepare($koneksi,
                'INSERT INTO detail_pesanan (id_pesanan, id_produk, harga_satuan, jumlah, subtotal)
                 VALUES (?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'iidid', $id_pesanan, $item['id_produk'], $item['harga'], $item['jumlah'], $subtotal);
            mysqli_stmt_execute($stmt);
        }

        $stmt = mysqli_prepare($koneksi, 'UPDATE pesanan SET total_harga = ? WHERE id_pesanan = ?');
        mysqli_stmt_bind_param($stmt, 'di', $total_harga, $id_pesanan);
        mysqli_stmt_execute($stmt);

        $stmt = mysqli_prepare($koneksi, 'DELETE FROM keranjang WHERE id_user = ?');
        mysqli_stmt_bind_param($stmt, 'i', $id_user);
        mysqli_stmt_execute($stmt);

        mysqli_commit($koneksi);
        redirect('pesanan/sukses.php?kode=' . urlencode($kode_pesanan));
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        set_flash('danger', 'Checkout gagal: ' . $e->getMessage());
        redirect('keranjang/index.php');
    }
}

$page_title = 'Checkout';
require_once __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-4">Konfirmasi Checkout</h4>
<div class="row g-4">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">Rincian Pesanan</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= e($item['nama_produk']) ?></td>
                            <td><?= rupiah($item['harga']) ?></td>
                            <td><?= (int) $item['jumlah'] ?></td>
                            <td><?= rupiah($item['subtotal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Total</td>
                            <td class="fw-bold text-primary"><?= rupiah($total) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">Alamat Pengiriman</div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <textarea name="alamat" class="form-control" rows="4" required
                                  placeholder="Tulis alamat lengkap pengiriman..."><?= e($_POST['alamat'] ?? ($profil['alamat'] ?? '')) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"
                            onclick="return confirm('Buat pesanan sekarang?')">
                        <i class="bi bi-check-circle"></i> Buat Pesanan
                    </button>
                    <a href="<?= BASE_URL ?>/keranjang/index.php" class="btn btn-outline-secondary w-100 mt-2">
                        Kembali ke Keranjang
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
