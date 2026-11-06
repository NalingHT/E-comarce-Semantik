<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/functions.php';

function require_login() {
    if (!is_logged_in()) {
        set_flash('warning', 'Silakan login terlebih dahulu.');
        redirect('auth/login.php');
    }
}

function require_role($roles) {
    require_login();
    $roles = (array) $roles;
    if (!in_array($_SESSION['user']['role'], $roles, true)) {
        set_flash('danger', 'Anda tidak memiliki akses ke halaman tersebut.');
        redirect('index.php');
    }
}
