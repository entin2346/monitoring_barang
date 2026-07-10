<?php
// Include koneksi ke database Anda
include "../config/koneksi.php";

// 1. AMBIL KATA KUNCI DAN KATEGORI DARI URL
$cari = $_GET['cari'] ?? '';
$kategori = $_GET['kategori'] ?? ''; 

$cari_pencarian = urldecode($cari);
$cari_db = mysqli_real_escape_string($conn, $cari_pencarian);
$cari_clean = trim($cari_db); 

$kategori_db = mysqli_real_escape_string($conn, trim($kategori));

// 2. MENYUSUN KONDISI WHERE SECARA DINAMIS
$whereConditions = [];
$whereConditions[] = "TRIM(m.nama_material) <> '' AND m.nama_material IS NOT NULL";

if ($cari_clean !== '') {
    $whereConditions[] = "(m.nama_material LIKE '%$cari_clean%')";
}

if ($kategori_db !== '') {
    $kat_cari = trim(strtolower($kategori_db));
    if ($kat_cari !== '') {
        if ($kat_cari == 'stok' || $kat_cari == 'stock') {
            $whereConditions[] = "(m.id <= 63 OR TRIM(LOWER(m.jenis_kategori)) = 'stok' OR TRIM(LOWER(m.jenis_kategori)) = 'stock')";
        } elseif ($kat_cari == 'non stock' || $kat_cari == 'non-stock' || $kat_cari == 'non stok' || $kat_cari == 'non-stok' || $kat_cari == 'non stock') {
            $whereConditions[] = "m.id > 63 AND (TRIM(m.jenis_kategori) = '' OR m.jenis_kategori IS NULL OR TRIM(LOWER(m.jenis_kategori)) IN ('stok', 'stock', 'non stok', 'non-stok', 'non stock'))";
        } else {
            $whereConditions[] = "(TRIM(LOWER(m.jenis_kategori)) = '$kat_cari')";
        }
    }
}

$whereClause = implode(" AND ", $whereConditions);

// Set header agar browser mendownload file sebagai format asli Excel .xls
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=material_gudang.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Kirim BOM UTF-8
echo "\xEF\xBB\xBF";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table {
            border-collapse: collapse;
        }
        th {
            font-family: 'Calibri', 'Arial', sans-serif !important;
            font-size: 11pt !important;
            background-color: #f2f2f2 !important;
            font-weight: bold !important;
            text-align: center !important;
            vertical-align: middle !important;
            border: 0.5pt solid #000000;
        }
        td {
            font-family: 'Calibri', 'Arial', sans-serif !important;
            font-size: 11pt !important;
            font-weight: normal !important; /* Menghapus huruf tebal (bold) */
            vertical-align: middle !important;
            border: 0.5pt solid #000000;
            white-space: nowrap !important; /* KUNCI UTAMA: Melarang teks turun ke bawah/tumpang tindih */
            mso-number-format: "\@"; /* Memaksa kolom dibaca teks murni oleh Excel */
        }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
    </style>
</head>
<body>

<table border="1">
    <thead>
        <tr style="height: 28px;">
            <th style="width: 45px;">NO</th>
            <th style="width: 380px;">NAMA KELOMPOK MATERIAL GUDANG</th>
            <th style="width: 110px;">KATEGORI</th>
            <th style="width: 90px;">SATUAN</th>
            <th style="width: 100px;">JUMLAH STOK</th>
            <th style="width: 100px;">NOMOR RAK</th>
            <th style="width: 120px;">STATUS KONDISI</th>
            <th style="width: 220px;">LOKASI PENYIMPANAN</th>
            <th style="width: 240px;">SUMBER MATERIAL</th>
            <th style="width: 280px;">KETERANGAN</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $no = 1;

    $data = mysqli_query($conn, "
        SELECT 
            m.id AS id,
            m.nama_material,
            m.jenis_kategori AS jenis_kategori,
            m.jumlah AS jumlah,
            m.no_rak,
            m.kondisi,
            m.lokasi_penyimpanan,
            COALESCE(NULLIF(TRIM(ba.satuan), ''), m.satuan) AS satuan,
            COALESCE(NULLIF(TRIM(ba.sumber_barang), ''), m.sumber_barang) AS sumber_barang,
            COALESCE(NULLIF(TRIM(ba.keterangan), ''), m.keterangan) AS keterangan
        FROM material_gudang m
        LEFT JOIN (
            SELECT 
                TRIM(LOWER(nama_barang)) AS key_nama,
                MAX(satuan) AS satuan, 
                MAX(sumber_barang) AS sumber_barang, 
                MAX(keterangan) AS keterangan
            FROM database_ba
            WHERE nama_barang <> '' AND nama_barang IS NOT NULL
            GROUP BY TRIM(LOWER(nama_barang))
        ) ba ON TRIM(LOWER(m.nama_material)) = ba.key_nama 
        WHERE $whereClause
        GROUP BY m.id
        ORDER BY m.id ASC
    ");

    if ($data && mysqli_num_rows($data) > 0) {
        while($d = mysqli_fetch_assoc($data)){
            $kat_real = strtolower(trim($d['jenis_kategori'] ?? ''));
            $id_material = (int)$d['id'];
            $kategori_aktif = isset($kategori_db) ? strtolower(trim($kategori_db)) : '';
            
            $kategori_tampil = '';
            if ($kategori_aktif == 'stok' || $kategori_aktif == 'stock') {
                $kategori_tampil = 'Stok';
            } elseif ($kategori_aktif == 'non stok' || $kategori_aktif == 'non-stok' || $kategori_aktif == 'non stock') {
                $kategori_tampil = 'Non Stok';
            } else {
                if (($d['keterangan'] ?? '') == 'Otomatis dari Registrasi BA') {
                    $kat = strtoupper(trim($d['jenis_kategori']));
                    if ($kat == 'STOCK' || $kat == 'STOK') {
                        $kategori_tampil = 'STOCK';
                    } elseif ($kat == 'NON STOCK' || $kat == 'NON-STOK') {
                        $kategori_tampil = 'NON STOCK';
                    } else {
                        $kategori_tampil = htmlspecialchars($kat);
                    }
                } else {
                    if ($kat_real == '' || $kat_real == 'stok' || $kat_real == 'stock' || $kat_real == 'non stok' || $kat_real == 'non-stok' || $kat_real == 'non stock') {
                        if ($id_material <= 63) {
                            $kategori_tampil = 'Stok';
                        } else {
                            $kategori_tampil = 'Non Stok';
                        }
                    } else {
                        $kategori_tampil = htmlspecialchars(strtoupper($d['jenis_kategori']));
                    }
                }
            }
    ?>
        <tr style="height: 22px;">
            <td class="text-center"><?= str_pad($no++, 2, '0', STR_PAD_LEFT); ?></td>
            <td><?= htmlspecialchars($d['nama_material'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="text-center"><?= $kategori_tampil; ?></td>
            <td class="text-center"><?= htmlspecialchars($d['satuan'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
            
            <td class="text-right" style="mso-number-format: '#,##0';"><?= abs((int)$d['jumlah']); ?></td>
            
            <td class="text-center"><?= htmlspecialchars($d['no_rak'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="text-center"><?= htmlspecialchars(strtoupper($d['kondisi'] ?: '-'), ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?= htmlspecialchars($d['lokasi_penyimpanan'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?= htmlspecialchars($d['sumber_barang'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?= htmlspecialchars($d['keterangan'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
    <?php 
        } 
    } else {
    ?>
        <tr style="height: 30px;">
            <td colspan="10" class="text-center" style="font-weight: bold;">Material tidak ditemukan.</td>
        </tr>
    <?php } ?>
    </tbody>
</table>

</body>
</html>