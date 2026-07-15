<?php
include "../config/koneksi.php";

$id = (int)$_GET['id'];

// 1. Ambil nama_material dan foto sebelum dihapus untuk acuan hapus silang
$data = mysqli_fetch_assoc(
    mysqli_query($conn, "
        SELECT nama_material, foto_material 
        FROM material_gudang 
        WHERE id='$id'
    ")
);
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

if(strtolower($_SESSION['role']) != 'admin'){
    die("Akses ditolak.");
}
if ($data) {
    $nama_material = mysqli_real_escape_string($conn, trim($data['nama_material']));

    // Hapus fisik file gambar jika ada
    if (!empty($data['foto_material'])) {
        @unlink("upload/" . $data['foto_material']);
    }

    // 2. SINKRONISASI: Hapus data di database_ba yang namanya sama
    if (!empty($nama_material)) {
        mysqli_query($conn, "
            DELETE FROM database_ba 
            WHERE TRIM(LOWER(nama_barang)) = TRIM(LOWER('$nama_material'))
        ");
    }

    // 3. Hapus data utama di material_gudang
    mysqli_query($conn, "
        DELETE FROM material_gudang
        WHERE id='$id'
    ");
}

header("Location:index.php");
exit;
?>