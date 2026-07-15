<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

/* ==========================================================================
   RINGKASAN DATA BA 
   ========================================================================== */
$barang_masuk_q = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM database_ba WHERE UPPER(jenis_berita_acara)='MASUK'");
$barang_masuk = mysqli_fetch_assoc($barang_masuk_q);

$barang_keluar_q = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM database_ba WHERE UPPER(jenis_berita_acara)='KELUAR'");
$barang_keluar = mysqli_fetch_assoc($barang_keluar_q);

$total_ba_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM database_ba WHERE nama_barang <> ''");
$total_ba = mysqli_fetch_assoc($total_ba_q);

// PERBAIKAN: Menggunakan abs() agar hasil minus otomatis dibalik menjadi angka nominal positif
$stok_hitung = ($barang_masuk['total'] ?? 0) - ($barang_keluar['total'] ?? 0);
$stok = abs($stok_hitung); 

/* ==========================================================================
   PENCARIAN SECURE ENGINE & PAGINATION
   ========================================================================== */
$cari = $_GET['cari'] ?? '';
$cari_pencarian = urldecode($cari); 
$cari_clean = trim($cari_pencarian);

$limit = 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1){ $page = 1; }
$offset = ($page - 1) * $limit;

if ($cari_clean !== '') {
    $param_cari = '%' . $cari_clean . '%';

    $stmt_total = $conn->prepare("SELECT COUNT(*) as total FROM database_ba WHERE nama_barang IS NOT NULL AND nama_barang <> '' AND nama_barang LIKE ?");
    $stmt_total->bind_param("s", $param_cari);
    $stmt_total->execute();
    $total_data = $stmt_total->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt_total->close();

    $stmt_data = $conn->prepare("SELECT * FROM database_ba WHERE nama_barang IS NOT NULL AND nama_barang <> '' AND nama_barang LIKE ? ORDER BY tanggal DESC, id DESC LIMIT ?, ?");
    $stmt_data->bind_param("sii", $param_cari, $offset, $limit);
    $stmt_data->execute();
    $query = $stmt_data->get_result();
    $stmt_data->close();
} else {
    $total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM database_ba WHERE nama_barang IS NOT NULL AND nama_barang <> ''");
    $total_data = mysqli_fetch_assoc($total_query)['total'] ?? 0;

    $query = mysqli_query($conn, "SELECT * FROM database_ba WHERE nama_barang IS NOT NULL AND nama_barang <> '' ORDER BY tanggal DESC, id DESC LIMIT $offset, $limit");
}

$total_halaman = ceil($total_data / $limit);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I-CALM | Database Berita Acara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        :root {
            --bg-body: #f4f7fc;
            --bg-card: #ffffff; 
            --primary: #0284c7;       
            --text-main: #0f172a;           
            --text-muted: #64748b;          
            --border-color: rgba(148, 163, 184, 0.12);
            --bg-sidebar: #d0e1f9; 
        }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary); }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }

        body { 
            background: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .sidebar {
            position: fixed; left: 0; top: 0; width: 260px; height: 100%;
            background-color: var(--bg-sidebar);
            border-right: 1px solid rgba(2, 132, 199, 0.15);
            padding: 35px 20px; z-index: 1050; display: flex; flex-direction: column;
        }
        .sidebar h3 { 
            font-size: 1.25rem; font-weight: 800; color: #1e3a8a; 
            margin-bottom: 35px; padding-left: 6px; display: flex; align-items: center; gap: 10px;
        }
        
        .sidebar a, .dropdown-btn { 
            display: flex; align-items: center; justify-content: space-between; 
            color: #1e3a8a; text-decoration: none; padding: 11px 14px; 
            font-size: 0.9rem; font-weight: 700; border: none; background: transparent; 
            width: 100%; cursor: pointer; border-radius: 10px; margin-bottom: 5px; 
            transition: all 0.2s ease-in-out;
        }
        
        .sidebar a:hover, .dropdown-btn:hover { 
            color: #025a9c; 
            background: rgba(2, 132, 199, 0.12); 
            transform: translateX(4px);
        }
        
        .sidebar .menu-content-wrapper { display: flex; align-items: center; gap: 12px; }
        .sidebar a i, .dropdown-btn i.menu-icon { font-size: 1.05rem; width: 20px; text-align: center; color: #1e40af; }
        .sidebar a:hover i, .dropdown-btn:hover i.menu-icon { color: #025a9c; }
        
        .sidebar .active-menu {
            color: #ffffff !important; 
            background: #0284c7 !important; 
            font-weight: 700;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25);
            border-radius: 10px;
            transform: translateX(4px);
        }
        .sidebar .active-menu i { color: #ffffff !important; }

        .dropdown-chevron { font-size: 0.75rem !important; transition: transform 0.2s ease; color: #1e40af !important; }
        .dropdown-btn.active .dropdown-chevron { transform: rotate(180deg); color: #ffffff !important; }
        .dropdown-btn.active { color: #ffffff !important; background: #0284c7 !important; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); }
        .dropdown-btn.active i.menu-icon { color: #ffffff !important; }
        
        .dropdown-container { display: none; padding-left: 12px; margin-bottom: 6px; margin-top: 4px; }
        .dropdown-container a { 
            padding: 9px 14px; font-size: 0.85rem; color: #1e40af; font-weight: 600; background: rgba(255, 255, 255, 0.3);
        }
        .dropdown-container a:hover { background: #ffffff; color: #0284c7; }

        .sidebar .logout-button { 
            margin-top: auto; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; 
        }
        .sidebar .logout-button i, .sidebar .logout-button span { color: #b91c1c !important; }
        .sidebar .logout-button:hover { background: #fee2e2; transform: none; }

        .content { margin-left: 260px; position: relative; }
        .navbar-cyber { background: #ffffff; padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999; }
        .main-body-wrapper { padding: 40px; }

        .glass-stat-link { text-decoration: none; display: block; }
        .glass-stat-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); position: relative; overflow: hidden; transition: all 0.25s ease; }
        .glass-stat-link:hover .glass-stat-card { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.06); }
        
        .stat-icon-box { position: absolute; right: 20px; top: 24px; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
        
        .card-masuk { border-top: 4px solid #10b981; }
        .card-masuk .stat-icon-box { background: rgba(16, 185, 129, 0.08); color: #10b981; }
        
        .card-keluar { border-top: 4px solid #ef4444; }
        .card-keluar .stat-icon-box { background: rgba(239, 68, 68, 0.08); color: #ef4444; }
        
        .card-stok { border-top: 4px solid var(--primary); }
        .card-stok .stat-icon-box { background: rgba(2, 132, 199, 0.08); color: var(--primary); }
        
        .card-arsip { border-top: 4px solid #f59e0b; }
        .card-arsip .stat-icon-box { background: rgba(245, 158, 11, 0.08); color: #f59e0b; }
        
        .stat-label { font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; margin-bottom: 8px; letter-spacing: 0.3px; }
        .stat-number { font-size: 1.7rem; font-weight: 800; color: #0f172a; }

        .cyber-search-box { background: #ffffff; border: 1px solid var(--border-color); border-radius: 16px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.01); }
        .search-input-wrapper { position: relative; flex-grow: 1; }
        .search-input-wrapper i.fa-magnifying-glass { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
        .search-spinner { display: none; position: absolute; right: 45px; top: 50%; transform: translateY(-50%); color: var(--primary); z-index: 5; }
        
        .btn-clear-search { position: absolute; right: 18px; top: 50%; transform: translateY(-50%); border: none; background: transparent; color: #94a3b8; cursor: pointer; padding: 0; display: flex; align-items: center; justify-content: center; font-size: 1rem; z-index: 6; transition: color 0.2s; }
        .btn-clear-search:hover { color: #ef4444; }

        .form-control-cyber { background: #f8fafc !important; border: 1px solid #cbd5e1 !important; border-radius: 12px !important; padding: 14px 45px 14px 50px; font-weight: 500; font-size: 0.92rem; color: #0f172a; }
        .form-control-cyber:focus { box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.1) !important; border-color: var(--primary) !important; background: #fff !important; }

        .autocomplete-suggestions {
            position: absolute; top: 100%; left: 0; right: 0; background: #ffffff;
            border: 1px solid #cbd5e1; border-radius: 12px; margin-top: 4px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); max-height: 300px; overflow-y: auto;
            display: none; padding: 6px 0; z-index: 99999 !important;
        }
        .autocomplete-suggestion-item { padding: 12px 20px; cursor: pointer; font-size: 0.9rem; font-weight: 600; color: #334155; transition: background 0.1s; }
        .autocomplete-suggestion-item:hover { background-color: #f1f5f9; color: var(--primary); }

        .cyber-table-wrapper { background: #ffffff; border: 1px solid var(--border-color); border-radius: 16px; width: 100%; overflow-x: auto !important; box-shadow: 0 4px 16px rgba(0,0,0,0.02); }
        .table-cyber-clean { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 2000px; }
        .table-cyber-clean thead th { background-color: #f8fafc !important; color: var(--text-muted) !important; font-weight: 700; padding: 16px 20px; font-size: 0.72rem; text-transform: uppercase; border-bottom: 1px solid var(--border-color) !important; }
        .table-cyber-clean tbody tr:hover { background-color: #f8fafc !important; }
        .table-cyber-clean tbody td { padding: 15px 20px !important; font-size: 0.88rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        .table-cyber-clean th.max-col-width, .table-cyber-clean td.max-col-width { white-space: normal !important; word-break: break-word; min-width: 250px !important; max-width: 330px !important; }

        .btn-action-group-cyber { display: inline-flex; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 3px; gap: 2px; }
        .btn-action-item-cyber { width: 32px; height: 32px; border-radius: 7px; color: var(--text-muted); display: flex; align-items: center; justify-content: center; text-decoration: none; border: none; background: none; cursor: pointer; }
        .btn-action-item-cyber.btn-view:hover { color: var(--primary); background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .btn-action-item-cyber.btn-edit:hover { color: #d97706; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .btn-action-item-cyber.btn-delete:hover { color: #ef4444; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }

        .badge-premium { display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 0.72rem; }
        .badge-masuk { background: rgba(16, 185, 129, 0.08); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.15); }
        .badge-keluar { background: rgba(239, 68, 68, 0.08); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.15); }
        .badge-return { background: rgba(245, 158, 11, 0.08); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.15); }

        .page-link-cyber { background: #fff !important; color: #0f172a !important; border: 1px solid #e2e8f0 !important; padding: 10px 16px; border-radius: 10px; margin: 0 2px; font-weight: 600; }
        .page-item.active .page-link-cyber { background: var(--primary) !important; color: white !important; border-color: var(--primary) !important; }

        /* MODIFIKASI UNTUK SLIDER NAVIGASI NOMOR */
        .slider-page-container {
            display: inline-flex;
            max-width: 260px; /* Batasi lebar container angka agar muat sekitar 4-5 nomor saja */
            overflow: hidden;
            scroll-behavior: smooth;
            vertical-align: middle;
        }
        .slider-page-wrapper {
            display: inline-flex;
            transition: transform 0.3s ease-in-out;
        }
        .page-item-number {
            flex-shrink: 0;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fa-solid fa-bolt text-primary"></i> I-CALM Panel</h3>
    <a href="../dashboard/index.php">
        <span class="menu-content-wrapper">
            <i class="fa-solid fa-chart-pie"></i>
            <span>Dashboard</span>
        </span>
    </a>
    <button class="dropdown-btn active">
        <span class="menu-content-wrapper">
            <i class="fa-solid fa-layer-group menu-icon"></i>
            <span>Monitoring</span>
        </span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container" style="display: block;">
        <a href="../material/index.php">Material Gudang</a>
        <a href="../ba/index.php" class="active-menu">Database BA</a>
    </div>

    <button class="dropdown-btn">
        <span class="menu-content-wrapper">
            <i class="fa-solid fa-tags menu-icon"></i>
            <span>Kategori</span>
        </span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../kategori/stok/stok.php">Stok</a>
        <a href="../kategori/non_stok/non_stok.php">Non Stok</a>
        <a href="../kategori/non_po/non_po.php">Non PO</a>
        <a href="../kategori/ex_bongkaran/ex_bongkaran.php">Ex Bongkaran</a>
        <a href="../kategori/pre_memory/pre_memory.php">Pre Memory</a>
        <a href="../kategori/peminjaman/peminjaman.php">Peminjaman</a>
        <a href="../kategori/pemakaian/pemakaian.php">Pemakaian</a>
    </div>

    <button class="dropdown-btn">
        <span class="menu-content-wrapper">
            <i class="fa-solid fa-file-import menu-icon"></i>
            <span>Import</span>
        </span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../import/material.php">Import Material</a>
        <a href="../import/ba.php">Import BA</a>
        <a href="../import/form_stok.php">Import Stok</a>
        <a href="../import/form_non_stok.php">Import Non Stok</a>
        <a href="../import/form_non_po.php">Import Non PO</a>
        <a href="../import/form_ex_bongkaran.php">Import Ex Bongkaran</a>
        <a href="../import/form_pre_memory.php">Import Pre Memory</a>
        <a href="../import/form_peminjaman.php">Import Peminjaman</a>
        <a href="../import/form_pemakaian.php">Import Pemakaian</a>
    </div>
    <button class="dropdown-btn">
        <span class="menu-content-wrapper">
            <i class="fa-solid fa-file-export menu-icon"></i>
            <span>Export</span>
        </span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../export/material_excel.php">Export Material</a>
        <a href="../export/ba_excel.php">Export BA</a>
    </div>
    <a href="../login/logout.php" class="logout-button">
        <span class="menu-content-wrapper">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
        </span>
    </a>
</div>

<div class="content">
    <nav class="navbar navbar-expand-lg navbar-cyber">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center" style="color: #0f172a; font-weight: 800; font-size: 1.3rem;">
                <i class="fa-solid fa-folder-open text-primary opacity-75 me-2"></i> DATABASE BERITA ACARA
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
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
                        <div class="stat-number" style="color: var(--primary);"><?= number_format($stok); ?></div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <div class="glass-stat-card card-arsip">
                    <div class="stat-icon-box"><i class="fa-solid fa-receipt"></i></div>
                    <div class="stat-label">Total Arsip Berkas BA</div>
                    <div class="stat-number" style="color: #f59e0b;"><?= number_format($total_ba['total'] ?? 0); ?></div>
                </div>
            </div>
        </div>

        <div class="cyber-search-box mb-4">
            <form method="GET" id="searchForm">
                <div class="d-flex gap-2">
                    <div class="search-input-wrapper">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="cari" id="searchInput" autocomplete="off" class="form-control form-control-cyber" placeholder="Ketik kata kunci nama komponen atau material..." value="<?= htmlspecialchars($cari_clean, ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="fa-solid fa-spinner fa-spin search-spinner" id="searchSpinner"></i>
                        
                        <?php if(!empty($cari_clean)) { ?>
                            <button type="button" class="btn-clear-search" id="btnClearSearch" title="Clear pencarian"><i class="fa-solid fa-circle-xmark"></i></button>
                        <?php } ?>
                        
                        <div id="autocompleteBox" class="autocomplete-suggestions"></div>
                    </div>
                    <button type="submit" class="btn btn-dark fw-bold px-4 text-nowrap" style="background: #0f172a; border:none; border-radius:12px;">
                        Cari Komponen
                    </button>
                    <?php if(strtolower($_SESSION['role']) == 'admin'){ ?>

<a href="tambah.php"
   class="btn btn-primary fw-bold px-4 d-flex align-items-center gap-1 text-nowrap"
   style="border-radius:12px;background:var(--primary);border:none;">
    <i class="fa-solid fa-plus"></i> Tambah Database
</a>

<?php } ?>
                </div>
            </form>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 px-1">
            <span class="text-muted fw-semibold" style="font-size:0.85rem;">Total Entri Data Ditemukan: <strong class="text-primary"><?= number_format($total_data); ?></strong> baris log</span>
            <?php if(!empty($cari_clean)) { ?>
                <span class="small text-muted">Hasil filter kata kunci: <strong class="text-primary">"<?= htmlspecialchars($cari_clean, ENT_QUOTES, 'UTF-8') ?>"</strong></span>
            <?php } ?>
        </div>

        <div class="cyber-table-wrapper mb-4">
            <table class="table table-cyber-clean">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 70px;">NO</th>
                        <th>TANGGAL RECORD</th>
                        <th>TUJUAN</th> 
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
                    if($query && mysqli_num_rows($query) > 0){
                        while($d = mysqli_fetch_assoc($query)){
                            $tanggal = (!empty($d['tanggal']) && $d['tanggal'] != '0000-00-00') ? date('d-m-Y', strtotime($d['tanggal'])) : '-';
                            $kategori = strtoupper($d['jenis_berita_acara'] ?? '');
                    ?>
                    <tr>
                        <td class="text-center fw-bold text-muted"><?= $no++; ?></td>
                        <td class="fw-semibold text-muted"><?= $tanggal; ?></td>
                        
                        <td class="fw-bold text-dark">
                            <?php 
                            if(strpos($kategori, 'MASUK') !== false){
                                echo "-";
                            } else {
                                echo htmlspecialchars(strtoupper($d['tujuan'] ?? '-'), ENT_QUOTES, 'UTF-8'); 
                            }
                            ?>
                        </td> 
                        
                        <td class="max-col-width">
                            <a href="detail.php?id=<?= $d['id']; ?>" style="text-decoration:none; font-weight:700; color: var(--primary) !important;">
                                <?= htmlspecialchars($d['nama_barang'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($d['merk_jenis'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($d['jenis_barang'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="max-col-width text-muted"><?= htmlspecialchars($d['sumber_barang'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="fw-bold text-muted"><?= htmlspecialchars($d['satuan'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="fw-bold text-dark"><?= number_format($d['jumlah'] ?? 0); ?></td>
                        <td class="fw-bold text-primary" style="font-family: monospace; font-size: 0.9rem;"><?= htmlspecialchars($d['no_seri'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($d['asal_barang_vendor'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="text-center">
                            <?php
                            if(strpos($kategori,'MASUK') !== false){ echo "<span class='badge-premium badge-masuk'><i class='fa-solid fa-circle-down me-1'></i>MASUK</span>"; }
                            elseif(strpos($kategori,'KELUAR') !== false || strpos($kategori,'TERPAKAI') !== false){ echo "<span class='badge-premium badge-keluar'><i class='fa-solid fa-circle-up me-1'></i>KELUAR</span>"; }
                            else { echo "<span class='badge-premium badge-return'><i class='fa-solid fa-rotate-left me-1'></i>RETURN</span>"; }
                            ?>
                        </td>
                        <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text-muted)" title="<?= htmlspecialchars($d['keterangan'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <?= htmlspecialchars($d['keterangan'] ?: '-', ENT_QUOTES, 'UTF-8'); ?>
                        </td>
                       <td class="text-center">
    <div class="btn-action-group-cyber">

        <!-- Semua user boleh melihat detail -->
        <a href="detail.php?id=<?= $d['id']; ?>" class="btn-action-item-cyber btn-view">
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
        </a>

        <?php if(strtolower($_SESSION['role']) == 'admin'){ ?>

            <a href="edit.php?id=<?= $d['id']; ?>" class="btn-action-item-cyber btn-edit">
                <i class="fa-solid fa-pen-to-square"></i>
            </a>

            <a href="hapus.php?id=<?= $d['id']; ?>" class="btn-action-item-cyber btn-delete tombol-hapus">
                <i class="fa-solid fa-trash-can"></i>
            </a>

        <?php } ?>

    </div>
</td>
                    </tr>
                    <?php } } else { ?>
                    <tr><td colspan="15" class="text-center py-5 fw-bold text-muted"><i class="fa-solid fa-box-open d-block fs-1 mb-3 opacity-25"></i>Data Berita Acara tidak ditemukan</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php if($total_halaman > 1) { ?>
        <nav class="pt-2 pb-4">
            <ul class="pagination justify-content-center align-items-center">
                
                <li class="page-item">
                    <button type="button" class="page-link page-link-cyber" id="btnSliderPrev">
                        <i class="fa-solid fa-angle-left"></i>
                    </button>
                </li>

                <div class="slider-page-container" id="sliderPageContainer">
                    <div class="slider-page-wrapper">
                        <?php for($i = 1; $i <= $total_halaman; $i++) { ?>
                            <li class="page-item page-item-number <?= ($page == $i) ? 'active' : ''; ?>" data-index="<?= $i; ?>">
                                <a class="page-link page-link-cyber" href="?cari=<?= urlencode($cari_clean); ?>&page=<?= $i; ?>"><?= $i; ?></a>
                            </li>
                        <?php } ?>
                    </div>
                </div>

                <li class="page-item">
                    <button type="button" class="page-link page-link-cyber" id="btnSliderNext">
                        <i class="fa-solid fa-angle-right"></i>
                    </button>
                </li>

            </ul>
        </nav>
        <?php } ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    /* ==========================================================================
       SCRIPT UNTUK SLIDER NAVIGASI ANGKA PAGINATION
       ========================================================================== */
    document.addEventListener("DOMContentLoaded", function() {
        const container = document.getElementById('sliderPageContainer');
        const btnPrev = document.getElementById('btnSliderPrev');
        const btnNext = document.getElementById('btnSliderNext');

        if(container && btnPrev && btnNext) {
            const scrollAmount = 52 * 3; 

            const activeItem = container.querySelector('.page-item-number.active');
            if(activeItem) {
                container.scrollLeft = activeItem.offsetLeft - (container.offsetWidth / 2) + (activeItem.offsetWidth / 2);
            }

            btnPrev.addEventListener('click', function() {
                container.scrollLeft -= scrollAmount;
            });

            btnNext.addEventListener('click', function() {
                container.scrollLeft += scrollAmount;
            });
        }
    });

    /* ==========================================================================
       SIDEBAR & CONFIRMATION ACTION SCRIPT
       ========================================================================== */
    document.querySelectorAll('.dropdown-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const container = this.nextElementSibling;
            this.classList.toggle('active');
            
            if (window.getComputedStyle(container).display === "block") {
                container.style.display = "none";
            } else {
                container.style.display = "block";
            }
        });
    });

    document.querySelectorAll('.tombol-hapus').forEach(function(btn){
        btn.addEventListener('click', function(e){
            e.preventDefault();
            let url = this.getAttribute('href');
            Swal.fire({
                title: 'Hapus Data?',
                text: 'Data mutasi ini akan dihapus permanen dari sistem log!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => { if(result.isConfirmed){ window.location.href = url; } });
        });
    });

    /* ==========================================================================
       AUTOCOMPLETE SEARCH ENGINE SCRIPT
       ========================================================================== */
    const searchInput = document.getElementById('searchInput');
    const autocompleteBox = document.getElementById('autocompleteBox');
    const searchForm = document.getElementById('searchForm');
    const searchSpinner = document.getElementById('searchSpinner');
    const btnClearSearch = document.getElementById('btnClearSearch');

    if(btnClearSearch) {
        btnClearSearch.addEventListener('click', function() {
            searchInput.value = '';
            searchForm.submit();
        });
    }

    let abortController = null;

    searchInput.addEventListener('input', function() {
        let keywordValue = searchInput.value.trim();

        if (keywordValue.length < 1) {
            autocompleteBox.innerHTML = '';
            autocompleteBox.style.display = 'none';
            searchSpinner.style.display = 'none';
            return;
        }

        searchSpinner.style.display = 'block';

        if (abortController) {
            abortController.abort();
        }
        abortController = new AbortController();

        let payload = new FormData();
        payload.append('keyword', keywordValue);

        fetch('saran_barang.php', {
            method: 'POST',
            body: payload,
            signal: abortController.signal
        })
        .then(response => {
            if (!response.ok) throw new Error('Koneksi bermasalah');
            return response.json();
        })
        .then(data => {
            searchSpinner.style.display = 'none';
            autocompleteBox.innerHTML = '';

            if (data && data.length > 0) {
                data.forEach(item => {
                    let div = document.createElement('div');
                    div.className = 'autocomplete-suggestion-item';
                    div.textContent = item;

                    div.addEventListener('click', function() {
                        searchInput.value = item;
                        autocompleteBox.style.display = 'none';
                        searchForm.submit();
                    });
                    autocompleteBox.appendChild(div);
                });
                autocompleteBox.style.display = 'block';
            } else {
                autocompleteBox.style.display = 'none';
            }
        })
        .catch(err => {
            if (err.name !== 'AbortError') {
                console.error('AJAX Error:', err);
                searchSpinner.style.display = 'none';
            }
        });
    });

    document.addEventListener('click', function(e) {
        if (e.target !== searchInput && e.target !== autocompleteBox) {
            autocompleteBox.style.display = 'none';
        }
    });
</script>
</body>
</html>