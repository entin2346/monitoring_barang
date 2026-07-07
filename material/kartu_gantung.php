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

// Simpan data riwayat ke dalam array terlebih dahulu agar bisa di-loop 2 kali (untuk kartu kiri dan kanan)
$riwayat_items = [];
while($row = mysqli_fetch_assoc($riwayat)){
    $riwayat_items[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kartu Gantung Barang - Double Landscape</title>

<style>
* {
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f0f0f0;
}

/* PEMBUNGKUS UTAMA UNTUK MENAMPUNG 2 KARTU BERDAMPINGAN */
.print-wrapper {
    display: flex;
    justify-content: center;
    gap: 0;
    width: 297mm; /* Lebar penuh A4 Landscape */
    margin: 10px auto;
}

/* CONTAINER SETENGAH A4 (A5 LANDSCAPE) */
.container {
    width: 148.5mm;    /* Setengah dari lebar 297mm */
    height: 210mm;     /* Sesuai tinggi A4 */
    padding: 8mm;
    background-color: #fff;
    border: 1px dashed #000; /* Garis putus pembatas potong kertas */
    position: relative;
    overflow: hidden;
}

.header {
    width: 100%;
    border-collapse: collapse;
}

.logo {
    width: 50px;
}

.logo img {
    width: 45px;
}

.judul {
    font-size: 9px;
    font-weight: bold;
    line-height: 12px;
}

.tug {
    text-align: right;
    font-size: 11px;
    font-weight: bold;
    vertical-align: top;
}

.judul-kartu {
    text-align: center;
    font-size: 14px;
    font-weight: bold;
    text-decoration: underline;
    margin-top: 5px;
    margin-bottom: 5px;
}

.nomor-box {
    width: 120px;
    height: 25px;
    border: 1px solid #000;
    transform: rotate(-10deg);
    margin-left: 5px;
    margin-top: 5px;
}

.nomor-text {
    position: absolute;
    width: 120px;
    text-align: center;
    font-size: 10px;
    font-weight: bold;
    transform: rotate(-10deg);
    margin-top: -20px;
    margin-left: 5px;
}

.nama-barang-area {
    width: 100%;
    margin-top: 2px;
}

.nama-baris-1 {
    width: 100%;
    font-size: 11px;
}

.garis-full {
    width: 100%;
    height: 14px;
    border-bottom: 2px dotted #000;
    margin-top: 1px;
}

.kotak-kanan {
    width: 90px;
    border-collapse: collapse;
    margin-left: auto;
}

.kotak-kanan td {
    border: 1px solid #000;
    text-align: center;
    font-size: 10px;
    padding: 2px;
}

.tabel {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.tabel th,
.tabel td {
    border: 1px solid #000;
    font-size: 9px;
    padding: 3px 2px;
}

.tabel th {
    text-align: center;
    background-color: #fafafa;
}

.center {
    text-align: center;
}

@media print {
    .no-print {
        display: none;
    }
    body {
        background: none;
    }
    .print-wrapper {
        margin: 0;
        width: 100%;
    }
    .container {
        border: 1px dashed #000; /* Tetap tampilkan garis putus-putus untuk panduan memotong */
    }
    @page {
        size: A4 landscape; /* Set kertas ke arah mendatar */
        margin: 0;
    }
}
</style>
</head>
<body>

<div class="no-print" style="margin:20px; text-align: center;">
    <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; font-weight: bold;">🖨 Cetak 2-in-1 (A4 Landscape)</button>
</div>

<div class="print-wrapper">

    <?php for($k = 0; $k < 2; $k++): ?>
    <div class="container">

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

        <table width="100%" style="margin-top: 5px;">
            <tr>
                <td width="130" valign="top">
                    <div class="nomor-box"></div>
                    <div class="nomor-text">Nomor Normalisasi</div>

                    <div style="margin-top:35px; text-align:center; font-weight:bold; font-size:12px;">
                        Kartu
                    </div>
                    <div style="margin-top:3px; text-align:center; font-weight:bold; font-size:12px;">
                        No: ............................
                    </div>
                </td>

                <td valign="top" style="padding: 0 5px;">
                    <div class="judul-kartu">
                        KARTU GANTUNG BARANG
                    </div>
                    <div class="nama-barang-area">
                        <div class="nama-baris-1">
                            <b>Nama Barang :</b>
                            <?= htmlspecialchars($d['nama_material']); ?>
                        </div>
                        <div class="garis-full"></div>
                        <div class="garis-full"></div>
                    </div>
                </td>

                <td width="95" valign="top">
                    <table class="kotak-kanan">
                        <tr>
                            <td><b>Lokasi</b></td>
                        </tr>
                        <tr>
                            <td height="18">2518</td>
                        </tr>
                    </table>
                    <div style="margin-top: 5px;"></div>
                    <table class="kotak-kanan">
                        <tr>
                            <td><b>Satuan</b></td>
                        </tr>
                        <tr>
                            <td height="18">
                                <?= htmlspecialchars($d['satuan']); ?>
                            </td>
                        </tr>
                    </table>
                </td>
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
            $sisa = 0;
            $baris_terisi = 0;
            foreach($riwayat_items as $r){
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
                $baris_terisi++;
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
            // Batas aman baris agar tidak meluber ke bawah karena space landscape lebih pendek
            $sisa_baris_kosong = 10 - $baris_terisi;
            if($sisa_baris_kosong < 3) { $sisa_baris_kosong = 3; } 
            for($i=0; $i<$sisa_baris_kosong; $i++){
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
            <?php } ?>
        </table>

    </div>
    <?php endfor; ?>

</div>

</body>
</html>