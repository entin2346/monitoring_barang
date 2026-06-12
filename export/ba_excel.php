<?php
include "../config/koneksi.php";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=database_ba.xls");
?>

<table border="1">

<tr>
    <th>No</th>
    <th>Tanggal</th>
    <th>Nama Barang</th>
    <th>Jumlah</th>
    <th>Tujuan</th>
    <th>Kondisi</th>
</tr>

<?php

$no=1;

$data = mysqli_query($conn,"
SELECT *
FROM database_ba
WHERE nama_barang <> ''
ORDER BY tanggal DESC
");

while($d=mysqli_fetch_assoc($data)){

?>

<tr>

<td><?= $no++; ?></td>
<td><?= $d['tanggal']; ?></td>
<td><?= $d['nama_barang']; ?></td>
<td><?= $d['jumlah']; ?></td>
<td><?= $d['tujuan']; ?></td>
<td><?= $d['kondisi_material']; ?></td>

</tr>

<?php } ?>

</table>