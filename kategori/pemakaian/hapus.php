<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: /monitoring_barang/login/index.php");
    exit;
}
include "../../config/koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $query = "DELETE FROM material_gudang WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        header("Location: pemakaian.php");
        exit;
    } else {
        echo "<script>alert('Gagal menghapus data!'); window.location='pemakaian.php';</script>";
    }
} else {
    header("Location: pemakaian.php");
    exit;
}
?>