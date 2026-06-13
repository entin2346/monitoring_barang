<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

if(isset($_POST['simpan'])){

    $jenis_berita_acara = mysqli_real_escape_string($conn,$_POST['jenis_berita_acara']);
    $tanggal = mysqli_real_escape_string($conn,$_POST['tanggal']);
    $nama_barang = mysqli_real_escape_string($conn,$_POST['nama_barang']);
    $merk_jenis = mysqli_real_escape_string($conn,$_POST['merk_jenis']);
    $jenis_barang = mysqli_real_escape_string($conn,$_POST['jenis_barang']);
    $sumber_barang = mysqli_real_escape_string($conn,$_POST['sumber_barang']);
    $satuan = mysqli_real_escape_string($conn,$_POST['satuan']);
    $jumlah = (int)$_POST['jumlah'];
    $tujuan = mysqli_real_escape_string($conn,$_POST['tujuan']);
    $kondisi_material = mysqli_real_escape_string($conn,$_POST['kondisi_material']);
    $keterangan = mysqli_real_escape_string($conn,$_POST['keterangan']);

    mysqli_query($conn,"
    INSERT INTO database_ba
    (
        jenis_berita_acara,
        tanggal,
        nama_barang,
        merk_jenis,
        jenis_barang,
        sumber_barang,
        satuan,
        jumlah,
        tujuan,
        kondisi_material,
        keterangan
    )
    VALUES
    (
        '$jenis_berita_acara',
        '$tanggal',
        '$nama_barang',
        '$merk_jenis',
        '$jenis_barang',
        '$sumber_barang',
        '$satuan',
        '$jumlah',
        '$tujuan',
        '$kondisi_material',
        '$keterangan'
    )
    ");

    header("Location:index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>Tambah Data BA</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<div class="container mt-4">

<h3>Tambah Data BA</h3>

<form method="POST">

<div class="row">

<div class="col-md-6 mb-3">
<label>Jenis BA</label>
<select name="jenis_berita_acara" class="form-control" required>
<option value="MASUK">MASUK</option>
<option value="KELUAR">KELUAR</option>
<option value="PENGEMBALIAN">PENGEMBALIAN</option>
<option value="RETURN">RETURN</option>
<option value="PERBAIKAN">PERBAIKAN</option>
</select>
</div>

<div class="col-md-6 mb-3">
<label>Tanggal</label>
<input type="date"
name="tanggal"
class="form-control"
required>
</div>

<div class="col-md-12 mb-3">
<label>Nama Barang</label>
<input type="text"
name="nama_barang"
class="form-control"
required>
</div>

<div class="col-md-6 mb-3">
<label>Merk / Jenis</label>
<input type="text"
name="merk_jenis"
class="form-control">
</div>

<div class="col-md-6 mb-3">
<label>Jenis Barang</label>
<input type="text"
name="jenis_barang"
class="form-control">
</div>

<div class="col-md-12 mb-3">
<label>Sumber Barang</label>
<input type="text"
name="sumber_barang"
class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Satuan</label>
<input type="text"
name="satuan"
class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Jumlah</label>
<input type="number"
name="jumlah"
class="form-control"
required>
</div>

<div class="col-md-6 mb-3">
<label>Tujuan</label>
<input type="text"
name="tujuan"
class="form-control">
</div>

<div class="col-md-6 mb-3">
<label>Kondisi Material</label>
<select name="kondisi_material" class="form-control">
<option value="BAIK">BAIK</option>
<option value="RUSAK">RUSAK</option>
<option value="PERBAIKAN">PERBAIKAN</option>
</select>
</div>

<div class="col-md-12 mb-3">
<label>Keterangan</label>
<textarea
name="keterangan"
class="form-control"
rows="3"></textarea>
</div>

</div>

<button
type="submit"
name="simpan"
class="btn btn-success">
Simpan
</button>

<a href="index.php"
class="btn btn-secondary">
Kembali
</a>

</form>

</div>

</body>
</html>