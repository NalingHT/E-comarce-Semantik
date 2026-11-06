<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
require_role(['penjual', 'admin']);

$errors = [];
$daftar_kategori = mysqli_query($koneksi, 'SELECT * FROM kategori ORDER BY nama_kategori');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $id_kategori = (int) ($_POST['id_kategori'] ?? 0);
    $harga       = (float) ($_POST['harga'] ?? 0);
    $stok        = (int) ($_POST['stok'] ?? 0);
    $deskripsi   = trim($_POST['deskripsi'] ?? '');

    if ($nama_produk === '') {
        $errors[] = 'Nama produk wajib diisi.';
    }
    if ($id_kategori <= 0) {
        $errors[] = 'Kategori wajib dipilih.';
    }
    if ($harga <= 0) {
        $errors[] = 'Harga harus lebih dari 0.';
    }
    if ($stok < 0) {
        $errors[] = 'Stok tidak boleh negatif.';
    }

    $nama_gambar = null;
    if (!$errors) {
        $nama_gambar = upload_gambar('gambar', $upload_error);
        if ($nama_gambar === false) {
            $errors[] = $upload_error;
        }
    }

    if (!$errors) {
        $id_penjual = (int) current_user()['id_user'];
        $stmt = mysqli_prepare($koneksi,
            'INSERT INTO produk (id_penjual, id_kategori, nama_produk, deskripsi, harga, stok, gambar)
             VALUES (?, ?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'iissdis', $id_penjual, $id_kategori, $nama_produk, $deskripsi, $harga, $stok, $nama_gambar);
        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', 'Produk berhasil ditambahkan.');
            redirect('produk/saya.php');
        }
        $errors[] = 'Gagal menyimpan produk.';
    }
}

$page_title = 'Tambah Produk';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="mb-4">Tambah Produk</h4>
                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endforeach; ?>
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" name="nama_produk" class="form-control" required maxlength="150"
                               value="<?= e($_POST['nama_produk'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="id_kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php while ($kat = mysqli_fetch_assoc($daftar_kategori)): ?>
                                <option value="<?= $kat['id_kategori'] ?>"
                                    <?= (int) ($_POST['id_kategori'] ?? 0) === (int) $kat['id_kategori'] ? 'selected' : '' ?>>
                                    <?= e($kat['nama_kategori']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="harga" class="form-control" required min="1"
                                   value="<?= e($_POST['harga'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stok <span class="text-danger">*</span></label>
                            <input type="number" name="stok" class="form-control" required min="0"
                                   value="<?= e($_POST['stok'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar Produk (JPG/PNG/WebP, maks 2 MB)</label>
                        <input type="file" name="gambar" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="4"><?= e($_POST['deskripsi'] ?? '') ?></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="<?= BASE_URL ?>/produk/saya.php" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
