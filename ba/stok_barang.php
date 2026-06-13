<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">

    <h2>Stok Barang</h2>

    <a href="index.php" class="btn btn-secondary mb-3">
        ← Kembali
    </a>

    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Masuk</th>
                <th>Keluar</th>
                <th>Stok</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $query = mysqli_query($conn,"
            SELECT
                nama_barang,
                SUM(CASE 
                    WHEN jenis_berita_acara LIKE 'MASUK%' 
                    OR jenis_berita_acara='PENGEMBALIAN' 
                    OR jenis_berita_acara='RETURN' 
                    THEN jumlah ELSE 0 
                END) AS masuk,
                SUM(CASE 
                    WHEN jenis_berita_acara LIKE 'KELUAR%' 
                    OR jenis_berita_acara='TERPAKAI' 
                    OR jenis_berita_acara='PEMINJAMAN' 
                    THEN jumlah ELSE 0 
                END) AS keluar
            FROM database_ba
            WHERE nama_barang <> ''
            GROUP BY nama_barang
            ORDER BY nama_barang ASC
        ");

        $no = 1;
        if(mysqli_num_rows($query) > 0){
            while($d = mysqli_fetch_assoc($query)){
                $stok = $d['masuk'] - $d['keluar'];
                
                // Logika Warna Badge
                $badge = "success";
                if($stok <= 0){
                    $badge = "danger";
                } elseif($stok < 10){
                    $badge = "warning";
                }
        ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($d['nama_barang']); ?></td>
                <td><?= number_format($d['masuk']); ?></td>
                <td><?= number_format($d['keluar']); ?></td>
                <td>
                    <span class="badge bg-<?= $badge ?>">
                        <?= number_format($stok) ?>
                    </span>
                </td>
            </tr>
        <?php 
            }
        } else {
        ?>
            <tr>
                <td colspan="5" class="text-center">Data tidak ditemukan</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

</div>

</body>
</html>