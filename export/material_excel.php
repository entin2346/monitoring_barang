<?php
include "../config/koneksi.php";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=material_gudang.xls");
?>

<table border="1">

<tr>
    <th>No</th>
    <th>Nama Material</th>
    <th>Satuan</th>
    <th>Jumlah</th>
    <th>No Rak</th>
    <th>Kondisi</th>
    <th>Lokasi</th>
</tr>

<?php

$no=1;

$data = mysqli_query($conn,"
SELECT *
FROM material_gudang
ORDER BY nama_material ASC
");

while($d=mysqli_fetch_assoc($data)){

?>

<tr>

<td><?= $no++; ?></td>
<td><?= $d['nama_material']; ?></td>
<td><?= $d['satuan']; ?></td>
<td><?= $d['jumlah']; ?></td>
<td><?= $d['no_rak']; ?></td>
<td><?= $d['kondisi']; ?></td>
<td><?= $d['lokasi_penyimpanan']; ?></td>

</tr>

<?php } ?>

</table>