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
FROM material_gudang
WHERE id='$id'
");

$d = mysqli_fetch_assoc($data);

if(!$d){
    die("Data tidak ditemukan");
}

$nama_barang = mysqli_real_escape_string($conn,$d['nama_material']);

$riwayat = mysqli_query($conn,"
SELECT *
FROM database_ba
WHERE nama_barang='$nama_barang'
ORDER BY tanggal ASC,id ASC
");

$sisa = 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kartu Gantung Barang</title>

<style>

body{
    font-family: Arial, sans-serif;
    margin:0;
    padding:0;
}

.container{
    width:1000px;
    margin:auto;
    padding:10px;
}

.header{
    width:100%;
    border-collapse:collapse;
}

.logo{
    width:70px;
}

.logo img{
    width:60px;
}

.judul{
    font-size:12px;
    font-weight:bold;
    line-height:18px;
}

.tug{
    text-align:right;
    font-size:12px;
    font-weight:bold;
}

.judul-kartu{
    text-align:center;
    font-size:16px;
    font-weight:bold;
    margin-top:20px;
}

.info{
    width:100%;
    border-collapse:collapse;
    margin-bottom:10px;
}

.info td{
    font-size:12px;
    padding:3px;
}

.kotak{
    border:1px solid #000;
    width:120px;
    text-align:center;
    font-size:12px;
}

.nomor{
    width:150px;
    height:35px;
    border:1px solid #000;
    transform:rotate(-22deg);
    margin-left:10px;
    margin-top:20px;
}

.tabel{
    width:100%;
    border-collapse:collapse;
}

.tabel th,
.tabel td{
    border:1px solid #000;
    font-size:11px;
    padding:6px;
}

.tabel th{
    text-align:center;
}

.center{
    text-align:center;
}

@page{
    size:A4 portrait;
    margin:10mm;
}

body{
    font-family:Arial, sans-serif;
    margin:0;
    padding:0;
}

.container{
    width:190mm;
    margin:auto;
}

.header{
    width:100%;
}

.logo img{
    width:45px;
}

.judul{
    font-size:11px;
    font-weight:bold;
    line-height:18px;
}

.tug{
    font-size:11px;
    font-weight:bold;
    text-align:right;
    vertical-align:top;
}

.nomor-box{
    width:150px;
    height:28px;
    border:1px solid #000;
    transform:rotate(-22deg);
    margin-top:35px;
    margin-left:10px;
}

.nomor-text{
    text-align:center;
    font-weight:bold;
    font-size:11px;
    margin-top:25px;
}

.judul-kartu{
    text-align:center;
    font-size:18px;
    font-weight:bold;
    text-decoration:underline;
    margin-top:60px;
    margin-bottom:25px;
}

.garis{
    border-bottom:2px dotted #000;
    height:20px;
}

.kotak-kanan{
    width:105px;
    border-collapse:collapse;
}

.kotak-kanan td{
    border:1px solid #000;
    text-align:center;
    font-size:12px;
}

.info-nama{
    width:100%;
    margin-top:10px;
}

.info-nama td{
    font-size:12px;
    padding:2px;
}

.kotak-kanan td{
    border:1px solid #000;
    text-align:center;
    font-size:12px;
}

<table class="kotak-kanan" width="100%" cellspacing="0">

@media print{

    .no-print{
        display:none;
    }

    body{
        zoom:95%;
    }

}

</style>
</head>
<body>

<div class="container">

<div class="no-print" style="margin-bottom:10px;">
    <button onclick="window.print()">🖨 Cetak</button>
</div>

<table class="header">
<tr>

<td class="logo">
    <img src="../assets/logo_pln.png">
</td>

<td class="judul">
    PT. PLN (PERSERO)<br>
    UNIT INDUK PENYALURAN DAN PUSAT PENGATURAN BEBAN SULAWESI<br>
    UPT MAKASSAR
</td>

<td class="tug">
    TUG 2
</td>

</tr>
</table>

</td>

</tr>
</table>

<table width="100%">
<tr>

<td width="180" valign="top">

    <div class="nomor-box"></div>

    <div class="nomor-text">
        Nomor Normalisasi
    </div>

    <div style="margin-top:35px;text-align:center;font-weight:bold;">
        Kartu
    </div>

    <div style="margin-top:25px;font-weight:bold;">
        No: ............................
    </div>

</td>

<td valign="top">

    <div class="judul-kartu">
        KARTU GANTUNG BARANG
    </div>

    <table class="info-nama">

        <tr>
            <td width="120">
                <b>Nama Barang :</b>
            </td>

            <td>
                <?= htmlspecialchars($d['nama_material']); ?>
            </td>
        </tr>

        <tr>
            <td></td>
            <td class="garis"></td>
        </tr>

        <tr>
            <td></td>
            <td class="garis"></td>
        </tr>

        <tr>
            <td></td>
            <td class="garis"></td>
        </tr>

    </table>

</td>

<td width="120" valign="top">

    <table class="kotak-kanan">
        <tr>
            <td><b>Lokasi</b></td>
        </tr>
        <tr>
            <td height="30">2518</td>
        </tr>
    </table>

    <br><br><br><br><br>

    <table class="kotak-kanan">
        <tr>
            <td><b>Satuan</b></td>
        </tr>
        <tr>
            <td height="30">
                <?= htmlspecialchars($d['satuan']); ?>
            </td>
        </tr>
    </table>

</td>

</tr>
</table>

<br>
<table class="tabel">

<tr>
    <th rowspan="2">Tgl</th>
    <th rowspan="2">No Bon</th>
    <th rowspan="2">Masuk</th>
    <th rowspan="2">Keluar</th>
    <th colspan="3">Sisa Persediaan</th>
    <th rowspan="2">Catatan</th>
</tr>

<tr>
    <th>Rak</th>
    <th>Peti</th>
    <th>Jumlah</th>
</tr>

<?php
while($r = mysqli_fetch_assoc($riwayat)){

$masuk = '';
$keluar = '';

$jenis = strtoupper(trim($r['jenis_berita_acara']));

if($jenis == 'MASUK'){
    $masuk = $r['jumlah'];
    $sisa += $r['jumlah'];
}
elseif($jenis == 'KELUAR'){
    $keluar = $r['jumlah'];
    $sisa -= $r['jumlah'];
}
?>
<tr>

<td><?= date('d-m-Y',strtotime($r['tanggal'])); ?></td>

<td><?= $r['no_urut']; ?></td>

<td class="center"><?= $masuk; ?></td>

<td class="center"><?= $keluar; ?></td>

<td></td>

<td></td>

<td class="center"><?= $sisa; ?></td>

<td><?= htmlspecialchars($r['keterangan']); ?></td>

</tr>

<?php } ?>
<?php
for($i=0; $i<20; $i++){
?>
<tr>
    <td>&nbsp;</td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
</tr>
<?php
}
?>
</table>

</div>

</body>
</html>