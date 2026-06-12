<?php
include "../config/koneksi.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Material Gudang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-4">

    <h2>Data Material Gudang</h2>

    <div class="d-flex justify-content-between mb-3">
        <a href="../dashboard/index.php" class="btn btn-secondary">Kembali</a>
    </div>

    <form method="GET" class="mb-3">
        <input 
            type="text" 
            name="cari" 
            class="form-control" 
            placeholder="Cari Material..." 
            value="<?= htmlspecialchars($_GET['cari'] ?? ''); ?>">
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Nama Material</th>
                    <th>Sat</th>
                    <th>Jumlah</th>
                    <th>No Rak</th>
                    <th>Kondisi</th>
                    <th>Lokasi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Menangkap input pencarian
                $cari = $_GET['cari'] ?? '';

                // Query dengan filter pencarian
                $query = mysqli_query(
                    $conn,
                    "SELECT * FROM material_gudang 
                     WHERE nama_material LIKE '%$cari%' 
                     ORDER BY nama_material ASC"
                );

                $no = 1;
                if (mysqli_num_rows($query) > 0) {
                    while($d = mysqli_fetch_assoc($query)) {
                ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($d['nama_material']); ?></td>
                    <td><?= htmlspecialchars($d['satuan']); ?></td>
                    <td><?= htmlspecialchars($d['jumlah']); ?></td>
                    <td><?= htmlspecialchars($d['no_rak']); ?></td>
                    <td><?= htmlspecialchars($d['kondisi']); ?></td>
                    <td><?= htmlspecialchars($d['lokasi_penyimpanan']); ?></td>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>Data tidak ditemukan</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>