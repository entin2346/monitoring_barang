<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

if(!isset($_GET['id'])){
    die("ID tidak ditemukan");
}

$id = (int)$_GET['id'];

$data = mysqli_query($conn,"
    SELECT *
    FROM database_ba
    WHERE id='$id'
");

$d = mysqli_fetch_assoc($data);

if(!$d){
    die("Data tidak ditemukan");
}

$nama_barang = mysqli_real_escape_string($conn,$d['nama_barang']);

$riwayat = mysqli_query($conn,"
    SELECT *
    FROM database_ba
    WHERE nama_barang='$nama_barang'
    ORDER BY tanggal ASC,id ASC
");

$sisa = 0;
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Kartu Gantung Barang</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    padding:20px;
    font-size:13px;
}

table{
    width:100%;
}

.header{
    text-align:center;
    margin-bottom:20px;
}

@media print{
    .no-print{
        display:none;
    }
}
</style>

</head>
<body>

<div class="no-print mb-3">
    <button onclick="window.print()" class="btn btn-success">
        🖨 Cetak
    </button>

    <a href="detail.php?id=<?= $id ?>" class="btn btn-secondary">
        Kembali
    </a>
</div>

<div class="header">
    <h5>PT. PLN (PERSERO)</h5>
    <h6>UNIT INDUK PENYALURAN DAN PUSAT PENGATUR BEBAN SULAWESI</h6>
    <h6>UPT MAKASSAR</h6>

    <br>

    <h4><b>KARTU GANTUNG BARANG</b></h4>
</div>

<table class="mb-3">
<tr>
    <td width="20%"><b>Nama Barang</b></td>
    <td>: <?= htmlspecialchars($d['nama_barang']); ?></td>

    <td width="15%"><b>Lokasi</b></td>
    <td>: <?= htmlspecialchars($d['tujuan']); ?></td>
</tr>

<tr>
    <td><b>No Kartu</b></td>
    <td>: <?= $d['id']; ?></td>

    <td><b>Satuan</b></td>
    <td>: <?= htmlspecialchars($d['satuan']); ?></td>
</tr>
</table>

<table class="table table-bordered table-sm">
<thead>
<tr class="table-secondary text-center">
    <th>Tgl</th>
    <th>No Bon</th>
    <th>Masuk</th>
    <th>Keluar</th>
    <th>Sisa Persediaan</th>
    <th>Rak</th>
    <th>Peti</th>
    <th>Jumlah</th>
    <th>Catatan</th>
</tr>
</thead>

<tbody>

<?php
while($r = mysqli_fetch_assoc($riwayat)){

    $masuk = "";
    $keluar = "";

    $jenis = strtoupper(trim($r['jenis_berita_acara']));

    if($jenis == "MASUK"){
        $masuk = $r['jumlah'];
        $sisa += $r['jumlah'];
    }
    else{
        $keluar = $r['jumlah'];
        $sisa -= $r['jumlah'];
    }
?>
<tr>
    <td>
        <?= !empty($r['tanggal']) ? date('d-m-Y',strtotime($r['tanggal'])) : '-'; ?>
    </td>

    <td><?= htmlspecialchars($r['no_urut']); ?></td>

    <td class="text-center"><?= $masuk; ?></td>

    <td class="text-center"><?= $keluar; ?></td>

    <td class="text-center"><?= $sisa; ?></td>

    <td></td>

    <td></td>

    <td class="text-center"><?= $r['jumlah']; ?></td>

    <td><?= htmlspecialchars($r['keterangan']); ?></td>
</tr>

<?php } ?>

</tbody>
</table>

</body>
</html>