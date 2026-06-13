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
    <title>Barang Keluar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">

    <h2>Daftar Barang Keluar</h2>

    <a href="index.php" class="btn btn-secondary mb-3">
        ← Kembali
    </a>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Total Keluar</th>
            </tr>
        </thead>
        <tbody>
        <?php
        // Query yang diperbarui dengan LIKE 'KELUAR%'
        $query = mysqli_query($conn,"
            SELECT
                nama_barang,
                SUM(jumlah) AS total_keluar
            FROM database_ba
            WHERE jenis_berita_acara LIKE 'KELUAR%'
            GROUP BY nama_barang
            ORDER BY total_keluar DESC
        ");

        $no = 1;
        if(mysqli_num_rows($query) > 0){
            while($d = mysqli_fetch_assoc($query)){
        ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($d['nama_barang']); ?></td>
                <td><?= number_format($d['total_keluar']); ?></td>
            </tr>
        <?php 
            }
        } else {
        ?>
            <tr>
                <td colspan="3" class="text-center">Data tidak ditemukan</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

</div>

</body>
</html>