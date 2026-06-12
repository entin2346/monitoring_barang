<?php
include "../config/koneksi.php";

$cari = $_GET['cari'] ?? '';
$cari = mysqli_real_escape_string($conn, $cari);

// Pagination
$limit = 25;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if($page < 1){
    $page = 1;
}

$offset = ($page - 1) * $limit;

// Hitung total data
$total_query = mysqli_query($conn,"
    SELECT COUNT(*) as total
    FROM database_ba
    WHERE nama_barang IS NOT NULL
    AND nama_barang <> ''
    AND nama_barang LIKE '%$cari%'
");

$total_row = mysqli_fetch_assoc($total_query);
$total_data = $total_row['total'];

$total_halaman = ceil($total_data / $limit);

// Ambil data
$query = mysqli_query($conn,"
    SELECT *
    FROM database_ba
    WHERE nama_barang IS NOT NULL
    AND nama_barang <> ''
    AND nama_barang LIKE '%$cari%'
    ORDER BY tanggal DESC, id DESC
    LIMIT $offset,$limit
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database BA</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Database BA</h2>

        <div>

<a href="../dashboard/index.php"
class="btn btn-secondary">
← Dashboard
</a>

<a href="../export/ba_excel.php"
class="btn btn-success">
📥 Export Excel
</a>

</div>
    </div>

    <form method="GET" class="mb-3">

        <div class="row">

            <div class="col-md-6">
                <input
                    type="text"
                    name="cari"
                    class="form-control"
                    placeholder="Cari Nama Barang..."
                    value="<?= htmlspecialchars($cari); ?>">
            </div>

            <div class="col-md-2">
                <button
                    type="submit"
                    class="btn btn-primary w-100">
                    Cari
                </button>
            </div>

        </div>

    </form>

    <div class="alert alert-info">
        Total Data Ditemukan:
        <b><?= number_format($total_data); ?></b>
    </div>

    <div class="table-responsive">

        <table class="table table-bordered table-striped table-hover table-sm">

            <thead class="table-dark">
                <tr>
                    <th width="60">No</th>
                    <th width="120">Tanggal</th>
                    <th>Nama Barang</th>
                    <th width="100">Jumlah</th>
                    <th width="180">Tujuan</th>
                    <th width="150">Kondisi</th>
                </tr>
            </thead>

            <tbody>

            <?php

            $no = $offset + 1;

            if(mysqli_num_rows($query) > 0){

                while($d = mysqli_fetch_assoc($query)){

                    $tanggal = '';

                    if(
                        !empty($d['tanggal']) &&
                        $d['tanggal'] != '0000-00-00'
                    ){
                        $tanggal = date('d-m-Y', strtotime($d['tanggal']));
                    }

            ?>

                <tr>

                    <td><?= $no++; ?></td>

                    <td><?= $tanggal; ?></td>

                    <td><?= htmlspecialchars($d['nama_barang']); ?></td>

                    <td><?= number_format($d['jumlah']); ?></td>

                    <td><?= htmlspecialchars($d['tujuan']); ?></td>

                    <td><?= htmlspecialchars($d['kondisi_material']); ?></td>

                </tr>

            <?php

                }

            } else {

            ?>

                <tr>
                    <td colspan="6" class="text-center">
                        Data tidak ditemukan
                    </td>
                </tr>

            <?php } ?>

            </tbody>

        </table>

    </div>

    <!-- Pagination -->

    <nav class="mt-3">

        <ul class="pagination flex-wrap">

            <?php if($page > 1){ ?>

                <li class="page-item">
                    <a class="page-link"
                       href="?cari=<?= urlencode($cari); ?>&page=<?= $page-1; ?>">
                       Previous
                    </a>
                </li>

            <?php } ?>

            <?php

            for($i=1; $i<=$total_halaman; $i++){

                if(
                    $i == 1 ||
                    $i == $total_halaman ||
                    ($i >= $page-2 && $i <= $page+2)
                ){

            ?>

                <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">

                    <a class="page-link"
                       href="?cari=<?= urlencode($cari); ?>&page=<?= $i; ?>">

                       <?= $i; ?>

                    </a>

                </li>

            <?php

                }

            }

            ?>

            <?php if($page < $total_halaman){ ?>

                <li class="page-item">
                    <a class="page-link"
                       href="?cari=<?= urlencode($cari); ?>&page=<?= $page+1; ?>">
                       Next
                    </a>
                </li>

            <?php } ?>

        </ul>

    </nav>

</div>

</body>
</html>