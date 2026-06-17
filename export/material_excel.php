<?php
// Include koneksi ke database Anda
include "../config/koneksi.php";

// Set header agar browser mendownload file sebagai format Excel (.xls) dengan charset UTF-8
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=material_gudang.xls");
header("Pragma: no-cache");
header("Expires: 0");

// PENTING: Mengirimkan BOM UTF-8 agar Microsoft Excel langsung membaca semua karakter khusus,
// simbol (seperti tanda strip, kurung, U-BOLT dll) dengan benar tanpa berubah menjadi karakter aneh/China.
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
            <th>Nama Material</th>
            <th>Satuan</th>
            <th>Jumlah</th>
            <th>No Rak</th>
            <th>Kondisi</th>
            <th>Lokasi</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $no = 1;

    // Mengambil data dari tabel material_gudang diurutkan berdasarkan Nama Material secara Alfabetis (A-Z)
    $data = mysqli_query($conn, "
        SELECT *
        FROM material_gudang
        ORDER BY nama_material ASC
    ");

    if (mysqli_num_rows($data) > 0) {
        while($d = mysqli_fetch_assoc($data)){
    ?>
        <tr>
            <td align="center"><?= $no++; ?></td>
            
            <td class="format-teks"><?= htmlspecialchars($d['nama_material'], ENT_QUOTES, 'UTF-8'); ?></td>
            
            <td class="format-teks" align="center"><?= htmlspecialchars($d['satuan'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
            
            <td align="right"><?= number_format($d['jumlah']); ?></td>
            
            <td class="format-teks" align="center"><?= htmlspecialchars($d['no_rak'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
            
            <td class="format-teks" align="center"><?= htmlspecialchars($d['kondisi'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
            
            <td class="format-teks"><?= htmlspecialchars($d['lokasi_penyimpanan'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
    <?php 
        } 
    } else {
    ?>
        <tr>
            <td colspan="7" align="center" style="font-weight: bold; padding: 10px;">Data Material Gudang Kosong</td>
        </tr>
    <?php } ?>
    </tbody>
</table>

</body>
</html>