<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../../login/index.php");
    exit;
}
include "../../config/koneksi.php";

$id = $_GET['id'] ?? '';

if (!empty($id)) {
    // Amankan parameter ID dan pastikan data yang dihapus ber-kategori ex_bongkaran
    $id_clean = mysqli_real_escape_string($conn, $id);
    $query = "DELETE FROM material_gudang WHERE id = '$id_clean' AND jenis_kategori = 'ex_bongkaran'";
    
    if (mysqli_query($conn, $query)) {
        header("Location: ex_bongkaran.php");
        exit;
    } else {
        echo "Gagal menghapus data: " . mysqli_error($conn);
    }
} else {
    header("Location: ex_bongkaran.php");
    exit;
}
?>