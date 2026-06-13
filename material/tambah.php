<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

if(isset($_POST['simpan'])){

    $nama_material = mysqli_real_escape_string($conn,$_POST['nama_material']);
    $satuan = mysqli_real_escape_string($conn,$_POST['satuan']);
    $jumlah = (int)$_POST['jumlah'];
    $no_rak = mysqli_real_escape_string($conn,$_POST['no_rak']);
    $kondisi = mysqli_real_escape_string($conn,$_POST['kondisi']);
    $lokasi = mysqli_real_escape_string($conn,$_POST['lokasi']);
    $keterangan = mysqli_real_escape_string($conn,$_POST['keterangan']);

    $foto = '';

    if($_FILES['foto']['name'] != ''){

        $foto = time().'_'.$_FILES['foto']['name'];

        move_uploaded_file(
            $_FILES['foto']['tmp_name'],
            "upload/".$foto
        );
    }

    mysqli_query($conn,"
    INSERT INTO material_gudang
    (
        nama_material,
        satuan,
        jumlah,
        no_rak,
        kondisi,
        lokasi_penyimpanan,
        keterangan,
        foto_material
    )
    VALUES
    (
        '$nama_material',
        '$satuan',
        '$jumlah',
        '$no_rak',
        '$kondisi',
        '$lokasi',
        '$keterangan',
        '$foto'
    )
    ");

    header("Location:index.php");
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Tambah Material</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<div class="container mt-4">

<h3>Tambah Material</h3>

<form method="POST" enctype="multipart/form-data">

<div class="mb-3">
<label>Nama Material</label>
<input type="text" name="nama_material" class="form-control" required>
</div>

<div class="mb-3">
<label>Satuan</label>
<input type="text" name="satuan" class="form-control">
</div>

<div class="mb-3">
<label>Jumlah</label>
<input type="number" name="jumlah" class="form-control">
</div>

<div class="mb-3">
<label>No Rak</label>
<input type="text" name="no_rak" class="form-control">
</div>

<div class="mb-3">
<label>Kondisi</label>
<input type="text" name="kondisi" class="form-control">
</div>

<div class="mb-3">
<label>Lokasi</label>
<input type="text" name="lokasi" class="form-control">
</div>

<div class="mb-3">
<label>Keterangan</label>
<textarea name="keterangan" class="form-control"></textarea>
</div>

<div class="mb-3">
<label>Foto Material</label>
<input type="file" name="foto" class="form-control">
</div>

<button type="submit" name="simpan" class="btn btn-success">
Simpan
</button>

<a href="index.php" class="btn btn-secondary">
Kembali
</a>

</form>

</div>

</body>
</html>