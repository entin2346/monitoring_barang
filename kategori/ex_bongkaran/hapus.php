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

if ($id > 0) {
    $query = "DELETE FROM material_gudang WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        header("Location: ex_bongkaran.php");
        exit;
    } else {
        echo "<script>alert('Gagal menghapus data!'); window.location='ex_bongkaran.php';</script>";
    }
} else {
    header("Location: ex_bongkaran.php");
    exit;
}
?>