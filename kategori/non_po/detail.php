<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../../login/index.php");
    exit;
}
// PERBAIKAN PATH: Naik dua tingkat (../../) agar bisa menemukan folder config dengan benar
include "../../config/koneksi.php";

$id = $_GET['id'] ?? '';
$query = mysqli_query($conn, "SELECT * FROM material_gudang WHERE id = '$id'");
$d = mysqli_fetch_assoc($query);

if(!$d) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='non_po.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>I-CALM | Detail Non PO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background: #f4f7fc; font-family: 'Plus Jakarta Sans', sans-serif; padding: 40px 0; }
        .detail-table th { width: 30%; background: #f8fafc; color: #64748b; font-weight: 700; text-transform: uppercase; font-size: 0.8rem; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-5" style="border-radius: 16px; background:#fff;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold m-0 text-primary">Detail Logistik Non PO</h4>
                    <div>
                        <a href="edit.php?id=<?= $d['id']; ?>" class="btn btn-warning text-white btn-sm fw-bold px-3">Edit</a>
                        <a href="non_po.php" class="btn btn-secondary btn-sm px-3">Kembali</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered detail-table">
                        <tr><th>Jenis BA</th><td><?= htmlspecialchars($d['jenis_ba']); ?></td></tr>
                        <tr><th>Tanggal</th><td><?= !empty($d['tanggal']) ? date('d-m-Y', strtotime($d['tanggal'])) : '-'; ?></td></tr>
                        <tr><th>Nama Barang</th><td><?= htmlspecialchars($d['nama_material']); ?></td></tr>
                        <tr><th>Merk / Jenis</th><td><?= htmlspecialchars($d['merk_jenis']); ?></td></tr>
                        <tr><th>Jenis Barang</th><td><?= htmlspecialchars($d['jenis_barang']); ?></td></tr>
                        <tr><th>Sumber Barang</th><td><?= htmlspecialchars($d['sumber_barang']); ?></td></tr>
                        <tr><th>Volume Stok</th><td><span class="badge bg-primary fs-6"><?= number_format($d['jumlah'])." ".$d['satuan']; ?></span></td></tr>
                        <tr><th>Tujuan</th><td><?= htmlspecialchars($d['tujuan']); ?></td></tr>
                        <tr><th>Kondisi</th><td><?= htmlspecialchars($d['kondisi']); ?></td></tr>
                        <tr><th>Vendor</th><td><?= htmlspecialchars($d['vendor']); ?></td></tr>
                        <tr><th>Berita Acara</th><td><?= htmlspecialchars($d['berita_acara']); ?></td></tr>
                        <tr><th>Kategori Sistem</th><td><span class="badge bg-secondary"><?= htmlspecialchars($d['jenis_kategori']); ?></span></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>