<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}
include "../config/koneksi.php";

$id = $_GET['id'] ?? '';

if(!empty($id)){
    // Melakukan query hapus data berdasarkan ID
    $query = "DELETE FROM material_gudang WHERE id = '$id'";
    
    if(mysqli_query($conn, $query)){
        echo "<script>
                alert('Data berhasil dihapus!');
                window.location.href = 'non_po.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal menghapus data: " . mysqli_error($conn) . "');
                window.location.href = 'non_po.php';
              </script>";
    }
} else {
    header("Location: non_po.php");
    exit;
}
?>