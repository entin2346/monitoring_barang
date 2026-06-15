<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

$cari = $_GET['cari'] ?? '';
$cari = mysqli_real_escape_string($conn, $cari);

$query = mysqli_query($conn,"
    SELECT *
    FROM database_ba
    WHERE UPPER(jenis_berita_acara) LIKE 'KELUAR%'
    AND (
        nama_barang LIKE '%$cari%'
        OR merk_jenis LIKE '%$cari%'
        OR jenis_barang LIKE '%$cari%'
        OR sumber_barang LIKE '%$cari%'
        OR no_seri LIKE '%$cari%'
        OR asal_barang_vendor LIKE '%$cari%'
        OR kategori_material LIKE '%$cari%'
        OR keterangan LIKE '%$cari%'
    )
    ORDER BY tanggal DESC, id DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang Keluar</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body{
            background:#f4f6f9;
        }

        .header{
            background:#dc3545;
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
        <h2>📤 Data Barang Keluar</h2>
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
                    placeholder="Cari Nama Material, Merk, No Seri, Keterangan..."
                    value="<?= htmlspecialchars($cari); ?>"
                >
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-danger w-100">
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
                    <th>Jumlah</th>
                    <th>Nomor Seri</th>
                    <th>Pemasok/Asal Material</th>
                    <th>Kategori Material</th>
                    <th>Keterangan</th>
                </tr>
            </thead>

            <tbody>

            <?php
            $no = 1;

            if(mysqli_num_rows($query) > 0){

                while($d = mysqli_fetch_assoc($query)){
            ?>

                <tr>

                    <td><?= $no++; ?></td>

                    <td>
                        <?= !empty($d['tanggal']) ? date('d-m-Y', strtotime($d['tanggal'])) : '-'; ?>
                    </td>

                    <td><?= htmlspecialchars($d['nama_barang']); ?></td>

                    <td><?= htmlspecialchars($d['merk_jenis']); ?></td>

                    <td><?= htmlspecialchars($d['jenis_barang']); ?></td>

                    <td><?= htmlspecialchars($d['sumber_barang']); ?></td>

                    <td><?= htmlspecialchars($d['satuan']); ?></td>

                    <td><?= number_format($d['jumlah']); ?></td>

                    <td><?= htmlspecialchars($d['no_seri']); ?></td>

                    <td><?= htmlspecialchars($d['asal_barang_vendor']); ?></td>

                    <td><?= htmlspecialchars($d['kategori_material']); ?></td>

                    <td><?= htmlspecialchars($d['keterangan']); ?></td>

                </tr>

            <?php
                }

            }else{
            ?>

                <tr>
                    <td colspan="12" class="text-center">
                        Data tidak ditemukan
                    </td>
                </tr>

            <?php } ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>