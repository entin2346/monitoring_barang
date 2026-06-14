<?php
include "../config/koneksi.php";

/* ======================
    RINGKASAN DATA BA
====================== */

$barang_masuk = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT SUM(jumlah) as total FROM database_ba WHERE UPPER(jenis_berita_acara)='MASUK'")
);

$barang_keluar = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT SUM(jumlah) as total FROM database_ba WHERE UPPER(jenis_berita_acara)='KELUAR'")
);

$total_ba = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) as total FROM database_ba WHERE nama_barang <> ''")
);

$stok = ($barang_masuk['total'] ?? 0) - ($barang_keluar['total'] ?? 0);

/* ======================
   PENCARIAN & PAGINATION
====================== */

$cari = $_GET['cari'] ?? '';
$cari = mysqli_real_escape_string($conn, $cari);

$limit = 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1){ $page = 1; }
$offset = ($page - 1) * $limit;

$total_query = mysqli_query($conn,"SELECT COUNT(*) as total FROM database_ba WHERE nama_barang IS NOT NULL AND nama_barang <> '' AND nama_barang LIKE '%$cari%'");
$total_row = mysqli_fetch_assoc($total_query);
$total_data = $total_row['total'];
$total_halaman = ceil($total_data / $limit);

$query = mysqli_query($conn,"SELECT * FROM database_ba WHERE nama_barang IS NOT NULL AND nama_barang <> '' AND nama_barang LIKE '%$cari%' ORDER BY tanggal DESC, id DESC LIMIT $offset,$limit");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I-CALM | Database BA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{ background:#f4f6f9; }
        .card{ border:none; border-radius:15px; transition:0.3s; }
        .card:hover{ transform:translateY(-3px); }
        .header-icalm{ background:#0d6efd; color:white; padding:25px; margin-bottom:25px; border-radius:15px; box-shadow:0 2px 10px rgba(0,0,0,.15); }
        .header-icalm h1{ margin:0; font-size:40px; font-weight:bold; }
        .header-icalm p{ margin:0; opacity:.9; }
        .stat-card h3{ font-weight:bold; }
        .table{ background:white; }
        .table th, .table td{ white-space: nowrap; vertical-align: middle; font-size: 13px; }
    </style>
</head>
<body>

<div class="container-fluid mt-4">

    <div class="header-icalm">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>📄 I-CALM - Database BA</h1>
                <p>Integrated Control and Logistics Monitoring</p>
            </div>
            <div>
                <a href="tambah.php" class="btn btn-light">➕ Tambah Data</a>
                <a href="../export/ba_excel.php" class="btn btn-success">📥 Export Excel</a>
                <a href="../dashboard/index.php" class="btn btn-dark">← Dashboard</a>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><a href="barang_masuk.php" style="text-decoration:none;"><div class="card stat-card bg-success text-white shadow"><div class="card-body text-center"><h6>Barang Masuk</h6><h3><?= number_format($barang_masuk['total'] ?? 0); ?></h3></div></div></a></div>
        <div class="col-md-3"><a href="barang_keluar.php" style="text-decoration:none;"><div class="card stat-card bg-danger text-white shadow"><div class="card-body text-center"><h6>Barang Keluar</h6><h3><?= number_format($barang_keluar['total'] ?? 0); ?></h3></div></div></a></div>
        <div class="col-md-3"><a href="stok_barang.php" style="text-decoration:none;"><div class="card stat-card bg-primary text-white shadow"><div class="card-body text-center"><h6>Sisa Stok</h6><h3><?= number_format($stok); ?></h3></div></div></a></div>
        <div class="col-md-3"><div class="card stat-card bg-warning shadow"><div class="card-body text-center"><h6>Total Data BA</h6><h3><?= number_format($total_ba['total']); ?></h3></div></div></div>
    </div>

    <form method="GET" class="mb-3">
        <div class="row">
            <div class="col-md-6"><input type="text" name="cari" class="form-control" placeholder="Cari Nama Barang..." value="<?= htmlspecialchars($cari); ?>"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Cari</button></div>
        </div>
    </form>

    <div class="alert alert-info">Total Data Ditemukan: <b><?= number_format($total_data); ?></b></div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover table-sm">
            <thead class="table-dark text-center">
                <tr>
                    <th>No</th><th>Tanggal</th><th>Nama Material</th><th>Merk/Jenis</th><th>Jenis Material</th><th>Sumber Material</th><th>Satuan</th><th>Jumlah</th><th>Nomor Seri</th><th>Pemasok/Asal Material</th><th>Kategori Material</th><th>Keterangan</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = $offset + 1;
                if(mysqli_num_rows($query) > 0){
                    while($d = mysqli_fetch_assoc($query)){
                        $tanggal = (!empty($d['tanggal']) && $d['tanggal'] != '0000-00-00') ? date('d-m-Y', strtotime($d['tanggal'])) : '-';
                ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= $tanggal; ?></td>
                    <td><a href="detail.php?id=<?= $d['id']; ?>" style="text-decoration:none;font-weight:bold;"><?= htmlspecialchars($d['nama_barang']); ?></a></td>
                    <td><?= htmlspecialchars($d['merk_jenis']); ?></td>
                    <td><?= htmlspecialchars($d['jenis_barang']); ?></td>
                    <td><?= htmlspecialchars($d['sumber_barang']); ?></td>
                    <td><?= htmlspecialchars($d['satuan']); ?></td>
                    <td><?= number_format($d['jumlah']); ?></td>
                    <td><?= htmlspecialchars($d['no_seri']); ?></td>
                    <td><?= htmlspecialchars($d['asal_barang_vendor']); ?></td>
                    <td>
                        <?php
                        $kategori = strtoupper($d['jenis_berita_acara']);
                        if(strpos($kategori,'MASUK') !== false){ echo "<span class='badge bg-success'>MASUK</span>"; }
                        elseif(strpos($kategori,'KELUAR') !== false || strpos($kategori,'TERPAKAI') !== false){ echo "<span class='badge bg-danger'>KELUAR</span>"; }
                        elseif(strpos($kategori,'RETURN') !== false || strpos($kategori,'PENGEMBALIAN') !== false){ echo "<span class='badge bg-warning text-dark'>RETURN</span>"; }
                        else{ echo "<span class='badge bg-secondary'>".$kategori."</span>"; }
                        ?>
                    </td>
                    <td><?= htmlspecialchars($d['keterangan']); ?></td>
                    <td nowrap>
                        <a href="detail.php?id=<?= $d['id']; ?>" class="btn btn-info btn-sm">Detail</a>
                        <a href="edit.php?id=<?= $d['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="hapus.php?id=<?= $d['id']; ?>" class="btn btn-danger btn-sm tombol-hapus">🗑 Hapus</a>
                    </td>
                </tr>
                <?php } } else { ?>
                <tr><td colspan="13" class="text-center">Data tidak ditemukan</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <nav class="mt-3"><ul class="pagination flex-wrap">
        <?php if($page > 1){ ?><li class="page-item"><a class="page-link" href="?cari=<?= urlencode($cari); ?>&page=<?= $page-1; ?>">Previous</a></li><?php } ?>
        <?php for($i=1; $i<=$total_halaman; $i++){ if($i == 1 || $i == $total_halaman || ($i >= $page-2 && $i <= $page+2)){ ?>
            <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>"><a class="page-link" href="?cari=<?= urlencode($cari); ?>&page=<?= $i; ?>"><?= $i; ?></a></li>
        <?php } } ?>
        <?php if($page < $total_halaman){ ?><li class="page-item"><a class="page-link" href="?cari=<?= urlencode($cari); ?>&page=<?= $page+1; ?>">Next</a></li><?php } ?>
    </ul></nav>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelectorAll('.tombol-hapus').forEach(function(btn){
    btn.addEventListener('click', function(e){
        e.preventDefault();
        let url = this.getAttribute('href');
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: 'Data yang sudah dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if(result.isConfirmed){
                window.location.href = url;
            }
        });
    });
});
</script>
</body>
</html>