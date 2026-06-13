<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

$id = $_GET['id'];

$data = mysqli_query($conn,"
    SELECT *
    FROM database_ba
    WHERE id='$id'
");

$d = mysqli_fetch_assoc($data);

if(isset($_POST['update'])){

    $jenis_berita_acara = $_POST['jenis_berita_acara'];
    $tanggal = $_POST['tanggal'];
    $nama_barang = $_POST['nama_barang'];
    $merk_jenis = $_POST['merk_jenis'];
    $jenis_barang = $_POST['jenis_barang'];
    $sumber_barang = $_POST['sumber_barang'];
    $satuan = $_POST['satuan'];
    $jumlah = $_POST['jumlah'];
    $tujuan = $_POST['tujuan'];
    $kondisi_material = $_POST['kondisi_material'];

    mysqli_query($conn,"
        UPDATE database_ba SET

        jenis_berita_acara='$jenis_berita_acara',
        tanggal='$tanggal',
        nama_barang='$nama_barang',
        merk_jenis='$merk_jenis',
        jenis_barang='$jenis_barang',
        sumber_barang='$sumber_barang',
        satuan='$satuan',
        jumlah='$jumlah',
        tujuan='$tujuan',
        kondisi_material='$kondisi_material'

        WHERE id='$id'
    ");

    echo "
    <script>
        alert('Data berhasil diupdate');
        window.location='index.php';
    </script>
    ";
}
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>Edit BA</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-4">

<h2>Edit Database BA</h2>

<form method="POST">

<div class="mb-3">
<label>Jenis Berita Acara</label>
<input type="text"
name="jenis_berita_acara"
class="form-control"
value="<?= htmlspecialchars($d['jenis_berita_acara']); ?>">
</div>

<div class="mb-3">
<label>Tanggal</label>
<input type="date"
name="tanggal"
class="form-control"
value="<?= $d['tanggal']; ?>">
</div>

<div class="mb-3">
<label>Nama Barang</label>
<input type="text"
name="nama_barang"
class="form-control"
value="<?= htmlspecialchars($d['nama_barang']); ?>">
</div>

<div class="mb-3">
<label>Merk / Jenis</label>
<input type="text"
name="merk_jenis"
class="form-control"
value="<?= htmlspecialchars($d['merk_jenis']); ?>">
</div>

<div class="mb-3">
<label>Jenis Barang</label>
<input type="text"
name="jenis_barang"
class="form-control"
value="<?= htmlspecialchars($d['jenis_barang']); ?>">
</div>

<div class="mb-3">
<label>Sumber Barang</label>
<input type="text"
name="sumber_barang"
class="form-control"
value="<?= htmlspecialchars($d['sumber_barang']); ?>">
</div>

<div class="mb-3">
<label>Satuan</label>
<input type="text"
name="satuan"
class="form-control"
value="<?= htmlspecialchars($d['satuan']); ?>">
</div>

<div class="mb-3">
<label>Jumlah</label>
<input type="number"
name="jumlah"
class="form-control"
value="<?= $d['jumlah']; ?>">
</div>

<div class="mb-3">
<label>Tujuan</label>
<input type="text"
name="tujuan"
class="form-control"
value="<?= htmlspecialchars($d['tujuan']); ?>">
</div>

<div class="mb-3">
<label>Kondisi Material</label>
<input type="text"
name="kondisi_material"
class="form-control"
value="<?= htmlspecialchars($d['kondisi_material']); ?>">
</div>

<button type="submit"
name="update"
class="btn btn-primary">
Update
</button>

<a href="index.php"
class="btn btn-secondary">
Kembali
</a>

</form>

</div>

</body>
</html>