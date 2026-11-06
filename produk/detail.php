<?php
require_once __DIR__ . '/../includes/auth.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = mysqli_prepare($koneksi,
    'SELECT p.*, k.nama_kategori, u.nama AS nama_penjual
     FROM produk p
     JOIN kategori k ON k.id_kategori = p.id_kategori
     JOIN users u ON u.id_user = p.id_penjual
     WHERE p.id_produk = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$produk = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$produk) {
    set_flash('danger', 'Produk tidak ditemukan.');
    redirect('produk/index.php');
}

$user = current_user();
$bisa_beli = !$user || $user['role'] === 'pembeli';
$milik_sendiri = $user && (int) $user['id_user'] === (int) $produk['id_penjual'];

$page_title = $produk['nama_produk'];
require_once __DIR__ . '/../includes/header.php';

$gambar = produk_gambar_url($produk['gambar']);
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/produk/index.php">Beranda</a></li>
        <li class="breadcrumb-item">
            <a href="<?= BASE_URL ?>/produk/index.php?kategori=<?= $produk['id_kategori'] ?>"><?= e($produk['nama_kategori']) ?></a>
        </li>
        <li class="breadcrumb-item active"><?= e($produk['nama_produk']) ?></li>
    </ol>
</nav>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-5">
                <img src="<?= $gambar ?>" class="img-fluid rounded w-100"
                     alt="<?= e($produk['nama_produk']) ?>" style="max-height: 400px; object-fit: cover;">
            </div>
            <div class="col-md-7">
                <h3><?= e($produk['nama_produk']) ?></h3>
                <table class="table table-borderless w-auto">
                    <tr><td class="text-muted pe-3">Kategori</td><td><?= e($produk['nama_kategori']) ?></td></tr>
                    <tr><td class="text-muted pe-3">Penjual</td><td><?= e($produk['nama_penjual']) ?></td></tr>
                    <tr><td class="text-muted pe-3">Harga</td>
                        <td class="fs-4 fw-bold text-primary"><?= rupiah($produk['harga']) ?></td></tr>
                    <tr><td class="text-muted pe-3">Stok</td><td><?= (int) $produk['stok'] ?> pcs</td></tr>
                </table>

                <?php if ($milik_sendiri): ?>
                    <div class="alert alert-info">Ini produk milik Anda.
                        <a href="<?= BASE_URL ?>/produk/edit.php?id=<?= $produk['id_produk'] ?>">Edit produk</a>
                    </div>
                <?php elseif ((int) $produk['stok'] <= 0): ?>
                    <button class="btn btn-secondary" disabled>Stok Habis</button>
                <?php elseif ($bisa_beli): ?>
                    <form action="<?= BASE_URL ?>/keranjang/tambah.php" method="post" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="id_produk" value="<?= $produk['id_produk'] ?>">
                        <div class="input-group" style="width: 140px;">
                            <button type="button" class="btn btn-outline-secondary" onclick="aturJumlah(-1)">-</button>
                            <input type="number" name="jumlah" id="jumlah" class="form-control text-center"
                                   value="1" min="1" max="<?= (int) $produk['stok'] ?>">
                            <button type="button" class="btn btn-outline-secondary" onclick="aturJumlah(1)">+</button>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">Akun penjual/admin tidak dapat berbelanja.</div>
                <?php endif; ?>
            </div>
        </div>
        <hr>
        <h5>Deskripsi Produk</h5>
        <p><?= nl2br(e($produk['deskripsi'] ?? 'Tidak ada deskripsi.')) ?></p>
    </div>
</div>
<script>
function aturJumlah(delta) {
    const input = document.getElementById('jumlah');
    const min = parseInt(input.min) || 1;
    const max = parseInt(input.max) || 99;
    let nilai = (parseInt(input.value) || 1) + delta;
    input.value = Math.min(max, Math.max(min, nilai));
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
