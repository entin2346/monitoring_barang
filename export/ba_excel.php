<?php
// Include koneksi ke database Anda
include "../config/koneksi.php";

// Set header agar browser mendownload file sebagai format Excel (.xls) dengan charset UTF-8
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=database_ba.xls");
header("Pragma: no-cache");
header("Expires: 0");

// PENTING: Mengirimkan BOM UTF-8 agar Microsoft Excel langsung membaca semua karakter khusus,
// simbol (seperti tanda strip, kurung, dll) dengan benar tanpa berubah menjadi karakter aneh/China.
echo "\xEF\xBB\xBF";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        /* Style khusus untuk mengatur tampilan format saat dibuka di Excel */
        .format-teks {
            /* \@ memaksa Excel membaca isi kolom sebagai TEKS murni (bukan angka/tanggal otomatis) */
            mso-number-format: "\@"; 
            white-space: nowrap; 
        }
        .header-tabel {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>

<table border="1">
    <thead>
        <tr class="header-tabel">
            <th>No</th>
            <th>Tanggal</th>
            <th>Nama Barang</th>
            <th>Jumlah</th>
            <th>Tujuan</th>
            <th>Kondisi</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $no = 1;

    // Mengambil data dari database_ba yang nama barangnya tidak kosong
    $data = mysqli_query($conn, "
        SELECT *
        FROM database_ba
        WHERE nama_barang <> '' AND nama_barang IS NOT NULL
        ORDER BY tanggal DESC, id DESC
    ");

    if (mysqli_num_rows($data) > 0) {
        while($d = mysqli_fetch_assoc($data)){
            // Memastikan format tanggal aman saat dibaca di Excel (dd-mm-yyyy)
            $tanggal = (!empty($d['tanggal']) && $d['tanggal'] != '0000-00-00') ? date('d-m-Y', strtotime($d['tanggal'])) : '-';
    ?>
        <tr>
            <td align="center"><?= $no++; ?></td>
            
            <td class="format-teks" align="center"><?= $tanggal; ?></td>
            
            <td class="format-teks"><?= htmlspecialchars($d['nama_barang'], ENT_QUOTES, 'UTF-8'); ?></td>
            
            <td align="right"><?= number_format($d['jumlah']); ?></td>
            
            <td class="format-teks"><?= htmlspecialchars($d['tujuan'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
            
            <td class="format-teks" align="center"><?= htmlspecialchars($d['kondisi_material'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
    <?php 
        } 
    } else {
    ?>
        <tr>
            <td colspan="6" align="center" style="font-weight: bold; padding: 10px;">Data Berita Acara Kosong</td>
        </tr>
    <?php } ?>
    </tbody>
</table>

</body>
</html>