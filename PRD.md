# PRD: Aplikasi Web Marketplace Tradisional

*Product Requirements Document · Versi 1.0 · Stack: PHP Native, MySQLi, Bootstrap 5*

## 1. Ringkasan Produk

**Nama produk:** TokoKita Marketplace (nama sementara)

**Deskripsi:** Aplikasi web marketplace sederhana yang mempertemukan penjual dan pembeli. Penjual dapat mengelola produk yang ditawarkan, sedangkan pembeli dapat menjelajahi produk, memasukkannya ke keranjang, lalu membuat pesanan yang tersimpan di database.

**Pendekatan arsitektur:** Web tradisional (server-side rendering). Setiap halaman dirender oleh PHP Native, data disimpan di MySQL melalui MySQLi, dan tampilan memakai Bootstrap.

## 2. Tujuan dan Ruang Lingkup

### 2.1 Tujuan

- Menyediakan alur belanja dasar dari memilih produk sampai pesanan tersimpan.
- Menyediakan pengelolaan data produk (CRUD) bagi penjual dan admin.
- Menghasilkan rancangan sistem yang terdokumentasi (entitas, database, ERD, alur proses, mockup) sebelum implementasi.

### 2.2 Dalam Ruang Lingkup (In Scope)

- Registrasi, login, dan logout dengan tiga peran: admin, penjual, pembeli.
- Daftar produk (dengan pencarian dan filter kategori) dan detail produk.
- CRUD produk dan kategori.
- Keranjang belanja (tambah, ubah jumlah, hapus item).
- Checkout: membuat dan menyimpan transaksi pemesanan (header dan detail) ke database.
- Riwayat pesanan pembeli dan daftar pesanan masuk untuk penjual.

### 2.2 Di Luar Ruang Lingkup (Out of Scope)

- Integrasi payment gateway dan ongkos kirim otomatis (pembayaran dicatat sebagai status manual).
- Chat penjual-pembeli, ulasan dan rating, kupon atau promo.
- Aplikasi mobile native dan REST API.

## 3. Pengguna dan Peran

| Peran | Deskripsi | Hak Akses Utama |
| --- | --- | --- |
| Pengunjung | Belum login | Melihat daftar dan detail produk, registrasi, login |
| Pembeli | Pengguna terdaftar | Keranjang, checkout, riwayat pesanan sendiri |
| Penjual | Pemilik produk | CRUD produk miliknya, melihat dan memperbarui status pesanan atas produknya |
| Admin | Pengelola sistem | CRUD semua produk dan kategori, kelola pengguna, melihat semua pesanan |

## 4. Analisis dan Perancangan Sistem

### 4.1 Identifikasi Entitas (butir a)

Aplikasi membutuhkan **6 entitas**:

| No | Entitas | Fungsi |
| --- | --- | --- |
| 1 | users | Menyimpan akun admin, penjual, dan pembeli |
| 2 | kategori | Pengelompokan produk |
| 3 | produk | Barang yang dijual oleh penjual |
| 4 | keranjang | Item sementara yang dipilih pembeli sebelum checkout |
| 5 | pesanan | Header transaksi pemesanan |
| 6 | detail\_pesanan | Rincian produk pada setiap pesanan |

### 4.2 Rancangan Database Relasional (butir b)

Nama database: `db_marketplace` · Engine: InnoDB · Charset: utf8mb4

**Tabel `users`**

| Atribut | Tipe | Keterangan |
| --- | --- | --- |
| id\_user | INT AUTO\_INCREMENT | **PK** |
| nama | VARCHAR(100) | Nama lengkap |
| email | VARCHAR(100) | Unik |
| password | VARCHAR(255) | Hash dari `password_hash()` |
| no\_hp | VARCHAR(20) | Opsional |
| alamat | TEXT | Alamat pengiriman default |
| role | ENUM('admin','penjual','pembeli') | Default `pembeli` |
| created\_at | TIMESTAMP | Default waktu sekarang |

**Tabel `kategori`**

| Atribut | Tipe | Keterangan |
| --- | --- | --- |
| id\_kategori | INT AUTO\_INCREMENT | **PK** |
| nama\_kategori | VARCHAR(100) | Unik |

**Tabel `produk`**

| Atribut | Tipe | Keterangan |
| --- | --- | --- |
| id\_produk | INT AUTO\_INCREMENT | **PK** |
| id\_penjual | INT | **FK** → users.id\_user |
| id\_kategori | INT | **FK** → kategori.id\_kategori |
| nama\_produk | VARCHAR(150) | Wajib |
| deskripsi | TEXT | Deskripsi lengkap |
| harga | DECIMAL(12,2) | Harga satuan |
| stok | INT | Jumlah tersedia |
| gambar | VARCHAR(255) | Nama file gambar |
| created\_at | TIMESTAMP | Default waktu sekarang |

**Tabel `keranjang`**

| Atribut | Tipe | Keterangan |
| --- | --- | --- |
| id\_keranjang | INT AUTO\_INCREMENT | **PK** |
| id\_user | INT | **FK** → users.id\_user (pembeli) |
| id\_produk | INT | **FK** → produk.id\_produk |
| jumlah | INT | Minimal 1 |
| created\_at | TIMESTAMP | Default waktu sekarang |

Constraint: UNIQUE (id\_user, id\_produk) agar produk yang sama tidak terduplikasi, jumlahnya ditambah.

**Tabel `pesanan`**

| Atribut | Tipe | Keterangan |
| --- | --- | --- |
| id\_pesanan | INT AUTO\_INCREMENT | **PK** |
| kode\_pesanan | VARCHAR(20) | Unik, contoh `ORD-20261106-0001` |
| id\_user | INT | **FK** → users.id\_user (pembeli) |
| tanggal\_pesanan | DATETIME | Waktu checkout |
| total\_harga | DECIMAL(12,2) | Total seluruh item |
| alamat\_pengiriman | TEXT | Snapshot alamat saat checkout |
| status | ENUM('menunggu','dibayar','dikirim','selesai','batal') | Default `menunggu` |

**Tabel `detail_pesanan`**

| Atribut | Tipe | Keterangan |
| --- | --- | --- |
| id\_detail | INT AUTO\_INCREMENT | **PK** |
| id\_pesanan | INT | **FK** → pesanan.id\_pesanan (ON DELETE CASCADE) |
| id\_produk | INT | **FK** → produk.id\_produk |
| harga\_satuan | DECIMAL(12,2) | Snapshot harga saat checkout |
| jumlah | INT | Jumlah dibeli |
| subtotal | DECIMAL(12,2) | harga\_satuan × jumlah |

**Relasi antar-tabel**

| Relasi | Kardinalitas | Penjelasan |
| --- | --- | --- |
| users → produk | 1 : N | Satu penjual memiliki banyak produk |
| kategori → produk | 1 : N | Satu kategori memuat banyak produk |
| users → keranjang | 1 : N | Satu pembeli memiliki banyak item keranjang |
| produk → keranjang | 1 : N | Satu produk bisa ada di banyak keranjang |
| users → pesanan | 1 : N | Satu pembeli membuat banyak pesanan |
| pesanan → detail\_pesanan | 1 : N | Satu pesanan terdiri dari banyak item |
| produk → detail\_pesanan | 1 : N | Satu produk bisa ada di banyak detail pesanan |

Catatan: relasi M:N antara pesanan dan produk dipecah oleh tabel `detail_pesanan`. Relasi M:N antara pembeli dan produk (keranjang) dipecah oleh tabel `keranjang`.

### 4.3 ERD (butir c)

ERD notasi sederhana (PK = primary key, FK = foreign key). Versi gambar visual dapat dibuat dengan draw.io, dbdiagram.io, atau MySQL Workbench berdasarkan struktur ini.

```
+------------------+        +--------------------+
|    kategori      |        |       users        |
+------------------+        +--------------------+
| PK id_kategori   |        | PK id_user         |
|    nama_kategori |        |    nama            |
+------------------+        |    email           |
        | 1                 |    password        |
        |                   |    no_hp, alamat   |
        | memiliki          |    role            |
        | N                 +--------------------+
+------------------+          | 1        | 1       | 1
|     produk       | N        |          |         |
+------------------+ <--------+          |         |
| PK id_produk     |  menjual            |         |
| FK id_penjual    |                     |         |
| FK id_kategori   |                     |         |
|    nama_produk   |         mengisi     |         | membuat
|    deskripsi     |                     | N       | N
|    harga, stok   |          +----------------+  +----------------+
|    gambar        |          |   keranjang    |  |    pesanan     |
+------------------+          +----------------+  +----------------+
   | 1          | 1           | PK id_keranjang|  | PK id_pesanan  |
   |            |             | FK id_user     |  |    kode_pesanan|
   |            +-----------> | FK id_produk   |  | FK id_user     |
   |              N           |    jumlah      |  |    tanggal     |
   |                          +----------------+  |    total_harga |
   | N                                            |    alamat      |
+--------------------+                           |    status      |
|  detail_pesanan    | N                      1 +----------------+
+--------------------+ <------------------------------+
| PK id_detail       |            terdiri dari
| FK id_pesanan      |
| FK id_produk       |
|    harga_satuan    |
|    jumlah          |
|    subtotal        |
+--------------------+
```

### 4.4 Alur Proses Transaksi (butir d)

Alur dari pembeli memilih produk sampai pesanan dibuat:

1. Pembeli membuka **Daftar Produk** (bisa mencari atau memfilter kategori).
2. Pembeli membuka **Detail Produk** untuk melihat harga, stok, dan deskripsi.
3. Pembeli menekan **Tambah ke Keranjang** dengan menentukan jumlah.
4. Sistem memeriksa status login. Jika belum login, diarahkan ke halaman login lalu kembali ke produk.
5. Sistem memvalidasi jumlah terhadap stok. Jika melebihi stok, tampil pesan error dan item tidak ditambahkan.
6. Jika valid, item disimpan ke tabel `keranjang` (jika produk sudah ada, jumlahnya ditambah).
7. Pembeli membuka **Keranjang Belanja**, dapat mengubah jumlah atau menghapus item, lalu menekan **Checkout**.
8. Pembeli mengonfirmasi alamat pengiriman.
9. Sistem menjalankan **transaksi database** (`begin_transaction`):
   - Membuat satu baris di `pesanan` dengan status `menunggu`.
   - Untuk setiap item keranjang: memeriksa stok ulang, menyimpan ke `detail_pesanan` (dengan snapshot harga), dan mengurangi `produk.stok`.
   - Menghitung dan memperbarui `total_harga`.
   - Mengosongkan keranjang pembeli.
   - `commit` jika semua berhasil, `rollback` jika ada kegagalan.
10. Sistem menampilkan **halaman konfirmasi pesanan** berisi kode pesanan dan rincian item.

Flowchart ringkas:

```
[Mulai] -> [Daftar Produk] -> [Detail Produk] -> [Tambah ke Keranjang]
   -> <Sudah login?> -- Tidak --> [Login] --+
          | Ya                              |
          v  <-----------------------------+
   <Jumlah <= stok?> -- Tidak --> [Pesan error]
          | Ya
          v
   [Simpan ke tabel keranjang] -> [Halaman Keranjang] -> [Checkout]
   -> [Konfirmasi alamat] -> [BEGIN TRANSACTION]
   -> [Insert pesanan] -> [Insert detail_pesanan + kurangi stok]
   -> <Semua berhasil?> -- Tidak --> [ROLLBACK] -> [Pesan gagal]
          | Ya
          v
   [COMMIT] -> [Kosongkan keranjang] -> [Halaman konfirmasi] -> [Selesai]
```

### 4.5 Rancangan Antarmuka / Mockup (butir e)

Mockup berikut berupa wireframe teks. Versi visual berwarna dapat dibuat dari spesifikasi ini.

**a. Halaman Daftar Produk**

```
+----------------------------------------------------------------+
| [Logo] TokoKita    [ Cari produk...        ][Cari]  Keranjang(2)|
|                                       Login | Daftar            |
+----------------------------------------------------------------+
| Kategori: [Semua v]   Urutkan: [Terbaru v]                      |
+----------------------------------------------------------------+
| +------------+  +------------+  +------------+  +------------+ |
| |  [gambar]  |  |  [gambar]  |  |  [gambar]  |  |  [gambar]  | |
| | Nama Produk|  | Nama Produk|  | Nama Produk|  | Nama Produk| |
| | Rp 150.000 |  | Rp 75.000  |  | Rp 300.000 |  | Rp 45.000  | |
| | Stok: 10   |  | Stok: 5    |  | Stok: 0    |  | Stok: 20   | |
| |[Lihat Detail] |[Lihat Detail] |[Habis]     |  |[Lihat Detail] |
| +------------+  +------------+  +------------+  +------------+ |
|                  [< 1 2 3 4 >]  (pagination)                    |
+----------------------------------------------------------------+
| Footer: (c) 2026 TokoKita                                       |
+----------------------------------------------------------------+
```

**b. Halaman Detail Produk**

```
+----------------------------------------------------------------+
| [Navbar sama seperti halaman daftar]                            |
+----------------------------------------------------------------+
| Beranda > Kategori > Nama Produk                                |
+----------------------------------------------------------------+
| +----------------------+   Nama Produk Lengkap                  |
| |                      |   Kategori : Elektronik                |
| |      [GAMBAR         |   Penjual  : Toko Budi                 |
| |       PRODUK]        |   Harga    : Rp 150.000                |
| |                      |   Stok     : 10                        |
| +----------------------+   Jumlah   : [ - ][ 1 ][ + ]            |
|                            [ Tambah ke Keranjang ]              |
+----------------------------------------------------------------+
| Deskripsi Produk                                                |
| Lorem ipsum dolor sit amet, consectetur adipiscing elit...      |
+----------------------------------------------------------------+
```

**c. Halaman Keranjang Belanja**

```
+----------------------------------------------------------------+
| [Navbar]                                                        |
+----------------------------------------------------------------+
| Keranjang Belanja                                               |
+----------------------------------------------------------------+
| Produk          | Harga      | Jumlah     | Subtotal   | Aksi    |
|-----------------+------------+------------+------------+---------|
| [img] Produk A  | Rp 150.000 | [ - ]2[ + ]| Rp 300.000 | [Hapus] |
| [img] Produk B  | Rp 75.000  | [ - ]1[ + ]| Rp 75.000  | [Hapus] |
+----------------------------------------------------------------+
|                               Total :  Rp 375.000               |
|                                                                 |
|  [< Lanjut Belanja]  [Perbarui Keranjang]  [ Checkout > ]       |
+----------------------------------------------------------------+
```

## 5. Kebutuhan Fungsional (butir g)

| ID | Fitur | Deskripsi | Prioritas |
| --- | --- | --- | --- |
| FR-01 | Registrasi | Pengguna mendaftar dengan nama, email, password; role default pembeli | Must |
| FR-02 | Login/Logout | Autentikasi dengan session PHP; password diverifikasi dengan `password_verify()` | Must |
| FR-03 | Daftar produk | Menampilkan produk dalam grid kartu, dengan pencarian, filter kategori, dan pagination | Must |
| FR-04 | Detail produk | Menampilkan gambar, nama, harga, stok, kategori, penjual, dan deskripsi | Must |
| FR-05 | CRUD produk | Penjual/admin dapat menambah, melihat, mengubah, dan menghapus produk, termasuk upload gambar | Must |
| FR-06 | CRUD kategori | Admin mengelola kategori produk | Should |
| FR-07 | Tambah ke keranjang | Pembeli menambahkan produk dengan jumlah tertentu; divalidasi terhadap stok | Must |
| FR-08 | Kelola keranjang | Ubah jumlah, hapus item, tampil total otomatis | Must |
| FR-09 | Checkout | Membuat pesanan dan detailnya dalam satu transaksi database, mengurangi stok, mengosongkan keranjang | Must |
| FR-10 | Riwayat pesanan | Pembeli melihat daftar dan detail pesanan miliknya | Should |
| FR-11 | Pesanan masuk | Penjual melihat pesanan atas produknya dan mengubah status | Could |
| FR-12 | Kelola pengguna | Admin melihat daftar pengguna | Could |

### Aturan Bisnis

- Jumlah pembelian tidak boleh melebihi stok dan minimal 1.
- Produk dengan stok 0 tidak dapat ditambahkan ke keranjang.
- Harga pada `detail_pesanan` adalah snapshot saat checkout, perubahan harga produk setelahnya tidak memengaruhi pesanan lama.
- Penjual hanya dapat mengubah atau menghapus produk miliknya sendiri (admin dapat semua).
- Produk yang sudah ada di `detail_pesanan` tidak boleh dihapus permanen (ditolak oleh foreign key), cukup stok dikosongkan.
- Penjual tidak dapat membeli produknya sendiri.

## 6. Kebutuhan Non-Fungsional

| Aspek | Kebutuhan |
| --- | --- |
| Keamanan | Prepared statement MySQLi untuk semua query (cegah SQL injection); `htmlspecialchars()` pada output (cegah XSS); password di-hash; pemeriksaan role pada setiap halaman terproteksi |
| Validasi upload | Hanya JPG/PNG/WebP, maksimal 2 MB, nama file di-rename unik |
| Integritas data | Foreign key InnoDB dan transaksi database pada checkout |
| Kinerja | Halaman daftar produk dimuat di bawah 3 detik dengan pagination (12 produk per halaman) |
| Kompatibilitas | Responsif (mobile, tablet, desktop) via Bootstrap 5; browser modern Chrome, Firefox, Edge |
| Kemudahan perawatan | Struktur folder terpisah, koneksi database terpusat di satu file |

## 7. Spesifikasi Teknis (butir f)

| Komponen | Teknologi |
| --- | --- |
| Bahasa server | PHP 8.x Native (tanpa framework) |
| Database | MySQL/MariaDB, akses via MySQLi (prosedural atau OOP) |
| Front-end | Bootstrap 5 (CDN), HTML5, sedikit JavaScript |
| Server lokal | XAMPP atau Laragon |
| Version control | Git (opsional) |

**Struktur folder yang disarankan**

```
marketplace/
├── config/
│   └── koneksi.php          # koneksi MySQLi
├── includes/
│   ├── header.php           # navbar + Bootstrap CSS
│   ├── footer.php
│   └── auth.php             # cek session & role
├── assets/
│   └── uploads/             # gambar produk
├── auth/
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── produk/
│   ├── index.php            # daftar produk
│   ├── detail.php           # detail produk
│   ├── tambah.php           # create
│   ├── edit.php             # update
│   └── hapus.php            # delete
├── keranjang/
│   ├── index.php
│   ├── tambah.php
│   ├── update.php
│   └── hapus.php
├── pesanan/
│   ├── checkout.php         # simpan transaksi
│   ├── sukses.php
│   └── riwayat.php
├── database/
│   └── db_marketplace.sql   # skrip tabel + data contoh
└── index.php
```

## 8. Kriteria Penerimaan

| Fitur | Skenario Uji | Hasil yang Diharapkan |
| --- | --- | --- |
| Daftar produk | Buka halaman produk | Semua produk tampil dalam grid dengan gambar, nama, harga, stok |
| Detail produk | Klik "Lihat Detail" | Informasi lengkap produk tampil sesuai id |
| Tambah produk | Isi form valid + gambar | Data tersimpan di tabel `produk` dan muncul di daftar |
| Edit produk | Ubah harga | Harga baru tampil di daftar dan detail |
| Hapus produk | Hapus produk yang belum pernah dipesan | Produk hilang dari daftar dan database |
| Tambah ke keranjang | Tambah jumlah 2 | Item tersimpan di `keranjang`, badge jumlah keranjang bertambah |
| Validasi stok | Tambah jumlah melebihi stok | Muncul pesan error, tidak ada data tersimpan |
| Checkout | Checkout 2 item | 1 baris `pesanan` + 2 baris `detail_pesanan`, stok berkurang, keranjang kosong |
| Transaksi gagal | Simulasikan error di tengah checkout | Rollback, tidak ada data parsial tersimpan |
| Akses role | Pembeli membuka halaman tambah produk | Ditolak dan diarahkan ke beranda |

## 9. Rencana Pengerjaan

| Tahap | Aktivitas | Output |
| --- | --- | --- |
| 1 | Analisis kebutuhan dan identifikasi entitas | Daftar entitas |
| 2 | Perancangan database dan ERD | Skema tabel + ERD |
| 3 | Perancangan alur transaksi dan mockup UI | Flowchart + 3 mockup |
| 4 | Setup proyek, database, koneksi, layout Bootstrap | Kerangka aplikasi |
| 5 | Implementasi autentikasi dan CRUD produk | Modul produk berjalan |
| 6 | Implementasi daftar dan detail produk | Halaman publik |
| 7 | Implementasi keranjang dan checkout | Transaksi tersimpan di database |
| 8 | Pengujian dan perbaikan | Hasil uji sesuai kriteria penerimaan |
| 9 | Dokumentasi dan pengumpulan | Laporan, source code, file SQL |

## 10. Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
| --- | --- | --- |
| Stok negatif saat checkout bersamaan | Data tidak konsisten | Cek stok ulang di dalam transaksi, gunakan `UPDATE ... WHERE stok >= jumlah` |
| SQL injection | Kebocoran data | Wajib prepared statement |
| Upload file berbahaya | Keamanan server | Validasi ekstensi, MIME type, ukuran, dan rename file |
| Ruang lingkup membengkak | Tugas tidak selesai | Prioritaskan fitur *Must*, kerjakan *Could* jika waktu ada |

## 11. Checklist Pemetaan ke Soal

| Butir Soal | Lokasi pada Dokumen |
| --- | --- |
| a. Minimal 5 entitas | Bagian 4.1 (6 entitas) |
| b. Rancangan database | Bagian 4.2 |
| c. ERD | Bagian 4.3 |
| d. Alur transaksi | Bagian 4.4 |
| e. Mockup UI (daftar produk, detail produk, keranjang) | Bagian 4.5 |
| f. Implementasi PHP Native, MySQLi, Bootstrap | Bagian 7 |
| g. Fitur minimal (daftar, detail, CRUD, keranjang, simpan transaksi) | Bagian 5 (FR-03 sampai FR-09) |
