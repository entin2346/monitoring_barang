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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id > 0){
    $query = "DELETE FROM material_gudang WHERE id = $id";
    if(mysqli_query($conn, $query)){
        echo "<script>alert('Data berhasil dihapus!'); window.location.href='stok.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data: ".mysqli_error($conn)."'); window.location.href='stok.php';</script>";
    }
} else {
    header("Location: stok.php");
}
?>