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

if(!$d){
    echo "Data tidak ditemukan";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Detail BA</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-4">

<h2>Detail Berita Acara</h2>

<hr>

<table class="table table-bordered">

<tr>
<th width="250">Jenis Berita Acara</th>
<td><?= htmlspecialchars($d['jenis_berita_acara']); ?></td>
</tr>

<tr>
<th>Tanggal</th>
<td><?= date('d-m-Y', strtotime($d['tanggal'])); ?></td>
</tr>

<tr>
<th>Nama Barang</th>
<td><?= htmlspecialchars($d['nama_barang']); ?></td>
</tr>

<tr>
<th>Merk / Jenis</th>
<td><?= htmlspecialchars($d['merk_jenis']); ?></td>
</tr>

<tr>
<th>Jenis Barang</th>
<td><?= htmlspecialchars($d['jenis_barang']); ?></td>
</tr>

<tr>
<th>Sumber Barang</th>
<td><?= htmlspecialchars($d['sumber_barang']); ?></td>
</tr>

<tr>
<th>Satuan</th>
<td><?= htmlspecialchars($d['satuan']); ?></td>
</tr>

<tr>
<th>Jumlah</th>
<td><?= number_format($d['jumlah']); ?></td>
</tr>

<tr>
<th>Tujuan</th>
<td><?= htmlspecialchars($d['tujuan']); ?></td>
</tr>

<tr>
<th>Kondisi Material</th>
<td><?= htmlspecialchars($d['kondisi_material']); ?></td>
</tr>

<tr>
<th>No Seri</th>
<td><?= htmlspecialchars($d['no_seri']); ?></td>
</tr>

<tr>
<th>Vendor</th>
<td><?= htmlspecialchars($d['asal_barang_vendor']); ?></td>
</tr>

<tr>
<th>Berita Acara</th>
<td><?= htmlspecialchars($d['berita_acara']); ?></td>
</tr>

<tr>
<th>Dokumentasi BA Kembali</th>
<td><?= htmlspecialchars($d['dokumentasi_ba_kembali']); ?></td>
</tr>

<tr>
<th>Keterangan</th>
<td><?= htmlspecialchars($d['keterangan']); ?></td>
</tr>

<tr>
<th>Keterangan Tambahan</th>
<td><?= htmlspecialchars($d['keterangan_tambahan']); ?></td>
</tr>

<tr>
<th>TUG 5</th>
<td><?= htmlspecialchars($d['tug5']); ?></td>
</tr>

</table>

<a href="index.php"
class="btn btn-secondary">
Kembali
</a>

<a href="edit.php?id=<?= $d['id']; ?>"
class="btn btn-warning">
Edit
</a>

</div>

</body>
</html>