<?php
session_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

if(strtolower($_SESSION['role']) != 'admin'){
    die("Akses ditolak.");
}
include "../config/koneksi.php";

$id = (int)$_GET['id'];

mysqli_query($conn,"
    DELETE FROM database_ba
    WHERE id='$id'
");

echo "
<script>
alert('Data berhasil dihapus');
window.location='index.php';
</script>
";
?>