<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

$cari = $_GET['cari'] ?? '';
$cari = mysqli_real_escape_string($conn,$cari);

$query = mysqli_query($conn,"
SELECT
    nama_barang,
    MAX(tanggal) as tanggal,
    MAX(merk_jenis) as merk_jenis,
    MAX(jenis_barang) as jenis_barang,
    MAX(sumber_barang) as sumber_barang,
    MAX(satuan) as satuan,
    MAX(no_seri) as no_seri,
    MAX(asal_barang_vendor) as asal_barang_vendor,
    MAX(kategori_material) as kategori_material,
    MAX(keterangan) as keterangan,

    SUM(
        CASE
            WHEN UPPER(jenis_berita_acara)='MASUK'
            OR UPPER(jenis_berita_acara)='RETURN'
            OR UPPER(jenis_berita_acara)='PENGEMBALIAN'
            THEN jumlah
            ELSE 0
        END
    ) AS total_masuk,

    SUM(
        CASE
            WHEN UPPER(jenis_berita_acara)='KELUAR'
            OR UPPER(jenis_berita_acara)='TERPAKAI'
            OR UPPER(jenis_berita_acara)='PEMINJAMAN'
            THEN jumlah
            ELSE 0
        END
    ) AS total_keluar

FROM database_ba

WHERE
    nama_barang LIKE '%$cari%'
    OR merk_jenis LIKE '%$cari%'
    OR jenis_barang LIKE '%$cari%'
    OR sumber_barang LIKE '%$cari%'
    OR no_seri LIKE '%$cari%'
    OR asal_barang_vendor LIKE '%$cari%'
    OR kategori_material LIKE '%$cari%'
    OR keterangan LIKE '%$cari%'

GROUP BY nama_barang
ORDER BY nama_barang ASC
");

if(!$query){
    die(mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stok Barang</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f4f6f9;
}

.header{
    background:#0d6efd;
    color:white;
    padding:20px;
    border-radius:10px;
    margin-bottom:20px;
}

.table th{
    white-space: nowrap;
    font-size:13px;
}

.table td{
    white-space: nowrap;
    font-size:13px;
    vertical-align: middle;
}

</style>
</head>
<body>

<div class="container-fluid mt-4">

<div class="header">
    <h2>📦 Stok Barang</h2>
    <small>Monitoring Distribusi Alat & Material</small>
</div>

<div class="mb-3">
    <a href="index.php" class="btn btn-secondary">
        ← Kembali
    </a>
</div>

<form method="GET" class="mb-3">
    <div class="row">
        <div class="col-md-6">
            <input
                type="text"
                name="cari"
                class="form-control"
                placeholder="Cari Material..."
                value="<?= htmlspecialchars($cari); ?>">
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
                Cari
            </button>
        </div>
    </div>
</form>

<div class="table-responsive">

<table class="table table-bordered table-striped table-hover">

<thead class="table-dark text-center">
<tr>
    <th>No</th>
    <th>Tanggal</th>
    <th>Nama Material</th>
    <th>Merk/Jenis</th>
    <th>Jenis Material</th>
    <th>Sumber Material</th>
    <th>Satuan</th>
    <th>Stok Saat Ini</th>
    <th>Nomor Seri</th>
    <th>Pemasok/Asal Material</th>
    <th>Kategori Material</th>
    <th>Keterangan</th>
</tr>
</thead>

<tbody>

<?php
$no = 1;

while($d = mysqli_fetch_assoc($query)){

    $stok = abs($d['total_masuk'] - $d['total_keluar']);

    if($stok == 0){
        $badge = "danger";
    }elseif($stok < 10){
        $badge = "warning";
    }else{
        $badge = "success";
    }
?>

<tr>

<td><?= $no++; ?></td>

<td>
<?= !empty($d['tanggal']) ? date('d-m-Y',strtotime($d['tanggal'])) : '-'; ?>
</td>

<td><?= htmlspecialchars($d['nama_barang']); ?></td>

<td><?= htmlspecialchars($d['merk_jenis']); ?></td>

<td><?= htmlspecialchars($d['jenis_barang']); ?></td>

<td><?= htmlspecialchars($d['sumber_barang']); ?></td>

<td><?= htmlspecialchars($d['satuan']); ?></td>

<td class="text-center">
    <span class="badge bg-<?= $badge ?>">
        <?= number_format($stok); ?>
    </span>
</td>

<td><?= htmlspecialchars($d['no_seri']); ?></td>

<td><?= htmlspecialchars($d['asal_barang_vendor']); ?></td>

<td><?= htmlspecialchars($d['kategori_material']); ?></td>

<td><?= htmlspecialchars($d['keterangan']); ?></td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="text-center">
                    <tr>
                        <th>No</th><th>Tanggal</th><th>Nama Material</th><th>Merk/Jenis</th><th>Jenis Material</th>
                        <th>Sumber</th><th>Satuan</th><th>Stok Saat Ini</th><th>Nomor Seri</th>
                        <th>Pemasok</th><th>Kategori</th><th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    if(mysqli_num_rows($query) > 0){
                        while($d = mysqli_fetch_assoc($query)){
                            $stok = max(0, $d['total_masuk'] - $d['total_keluar']);
$badge = ($stok == 0) ? "danger" : (($stok < 10) ? "warning" : "success");
                    ?>
                    <tr>
                        <td class="text-center fw-bold"><?= $no++; ?></td>
                        <td class="text-center"><?= !empty($d['tanggal']) ? date('d-m-Y', strtotime($d['tanggal'])) : '-'; ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($d['nama_barang']); ?></td>
                        <td><?= htmlspecialchars($d['merk_jenis']); ?></td>
                        <td><?= htmlspecialchars($d['jenis_barang']); ?></td>
                        <td><?= htmlspecialchars($d['sumber_barang']); ?></td>
                        <td class="text-center"><?= htmlspecialchars($d['satuan']); ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $badge ?> px-3 rounded-pill"><?= number_format($stok); ?></span>
                        </td>
                        <td><?= htmlspecialchars($d['no_seri']); ?></td>
                        <td><?= htmlspecialchars($d['asal_barang_vendor']); ?></td>
                        <td><?= htmlspecialchars($d['kategori_material']); ?></td>
                        <td><?= htmlspecialchars($d['keterangan']); ?></td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='12' class='text-center py-4'>Data tidak ditemukan</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>