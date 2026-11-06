# Panduan Deployment & Publikasi — TokoKita Marketplace

Dokumen ini memandu langkah **h (InfinityFree)** dan **i (GitHub)** beserta bahan laporan **j**.

---

## h. Deploy ke InfinityFree

### 1. Daftar akun dan buat hosting
1. Buka https://infinityfree.com → **Sign Up** (gratis).
2. Setelah login, klik **Create Account** → pilih subdomain gratis, misal `tokokita.rf.gd`.
3. Tunggu akun aktif, lalu catat di **Account Details**:
   - **MySQL Hostname** (contoh: `sql123.infinityfree.com`)
   - **MySQL Username** (contoh: `if0_38123456`)
   - **MySQL Database** — buat dulu lewat menu **MySQL Databases** dengan nama `db_marketplace`, sehingga nama lengkapnya menjadi `if0_38123456_db_marketplace`
   - **Password hosting** = password akun InfinityFree Anda.

### 2. Import database
1. Di panel InfinityFree buka **phpMyAdmin**.
2. Pilih database `if0_XXXXXXX_db_marketplace` → tab **Import**.
3. Pilih file `database/db_marketplace.sql` → **Go**.

### 3. Upload source code
1. Buka **File Manager** (atau FTP: hostname `ftpupload.net`, username/password akun hosting).
2. Masuk ke folder `htdocs/`.
3. Upload **seluruh isi folder proyek** (bukan foldernya), sehingga `index.php` berada langsung di dalam `htdocs/`.
   - Aplikasi sudah otomatis mendeteksi BASE_URL, jadi tidak perlu ubah kode apa pun.
4. Buat file `config/database.local.php` di server dengan isi kredensial InfinityFree:

```php
<?php
$host     = 'sql123.infinityfree.com';        // MySQL Hostname Anda
$username = 'if0_38123456';                   // MySQL Username Anda
$password = 'PASSWORD_HOSTING_ANDA';          // password akun hosting
$database = 'if0_38123456_db_marketplace';    // nama database lengkap
```

> File ini sudah tercantum di `.gitignore` sehingga aman — tidak ikut ke GitHub.

### 4. Uji
Buka `http://tokokita.rf.gd` (ganti dengan subdomain Anda). Login dengan akun contoh:
- Admin: `admin@tokokita.test` / `password`
- Penjual: `budi@tokokita.test` / `password`
- Pembeli: `andi@tokokita.test` / `password`

**URL demo untuk laporan:** `http://____________________` ← isi subdomain Anda

---

## i. Upload Source Code ke GitHub

Git tidak terinstal di komputer ini, jadi cara termudah adalah lewat web GitHub:

1. Buka https://github.com → login → **New repository**.
2. Nama: `tokokita-marketplace` → **Public** → **Create repository**.
3. Klik **uploading an existing file**.
4. Drag & drop **seluruh isi folder proyek** ke halaman upload.
   - **PENTING:** Pastikan file `config/database.local.php` TIDAK ikut terupload (berisi kredensial). Cukup jangan seret file itu — GitHub web upload tidak membaca `.gitignore` secara otomatis.
5. Klik **Commit changes**.

Alternatif (jika nanti Git terinstal):

```bash
git init
git add .
git commit -m "TokoKita Marketplace - PHP Native, MySQLi, Bootstrap 5"
git branch -M main
git remote add origin https://github.com/USERNAME/tokokita-marketplace.git
git push -u origin main
```

`.gitignore` sudah disiapkan sehingga `config/database.local.php` dan gambar upload otomatis dikecualikan.

**URL repository untuk laporan:** `https://github.com/____________________` ← isi milik Anda

---

## j. Bahan Laporan

| Item | Lokasi |
| --- | --- |
| Dokumen perancangan (PRD: entitas, skema DB, ERD, alur transaksi, mockup) | `PRD.md` |
| Source code | folder proyek ini |
| Skrip database + data contoh | `database/db_marketplace.sql` |
| URL demo InfinityFree | isi setelah langkah h selesai |
| URL GitHub | isi setelah langkah i selesai |

### Screenshot yang perlu diambil
1. **Aplikasi berjalan** — halaman daftar produk, detail produk, keranjang (bisa dari localhost maupun URL demo).
2. **Struktur database** — buka phpMyAdmin → database `db_marketplace` → tab **Structure**, dan/atau menu **Designer** untuk diagram relasi.
3. **Hasil pengujian transaksi** — buka tabel `pesanan` dan `detail_pesanan` di phpMyAdmin setelah checkout, dan halaman riwayat pesanan pembeli.

### Hasil pengujian transaksi (sudah dijalankan, siap disalin ke laporan)

Skenario: pembeli `andi@tokokita.test` checkout 2 item (2× Headphone Rp350.000 + 1× Kopi Rp68.000).

| Pemeriksaan | Hasil |
| --- | --- |
| Kode pesanan terbentuk | `ORD-20261106-0001` |
| Baris `pesanan` | 1 baris, total Rp768.000, status `menunggu` |
| Baris `detail_pesanan` | 2 baris dengan snapshot harga (Rp700.000 + Rp68.000) |
| Stok produk berkurang | Headphone 15→13, Kopi 30→29 |
| Keranjang pembeli | kosong setelah checkout |
| Rollback saat stok tidak cukup | `UPDATE ... WHERE stok >= jumlah` gagal → transaksi dibatalkan, tidak ada data parsial |
