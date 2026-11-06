<?php
require_once __DIR__ . '/../includes/auth.php';

$q         = trim($_GET['q'] ?? '');
$kategori  = (int) ($_GET['kategori'] ?? 0);
$urutkan   = $_GET['urutkan'] ?? 'terbaru';
$halaman   = max(1, (int) ($_GET['halaman'] ?? 1));
$per_halaman = 12;
$offset    = ($halaman - 1) * $per_halaman;

$where  = [];
$params = [];
$types  = '';

if ($q !== '') {
    $where[]  = 'p.nama_produk LIKE ?';
    $params[] = '%' . $q . '%';
    $types   .= 's';
}
if ($kategori > 0) {
    $where[]  = 'p.id_kategori = ?';
    $params[] = $kategori;
    $types   .= 'i';
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$order_sql = match ($urutkan) {
    'termurah'  => 'ORDER BY p.harga ASC',
    'termahal'  => 'ORDER BY p.harga DESC',
    'nama'      => 'ORDER BY p.nama_produk ASC',
    default     => 'ORDER BY p.created_at DESC',
};

$stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM produk p $where_sql");
if ($types) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$total_produk  = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
$total_halaman = max(1, (int) ceil($total_produk / $per_halaman));

$sql = "SELECT p.*, k.nama_kategori
        FROM produk p
        JOIN kategori k ON k.id_kategori = p.id_kategori
        $where_sql $order_sql
        LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($koneksi, $sql);
$bind_types  = $types . 'ii';
$bind_params = array_merge($params, [$per_halaman, $offset]);
mysqli_stmt_bind_param($stmt, $bind_types, ...$bind_params);
mysqli_stmt_execute($stmt);
$produk_list = mysqli_stmt_get_result($stmt);

$daftar_kategori = mysqli_query($koneksi, 'SELECT * FROM kategori ORDER BY nama_kategori');

function build_query($overrides = []) {
    $query = array_merge($_GET, $overrides);
    return http_build_query(array_filter($query, fn($v) => $v !== '' && $v !== 0));
}

$page_title = 'Daftar Produk';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <h4 class="mb-0">Daftar Produk</h4>
    <form class="d-flex gap-2" method="get">
        <?php if ($q !== ''): ?>
            <input type="hidden" name="q" value="<?= e($q) ?>">
        <?php endif; ?>
        <select name="kategori" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="0">Semua Kategori</option>
            <?php while ($kat = mysqli_fetch_assoc($daftar_kategori)): ?>
                <option value="<?= $kat['id_kategori'] ?>" <?= $kategori === (int) $kat['id_kategori'] ? 'selected' : '' ?>>
                    <?= e($kat['nama_kategori']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <select name="urutkan" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="terbaru" <?= $urutkan === 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
            <option value="termurah" <?= $urutkan === 'termurah' ? 'selected' : '' ?>>Termurah</option>
            <option value="termahal" <?= $urutkan === 'termahal' ? 'selected' : '' ?>>Termahal</option>
            <option value="nama" <?= $urutkan === 'nama' ? 'selected' : '' ?>>Nama A-Z</option>
        </select>
    </form>
</div>

<?php if ($q !== ''): ?>
    <p class="text-muted">Hasil pencarian untuk "<strong><?= e($q) ?></strong>": <?= $total_produk ?> produk ditemukan.</p>
<?php endif; ?>

<?php if ($total_produk === 0): ?>
    <div class="alert alert-info">Tidak ada produk yang ditemukan.</div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
        <?php while ($produk = mysqli_fetch_assoc($produk_list)): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <img src="<?= produk_gambar_url($produk['gambar']) ?>" class="card-img-top"
                         alt="<?= e($produk['nama_produk']) ?>" style="height: 180px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <span class="badge text-bg-secondary align-self-start mb-2"><?= e($produk['nama_kategori']) ?></span>
                        <h6 class="card-title"><?= e($produk['nama_produk']) ?></h6>
                        <p class="fw-bold text-primary mb-1"><?= rupiah($produk['harga']) ?></p>
                        <p class="text-muted small mb-3">Stok: <?= (int) $produk['stok'] ?></p>
                        <div class="mt-auto">
                            <?php if ((int) $produk['stok'] > 0): ?>
                                <a href="<?= BASE_URL ?>/produk/detail.php?id=<?= $produk['id_produk'] ?>"
                                   class="btn btn-outline-primary btn-sm w-100">Lihat Detail</a>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm w-100" disabled>Stok Habis</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <?php if ($total_halaman > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $halaman <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= build_query(['halaman' => $halaman - 1]) ?>">&laquo;</a>
                </li>
                <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                    <li class="page-item <?= $i === $halaman ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= build_query(['halaman' => $i]) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $halaman >= $total_halaman ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= build_query(['halaman' => $halaman + 1]) ?>">&raquo;</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
