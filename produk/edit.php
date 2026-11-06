<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
require_role(['penjual', 'admin']);

$user = current_user();
$id   = (int) ($_GET['id'] ?? 0);

$stmt = mysqli_prepare($koneksi, 'SELECT * FROM produk WHERE id_produk = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$produk = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$produk) {
    set_flash('danger', 'Produk tidak ditemukan.');
    redirect('produk/saya.php');
}

if ($user['role'] !== 'admin' && (int) $produk['id_penjual'] !== (int) $user['id_user']) {
    set_flash('danger', 'Anda hanya dapat mengubah produk milik sendiri.');
    redirect('produk/saya.php');
}

$errors = [];
$daftar_kategori = mysqli_query($koneksi, 'SELECT * FROM kategori ORDER BY nama_kategori');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $id_kategori = (int) ($_POST['id_kategori'] ?? 0);
    $harga       = (float) ($_POST['harga'] ?? 0);
    $stok        = (int) ($_POST['stok'] ?? 0);
    $deskripsi   = trim($_POST['deskripsi'] ?? '');

    if ($nama_produk === '') $errors[] = 'Nama produk wajib diisi.';
    if ($id_kategori <= 0)   $errors[] = 'Kategori wajib dipilih.';
    if ($harga <= 0)         $errors[] = 'Harga harus lebih dari 0.';
    if ($stok < 0)           $errors[] = 'Stok tidak boleh negatif.';

    $nama_gambar = $produk['gambar'];
    if (!$errors && !empty($_FILES['gambar']['name'])) {
        $hasil = upload_gambar('gambar', $upload_error);
        if ($hasil === false) {
            $errors[] = $upload_error;
        } elseif ($hasil !== null) {
            hapus_gambar($produk['gambar']);
            $nama_gambar = $hasil;
        }
    }

    if (!$errors) {
        $stmt = mysqli_prepare($koneksi,
            'UPDATE produk SET id_kategori = ?, nama_produk = ?, deskripsi = ?, harga = ?, stok = ?, gambar = ?
             WHERE id_produk = ?');
        mysqli_stmt_bind_param($stmt, 'issdisi', $id_kategori, $nama_produk, $deskripsi, $harga, $stok, $nama_gambar, $id);
        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', 'Produk berhasil diperbarui.');
            redirect('produk/saya.php');
        }
        $errors[] = 'Gagal memperbarui produk.';
    }

    $produk = array_merge($produk, compact('nama_produk', 'id_kategori', 'harga', 'stok', 'deskripsi'));
}

$page_title = 'Edit Produk';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="mb-4">Edit Produk</h4>
                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endforeach; ?>
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" name="nama_produk" class="form-control" required maxlength="150"
                               value="<?= e($produk['nama_produk']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="id_kategori" class="form-select" required>
                            <?php while ($kat = mysqli_fetch_assoc($daftar_kategori)): ?>
                                <option value="<?= $kat['id_kategori'] ?>"
                                    <?= (int) $produk['id_kategori'] === (int) $kat['id_kategori'] ? 'selected' : '' ?>>
                                    <?= e($kat['nama_kategori']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="harga" class="form-control" required min="1"
                                   value="<?= e($produk['harga']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stok <span class="text-danger">*</span></label>
                            <input type="number" name="stok" class="form-control" required min="0"
                                   value="<?= e($produk['stok']) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-block">Gambar Saat Ini</label>
                        <img src="<?= produk_gambar_url($produk['gambar']) ?>"
                             class="rounded mb-2" width="160" alt="">
                        <input type="file" name="gambar" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        <small class="text-muted">Biarkan kosong jika tidak ingin mengganti gambar.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="4"><?= e($produk['deskripsi']) ?></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="<?= BASE_URL ?>/produk/saya.php" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
