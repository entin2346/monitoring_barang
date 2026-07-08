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
            <th>Tanggal Record</th>
            <th>Kategori Kelompok</th>
            <th>Tujuan</th>
            <th>Nama Material</th>
            <th>Merk/Jenis</th>
            <th>Jenis Material</th>
            <th>Sumber Material</th>
            <th>Satuan</th>
            <th>Jumlah</th>
            <th>Nomor Seri</th>
            <th>Pemasok/Vendor</th>
            <th>Kategori BA</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $no = 1;

    // Mengambil data dari database_ba yang nama barangnya tidak kosong (Sesuai query dasar index.php)
    $data = mysqli_query($conn, "
        SELECT *
        FROM database_ba
        WHERE nama_barang <> '' AND nama_barang IS NOT NULL
        ORDER BY tanggal DESC, id DESC
    ");

    if ($data && mysqli_num_rows($data) > 0) {
        while($d = mysqli_fetch_assoc($data)){
            // Memastikan format tanggal aman saat dibaca di Excel (dd-mm-yyyy)
            $tanggal = (!empty($d['tanggal']) && $d['tanggal'] != '0000-00-00') ? date('d-m-Y', strtotime($d['tanggal'])) : '-';
            $kategori = strtoupper($d['jenis_berita_acara'] ?? '');
    ?>
        <tr>
            <td align="center"><?= $no++; ?></td>
            
            <td class="format-teks" align="center"><?= $tanggal; ?></td>
            
            <td class="format-teks"><?= htmlspecialchars(strtoupper($d['jenis_kategori'] ?: '-'), ENT_QUOTES, 'UTF-8'); ?></td>
            
            <td class="format-teks">
                <?php 
                if(strpos($kategori, 'MASUK') !== false){
                    echo "-";
                } else {
                    echo htmlspecialchars(strtoupper($d['tujuan'] ?? '-'), ENT_QUOTES, 'UTF-8'); 
                }
                ?>
            </td>
            
            <td class="format-teks"><?= htmlspecialchars($d['nama_barang'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
            
            <td class="format-teks"><?= htmlspecialchars($d['merk_jenis'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
            
            <td class="format-teks"><?= htmlspecialchars($d['jenis_barang'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
            
            <td class="format-teks"><?= htmlspecialchars($d['sumber_barang'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
            
            <td class="format-teks" align="center"><?= htmlspecialchars($d['satuan'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
            
            <td align="right"><?= number_format($d['jumlah'] ?? 0); ?></td>
            
            <td class="format-teks"><?= htmlspecialchars($d['no_seri'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
            
            <td class="format-teks"><?= htmlspecialchars($d['asal_barang_vendor'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
            
            <td class="format-teks" align="center">
                <?php
                if(strpos($kategori, 'MASUK') !== false) { 
                    echo "MASUK"; 
                } elseif(strpos($kategori, 'KELUAR') !== false || strpos($kategori, 'TERPAKAI') !== false) { 
                    echo "KELUAR"; 
                } else { 
                    echo "RETURN"; 
                }
                ?>
            </td>
            
            <td class="format-teks"><?= htmlspecialchars($d['keterangan'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
    <?php 
        } 
    } else {
    ?>
        <tr>
            <td colspan="14" align="center" style="font-weight: bold; padding: 10px;">Data Berita Acara Kosong</td>
        </tr>
    <?php } ?>
    </tbody>
</table>

</body>
</html>