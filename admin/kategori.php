<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah') {
        $nama = trim($_POST['nama_kategori'] ?? '');
        if ($nama === '') {
            set_flash('danger', 'Nama kategori wajib diisi.');
        } else {
            $stmt = mysqli_prepare($koneksi, 'INSERT INTO kategori (nama_kategori) VALUES (?)');
            mysqli_stmt_bind_param($stmt, 's', $nama);
            if (mysqli_stmt_execute($stmt)) {
                set_flash('success', 'Kategori berhasil ditambahkan.');
            } else {
                set_flash('danger', 'Nama kategori sudah ada.');
            }
        }
    } elseif ($aksi === 'edit') {
        $id   = (int) ($_POST['id_kategori'] ?? 0);
        $nama = trim($_POST['nama_kategori'] ?? '');
        if ($nama === '') {
            set_flash('danger', 'Nama kategori wajib diisi.');
        } else {
            $stmt = mysqli_prepare($koneksi, 'UPDATE kategori SET nama_kategori = ? WHERE id_kategori = ?');
            mysqli_stmt_bind_param($stmt, 'si', $nama, $id);
            if (mysqli_stmt_execute($stmt)) {
                set_flash('success', 'Kategori berhasil diperbarui.');
            } else {
                set_flash('danger', 'Nama kategori sudah ada.');
            }
        }
    } elseif ($aksi === 'hapus') {
        $id = (int) ($_POST['id_kategori'] ?? 0);
        $stmt = mysqli_prepare($koneksi, 'DELETE FROM kategori WHERE id_kategori = ?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', 'Kategori berhasil dihapus.');
        } else {
            set_flash('danger', 'Kategori tidak dapat dihapus karena masih dipakai produk.');
        }
    }
    redirect('admin/kategori.php');
}

$daftar_kategori = mysqli_query($koneksi,
    'SELECT k.*, COUNT(p.id_produk) AS jumlah_produk
     FROM kategori k
     LEFT JOIN produk p ON p.id_kategori = k.id_kategori
     GROUP BY k.id_kategori
     ORDER BY k.nama_kategori');

$page_title = 'Kelola Kategori';
require_once __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-4">Kelola Kategori</h4>
<div class="row g-4">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">Tambah Kategori</div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="aksi" value="tambah">
                    <div class="mb-3">
                        <input type="text" name="nama_kategori" class="form-control"
                               placeholder="Nama kategori baru" required maxlength="100">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Tambah</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Nama Kategori</th><th>Jumlah Produk</th><th class="text-end">Aksi</th></tr>
                    </thead>
                    <tbody>
                    <?php while ($kat = mysqli_fetch_assoc($daftar_kategori)): ?>
                        <tr>
                            <td><?= e($kat['nama_kategori']) ?></td>
                            <td><?= (int) $kat['jumlah_produk'] ?></td>
                            <td class="text-end">
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#editModal<?= $kat['id_kategori'] ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="post" class="d-inline"
                                      onsubmit="return confirm('Hapus kategori ini?')">
                                    <input type="hidden" name="aksi" value="hapus">
                                    <input type="hidden" name="id_kategori" value="<?= $kat['id_kategori'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <div class="modal fade" id="editModal<?= $kat['id_kategori'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <form method="post" class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Kategori</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="aksi" value="edit">
                                        <input type="hidden" name="id_kategori" value="<?= $kat['id_kategori'] ?>">
                                        <input type="text" name="nama_kategori" class="form-control"
                                               value="<?= e($kat['nama_kategori']) ?>" required maxlength="100">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
