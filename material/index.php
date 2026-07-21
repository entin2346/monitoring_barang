<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

// 1. Ambil kata kunci DAN kategori dari URL (Metode GET)
$cari = $_GET['cari'] ?? '';
$kategori = $_GET['kategori'] ?? ''; 
/* --- TAMBAHAN FITUR: AMBIL PARAMETER GUDANG DARI URL --- */
$gudang_filter = $_GET['gudang'] ?? ''; 

// Gunakan urldecode() agar tanda + atau %2B dikembalikan menjadi spasi asli
$cari_pencarian = urldecode($cari);

// 2. Amankan data dari SQL Injection & bersihkan spasi liar di ujung kata
$cari_db = mysqli_real_escape_string($conn, $cari_pencarian);
$cari_clean = trim($cari_db); 

$kategori_db = mysqli_real_escape_string($conn, trim($kategori));
$kategori_query = strtoupper(str_replace("_", " ", $kategori_db)); 

$limit = 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1){ $page = 1; }
$offset = ($page - 1) * $limit;

// 3. Menyusun kondisi WHERE secara dinamis
$whereConditions = [];
$whereConditions[] = "TRIM(m.nama_material) <> '' AND m.nama_material IS NOT NULL";

// KECUALIKAN KATEGORI EX BONGKARAN DARI TAMPILAN MATERIAL GUDANG
$whereConditions[] = "LOWER(TRIM(m.jenis_kategori)) NOT IN ('ex bongkaran', 'eks bongkaran')";

if ($cari_clean !== '') {
    $whereConditions[] = "(m.nama_material LIKE '%$cari_clean%')";
}

// Sinkronisasi Filter Pencarian Kategori dari URL
if ($kategori_db !== '') {

    $kat_cari = strtolower(trim($kategori_db));

    if ($kat_cari == 'stock') {
        $kat_cari = 'stok';
    }

    if ($kat_cari == 'non-stock' || $kat_cari == 'non stock') {
        $kat_cari = 'non stok';
    }

    $whereConditions[] =
        "LOWER(TRIM(m.jenis_kategori)) = '$kat_cari'";
}

/* --- QUERY KHUSUS HITUNG STATISTIK GLOBAL (Berdasarkan Gudang Baru) --- */
$whereClauseStats = implode(" AND ", $whereConditions);

// Hitung total Stok Gudang Latimojong
$stok_latimojong_query = mysqli_query($conn, "SELECT SUM(m.jumlah) AS total FROM material_gudang m WHERE $whereClauseStats AND (m.lokasi_penyimpanan LIKE '%latimojong%' OR m.lokasi_penyimpanan LIKE '%ltm%')");
$total_latimojong = mysqli_fetch_assoc($stok_latimojong_query)['total'] ?? 0;

// Hitung total Stok Gudang Hertasning
$stok_hertasning_query = mysqli_query($conn, "SELECT SUM(m.jumlah) AS total FROM material_gudang m WHERE $whereClauseStats AND (m.lokasi_penyimpanan LIKE '%hertasning%' OR m.lokasi_penyimpanan LIKE '%htn%')");
$total_hertasning = mysqli_fetch_assoc($stok_hertasning_query)['total'] ?? 0;
/* --- END STATISTIK GLOBAL --- */


/* --- FILTER KLIK KOTAK GUDANG --- */
if ($gudang_filter === 'latimojong') {
    $whereConditions[] = "(m.lokasi_penyimpanan LIKE '%latimojong%' OR m.lokasi_penyimpanan LIKE '%ltm%')";
} elseif ($gudang_filter === 'hertasning') {
    $whereConditions[] = "(m.lokasi_penyimpanan LIKE '%hertasning%' OR m.lokasi_penyimpanan LIKE '%htn%')";
}
/* --- END FILTER KOTAK --- */


// Gabungkan semua kondisi menjadi klausa WHERE final untuk SQL Tabel Utama
$whereClause = implode(" AND ", $whereConditions);


/* TOTAL DATA BERDASARKAN FILTER */
$total_query = mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM (
        SELECT m.id 
        FROM material_gudang m
        LEFT JOIN (
            SELECT 
                TRIM(LOWER(nama_barang)) AS key_nama
            FROM database_ba
            WHERE nama_barang <> '' AND nama_barang IS NOT NULL
            GROUP BY TRIM(LOWER(nama_barang))
        ) ba ON TRIM(LOWER(m.nama_material)) = ba.key_nama 
        WHERE $whereClause
        GROUP BY m.id
    ) AS subquery_total
");

if (!$total_query) {
    die("Gagal menghitung total data: " . mysqli_error($conn));
}

$total_data = mysqli_fetch_assoc($total_query)['total'] ?? 0;
$total_halaman = ceil($total_data / $limit);


/* TOTAL STOK BERDASARKAN FILTER */
$stok_query = mysqli_query($conn, "
    SELECT SUM(m.jumlah) AS total 
    FROM material_gudang m
    WHERE $whereClause
");

if (!$stok_query) {
    die("Gagal menghitung akumulasi stok: " . mysqli_error($conn));
}

$total_stok = mysqli_fetch_assoc($stok_query);


/* QUERY UTAMA */
$query = mysqli_query($conn,"
    SELECT 
        m.id AS id,
        m.nama_material,
        m.jenis_kategori AS jenis_kategori,
        m.jumlah AS jumlah,
        m.no_rak,
        m.kondisi,
        m.lokasi_penyimpanan,
        COALESCE(NULLIF(TRIM(ba.satuan), ''), m.satuan) AS satuan,
        COALESCE(NULLIF(TRIM(ba.sumber_barang), ''), m.sumber_barang) AS sumber_barang,
        COALESCE(NULLIF(TRIM(ba.keterangan), ''), m.keterangan) AS keterangan
    FROM material_gudang m
    LEFT JOIN (
        SELECT 
            TRIM(LOWER(nama_barang)) AS key_nama,
            MAX(satuan) AS satuan, 
            MAX(sumber_barang) AS sumber_barang, 
            MAX(keterangan) AS keterangan
        FROM database_ba
        WHERE nama_barang <> '' AND nama_barang IS NOT NULL
        GROUP BY TRIM(LOWER(nama_barang))
    ) ba ON TRIM(LOWER(m.nama_material)) = ba.key_nama 
    WHERE $whereClause
    GROUP BY m.id
    ORDER BY m.id ASC
    LIMIT $offset,$limit
");

if(!$query){
    die("Gagal memuat tabel utama: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Material Gudang Premium</title>
    
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

        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body { 
            background: var(--bg-body); color: var(--text-main);
            min-height: 100vh; overflow-x: hidden;
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
        .navbar-custom { background: #ffffff; padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999; }
        .navbar-custom .navbar-brand { color: var(--text-main); font-weight: 800; font-size: 1.3rem; }
        .main-body-wrapper { padding: 40px; }

        /* --- STYLE UNTUK KOTAK STATISTIK --- */
        .glass-stat-card { 
            background: var(--bg-card); 
            border: 1px solid var(--border-color); 
            border-top: 4px solid #cbd5e1;
            border-radius: 16px; 
            padding: 24px; 
            transition: all 0.2s ease; 
            text-decoration: none; 
            display: block; 
            position: relative;
        }
        .glass-stat-card:hover { transform: translateY(-4px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); cursor: pointer; }
        
        .stat-label { 
            font-size: 0.8rem; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            color: #475569; 
            font-weight: 700; 
            margin-bottom: 12px; 
        }
        .stat-number { font-size: 2.2rem; font-weight: 800; margin: 0; line-height: 1; }
        
        .stat-icon-circle {
            position: absolute;
            right: 24px;
            top: 50%;
            transform: translateY(-50%);
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .card-blue { border-top-color: #3b82f6; }
        .card-blue .stat-number { color: #1e40af; }
        .card-blue .stat-icon-circle { background: rgba(59, 130, 246, 0.08); color: #3b82f6; }

        .card-green { border-top-color: #10b981; }
        .card-green .stat-number { color: #10b981; }
        .card-green .stat-icon-circle { background: rgba(16, 185, 129, 0.08); color: #10b981; }

        .card-orange { border-top-color: #f59e0b; }
        .card-orange .stat-number { color: #d97706; }
        .card-orange .stat-icon-circle { background: rgba(245, 158, 11, 0.08); color: #f59e0b; }

        .card-red { border-top-color: #ef4444; }
        .card-red .stat-number { color: #ef4444; }
        .card-red .stat-icon-circle { background: rgba(239, 68, 68, 0.08); color: #ef4444; }
        
        .card-active { background: #f8fafc; box-shadow: inset 0 0 0 2px var(--primary); }

        .cyber-search-box { background: #ffffff; border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; }
        .input-cyber-group { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; overflow: hidden; }
        .input-cyber-group input { background: transparent !important; border: none !important; color: var(--text-main) !important; padding: 12px 18px; }
        .input-cyber-group .input-group-text { background: transparent; border: none; color: #64748b; padding-left: 18px; }

        .autocomplete-box { position: absolute; left: 0; right: 0; top: 100%; background: #ffffff; border: none; border-radius: 12px; margin-top: 6px; z-index: 99999 !important; max-height: 260px; overflow-y: auto; padding: 0; }
        .autocomplete-box.show-box { border: 1px solid #cbd5e1; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); padding: 5px 0; }
        .autocomplete-item { padding: 12px 16px; cursor: pointer; border-bottom: 1px solid #f1f5f9; color: var(--text-main); font-size: 0.95rem; text-align: left;}
        .autocomplete-item:hover { background: #bae6fd; color: #0369a1; }

        .cyber-table-wrapper { 
            background: #ffffff !important; 
            border: 1px solid var(--border-color); 
            border-radius: 16px; 
            overflow-x: auto; 
            -webkit-overflow-scrolling: touch; 
        }
        
        .cyber-table-wrapper table { width: 100%; border-collapse: separate; border-spacing: 0; margin: 0; }
        .table-cyber thead th { background: #f8fafc !important; color: #334155 !important; font-weight: 700; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.5px; padding: 16px 22px; border-bottom: 1px solid var(--border-color); white-space: nowrap; }
        .table-cyber tbody tr:not(:last-child) td { border-bottom: 1px solid var(--border-color); }
        .table-cyber tbody tr:hover td { background: #f8fafc; }
        .table-cyber tbody td { padding: 15px 22px; font-size: 0.88rem; vertical-align: middle; color: var(--text-main) !important; white-space: nowrap; }

        .neon-badge-stock { background: rgba(59, 130, 246, 0.06) !important; color: #3b82f6 !important; border: 1px solid rgba(59, 130, 246, 0.1) !important; border-radius: 8px; padding: 5px 12px; font-weight: 700; font-size: 0.8rem; display: inline-block; }
        .badge-status-baik { background: rgba(16, 185, 129, 0.1) !important; color: #10b981 !important; border: 1px solid rgba(16, 185, 129, 0.2) !important; padding: 6px 12px; border-radius: 8px; font-weight: 700; }
        .badge-status-other { background: rgba(245, 158, 11, 0.1) !important; color: #d97706 !important; border: 1px solid rgba(245, 158, 11, 0.2) !important; padding: 6px 12px; border-radius: 8px; font-weight: 700; }
        
        .badge-kat { display: inline-block; padding: 5px 10px; font-size: 0.78rem; font-weight: 700; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.3px; }
        .kat-stock { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .kat-nonstock { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .kat-other { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

        .btn-action-group { background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 10px; display: inline-flex; overflow: hidden; }
        .btn-action-item { background: transparent; border: none; color: var(--text-muted); padding: 8px 14px; text-decoration: none; transition: all 0.2s; }
        .btn-action-item.btn-view:hover { color: #3b82f6; background: rgba(59, 130, 246, 0.08); }
        .btn-action-item.btn-edit:hover { color: #d97706; background: rgba(217, 119, 6, 0.08); }
        .btn-action-item.btn-delete:hover { color: #ef4444; background: rgba(239, 68, 68, 0.08); }

        .pagination .page-link { background-color: #ffffff !important; border: 1px solid #e2e8f0 !important; color: var(--text-muted) !important; padding: 10px 18px; border-radius: 10px; margin: 0 3px; }
        .pagination .page-item.active .page-link { background: #3b82f6 !important; color: #ffffff !important; border: none !important; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.25); }
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
        <a href="../material/index.php" class="active-menu">Material Gudang</a>
        <a href="../ba/index.php">Database BA</a>
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
    <nav class="navbar navbar-custom">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center">
                <i class="fa-solid fa-boxes-stacked text-primary me-2"></i> KENDALI LOGISTIK 
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Material Gudang <?= !empty($kategori_db) ? '('.strtoupper($kategori_db).')' : ''; ?></span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <!-- --- BAGIAN KOTAK STATISTIK --- -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <a href="?cari=<?= urlencode($cari_clean); ?>&kategori=<?= urlencode($kategori_db); ?>" class="glass-stat-card card-blue <?= empty($gudang_filter) ? 'card-active' : ''; ?>">
                    <div class="stat-label">Total Klasifikasi Material</div>
                    <div class="stat-number"><?= number_format($total_data); ?></div>
                    <div class="stat-icon-circle"><i class="fa-solid fa-boxes-stacked"></i></div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="glass-stat-card card-green">
                    <div class="stat-label">Volume Akumulasi Stok</div>
                    <div class="stat-number"><?= number_format(abs($total_stok['total'] ?? 0)); ?></div>
                    <div class="stat-icon-circle"><i class="fa-solid fa-cubes"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <a href="?cari=<?= urlencode($cari_clean); ?>&kategori=<?= urlencode($kategori_db); ?>&gudang=latimojong" class="glass-stat-card card-orange <?= $gudang_filter === 'latimojong' ? 'card-active' : ''; ?>">
                    <div class="stat-label">GUDANG LATIMOJONG</div>
                    <div class="stat-number"><?= number_format(abs($total_latimojong)); ?></div>
                    <div class="stat-icon-circle"><i class="fa-solid fa-warehouse text-warning"></i></div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6">
                <a href="?cari=<?= urlencode($cari_clean); ?>&kategori=<?= urlencode($kategori_db); ?>&gudang=hertasning" class="glass-stat-card card-red <?= $gudang_filter === 'hertasning' ? 'card-active' : ''; ?>">
                    <div class="stat-label">GUDANG HERTASNING</div>
                    <div class="stat-number"><?= number_format(abs($total_hertasning)); ?></div>
                    <div class="stat-icon-circle"><i class="fa-solid fa-box-open text-danger"></i></div>
                </a>
            </div>
        </div>

        <div class="cyber-search-box mb-4">
            <form id="formCari" method="GET">
                <input type="hidden" name="kategori" id="formKategori" value="<?= htmlspecialchars($kategori_db, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="gudang" value="<?= htmlspecialchars($gudang_filter, ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="row g-3">
                    <div class="col-md-8" style="position: relative;">
                        <div class="input-group input-cyber-group">
                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="cari" id="cari" class="form-control" autocomplete="off" placeholder="Cari nama material..." value="<?= htmlspecialchars($cari_clean, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div id="hasil_autocomplete" class="autocomplete-box" style="display:none;"></div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2" style="border-radius: 12px; background: #3b82f6; border: none; height: 100%;"><i class="fa-solid fa-sliders me-1"></i> Cari Komponen</button>
                    </div>
                    <div class="col-md-2">
                     <?php if(strtolower($_SESSION['role']) == 'admin'){ ?>

<a href="tambah.php"
   class="btn btn-success w-100 fw-bold py-2 d-flex align-items-center justify-content-center"
   style="border-radius:12px;">
    <i class="fa-solid fa-circle-plus me-2"></i>
    Tambah Material
</a>

<?php } ?>
                    </div>
                </div>
            </form>
        </div>

        <div class="cyber-table-wrapper mb-4">
            <table class="table-cyber">
                <thead>
                    <tr>
                        <th width="60" class="text-center">NO</th>
                        <th>NAMA KELOMPOK MATERIAL GUDANG</th>
                        <th width="120">KATEGORI</th>
                        <th width="90">SATUAN</th>
                        <th width="130">JUMLAH STOK</th>
                        <th width="120">NOMOR RAK</th>
                        <th width="130">STATUS KONDISI</th>
                        <th>LOKASI PENYIMPANAN</th>
                        <th>SUMBER MATERIAL</th> 
                        <th>KETERANGAN</th>
                        <th width="160" class="text-center">MANAJEMEN OPSI</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $no = $offset + 1;
                    if(mysqli_num_rows($query) > 0){
                        while($d = mysqli_fetch_assoc($query)){
                            $kat_real = strtolower(trim($d['jenis_kategori'] ?? ''));
                            $id_material = (int)$d['id'];
                    ?>
                    <tr>
                        <td class="text-center fw-bold" style="color: var(--text-muted) !important;"><?= str_pad($no++, 2, '0', STR_PAD_LEFT); ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($d['nama_material'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <?php 
                            // PENCETAKAN DINAMIS SESUAI DATABASE:
                            switch($kat_real){
                                case 'stok':
                                case 'stock':
                                    echo '<span class="badge-kat kat-stock">Stok</span>';
                                    break;

                                case 'non stok':
                                case 'non-stok':
                                case 'non stock':
                                    echo '<span class="badge-kat kat-nonstock">Non Stok</span>';
                                    break;

                                case 'non po':
                                    echo '<span class="badge-kat kat-other">NON PO</span>';
                                    break;

                                case 'ex bongkaran':
                                    echo '<span class="badge-kat kat-other">EX BONGKARAN</span>';
                                    break;

                                case 'pre memory':
                                    echo '<span class="badge-kat kat-other">PRE MEMORY</span>';
                                    break;

                                case 'peminjaman':
                                    echo '<span class="badge-kat kat-other">PEMINJAMAN</span>';
                                    break;

                                case 'pemakaian':
                                    echo '<span class="badge-kat kat-other">PEMAKAIAN</span>';
                                    break;

                                default:
                                    echo '<span class="badge-kat kat-other">'
                                        . htmlspecialchars(strtoupper($d['jenis_kategori'] ?: '-'), ENT_QUOTES, 'UTF-8')
                                        . '</span>';
                                    break;
                            }
                            ?>
                        </td>
                        <td><span class="small px-2 py-1 rounded fw-semibold" style="background: rgba(0,0,0,0.03); border: 1px solid var(--border-color); color: var(--text-muted);"><?= htmlspecialchars($d['satuan'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></span></td>
                        <td><span class="neon-badge-stock"><?= number_format(abs((int)$d['jumlah'])); ?></span></td>
                        <td style="font-weight: 600;"><i class="fa-solid fa-layer-group text-muted me-2 small"></i><?= htmlspecialchars($d['no_rak'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <?php
                            if(strtoupper($d['kondisi'] ?? '') == 'BAIK'){
                                echo "<span class='badge-status-baik'><i class='fa-solid fa-circle-check me-1 small'></i> BAIK</span>";
                            }else{
                                echo "<span class='badge-status-other'>".htmlspecialchars(strtoupper($d['kondisi'] ?: '-'), ENT_QUOTES, 'UTF-8')."</span>";
                            }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($d['lokasi_penyimpanan'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="fw-semibold text-primary"><?= htmlspecialchars($d['sumber_barang'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><span class="small text-muted"><?= htmlspecialchars($d['keterangan'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></span></td>
                        <td class="text-center">
                            <div class="btn-action-group">
                                <!-- Semua user boleh melihat detail -->
                                <a href="detail.php?id=<?= $d['id']; ?>" class="btn-action-item btn-view">
                                    <i class="fa-solid fa-expand"></i>
                                </a>

                                <?php if(strtolower($_SESSION['role']) == 'admin'){ ?>

                                    <a href="edit.php?id=<?= $d['id']; ?>" class="btn-action-item btn-edit">
                                        <i class="fa-solid fa-user-pen"></i>
                                    </a>

                                    <a href="hapus.php?id=<?= $d['id']; ?>"
                                       class="btn-action-item btn-delete"
                                       onclick="return confirm('Hapus permanently?')">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>

                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="11" class="text-center py-5">Material tidak ditemukan.</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php if($total_halaman > 1) { ?>
        <nav class="pb-5">
            <ul class="pagination justify-content-center align-items-center">
                <?php if($page > 1){ ?>
                    <li class="page-item">
                        <a class="page-link" href="?cari=<?= urlencode($cari_clean); ?>&kategori=<?= urlencode($kategori_db); ?>&gudang=<?= urlencode($gudang_filter); ?>&page=<?= $page-1; ?>">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                    </li>
                <?php } else { ?>
                    <li class="page-item disabled">
                        <span class="page-link"><i class="fa-solid fa-chevron-left"></i></span>
                    </li>
                <?php } ?>

                <?php
                $jumlah_number = 2;
                $start_number = ($page > $jumlah_number) ? $page - $jumlah_number : 1;
                $end_number = ($page < ($total_halaman - $jumlah_number)) ? $page + $jumlah_number : $total_halaman;

                if($start_number > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?cari='.urlencode($cari_clean).'&kategori='.urlencode($kategori_db).'&gudang='.urlencode($gudang_filter).'&page=1">1</a></li>';
                    if($start_number > 2) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }

                for($i = $start_number; $i <= $end_number; $i++){
                    $link_active = ($page == $i) ? 'active' : '';
                ?>
                    <li class="page-item <?= $link_active; ?>">
                        <a class="page-link" href="?cari=<?= urlencode($cari_clean); ?>&kategori=<?= urlencode($kategori_db); ?>&gudang=<?= urlencode($gudang_filter); ?>&page=<?= $i; ?>"><?= $i; ?></a>
                    </li>
                <?php 
                } 

                if($end_number < $total_halaman) {
                    if($end_number < ($total_halaman - 1)) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    echo '<li class="page-item"><a class="page-link" href="?cari='.urlencode($cari_clean).'&kategori='.urlencode($kategori_db).'&gudang='.urlencode($gudang_filter).'&page='.$total_halaman.'">'.$total_halaman.'</a></li>';
                }
                ?>
                
                <?php if($page < $total_halaman){ ?>
                    <li class="page-item"><a class="page-link" href="?cari=<?= urlencode($cari_clean); ?>&kategori=<?= urlencode($kategori_db); ?>&gudang=<?= urlencode($gudang_filter); ?>&page=<?= $page+1; ?>"><i class="fa-solid fa-chevron-right"></i></a></li>
                <?php } else { ?>
                    <li class="page-item disabled"><span class="page-link"><i class="fa-solid fa-chevron-right"></i></span></li>
                <?php } ?>
            </ul>
        </nav>
        <?php } ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
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

    const inputCari = document.getElementById("cari");
    const hasil = document.getElementById("hasil_autocomplete");

    inputCari.addEventListener("input", function(){
        let keyword = this.value.trim();
        
        if(keyword.length < 1){
            hasil.innerHTML = "";
            hasil.style.display = "none";
            hasil.classList.remove("show-box");
            return;
        }

        const urlParams = new URLSearchParams(window.location.search);
        const kategoriAktif = urlParams.get('kategori') || '';

        fetch("autocomplete.php?keyword=" + encodeURIComponent(keyword) + "&kategori=" + encodeURIComponent(kategoriAktif))
        .then(res => res.text())
        .then(data => {
            if(data.trim() !== ""){
                hasil.innerHTML = data;
                hasil.style.display = "block";
                hasil.classList.add("show-box");
            }else{
                hasil.innerHTML = "";
                hasil.style.display = "none";
                hasil.classList.remove("show-box");
            }
        })
        .catch(err => console.error(err));
    });

    function pilihMaterial(namaEncoded){
        let nama = decodeURIComponent(namaEncoded);
        inputCari.value = nama;
        hasil.style.display = "none";
        hasil.classList.remove("show-box");
        document.getElementById("formCari").submit();
    }

    document.addEventListener("click", function(e){
        if(!hasil.contains(e.target) && e.target !== inputCari){
            hasil.style.display = "none";
            hasil.classList.remove("show-box");
        }
    });
</script>
</body>
</html>