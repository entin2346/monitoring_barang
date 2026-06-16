<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

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
    <title>I-CALM | Database Berita Acara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-base: #f1f5f9;            
            --bg-body: #f8fafc;
            --bg-card: rgba(255, 255, 255, 0.75); 
            --primary-brand: #0284c7;       
            --accent-blue: #3b82f6;         
            --text-main: #0f172a;           
            --text-muted: #64748b;          
            --border-glass: rgba(255, 255, 255, 0.8);
            --border-light: rgba(148, 163, 184, 0.12);
            --sidebar-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 40%, #0f2d59 100%);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary-brand); }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body { 
            background: radial-gradient(circle at top right, #eff6ff 0%, #f1f5f9 50%, #f8fafc 100%);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ========================================================
            SIDEBAR OCEAN BLUE PREMIUM
        ========================================================= */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 270px;
            height: 100%;
            background: var(--sidebar-gradient);
            box-shadow: 4px 0 25px rgba(15, 23, 42, 0.15);
            padding-top: 30px;
            z-index: 1050;
            transition: all 0.3s;
        }
        
        .sidebar h3 { 
            font-size: 1.25rem; 
            font-weight: 800; 
            padding: 0 24px; 
            margin-bottom: 35px; 
            color: #ffffff;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
        }
        .sidebar h3 i { color: #38bdf8 !important; text-shadow: 0 0 15px rgba(56, 189, 248, 0.5); }
        
        .sidebar a, .dropdown-btn { 
            display: flex; align-items: center; justify-content: space-between; color: #94a3b8; 
            text-decoration: none; padding: 12px 24px; font-size: 0.9rem; font-weight: 600; border: none; background: none; width: 100%; transition: all 0.2s ease; cursor: pointer;
        }
        .sidebar a:hover, .dropdown-btn:hover { color: #ffffff; background: rgba(255, 255, 255, 0.04); }

        .sidebar .active-menu {
            color: #ffffff !important; 
            background: linear-gradient(90deg, rgba(56, 189, 248, 0.15) 0%, rgba(56, 189, 248, 0.01) 100%) !important; 
            border-left: 4px solid #38bdf8; padding-left: 20px;
        }
        .sidebar .active-menu i { color: #38bdf8 !important; }
        .sidebar a i, .dropdown-btn i { margin-right: 12px; font-size: 1rem; width: 20px; text-align: center; color: #64748b; transition: color 0.2s; }
        .sidebar a:hover i, .dropdown-btn:hover i { color: #38bdf8; }
        .sidebar .menu-text { flex-grow: 1; }
        .dropdown-chevron { font-size: 0.75rem !important; transition: transform 0.2s ease; color: #64748b !important; }
        .dropdown-btn.active .dropdown-chevron { transform: rotate(180deg); color: #38bdf8 !important; }

        .dropdown-container { display: none; background: rgba(0, 0, 0, 0.2); padding: 4px 0; }
        .dropdown-container a { padding: 10px 24px 10px 56px; font-size: 0.85rem; font-weight: 500; color: #94a3b8; }
        .dropdown-container a:hover, .dropdown-container a.active-menu { color: #38bdf8 !important; }

        .sidebar .logout-button {
            position: absolute; bottom: 20px; left: 16px;
            background: rgba(239, 68, 68, 0.08); border-radius: 12px; width: calc(100% - 32px); padding: 12px 16px;
        }
        .sidebar .logout-button:hover { background: rgba(239, 68, 68, 0.18) !important; }
        .sidebar .logout-button i, .sidebar .logout-button .menu-text { color: #fca5a5 !important; }

        /* ========================================================
            LAYOUT CONTENT
        ========================================================= */
        .content { margin-left: 270px; min-height: 100vh; transition: all 0.3s; }
        
        .navbar-cyber {
            background: rgba(248, 250, 252, 0.7); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            padding: 18px 40px; border-bottom: 1px solid rgba(226, 232, 240, 0.8); position: sticky; top: 0; z-index: 999;
        }

        .main-body-wrapper { padding: 40px; }

        /* PREMIUM CARDS STATS */
        .glass-stat-link { text-decoration: none; display: block; }
        .glass-stat-card {
            background: var(--bg-card); border: 1px solid var(--border-glass); backdrop-filter: blur(10px);
            border-radius: 20px; padding: 24px; box-shadow: 0 10px 30px -10px rgba(148, 163, 184, 0.12);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden;
        }
        .glass-stat-card::before {
            content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; transition: width 0.2s ease;
        }
        .glass-stat-link:hover .glass-stat-card { transform: translateY(-5px); box-shadow: 0 20px 40px -15px rgba(148, 163, 184, 0.25); }
        
        .card-masuk::before { background: #10b981; }
        .card-keluar::before { background: #ef4444; }
        .card-stok::before { background: #0284c7; }
        .card-arsip::before { background: #f59e0b; }

        .stat-icon-box {
            position: absolute; right: 20px; top: 20px; width: 45px; height: 45px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; font-size: 1.2rem; opacity: 0.8;
        }
        .card-masuk .stat-icon-box { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .card-keluar .stat-icon-box { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .card-stok .stat-icon-box { background: rgba(2, 132, 199, 0.1); color: #0284c7; }
        .card-arsip .stat-icon-box { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }

        .stat-label { font-size: 0.78rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; letter-spacing: 0.8px; margin-bottom: 8px; }
        .stat-number { font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin: 0; line-height: 1; }

        /* SEARCH CONTAINER */
        .cyber-search-box {
            background: var(--bg-card); border: 1px solid var(--border-glass); backdrop-filter: blur(10px);
            border-radius: 20px; padding: 20px; box-shadow: 0 10px 30px -10px rgba(148, 163, 184, 0.08);
        }
        .search-input-wrapper { position: relative; flex-grow: 1; }
        .search-input-wrapper i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.95rem; }
        .form-control-cyber {
            background: rgba(255, 255, 255, 0.8) !important; border: 1px solid rgba(148, 163, 184, 0.25) !important;
            border-radius: 14px !important; padding: 14px 16px 14px 50px; font-weight: 500; font-size: 0.92rem; color: var(--text-main); transition: all 0.2s;
        }
        .form-control-cyber:focus { box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.1) !important; border-color: var(--primary-brand) !important; background: #fff !important; }

        /* REVOLUTIONARY CLEAN DATA TABLE */
        .cyber-table-wrapper {
            background: rgba(255, 255, 255, 0.7) !important; border: 1px solid var(--border-glass);
            backdrop-filter: blur(15px); border-radius: 24px; overflow: hidden;
            box-shadow: 0 20px 40px -20px rgba(148, 163, 184, 0.15);
            width: 100%; overflow-x: auto !important;
        }
        
        .table-cyber-clean { width: 100%; margin-bottom: 0; border-collapse: separate; border-spacing: 0; min-width: 1700px; }
        
        .table-cyber-clean thead th {
            background-color: rgba(241, 245, 249, 0.6) !important; color: var(--text-muted) !important;
            font-weight: 700; padding: 18px 20px; font-size: 0.72rem; text-transform: uppercase;
            letter-spacing: 1px; border-bottom: 1px solid rgba(148, 163, 184, 0.15) !important; white-space: nowrap;
        }
        
        .table-cyber-clean tbody tr { transition: background 0.2s; }
        .table-cyber-clean tbody tr:hover { background-color: rgba(248, 250, 252, 0.8) !important; }
        .table-cyber-clean tbody td { padding: 16px 20px !important; font-size: 0.85rem; vertical-align: middle; color: var(--text-main) !important; white-space: nowrap; border-bottom: 1px solid rgba(148, 163, 184, 0.08); background: transparent !important; }

        .table-cyber-clean th.max-col-width, .table-cyber-clean td.max-col-width { white-space: normal !important; word-break: break-word; min-width: 260px !important; max-width: 340px !important; }

        /* ACTION PILLS BUTTONS */
        .btn-action-group-cyber {
            display: inline-flex; background-color: #ffffff; border: 1px solid rgba(148, 163, 184, 0.15); border-radius: 12px; padding: 4px; gap: 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        }
        .btn-action-item-cyber { width: 32px; height: 32px; border-radius: 8px; color: var(--text-muted); font-size: 0.9rem; text-decoration: none; transition: all 0.2s; background: none; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .btn-action-item-cyber.btn-view:hover { color: var(--primary-brand); background: rgba(2, 132, 199, 0.08); }
        .btn-action-item-cyber.btn-edit:hover { color: #d97706; background: rgba(217, 119, 6, 0.08); }
        .btn-action-item-cyber.btn-delete:hover { color: #ef4444; background: rgba(239, 68, 68, 0.08); }

        /* FRESH MODERN VIBRANT BADGES */
        .badge-premium { display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 0.72rem; letter-spacing: 0.3px; }
        .badge-masuk { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.15); }
        .badge-keluar { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.15); }
        .badge-return { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.15); }

        /* PAGINATION PREMIUM */
        .page-link-cyber { background: rgba(255,255,255,0.7) !important; color: var(--text-main) !important; border: 1px solid rgba(148, 163, 184, 0.2) !important; padding: 10px 18px; border-radius: 10px; font-weight: 600; font-size: 0.88rem; margin: 0 3px; transition: all 0.2s; }
        .page-link-cyber:hover { background: #ffffff !important; color: var(--primary-brand) !important; border-color: rgba(2, 132, 199, 0.3) !important; }
        .page-item.active .page-link-cyber { background: var(--primary-brand) !important; color: white !important; border-color: var(--primary-brand) !important; box-shadow: 0 8px 20px -6px rgba(2,132,199,0.4); }
        
        .border-radius-20 { border-radius: 20px !important; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fa-solid fa-bolt me-2"></i>I-CALM Panel</h3>

    <a href="../dashboard/index.php">
        <span><i class="fa-solid fa-chart-pie me-2"></i><span class="menu-text">Dashboard</span></span>
    </a>

    <button class="dropdown-btn active">
        <span><i class="fa-solid fa-layer-group"></i><span class="menu-text">Monitoring</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container" style="display: block;">
        <a href="../material/index.php">Material Gudang</a>
        <a href="../ba/index.php" class="active-menu">Database BA</a>
    </div>

    <button class="dropdown-btn">
        <span><i class="fa-solid fa-file-import"></i><span class="menu-text">Import</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../import/material.php">Import Material</a>
        <a href="../import/ba.php">Import BA</a>
    </div>

    <button class="dropdown-btn">
        <span><i class="fa-solid fa-file-export"></i><span class="menu-text">Export</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../export/material_excel.php">Export Material</a>
        <a href="../export/ba_excel.php">Export BA</a>
    </div>

    <a href="../login/logout.php" class="logout-button">
        <span><i class="fa-solid fa-right-from-bracket"></i><span class="menu-text">Logout</span></span>
    </a>
</div>

<div class="content">

    <nav class="navbar navbar-expand-lg navbar-cyber">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center" style="color: var(--text-main); font-weight: 800; font-size: 1.35rem; letter-spacing: -0.3px;">
                <i class="fa-solid fa-folder-open text-primary opacity-75 me-2"></i> DATABASE BERITA ACARA
            </span>
            <div>
                <a href="tambah.php" class="btn btn-primary btn-sm fw-bold px-3 py-2 me-2" style="border-radius: 12px; background: var(--primary-brand); border: none; font-size:0.85rem; box-shadow:0 8px 16px -4px rgba(2,132,199,0.25);">
                    <i class="fa-solid fa-plus me-1"></i> Tambah Data
                </a>
                <a href="../export/ba_excel.php" class="btn btn-success btn-sm fw-bold px-3 py-2" style="border-radius: 12px; border: none; background: #10b981; font-size:0.85rem; box-shadow:0 8px 16px -4px rgba(16,185,129,0.25);">
                    <i class="fa-solid fa-file-excel me-1"></i> Export Excel
                </a>
            </div>
        </div>
    </nav>

    <div class="main-body-wrapper">

        <div class="mb-4">
            <h2 style="color: var(--text-main); font-weight: 800; font-size: 1.65rem; letter-spacing: -0.3px;">Dashboard Monitoring BA</h2>
            <p class="text-muted m-0" style="font-size: 0.88rem; font-weight: 500;">Sistem mutasi logistik & arsip berita acara aktif secara real-time.</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <a href="barang_masuk.php" class="glass-stat-link">
                    <div class="glass-stat-card card-masuk">
                        <div class="stat-icon-box"><i class="fa-solid fa-arrow-down-long"></i></div>
                        <div class="stat-label">Total Barang Masuk</div>
                        <div class="stat-number" style="color: #10b981;"><?= number_format($barang_masuk['total'] ?? 0); ?></div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="barang_keluar.php" class="glass-stat-link">
                    <div class="glass-stat-card card-keluar">
                        <div class="stat-icon-box"><i class="fa-solid fa-arrow-up-long"></i></div>
                        <div class="stat-label">Total Barang Keluar</div>
                        <div class="stat-number" style="color: #ef4444;"><?= number_format($barang_keluar['total'] ?? 0); ?></div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="stok_barang.php" class="glass-stat-link">
                    <div class="glass-stat-card card-stok">
                        <div class="stat-icon-box"><i class="fa-solid fa-box"></i></div>
                        <div class="stat-label">Sisa Ketersediaan Stok</div>
                        <div class="stat-number" style="color: #0284c7;"><?= number_format($stok); ?></div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <div class="glass-stat-card card-arsip">
                    <div class="stat-icon-box"><i class="fa-solid fa-receipt"></i></div>
                    <div class="stat-label">Total Arsip Berkas BA</div>
                    <div class="stat-number" style="color: #f59e0b;"><?= number_format($total_ba['total']); ?></div>
                </div>
            </div>
        </div>

        <div class="cyber-search-box mb-4">
            <form method="GET">
                <div class="d-flex gap-2">
                    <div class="search-input-wrapper">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="cari" class="form-control form-control-cyber" placeholder="Ketik kata kunci nama komponen atau material yang dicari..." value="<?= htmlspecialchars($cari); ?>">
                    </div>
                    <button type="submit" class="btn btn-dark fw-bold px-4" style="background: #0f172a; border:none; border-radius:14px; font-size:0.9rem; transition: background 0.2s;">
                        Cari Komponen
                    </button>
                </div>
            </form>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 px-1">
            <span class="text-muted fw-semibold" style="font-size:0.85rem;">Total Entri Data Ditemukan: <strong class="text-primary" style="font-weight:700;"><?= number_format($total_data); ?></strong> baris log</span>
        </div>

        <div class="cyber-table-wrapper mb-4">
            <table class="table table-cyber-clean">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 70px;">NO</th>
                        <th>TANGGAL RECORD</th>
                        <th class="max-col-width">NAMA MATERIAL</th>
                        <th>MERK/JENIS</th>
                        <th>JENIS MATERIAL</th>
                        <th class="max-col-width">SUMBER MATERIAL</th>
                        <th>SATUAN</th>
                        <th>JUMLAH</th>
                        <th>NOMOR SERI</th>
                        <th>PEMASOK/VENDOR</th>
                        <th class="text-center">KATEGORI BA</th>
                        <th>KETERANGAN</th>
                        <th class="text-center" style="width: 140px;">AKSI</th>
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
                        <td class="text-center fw-bold" style="color: var(--text-muted) !important;"><?= $no++; ?></td>
                        <td class="fw-semibold" style="color: var(--text-muted) !important;"><?= $tanggal; ?></td>
                        
                        <td class="max-col-width">
                            <a href="detail.php?id=<?= $d['id']; ?>" style="text-decoration:none; font-weight:700; color: var(--primary-brand) !important;">
                                <?= htmlspecialchars($d['nama_barang']); ?>
                            </a>
                        </td>
                        
                        <td class="fw-medium"><?= htmlspecialchars($d['merk_jenis'] ?: '-'); ?></td>
                        <td class="fw-medium"><?= htmlspecialchars($d['jenis_barang'] ?: '-'); ?></td>
                        <td class="max-col-width fw-medium" style="color: var(--text-muted) !important;"><?= htmlspecialchars($d['sumber_barang'] ?: '-'); ?></td>
                        <td class="fw-bold" style="color: var(--text-muted) !important;"><?= htmlspecialchars($d['satuan'] ?: '-'); ?></td>
                        <td class="fw-extrabold text-dark"><?= number_format($d['jumlah']); ?></td>
                        <td class="fw-bold" style="color: #0284c7 !important; font-family: monospace; font-size: 0.9rem; letter-spacing: 0.5px;"><?= htmlspecialchars($d['no_seri'] ?: '-'); ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($d['asal_barang_vendor'] ?: '-'); ?></td>
                        
                        <td class="text-center">
                            <?php
                            $kategori = strtoupper($d['jenis_berita_acara']);
                            if(strpos($kategori,'MASUK') !== false){ echo "<span class='badge-premium badge-masuk'><i class='fa-solid fa-circle-down me-1.5'></i>MASUK</span>"; }
                            elseif(strpos($kategori,'KELUAR') !== false || strpos($kategori,'TERPAKAI') !== false){ echo "<span class='badge-premium badge-keluar'><i class='fa-solid fa-circle-up me-1.5'></i>KELUAR</span>"; }
                            elseif(strpos($kategori,'RETURN') !== false || strpos($kategori,'PENGEMBALIAN') !== false){ echo "<span class='badge-premium badge-return'><i class='fa-solid fa-rotate-left me-1.5'></i>RETURN</span>"; }
                            else{ echo "<span class='badge bg-secondary text-uppercase py-1.5 px-2.5 rounded-3'>".$kategori."</span>"; }
                            ?>
                        </td>
                        
                        <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text-muted);" title="<?= htmlspecialchars($d['keterangan']); ?>">
                            <?= htmlspecialchars($d['keterangan'] ?: '-'); ?>
                        </td>
                        
                        <td class="text-center">
                            <div class="btn-action-group-cyber">
                                <a href="detail.php?id=<?= $d['id']; ?>" class="btn-action-item-cyber btn-view" title="Detail">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                                <a href="edit.php?id=<?= $d['id']; ?>" class="btn-action-item-cyber btn-edit" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <a href="hapus.php?id=<?= $d['id']; ?>" class="btn-action-item-cyber btn-delete tombol-hapus" title="Hapus">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php } } else { ?>
                    <tr>
                        <td colspan="13" class="text-center py-5 fw-bold" style="color: var(--text-muted) !important;">
                            <i class="fa-solid fa-box-open d-block fs-1 mb-3 opacity-25"></i>Berkas registrasi Berita Acara tidak ditemukan
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php if($total_halaman > 1) { ?>
        <nav class="pt-2 pb-4">
            <ul class="pagination justify-content-center flex-wrap">
                <?php if($page > 1){ ?>
                    <li class="page-item"><a class="page-link page-link-cyber" href="?cari=<?= urlencode($cari); ?>&page=<?= $page-1; ?>"><i class="fa-solid fa-angle-left"></i></a></li>
                <?php } ?>
                
                <?php for($i=1; $i<=$total_halaman; $i++){ if($i == 1 || $i == $total_halaman || ($i >= $page-2 && $i <= $page+2)){ ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>"><a class="page-link page-link-cyber" href="?cari=<?= urlencode($cari); ?>&page=<?= $i; ?>"><?= $i; ?></a></li>
                <?php } } ?>
                
                <?php if($page < $total_halaman){ ?>
                    <li class="page-item"><a class="page-link page-link-cyber" href="?cari=<?= urlencode($cari); ?>&page=<?= $page+1; ?>"><i class="fa-solid fa-angle-right"></i></a></li>
                <?php } ?>
            </ul>
        </nav>
        <?php } ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Dropdown Sidebar Manager
    var dropdown = document.getElementsByClassName("dropdown-btn");
    for (var i = 0; i < dropdown.length; i++) {
        dropdown[i].addEventListener("click", function() {
            this.classList.toggle("active");
            var dropdownContent = this.nextElementSibling;
            if (dropdownContent.style.display === "block") {
                dropdownContent.style.display = "none";
            } else {
                dropdownContent.style.display = "block";
            }
        });
    }

    // Interceptor Hapus Data SweetAlert2 Custom Premium Style
    document.querySelectorAll('.tombol-hapus').forEach(function(btn){
        btn.addEventListener('click', function(e){
            e.preventDefault();
            let url = this.getAttribute('href');
            Swal.fire({
                title: 'Konfirmasi Penghapusan?',
                text: 'Data mutasi rekaman BA ini akan dihapus permanen dari sistem log!',
                icon: 'warning',
                showCancelButton: true,
                background: '#ffffff',
                color: '#0f172a',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus Data',
                cancelButtonText: 'Batalkan',
                customClass: {
                    popup: 'border-radius-20'
                }
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