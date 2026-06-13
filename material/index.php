<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

$cari = $_GET['cari'] ?? '';
$cari = mysqli_real_escape_string($conn,$cari);

$limit = 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if($page < 1){ $page = 1; }

$offset = ($page - 1) * $limit;

/* TOTAL DATA */
$total_query = mysqli_query($conn,"
    SELECT COUNT(*) AS total
    FROM material_gudang
    WHERE nama_material <> ''
    AND nama_material LIKE '%$cari%'
");

$total_data = mysqli_fetch_assoc($total_query)['total'];
$total_halaman = ceil($total_data / $limit);

/* TOTAL STOK */
$total_stok = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT SUM(jumlah) AS total FROM material_gudang")
);

/* DATA MATERIAL */
$query = mysqli_query($conn,"
    SELECT *
    FROM material_gudang
    WHERE nama_material <> ''
    AND nama_material LIKE '%$cari%'
    ORDER BY nama_material ASC
    LIMIT $offset,$limit
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Material Gudang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f4f6f9; }
        .card { border:none; border-radius:15px; }
        .table th { white-space:nowrap; }
        .navbar-brand { font-weight:bold; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-primary shadow">
    <div class="container-fluid">
        <span class="navbar-brand">📦 I-CALM - Material Gudang</span>
        <div>
            <a href="../dashboard/index.php" class="btn btn-light btn-sm">Dashboard</a>
            <a href="../export/material_excel.php" class="btn btn-success btn-sm">Export Excel</a>
        </div>
    </div>
</nav>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <h6>Total Jenis Material</h6>
                    <h2><?= number_format($total_data); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <h6>Total Stok Material</h6>
                    <h2><?= number_format($total_stok['total'] ?? 0); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mt-4">
        <div class="card-body">
            <form method="GET">
                <div class="row">
                    <div class="col-md-10">
                        <input type="text" name="cari" class="form-control" placeholder="Cari Nama Material..." value="<?= htmlspecialchars($cari); ?>">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Cari</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mt-3">
        <div class="card-body">
            
            <a href="tambah.php" class="btn btn-success mb-3">+ Tambah Material</a>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Material</th>
                            <th>Sat</th>
                            <th>Jumlah</th>
                            <th>No Rak</th>
                            <th>Kondisi</th>
                            <th>Lokasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = $offset + 1;
                        if(mysqli_num_rows($query) > 0){
                            while($d = mysqli_fetch_assoc($query)){
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($d['nama_material']); ?></td>
                            <td><?= htmlspecialchars($d['satuan']); ?></td>
                            <td><?= number_format($d['jumlah']); ?></td>
                            <td><?= htmlspecialchars($d['no_rak']); ?></td>
                            <td>
                                <?php
                                if(strtoupper($d['kondisi']) == 'BAIK'){
                                    echo "<span class='badge bg-success'>BAIK</span>";
                                }else{
                                    echo "<span class='badge bg-warning text-dark'>".htmlspecialchars($d['kondisi'])."</span>";
                                }
                                ?>
                            </td>
                            <td><?= htmlspecialchars($d['lokasi_penyimpanan']); ?></td>
                            <td>
                                <a href="detail.php?id=<?= $d['id']; ?>" class="btn btn-info btn-sm">Detail</a>
                                <a href="edit.php?id=<?= $d['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="hapus.php?id=<?= $d['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus material ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php
                            }
                        }else{
                        ?>
                        <tr>
                            <td colspan="8" class="text-center">Data tidak ditemukan</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <nav>
                <ul class="pagination justify-content-center">
                    <?php if($page > 1){ ?>
                    <li class="page-item"><a class="page-link" href="?cari=<?= urlencode($cari); ?>&page=<?= $page-1; ?>">«</a></li>
                    <?php } ?>

                    <?php
                    for($i=1; $i<=$total_halaman; $i++){
                        if($i == 1 || $i == $total_halaman || ($i >= $page-2 && $i <= $page+2)){
                    ?>
                        <li class="page-item <?= ($i==$page)?'active':''; ?>">
                            <a class="page-link" href="?cari=<?= urlencode($cari); ?>&page=<?= $i; ?>"><?= $i; ?></a>
                        </li>
                    <?php
                        }
                    }
                    ?>

                    <?php if($page < $total_halaman){ ?>
                    <li class="page-item"><a class="page-link" href="?cari=<?= urlencode($cari); ?>&page=<?= $page+1; ?>">»</a></li>
                    <?php } ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

</body>
</html>