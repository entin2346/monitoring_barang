<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "monitoring_barang"
);

if(!$conn){
    die("Koneksi gagal");
}
?>