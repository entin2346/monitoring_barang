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
    font-size:18px;
    font-weight:bold;
    margin-top:20px;
    margin-bottom:15px;
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
    width:120px;
    height:40px;
    border:2px solid #000;
    transform:rotate(-20deg);
    text-align:center;
    font-size:11px;
}

.tabel{
    width:100%;
    border-collapse:collapse;
}

.tabel th,
.tabel td{
    border:1px solid #000;
    font-size:11px;
    padding:3px;
}

.tabel th{
    text-align:center;
}

.center{
    text-align:center;
}

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

<table width="100%">
<tr>

<td width="180">
    <div class="nomor">
        Nomor Material
    </div>
</td>

<td>
    <div class="judul-kartu">
        KARTU GANTUNG BARANG
    </div>
</td>

<td width="130">

    <table width="100%" border="1" cellspacing="0">
        <tr>
            <td class="center">Lokasi</td>
        </tr>
        <tr>
            <td height="35"></td>
        </tr>
    </table>

</td>

</tr>
</table>

<table class="info">

<tr>

<td width="120">Nama Barang :</td>

<td width="500">
    .............................................................
</td>

<td rowspan="3" width="130">

    <table width="100%" border="1" cellspacing="0">
        <tr>
            <td class="center">Satuan</td>
        </tr>
        <tr>
            <td height="35"></td>
        </tr>
    </table>

</td>

</tr>

<tr>
<td>Kartu</td>
<td>.............................................................</td>
</tr>

<tr>
<td>No :</td>
<td>.............................................................</td>
</tr>

</table>

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
for($i=1;$i<=25;$i++){
?>
<tr>
    <td height="22"></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
</tr>
<?php } ?>

</table>

</div>

</body>
</html>