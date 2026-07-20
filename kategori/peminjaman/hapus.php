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
    // Ganti nama tabel menjadi 'peminjaman' sesuai dengan struktur di edit.php
    $cek = mysqli_query($conn, "SELECT * FROM peminjaman WHERE id = '$id'");
    
    if(mysqli_num_rows($cek) > 0){
        $data = mysqli_fetch_assoc($cek);

        // 1. HAPUS FILE FISIK BA PENGAMBILAN
        $arr_ba_ambil = json_decode($data['link_ba_ambil'] ?? '[]', true);
        if (!is_array($arr_ba_ambil) && !empty($data['link_ba_ambil'])) { $arr_ba_ambil = [$data['link_ba_ambil']]; }
        foreach($arr_ba_ambil as $file) {
            if(!empty($file) && file_exists("upload/" . $file)) {
                unlink("upload/" . $file);
            }
        }

        // 2. HAPUS FILE FISIK BA PENGEMBALIAN
        $arr_ba_kembali = json_decode($data['link_ba_kembali'] ?? '[]', true);
        if (!is_array($arr_ba_kembali) && !empty($data['link_ba_kembali'])) { $arr_ba_kembali = [$data['link_ba_kembali']]; }
        foreach($arr_ba_kembali as $file) {
            if(!empty($file) && file_exists("upload/" . $file)) {
                unlink("upload/" . $file);
            }
        }

        // 3. HAPUS FILE FISIK DOKUMENTASI
        $arr_dokumentasi = json_decode($data['dokumentasi'] ?? '[]', true);
        if (!is_array($arr_dokumentasi) && !empty($data['dokumentasi'])) { $arr_dokumentasi = [$data['dokumentasi']]; }
        foreach($arr_dokumentasi as $file) {
            if(!empty($file) && file_exists("upload/" . $file)) {
                unlink("upload/" . $file);
            }
        }
        
        // 4. HAPUS DATA DARI DATABASE
        $hapus = mysqli_query($conn, "DELETE FROM peminjaman WHERE id = '$id'");
        
        if($hapus){
            echo "<script>alert('Data peminjaman beserta file lampirannya sukses dihapus!'); window.location='peminjaman.php';</script>";
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