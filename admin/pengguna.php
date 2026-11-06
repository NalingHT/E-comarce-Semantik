<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$pengguna_list = mysqli_query($koneksi,
    'SELECT id_user, nama, email, no_hp, role, created_at FROM users ORDER BY created_at DESC');

$page_title = 'Kelola Pengguna';
require_once __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-4">Kelola Pengguna</h4>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Nama</th><th>Email</th><th>No. HP</th><th>Role</th><th>Terdaftar</th></tr>
            </thead>
            <tbody>
            <?php while ($pengguna = mysqli_fetch_assoc($pengguna_list)): ?>
                <tr>
                    <td><?= e($pengguna['nama']) ?></td>
                    <td><?= e($pengguna['email']) ?></td>
                    <td><?= e($pengguna['no_hp'] ?? '-') ?></td>
                    <td>
                        <?php
                        $warna = ['admin' => 'danger', 'penjual' => 'warning', 'pembeli' => 'success'];
                        ?>
                        <span class="badge text-bg-<?= $warna[$pengguna['role']] ?>">
                            <?= e(ucfirst($pengguna['role'])) ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y', strtotime($pengguna['created_at'])) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
