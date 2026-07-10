<?php
session_start();
if(!isset($_SESSION['login'])){
    // PERBAIKAN PATH LOGIN: Naik 2 tingkat ke atas
    header("Location: ../../login/index.php");
    exit;
}
// PERBAIKAN PATH KONEKSI: Naik 2 tingkat (../../) agar keluar dari subfolder non_po dan kategori
include "../../config/koneksi.php";

if(isset($_POST['submit'])){
    $jenis_ba = mysqli_real_escape_string($conn, $_POST['jenis_ba']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $nama_material = mysqli_real_escape_string($conn, $_POST['nama_material']);
    $merk_jenis = mysqli_real_escape_string($conn, $_POST['merk_jenis']);
    $jenis_barang = mysqli_real_escape_string($conn, $_POST['jenis_barang']);
    $sumber_barang = mysqli_real_escape_string($conn, $_POST['sumber_barang']);
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);
    $jumlah = (int)$_POST['jumlah'];
    $tujuan = mysqli_real_escape_string($conn, $_POST['tujuan']);
    $kondisi = mysqli_real_escape_string($conn, $_POST['kondisi']);
    $vendor = mysqli_real_escape_string($conn, $_POST['vendor']);
    $berita_acara = mysqli_real_escape_string($conn, $_POST['berita_acara']);
    // Otomatis mengunci kategori sebagai "Non PO" sesuai dengan foldernya
    $jenis_kategori = "Non PO"; 

    $query = "INSERT INTO material_gudang (jenis_ba, tanggal, nama_material, merk_jenis, jenis_barang, sumber_barang, satuan, jumlah, tujuan, kondisi, vendor, berita_acara, jenis_kategori) 
              VALUES ('$jenis_ba', '$tanggal', '$nama_material', '$merk_jenis', '$jenis_barang', '$sumber_barang', '$satuan', '$jumlah', '$tujuan', '$kondisi', '$vendor', '$berita_acara', '$jenis_kategori')";

    if(mysqli_query($conn, $query)){
        echo "<script>alert('Data berhasil ditambahkan!'); window.location.href='non_po.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan data: ".mysqli_error($conn)."');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>I-CALM | Tambah Non PO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background: #f4f7fc; font-family: 'Plus Jakarta Sans', sans-serif; padding: 40px 0; }
        .card-custom { background: #ffffff; border: 1px solid rgba(148, 163, 184, 0.12); border-radius: 16px; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom p-5 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold m-0 text-primary">Tambah Material Non PO</h4>
                    <a href="non_po.php" class="btn btn-outline-secondary btn-sm fw-bold">Kembali</a>
                </div>
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label fw-bold">Jenis BA</label><input type="text" name="jenis_ba" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Tanggal</label><input type="date" name="tanggal" class="form-control" required></div>
                        <div class="col-md-12"><label class="form-label fw-bold">Nama Barang</label><input type="text" name="nama_material" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Merk / Jenis</label><input type="text" name="merk_jenis" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Jenis Barang</label><input type="text" name="jenis_barang" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Sumber Barang</label><input type="text" name="sumber_barang" class="form-control"></div>
                        <div class="col-md-3"><label class="form-label fw-bold">Satuan</label><input type="text" name="satuan" class="form-control" placeholder="Pcs/Mtr" required></div>
                        <div class="col-md-3"><label class="form-label fw-bold">Jumlah</label><input type="number" name="jumlah" class="form-control" min="0" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Tujuan</label><input type="text" name="tujuan" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Kondisi</label><input type="text" name="kondisi" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Vendor</label><input type="text" name="vendor" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Berita Acara</label><input type="text" name="berita_acara" class="form-control"></div>
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary w-100 mt-4 fw-bold py-2" style="border-radius: 10px;">Simpan Data</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>