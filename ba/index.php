<?php
include "../config/koneksi.php";
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

<div class="container mt-4">

    <h2>Database BA</h2>

    <div class="d-flex justify-content-between mb-3">
        <a href="../dashboard/index.php" class="btn btn-secondary">Kembali</a>
    </div>

    <form method="GET" class="mb-3">
        <input 
            type="text" 
            name="cari" 
            class="form-control" 
            placeholder="Cari Nama Barang..." 
            value="<?= htmlspecialchars($_GET['cari'] ?? ''); ?>">
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Tujuan</th>
                    <th>Kondisi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $cari = $_GET['cari'] ?? '';

                // Query dengan filter pencarian dan limit 100
                $query = mysqli_query(
                    $conn,
                    "SELECT * FROM database_ba 
                     WHERE nama_barang LIKE '%$cari%' 
                     ORDER BY id DESC 
                     LIMIT 100"
                );

                $no = 1;
                // Cek jika data ada
                if (mysqli_num_rows($query) > 0) {
                    while($d = mysqli_fetch_assoc($query)) {
                ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($d['tanggal']); ?></td>
                    <td><?= htmlspecialchars($d['nama_barang']); ?></td>
                    <td><?= htmlspecialchars($d['jumlah']); ?></td>
                    <td><?= htmlspecialchars($d['tujuan']); ?></td>
                    <td><?= htmlspecialchars($d['kondisi_material']); ?></td>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>Data tidak ditemukan</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <small class="text-muted">* Menampilkan maksimal 100 data terbaru.</small>
    </div>

</div>

</body>
</html>