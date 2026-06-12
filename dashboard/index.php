<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

/* =========================
   GRAFIK MATERIAL
========================= */

$grafik = mysqli_query($conn,"
    SELECT nama_material, jumlah
    FROM material_gudang
    WHERE nama_material IS NOT NULL
    AND nama_material <> ''
    AND jumlah > 0
    ORDER BY jumlah DESC
    LIMIT 10
");

$label = [];
$jumlah_material = [];

while($g = mysqli_fetch_assoc($grafik)){
    $label[] = $g['nama_material'];
    $jumlah_material[] = (int)$g['jumlah'];
}

/* =========================
   STATISTIK DASHBOARD
========================= */

$total_material = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) as total FROM material_gudang WHERE nama_material <> ''")
);

$total_ba = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) as total FROM database_ba WHERE nama_barang <> ''")
);

$total_stok = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT SUM(jumlah) as total FROM material_gudang")
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Monitoring Material</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { margin: 0; background: #f4f6f9; }
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100%;
            background: #0d6efd;
            padding-top: 20px;
        }
        .sidebar h3 { color: white; text-align: center; margin-bottom: 30px; }
        .sidebar a { display: block; color: white; text-decoration: none; padding: 15px 20px; }
        .sidebar a:hover { background: rgba(255,255,255,0.2); }
        .content { margin-left: 250px; padding: 20px; }
        .card { border: none; border-radius: 15px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>⚡ PLN UPT</h3>
    <a href="../dashboard/index.php">📊 Dashboard</a>
    <a href="../material/index.php">📦 Material Gudang</a>
    <a href="../ba/index.php">📄 Database BA</a>
    <a href="../import/material.php">⬆ Import Material</a>
    <a href="../import/ba.php">⬆ Import BA</a>
    <a href="../export/material_excel.php">📥 Export Material</a>
    <a href="../export/ba_excel.php">📥 Export BA</a>
    <a href="../login/logout.php">🚪 Logout</a>
</div>

<div class="content">
    
    <div class="mb-4">
        <h3>Dashboard Monitoring Material</h3>
        <p>Selamat datang, <b><?= htmlspecialchars($_SESSION['nama']); ?></b></p>
    </div>

    <div class="row">
        <div class="col-md-4"><div class="card bg-primary text-white shadow"><div class="card-body"><h5>Total Material</h5><h2><?= number_format($total_material['total'] ?? 0); ?></h2></div></div></div>
        <div class="col-md-4"><div class="card bg-success text-white shadow"><div class="card-body"><h5>Total Data BA</h5><h2><?= number_format($total_ba['total'] ?? 0); ?></h2></div></div></div>
        <div class="col-md-4"><div class="card bg-warning shadow"><div class="card-body"><h5>Total Stok Material</h5><h2><?= number_format($total_stok['total'] ?? 0); ?></h2></div></div></div>
    </div>

    <hr class="my-4">

    <h4>10 Data BA Terbaru</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr><th>No</th><th>Tanggal</th><th>Nama Barang</th><th>Jumlah</th><th>Tujuan</th></tr>
            </thead>
            <tbody>
                <?php
                $ba_terbaru = mysqli_query($conn,"
                    SELECT * FROM database_ba 
                    WHERE nama_barang IS NOT NULL AND nama_barang <> '' 
                    ORDER BY tanggal DESC, id DESC LIMIT 10
                ");
                $no = 1;
                while($d = mysqli_fetch_assoc($ba_terbaru)){
                ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= (!empty($d['tanggal']) && $d['tanggal'] != '0000-00-00') ? date('d-m-Y', strtotime($d['tanggal'])) : '-'; ?></td>
                    <td><?= htmlspecialchars($d['nama_barang']); ?></td>
                    <td><?= number_format($d['jumlah']); ?></td>
                    <td><?= htmlspecialchars($d['tujuan']); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <hr class="mt-5">
    <div class="card shadow mb-4">
        <div class="card-header"><h5>Top 10 Material Terbanyak</h5></div>
        <div class="card-body">
            <canvas id="chartMaterial"></canvas>
        </div>
    </div>

</div>

<script>
    const ctx = document.getElementById('chartMaterial').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($label); ?>,
            datasets: [{
                label: 'Jumlah Material',
                data: <?= json_encode($jumlah_material); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: { y: { beginAtZero: true } }
        }
    });
</script>

</body>
</html>