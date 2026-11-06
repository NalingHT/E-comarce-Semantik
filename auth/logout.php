<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/koneksi.php';

$_SESSION = [];
session_destroy();

session_start();
set_flash('success', 'Anda berhasil logout.');
redirect('auth/login.php');
