<?php

session_start();

include "../config/koneksi.php";

$id = (int)$_GET['id'];

$data = mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT *
FROM material_gudang
WHERE id='$id'
")
);

if(isset($_POST['update'])){

    $nama_material = mysqli_real_escape_string($conn,$_POST['nama_material']);
    $satuan = mysqli_real_escape_string($conn,$_POST['satuan']);
    $jumlah = (int)$_POST['jumlah'];
    $no_rak = mysqli_real_escape_string($conn,$_POST['no_rak']);
    $kondisi = mysqli_real_escape_string($conn,$_POST['kondisi']);
    $lokasi = mysqli_real_escape_string($conn,$_POST['lokasi']);
    $keterangan = mysqli_real_escape_string($conn,$_POST['keterangan']);

    $foto = $data['foto_material'];

    if($_FILES['foto']['name'] != ''){

        $foto = time().'_'.$_FILES['foto']['name'];

        move_uploaded_file(
            $_FILES['foto']['tmp_name'],
            "upload/".$foto
        );
    }

    mysqli_query($conn,"
    UPDATE material_gudang SET

    nama_material='$nama_material',
    satuan='$satuan',
    jumlah='$jumlah',
    no_rak='$no_rak',
    kondisi='$kondisi',
    lokasi_penyimpanan='$lokasi',
    keterangan='$keterangan',
    foto_material='$foto'

    WHERE id='$id'
    ");

    header("Location:index.php");
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Edit Material</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<div class="container mt-4">

<h3>Edit Material</h3>

<form method="POST" enctype="multipart/form-data">

<input type="text" name="nama_material"
value="<?= $data['nama_material']; ?>"
class="form-control mb-2">

<input type="text" name="satuan"
value="<?= $data['satuan']; ?>"
class="form-control mb-2">

<input type="number" name="jumlah"
value="<?= $data['jumlah']; ?>"
class="form-control mb-2">

<input type="text" name="no_rak"
value="<?= $data['no_rak']; ?>"
class="form-control mb-2">

<input type="text" name="kondisi"
value="<?= $data['kondisi']; ?>"
class="form-control mb-2">

<input type="text" name="lokasi"
value="<?= $data['lokasi_penyimpanan']; ?>"
class="form-control mb-2">

<textarea name="keterangan"
class="form-control mb-2"><?= $data['keterangan']; ?></textarea>

<input type="file" name="foto"
class="form-control mb-3">

<button class="btn btn-warning"
name="update">
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