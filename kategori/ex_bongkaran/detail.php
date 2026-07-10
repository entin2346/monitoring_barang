<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../../login/index.php");
    exit;
}
include "../../config/koneksi.php";

$id = $_GET['id'] ?? '';
$query = mysqli_query($conn, "SELECT * FROM material_gudang WHERE id = '$id' AND jenis_kategori = 'ex_bongkaran'");
$d = mysqli_fetch_assoc($query);

if (!$d) {
    die("Data tidak ditemukan atau Anda tidak memiliki akses.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Material Ex Bongkaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f4f7fc; font-family: 'Plus Jakarta Sans', sans-serif; padding: 40px 0; }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        table th { width: 30%; color: #64748b; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card p-4">
                <h4 class="fw-bold text-primary mb-4">Detail Material Ex Bongkaran</h4>
                <table class="table table-bordered">
                    <tr><th>Jenis BA</th><td><?= htmlspecialchars($d['jenis_ba']); ?></td></tr>
                    <tr><th>Tanggal</th><td><?= !empty($d['tanggal']) ? date('d-m-Y', strtotime($d['tanggal'])) : '-'; ?></td></tr>
                    <tr><th>Nama Barang / Material</th><td class="fw-bold text-dark"><?= htmlspecialchars($d['nama_material']); ?></td></tr>
                    <tr><th>Merk / Jenis</th><td><?= htmlspecialchars($d['merk_jenis']); ?></td></tr>
                    <tr><th>Jenis Barang</th><td><?= htmlspecialchars($d['jenis_barang']); ?></td></tr>
                    <tr><th>Sumber Barang</th><td><?= htmlspecialchars($d['sumber_barang']); ?></td></tr>
                    <tr><th>Satuan</th><td><?= htmlspecialchars($d['satuan']); ?></td></tr>
                    <tr><th>Jumlah</th><td><span class="badge bg-primary fs-6"><?= number_format($d['jumlah']); ?></span></td></tr>
                    <tr><th>Tujuan</th><td><?= htmlspecialchars($d['tujuan']); ?></td></tr>
                    <tr><th>Kondisi</th><td><?= htmlspecialchars($d['kondisi']); ?></td></tr>
                    <tr><th>Vendor</th><td><?= htmlspecialchars($d['vendor']); ?></td></tr>
                    <tr><th>Berita Acara</th><td><?= htmlspecialchars($d['berita_acara']); ?></td></tr>
                </table>
                <div class="mt-4">
                    <a href="ex_bongkaran.php" class="btn btn-secondary px-4">Kembali ke Panel</a>
                    <a href="edit.php?id=<?= $d['id']; ?>" class="btn btn-warning text-white px-4">Edit Data</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>