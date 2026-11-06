-- TokoKita Marketplace - Skema Database
-- Import file ini melalui phpMyAdmin (http://localhost/phpmyadmin)

CREATE DATABASE IF NOT EXISTS db_marketplace
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE db_marketplace;

-- ===================== TABEL USERS =====================
CREATE TABLE users (
  id_user    INT AUTO_INCREMENT PRIMARY KEY,
  nama       VARCHAR(100) NOT NULL,
  email      VARCHAR(100) NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  no_hp      VARCHAR(20) DEFAULT NULL,
  alamat     TEXT DEFAULT NULL,
  role       ENUM('admin','penjual','pembeli') NOT NULL DEFAULT 'pembeli',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ===================== TABEL KATEGORI =====================
CREATE TABLE kategori (
  id_kategori   INT AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ===================== TABEL PRODUK =====================
CREATE TABLE produk (
  id_produk   INT AUTO_INCREMENT PRIMARY KEY,
  id_penjual  INT NOT NULL,
  id_kategori INT NOT NULL,
  nama_produk VARCHAR(150) NOT NULL,
  deskripsi   TEXT,
  harga       DECIMAL(12,2) NOT NULL DEFAULT 0,
  stok        INT NOT NULL DEFAULT 0,
  gambar      VARCHAR(255) DEFAULT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_penjual)  REFERENCES users(id_user),
  FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori)
) ENGINE=InnoDB;

-- ===================== TABEL KERANJANG =====================
CREATE TABLE keranjang (
  id_keranjang INT AUTO_INCREMENT PRIMARY KEY,
  id_user      INT NOT NULL,
  id_produk    INT NOT NULL,
  jumlah       INT NOT NULL DEFAULT 1,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_keranjang (id_user, id_produk),
  FOREIGN KEY (id_user)   REFERENCES users(id_user),
  FOREIGN KEY (id_produk) REFERENCES produk(id_produk)
) ENGINE=InnoDB;

-- ===================== TABEL PESANAN =====================
CREATE TABLE pesanan (
  id_pesanan        INT AUTO_INCREMENT PRIMARY KEY,
  kode_pesanan      VARCHAR(20) NOT NULL UNIQUE,
  id_user           INT NOT NULL,
  tanggal_pesanan   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  total_harga       DECIMAL(12,2) NOT NULL DEFAULT 0,
  alamat_pengiriman TEXT,
  status            ENUM('menunggu','dibayar','dikirim','selesai','batal') NOT NULL DEFAULT 'menunggu',
  FOREIGN KEY (id_user) REFERENCES users(id_user)
) ENGINE=InnoDB;

-- ===================== TABEL DETAIL PESANAN =====================
CREATE TABLE detail_pesanan (
  id_detail    INT AUTO_INCREMENT PRIMARY KEY,
  id_pesanan   INT NOT NULL,
  id_produk    INT NOT NULL,
  harga_satuan DECIMAL(12,2) NOT NULL,
  jumlah       INT NOT NULL,
  subtotal     DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan) ON DELETE CASCADE,
  FOREIGN KEY (id_produk)  REFERENCES produk(id_produk)
) ENGINE=InnoDB;

-- ===================== DATA CONTOH =====================
-- Password semua akun contoh: password
INSERT INTO users (nama, email, password, no_hp, alamat, role) VALUES
('Admin TokoKita', 'admin@tokokita.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081200000001', 'Jl. Merdeka No. 1, Jakarta', 'admin'),
('Budi Santoso', 'budi@tokokita.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081200000002', 'Jl. Kenanga No. 5, Bandung', 'penjual'),
('Sari Wijaya', 'sari@tokokita.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081200000003', 'Jl. Melati No. 8, Surabaya', 'penjual'),
('Andi Pembeli', 'andi@tokokita.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081200000004', 'Jl. Anggrek No. 12, Yogyakarta', 'pembeli');

INSERT INTO kategori (nama_kategori) VALUES
('Elektronik'), ('Fashion'), ('Makanan'), ('Kesehatan'), ('Olahraga'), ('Rumah Tangga');

INSERT INTO produk (id_penjual, id_kategori, nama_produk, deskripsi, harga, stok, gambar) VALUES
(2, 1, 'Headphone Wireless Bass Pro', 'Headphone bluetooth dengan bass dalam, baterai 30 jam, dan noise isolation.', 350000, 15, 'headphone.svg'),
(2, 1, 'Mouse Gaming RGB 7200 DPI', 'Mouse gaming presisi tinggi dengan lampu RGB dan 6 tombol makro.', 175000, 25, 'mouse.svg'),
(2, 2, 'Kaos Polos Cotton Premium', 'Kaos 100% cotton combed 30s, adem dan tidak mudah melar. Tersedia ukuran M-XL.', 85000, 40, 'kaos.svg'),
(2, 5, 'Matras Yoga Anti Slip 6mm', 'Matras yoga tebal 6mm, anti slip, mudah digulung dan dibawa.', 150000, 10, 'matras.svg'),
(3, 3, 'Keripik Singkong Balado 250g', 'Keripik singkong renyah rasa balado pedas manis, kemasan 250 gram.', 25000, 50, 'keripik.svg'),
(3, 3, 'Kopi Arabika Gayo 200g', 'Biji kopi arabika asli Gayo, medium roast, sangrai segar setiap minggu.', 68000, 30, 'kopi.svg'),
(3, 4, 'Madu Murni Hutan 500ml', 'Madu hutan murni tanpa campuran, dipanen langsung dari petani lokal.', 120000, 20, 'madu.svg'),
(3, 6, 'Sapu Lidi Taman Premium', 'Sapu lidi kuat untuk taman dan halaman, gagang kayu mahoni.', 35000, 0, 'sapu.svg'),
(2, 1, 'Keyboard Mechanical TKL', 'Keyboard mechanical tenkeyless dengan switch tactile, keycap double-shot.', 520000, 8, 'keyboard.svg'),
(3, 2, 'Tas Selempang Kulit Asli', 'Tas selempang kulit sapi asli, jahitan rapi, cocok untuk harian.', 275000, 12, 'tas.svg');
