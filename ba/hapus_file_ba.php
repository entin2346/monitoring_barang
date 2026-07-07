<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan.");
}

$id = (int)$_GET['id'];

// 1. Ambil nama_barang dari database_ba sebelum barisnya dihapus
$query_ba = mysqli_query($conn, "SELECT nama_barang, file_ba FROM database_ba WHERE id='$id'");
$data_ba = mysqli_fetch_assoc($query_ba);

if ($data_ba) {
    $nama_barang = mysqli_real_escape_string($conn, trim($data_ba['nama_barang']));

    // Hapus file lampiran BA fisiknya jika ada
    if (!empty($data_ba['file_ba'])) {
        $files = json_decode($data_ba['file_ba'], true);
        if (is_array($files)) {
            foreach ($files as $file) {
                @unlink("../uploads/" . $file);
            }
        }
    }

    // 2. SINKRONISASI: Hapus data di material_gudang yang namanya sama
    if (!empty($nama_barang)) {
        mysqli_query($conn, "
            DELETE FROM material_gudang 
            WHERE TRIM(LOWER(nama_material)) = TRIM(LOWER('$nama_barang'))
        ");
    }

    // 3. Hapus data utama di database_ba itu sendiri
    mysqli_query($conn, "DELETE FROM database_ba WHERE id='$id'");
}

header("Location: index.php");
exit;
?>