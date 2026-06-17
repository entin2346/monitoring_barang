<?php
session_start();
if(!isset($_SESSION['login'])){ header("Location: ../login/index.php"); exit; }
include "../config/koneksi.php";

if(!isset($_GET['id'])){ echo "ID tidak ditemukan"; exit; }
$id = mysqli_real_escape_string($conn, $_GET['id']);
$data = mysqli_query($conn,"SELECT * FROM database_ba WHERE id='$id'");
$d = mysqli_fetch_assoc($data);
if(!$d){ echo "Data tidak ditemukan"; exit; }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail BA | I-CALM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-base: #e6eef8; --bg-card: rgba(255, 255, 255, 0.7); 
            --primary-brand: #0284c7; --text-main: #0f172a; --text-muted: #475569;
            --border-glass: rgba(255, 255, 255, 0.8);
        }
        body { background: radial-gradient(circle at top right, #dbeafe 0%, var(--bg-base) 50%, #eff6ff 100%); min-height: 100vh; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* Sidebar Ocean Blue Premium */
        .sidebar { position: fixed; left: 0; top: 0; width: 260px; height: 100%; background: linear-gradient(135deg, rgba(11, 27, 60, 0.98) 0%, rgba(7, 43, 102, 0.96) 60%, rgba(2, 110, 168, 0.95) 100%); backdrop-filter: blur(25px); z-index: 1000; padding-top: 28px; }
        .sidebar h3 { font-size: 1.35rem; font-weight: 800; padding: 0 24px; margin-bottom: 35px; color: #ffffff; display: flex; align-items: center; gap: 10px; }
        .sidebar a, .dropdown-btn { display: flex; align-items: center; color: rgba(255, 255, 255, 0.65); text-decoration: none; padding: 13px 24px; font-size: 0.9rem; font-weight: 600; border: none; background: none; width: 100%; }
        .sidebar .active-menu { color: #ffffff !important; background: linear-gradient(90deg, rgba(56, 189, 248, 0.15) 0%, rgba(56, 189, 248, 0.02) 100%) !important; border-left: 4px solid #38bdf8; }
        .sidebar .logout-button { margin-top: 40px; background: rgba(239, 68, 68, 0.08); border-radius: 12px; width: calc(100% - 32px); margin-left: 16px; padding: 12px 16px; color: #fca5a5 !important; }

        .content { margin-left: 260px; }
        .navbar-custom { background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(20px); padding: 18px 32px; border-bottom: 1px solid var(--border-glass); }
        .main-body { padding: 35px 32px; }
        
        /* Glass Table */
        .glass-card { background: var(--bg-card); backdrop-filter: blur(20px); border: 1px solid var(--border-glass); border-radius: 24px; padding: 30px; box-shadow: 0 15px 35px rgba(148,163,184,0.1); }
        .table-detail th { width: 30%; color: var(--text-muted); font-weight: 700; padding: 15px 20px; border-bottom: 1px solid rgba(0,0,0,0.05); }
        .table-detail td { color: var(--text-main); font-weight: 500; padding: 15px 20px; border-bottom: 1px solid rgba(0,0,0,0.05); }
        
        .badge-kategori-masuk { background: rgba(34, 197, 94, 0.15); color: #166534; padding: 4px 12px; border-radius: 8px; font-weight: 700; }
        .badge-kategori-keluar { background: rgba(239, 68, 68, 0.15); color: #991b1b; padding: 4px 12px; border-radius: 8px; font-weight: 700; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>⚡ I-CALM Panel</h3>
    <a href="../dashboard/index.php">📊 Dashboard</a>
    <a href="../ba/index.php" class="active-menu">🗂️ Database BA</a>
    <a href="../login/logout.php" class="logout-button">🚪 Logout</a>
</div>

<div class="content">
    <nav class="navbar navbar-custom">
        <span class="navbar-brand fw-bold">📄 DETAIL BERITA ACARA</span>
    </nav>

    <div class="main-body">
        <div class="glass-card">
            <table class="table table-borderless table-detail">
                <tr><th>Jenis Berita Acara</th><td>
                    <?php 
                    $k = strtoupper($d['jenis_berita_acara']);
                    if(strpos($k,'MASUK') !== false) echo "<span class='badge-kategori-masuk'>MASUK</span>";
                    elseif(strpos($k,'KELUAR') !== false) echo "<span class='badge-kategori-keluar'>KELUAR</span>";
                    else echo $d['jenis_berita_acara'];
                    ?>
                </td></tr>
                <tr><th>Tanggal</th><td><?= date('d-m-Y', strtotime($d['tanggal'])); ?></td></tr>
                <tr><th>Nama Barang</th><td class="fw-bold text-primary"><?= $d['nama_barang']; ?></td></tr>
                <tr><th>Merk / Jenis</th><td><?= $d['merk_jenis']; ?></td></tr>
                <tr><th>Jenis Barang</th><td><?= $d['jenis_barang']; ?></td></tr>
                <tr><th>Sumber Barang</th><td><?= $d['sumber_barang']; ?></td></tr>
                <tr><th>Satuan</th><td><?= $d['satuan']; ?></td></tr>
                <tr><th>Jumlah</th><td class="fs-5 fw-bold"><?= number_format($d['jumlah']); ?></td></tr>
                <tr><th>Tujuan</th><td><?= $d['tujuan']; ?></td></tr>
                <tr><th>Kondisi</th><td><?= $d['kondisi_material']; ?></td></tr>
                <tr><th>No Seri</th><td><?= $d['no_seri']; ?></td></tr>
                <tr><th>Vendor</th><td><?= $d['asal_barang_vendor']; ?></td></tr>
                <tr><th>Keterangan</th><td><?= $d['keterangan']; ?></td></tr>
                <tr><th>File BA</th><td>
                    <?php if(!empty($d['file_ba'])): ?>
                        <a href="../uploads/<?= urlencode($d['file_ba']); ?>" target="_blank" class="btn btn-sm btn-outline-dark">👁️ Lihat File</a>
                    <?php else: echo "Tidak ada"; endif; ?>
                </td></tr>
            </table>
            
            <div class="mt-4 pt-3 border-top">
                <a href="index.php" class="btn btn-secondary px-4">⬅️ Kembali</a>
                <a href="edit.php?id=<?= $d['id']; ?>" class="btn btn-warning px-4 ms-2">✏️ Edit Data</a>
                <a href="kartu_gantung.php?id=<?= $d['id']; ?>" target="_blank" class="btn btn-success px-4 ms-2">🏷️ Cetak Kartu Gantung</a>
                
                <a href="cetak_tug5.php?id=<?= $d['id']; ?>" target="_blank" class="btn btn-primary px-4 ms-2">
                    🖨️ Cetak Form TUG 5
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>