<?php
session_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['login'])){
    header("Location: ../../login/index.php");
    exit;
}

if(strtolower($_SESSION['role']) != 'admin'){
    die("Akses ditolak.");
}
include "../../config/koneksi.php";

$id = $_GET['id'] ?? 0;

if($id > 0){
    // Validasi pengaman agar data yang dihapus benar-benar berada di bawah kategori peminjaman
    $cek = mysqli_query($conn, "SELECT id FROM material_gudang WHERE id = '$id' AND jenis_kategori LIKE '%peminjaman%'");
    
    if(mysqli_num_rows($cek) > 0){
        $hapus = mysqli_query($conn, "DELETE FROM material_gudang WHERE id = '$id'");
        if($hapus){
            echo "<script>alert('Data peminjaman sukses dihapus!'); window.location='peminjaman.php';</script>";
        } else {
            echo "<script>alert('Gagal menghapus data dari database.'); window.location='peminjaman.php';</script>";
        }
    } else {
        echo "<script>alert('Data tidak valid atau tidak ditemukan!'); window.location='peminjaman.php';</script>";
    }
} else {
    header("Location: peminjaman.php");
    exit;
}
?>