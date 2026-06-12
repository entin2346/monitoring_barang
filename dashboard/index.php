<?php
include "../config/koneksi.php";

// Menghitung statistik dengan filter agar data kosong tidak ikut terhitung
$total_material = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) as total FROM material_gudang WHERE nama_material IS NOT NULL AND nama_material <> ''")
);

$total_ba = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) as total FROM database_ba WHERE nama_barang IS NOT NULL AND nama_barang <> ''")
);

$total_stok = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT SUM(jumlah) as total FROM material_gudang")
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Monitoring Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">

    <h2 class="mb-3">Dashboard Monitoring Material UPT Makassar</h2>

    <div class="mb-4">
        <a href="../material/index.php" class="btn btn-primary">📦 Data Material</a>
        <a href="../ba/index.php" class="btn btn-success">📄 Database BA</a>
        <a href="../import/material.php" class="btn btn-warning">⬆ Import Material</a>
        <a href="../import/ba.php" class="btn btn-info">⬆ Import BA</a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card bg-primary text-white mb-3">
                <div class="card-body">
                    <h5>Total Material</h5>
                    <h2><?= $total_material['total'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white mb-3">
                <div class="card-body">
                    <h5>Total Data BA</h5>
                    <h2><?= $total_ba['total'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark mb-3">
                <div class="card-body">
                    <h5>Total Stok Material</h5>
                    <h2><?= number_format($total_stok['total'] ?? 0); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h4>Data BA Terbaru</h4>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Tujuan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query dengan filter agar tidak menampilkan data kosong
                $data = mysqli_query($conn, "
                    SELECT * FROM database_ba 
                    WHERE nama_barang IS NOT NULL 
                    AND nama_barang <> '' 
                    ORDER BY id DESC 
                    LIMIT 10
                ");
                
                $no = 1;
                while($d = mysqli_fetch_assoc($data)) {
                ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($d['tanggal']); ?></td>
                    <td><?= htmlspecialchars($d['nama_barang']); ?></td>
                    <td><?= htmlspecialchars($d['jumlah']); ?></td>
                    <td><?= htmlspecialchars($d['tujuan']); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>