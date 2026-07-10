<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../../login/index.php");
    exit;
}
include "../../config/koneksi.php";

if (isset($_POST['submit'])) {
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
    $jenis_kategori = 'ex_bongkaran'; // Default kuncian kategori

    $query = "INSERT INTO material_gudang (jenis_ba, tanggal, nama_material, merk_jenis, jenis_barang, sumber_barang, satuan, jumlah, tujuan, kondisi, vendor, berita_acara, jenis_kategori) 
              VALUES ('$jenis_ba', '$tanggal', '$nama_material', '$merk_jenis', '$jenis_barang', '$sumber_barang', '$satuan', '$jumlah', '$tujuan', '$kondisi', '$vendor', '$berita_acara', '$jenis_kategori')";

    if (mysqli_query($conn, $query)) {
        header("Location: ex_bongkaran.php");
        exit;
    } else {
        $error = "Gagal menambah data: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Data Ex Bongkaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f4f7fc; font-family: 'Plus Jakarta Sans', sans-serif; padding: 40px 0; }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card p-4">
                <h4 class="fw-bold text-primary mb-4">Tambah Data Material Ex Bongkaran</h4>
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?= $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis BA</label>
                            <input type="text" name="jenis_ba" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nama Barang / Material</label>
                            <input type="text" name="nama_material" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Merk / Jenis</label>
                            <input type="text" name="merk_jenis" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Barang</label>
                            <input type="text" name="jenis_barang" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Sumber Barang</label>
                            <input type="text" name="sumber_barang" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Satuan</label>
                            <input type="text" name="satuan" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Jumlah</label>
                            <input type="number" name="jumlah" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tujuan</label>
                            <input type="text" name="tujuan" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kondisi</label>
                            <input type="text" name="kondisi" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vendor</label>
                            <input type="text" name="vendor" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Berita Acara</label>
                            <input type="text" name="berita_acara" class="form-control">
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" name="submit" class="btn btn-primary px-4 fw-bold">Simpan</button>
                        <a href="ex_bongkaran.php" class="btn btn-secondary px-4">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>