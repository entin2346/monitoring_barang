<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../../login/index.php");
    exit;
}
include "../../config/koneksi.php";

$id = (int)($_GET['id'] ?? 0);

if($id > 0){
    // Pastikan data yang dihapus berasal dari kategori Pre Memory demi keamanan data area lain
    $cek = mysqli_query($conn, "SELECT id FROM material_gudang WHERE id = '$id' AND jenis_kategori LIKE '%pre%memory%'");
    
    if(mysqli_num_rows($cek) > 0){
        $hapus = mysqli_query($conn, "DELETE FROM material_gudang WHERE id = '$id'");
        if($hapus){
            echo "<script>alert('Data sukses terhapus!'); window.location='pre_memory.php';</script>";
        } else {
            echo "<script>alert('Gagal menghapus data dari database.'); window.location='pre_memory.php';</script>";
        }
    } else {
        echo "<script>alert('Data ilegal atau tidak ditemukan!'); window.location='pre_memory.php';</script>";
    }
} else {
    header("Location: pre_memory.php");
    exit;
}
?>