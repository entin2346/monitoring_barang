<?php
session_start();
if(!isset($_SESSION['login'])){ header("Location: ../login/index.php"); exit; }
include "../config/koneksi.php";

// 1. AMBIL PARAMETER ID SEBAGAI ACUAN UTAMA
if(!isset($_GET['id'])){ echo "ID acuan data tidak ditemukan"; exit; }
$id = mysqli_real_escape_string($conn, $_GET['id']);

// 2. CARI TAHU DATA INI BERASAL DARI UNIT MANA, BULAN BERAPA, DAN TAHUN BERAPA (BERDASARKAN ID YANG DIKLIK)
$query_ref = mysqli_query($conn, "SELECT tujuan, MONTH(tanggal) as bulan, YEAR(tanggal) as tahun FROM database_ba WHERE id='$id'");
$ref = mysqli_fetch_assoc($query_ref);
if(!$ref){ echo "Data acuan tidak ditemukan"; exit; }

$tujuan     = $ref['tujuan'];
$bulan_ref  = $ref['bulan'];
$tahun_ref  = $ref['tahun'];

// 3. LOGIKA OTOMATISASI TANGGAL (DIKUNCI PER UNIT DAN PER BULAN/TAHUN BERJALAN)
$query_tanggal = mysqli_query($conn, "
    SELECT 
        MIN(tanggal) AS ba_pertama, 
        MAX(tanggal) AS ba_terakhir 
    FROM database_ba 
    WHERE tujuan='$tujuan' AND MONTH(tanggal)='$bulan_ref' AND YEAR(tanggal)='$tahun_ref'
");
$data_tanggal = mysqli_fetch_assoc($query_tanggal);

$ba_pertama   = $data_tanggal['ba_pertama'] ? $data_tanggal['ba_pertama'] : date('Y-m-d');
$ba_terakhir  = $data_tanggal['ba_terakhir'] ? $data_tanggal['ba_terakhir'] : date('Y-m-d');

// Fungsi Format Tanggal Indonesia (Contoh: 04 Mei 2026)
function tgl_indo($tanggal){
    $bulan_arr = array (1 => 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
    $split = explode('-', $tanggal);
    return $split[2] . ' ' . $bulan_arr[(int)$split[1]] . ' ' . $split[0];
}

// 4. QUERY DAFTAR BARANG (Hanya memunculkan barang milik unit tersebut di bulan & tahun yang sesuai)
$query_barang = mysqli_query($conn, "
    SELECT * FROM database_ba 
    WHERE tujuan='$tujuan' AND MONTH(tanggal)='$bulan_ref' AND YEAR(tanggal)='$tahun_ref'
    ORDER BY tanggal ASC
");

// 5. JALUR GAMBAR LOGO PLN (Keluar folder 'ba' menuju folder 'assets')
$path_logo = '../assets/logo_pln.png';
if (file_exists($path_logo)) {
    $type_logo = pathinfo($path_logo, PATHINFO_EXTENSION);
    $data_logo = file_get_contents($path_logo);
    $src_logo = 'data:image/' . $type_logo . ';base64,' . base64_encode($data_logo);
} else {
    $src_logo = "http://localhost/monitoring_barang/assets/logo_pln.png";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Form TUG 5 - PLN UPT MAKASSAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 11px; color: #000; background-color: #fff; }
        .kop-text { font-size: 11px; font-weight: bold; line-height: 1.4; }
        .judul-form { font-size: 16px; font-weight: bold; text-decoration: underline; margin-bottom: 2px; }
        
        /* Ukuran Logo Presisi */
        .logo-pln { width: 44px; height: auto; object-fit: contain; }
        
        /* Layout Tabel TUG 5 */
        .table-tug { border: 1px solid #000; width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table-tug th, .table-tug td { border: 1px solid #000; padding: 6px 4px; vertical-align: middle; }
        .table-tug th { text-align: center; font-weight: bold; background-color: #f2f2f2 !important; }
        
        .info-header td { padding: 2px 0; border: none !important; font-size: 11px; }
        .tanda-tangan td { border: none !important; text-align: center; font-size: 11px; }

        @media print {
            .no-print { display: none !important; }
            body { margin: 0.5cm; }
        }
    </style>
</head>
<body>

<div class="container-fluid my-3 no-print text-end">
    <button onclick="window.print();" class="btn btn-primary fw-bold px-4">
        🖨️ Langsung Print Dokumen
    </button>
    <a href="detail.php?id=<?= $id; ?>" class="btn btn-secondary fw-bold px-3 ms-2">⬅️ Kembali</a>
</div>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start">
        <div class="d-flex align-items-center gap-3">
            <img src="<?= $src_logo; ?>" alt="Logo PLN" class="logo-pln">
            
            <div class="kop-text">
                PT. PLN (PERSERO)<br>
                UNIT INDUK PENYALURAN DAN PUSAT PENGATUR BEBAN SULAWESI<br>
                UPT MAKASSAR<br>
                <?= htmlspecialchars(strtoupper($tujuan)); ?>
            </div>
        </div>
        <div class="text-end kop-text" style="font-size: 14px; padding-top: 5px;">
            TUG 5
        </div>
    </div>

    <div class="text-center my-3">
        <div class="judul-form">DAFTAR PERMINTAAN BARANG-BARANG</div>
        <div class="fw-bold">No : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; /TUG/<?= date('d/m/Y', strtotime($ba_pertama)); ?></div>
    </div>

    <table class="info-header w-100 mb-2">
        <tr>
            <td style="width: 12%;">Kepada</td>
            <td style="width: 2%;">:</td>
            <td style="width: 45%;">PT PLN (Persero) UPT MAKASSAR</td>
            <td style="width: 15%;">PLN</td>
            <td style="width: 2%;">:</td>
            <td>UIP3B SULAWESI</td>
        </tr>
        <tr>
            <td>Harap dikirim ke</td>
            <td>:</td>
            <td class="fw-bold"><?= htmlspecialchars(strtoupper($tujuan)); ?></td>
            <td>Cab./Sekt./Bkl.</td>
            <td>:</td>
            <td>UPT MAKASSAR</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>:</td>
            <td colspan="4">......................................................................................................................................</td>
        </tr>
    </table>

    <table class="table-tug">
        <thead>
            <tr>
                <th rowspan="2" style="width: 5%;">No. Urut</th>
                <th rowspan="2" style="width: 32%;">Nama Lengkap Barang</th>
                <th rowspan="2" style="width: 10%;">Nomor</th>
                <th rowspan="2" style="width: 6%;">Satuan</th>
                <th rowspan="2" style="width: 12%;">Pemakaian Rata-rata per bulan</th>
                <th rowspan="2" style="width: 8%;">Sisa Persediaan</th>
                <th colspan="3" style="width: 27%;">Permintaan</th>
                <th rowspan="2" style="width: 15%;">Keterangan</th>
            </tr>
            <tr>
                <th>Banyaknya</th>
                <th>DO No.</th>
                <th>Tanggal BA</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while($b = mysqli_fetch_assoc($query_barang)): 
            ?>
            <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td><?= htmlspecialchars($b['nama_barang']); ?></td>
                <td class="text-center"><?= htmlspecialchars($b['no_seri']); ?></td>
                <td class="text-center"><?= htmlspecialchars($b['satuan']); ?></td>
                <td class="text-center">-</td> 
                <td class="text-center">-</td> 
                <td class="text-center fw-bold"><?= number_format($b['jumlah']); ?></td>
                <td class="text-center">-</td>
                <td class="text-center"><?= date('d/m/Y', strtotime($b['tanggal'])); ?></td>
                <td><?= htmlspecialchars($b['keterangan']); ?></td>
            </tr>
            <?php endwhile; ?>
            
            <?php for($i=$no; $i<=6; $i++): ?>
            <tr>
                <td class="text-center"><?= $i; ?></td>
                <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <table class="w-100 mt-2" style="font-size: 10px;">
        <tr>
            <td style="width: 40%;">Perintah kerja : ............................................................</td>
            <td style="width: 30%;">Kode Akun : ....................................................</td>
            <td>Fungsi : ....................................................</td>
        </tr>
    </table>

    <table class="tanda-tangan w-100 mt-5">
        <tr>
            <td style="width: 35%;"></td>
            <td style="width: 30%;"></td>
            <td style="width: 35%;">Makassar, <?= tgl_indo($ba_terakhir); ?></td>
        </tr>
        <tr class="fw-bold">
            <td><br>MANAGER<br><?= htmlspecialchars(strtoupper($tujuan)); ?></td>
            <td></td>
            <td><br>MANAGER<br>UPT MAKASSAR</td>
        </tr>
        <tr style="height: 70px;">
            <td></td><td></td><td></td>
        </tr>
        <tr class="fw-bold">
            <td><u>...................................................</u></td>
            <td></td>
            <td><u>...................................................</u></td>
        </tr>
    </table>
</div>

</body>
</html>