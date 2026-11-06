<?php
function upload_gambar($field, &$error) {
    if (empty($_FILES[$field]['name'])) {
        return null;
    }

    $file     = $_FILES[$field];
    $maksimal = 2 * 1024 * 1024;
    $ekstensi_diizinkan = ['jpg', 'jpeg', 'png', 'webp'];
    $mime_diizinkan     = ['image/jpeg', 'image/png', 'image/webp'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload gambar gagal. Silakan coba lagi.';
        return false;
    }
    if ($file['size'] > $maksimal) {
        $error = 'Ukuran gambar maksimal 2 MB.';
        return false;
    }

    $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ekstensi, $ekstensi_diizinkan, true)) {
        $error = 'Format gambar harus JPG, PNG, atau WebP.';
        return false;
    }

    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $mime_diizinkan, true)) {
        $error = 'File yang diupload bukan gambar valid.';
        return false;
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $nama_baru = 'produk_' . uniqid('', true) . '.' . $ekstensi;
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $nama_baru)) {
        $error = 'Gagal menyimpan gambar.';
        return false;
    }

    return $nama_baru;
}

function hapus_gambar($nama_file) {
    if ($nama_file && is_file(UPLOAD_DIR . $nama_file)) {
        unlink(UPLOAD_DIR . $nama_file);
    }
}
