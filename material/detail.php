<?php
include "../config/koneksi.php";

$id = (int)$_GET['id'];

$data = mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT *
FROM material_gudang
WHERE id='$id'
"));
?>

<!DOCTYPE html>
<html>
<head>

<title>Detail Material</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<div class="container mt-4">

<h3>Detail Material</h3>

<a href="index.php"
class="btn btn-secondary mb-3">
Kembali
</a>

<table class="table table-bordered">

<tr>
<th>Nama Material</th>
<td><?= $data['nama_material']; ?></td>
</tr>

<tr>
<th>Satuan</th>
<td><?= $data['satuan']; ?></td>
</tr>

<tr>
<th>Jumlah</th>
<td><?= number_format($data['jumlah']); ?></td>
</tr>

<tr>
<th>No Rak</th>
<td><?= $data['no_rak']; ?></td>
</tr>

<tr>
<th>Kondisi</th>
<td><?= $data['kondisi']; ?></td>
</tr>

<tr>
<th>Lokasi</th>
<td><?= $data['lokasi_penyimpanan']; ?></td>
</tr>

<tr>
<th>Keterangan</th>
<td><?= $data['keterangan']; ?></td>
</tr>

<tr>
<th>Foto Material</th>
<td>

<?php if(!empty($data['foto_material'])){ ?>

<img
src="upload/<?= $data['foto_material']; ?>"
width="300"
class="img-thumbnail">

<?php }else{ ?>

Tidak ada foto

<?php } ?>

</td>
</tr>

</table>

</div>

</body>
</html>