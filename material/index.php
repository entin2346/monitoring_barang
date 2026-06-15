<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

$cari = $_GET['cari'] ?? '';
$cari = mysqli_real_escape_string($conn,$cari);

$limit = 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if($page < 1){ $page = 1; }

$offset = ($page - 1) * $limit;

/* TOTAL DATA */
$total_query = mysqli_query($conn,"
    SELECT COUNT(*) AS total
    FROM material_gudang
    WHERE nama_material <> ''
    AND nama_material LIKE '%$cari%'
");

$total_data = mysqli_fetch_assoc($total_query)['total'];
$total_halaman = ceil($total_data / $limit);

/* TOTAL STOK */
$total_stok = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT SUM(jumlah) AS total FROM material_gudang")
);

/* DATA MATERIAL */
$query = mysqli_query($conn,"
    SELECT *
    FROM material_gudang
    WHERE nama_material <> ''
    AND nama_material LIKE '%$cari%'
    ORDER BY nama_material ASC
    LIMIT $offset,$limit
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Material Gudang Futuristic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-dark: #090d16;           
            --bg-deep: #060814;
            --bg-surface: #0f132a;
            --bg-card: #151b3d;
            --primary-glow: #38bdf8;
            --secondary-glow: #818cf8;
            --emerald-glow: #34d399;
            --text-main: #f8fafc;
            --text-sub: #94a3b8;
            --border-glass: rgba(255, 255, 255, 0.06);
            --border-color: rgba(255, 255, 255, 0.05);
        }

        /* CUSTOM SCROLLBAR */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: var(--bg-dark); }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 20px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary-glow); }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body { 
            background: radial-gradient(circle at top right, #111638 0%, var(--bg-deep) 60%);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* SIDEBAR PREMIUM (PERSISTEN DI KIRI) */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100%;
            background: #111827;
            border-right: 1px solid var(--border-color);
            padding-top: 28px;
            z-index: 1000;
            box-shadow: 10px 0 30px rgba(0,0,0,0.2);
        }
        
        .sidebar h3 { 
            font-size: 1.4rem; 
            font-weight: 800; 
            padding: 0 24px; 
            margin-bottom: 35px; 
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #fff 30%, var(--primary-glow));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .sidebar a, .dropdown-btn { 
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: var(--text-sub); 
            text-decoration: none; 
            padding: 14px 24px; 
            font-size: 0.95rem;
            font-weight: 600;
            border: none;
            background: none;
            width: 100%;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }
        
        .sidebar a:hover, .dropdown-btn:hover { 
            background: rgba(255, 255, 255, 0.02); 
            color: var(--text-main);
        }

        .sidebar .active-menu {
            color: var(--primary-glow) !important; 
            background: rgba(56, 189, 248, 0.04) !important; 
            border-left: 4px solid var(--primary-glow); 
            padding-left: 20px;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.3);
        }

        .sidebar a i, .dropdown-btn i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
            transition: transform 0.2s;
        }

        .sidebar a:hover i { transform: scale(1.1); }
        .sidebar .menu-text { flex-grow: 1; }
        .dropdown-chevron { font-size: 0.8rem !important; transition: transform 0.2s ease; }
        .dropdown-btn.active .dropdown-chevron { transform: rotate(180deg); color: var(--primary-glow); }

        .dropdown-container {
            display: none;
            background: rgba(0, 0, 0, 0.2); 
            padding: 4px 0;
        }
        
        .dropdown-container a { padding: 11px 24px 11px 56px; font-size: 0.85rem; font-weight: 500; }

        /* CONTENT WRAPPER SHIFTED TO RIGHT */
        .content { 
            margin-left: 260px; 
            min-height: 100vh;
        }

        /* PREMIUM BLURRED GLASS NAVBAR */
        .navbar-cyber {
            background: rgba(15, 19, 42, 0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-glass);
            padding: 18px 32px;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        .navbar-brand-cyber {
            font-weight: 800;
            font-size: 1.3rem;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #ffffff 40%, var(--primary-glow));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .main-body-wrapper {
            padding: 40px 32px;
        }

        /* CARD RINGKASAN GRADASI GLOWING */
        .glass-stat-card {
            background: rgba(21, 27, 61, 0.5);
            border: 1px solid var(--border-glass);
            border-radius: 24px;
            padding: 28px 24px;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(5px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(56, 189, 248, 0.3);
            box-shadow: 0 15px 35px rgba(56, 189, 248, 0.1);
        }
        .glass-stat-card::after {
            content: '';
            position: absolute;
            width: 120px; height: 120px;
            background: var(--primary-glow);
            filter: blur(80px);
            top: -50px; right: -50px;
            opacity: 0.15;
            pointer-events: none;
        }
        .stat-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text-sub);
            font-weight: 700;
            margin-bottom: 12px;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: -1px;
            color: #ffffff;
        }

        /* DYNAMIC SEARCH BAR WITH NEON RADIUS */
        .cyber-search-box {
            background: rgba(15, 19, 42, 0.9);
            border: 1px solid var(--border-glass);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .input-cyber-group {
            background: #192048;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            overflow: hidden;
            transition: all 0.3s;
        }
        .input-cyber-group:focus-within {
            border-color: var(--primary-glow);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15);
        }
        .input-cyber-group input {
            background: transparent !important;
            border: none !important;
            color: #ffffff !important;
            padding: 14px 20px;
            font-size: 0.95rem;
        }
        .input-cyber-group input:focus { box-shadow: none !important; }
        .input-cyber-group .input-group-text {
            background: transparent;
            border: none;
            color: var(--text-sub);
            padding-left: 20px;
        }

        /* TABEL CYBERPUNK FUTURISTIC */
        .cyber-table-wrapper {
            background: rgba(15, 19, 42, 0.8) !important;
            border: 1px solid var(--border-glass);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0,0,0,0.4);
        }
        .table-cyber {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            background: transparent !important;
        }
        .table-cyber thead th {
            background: #111635 !important;
            color: var(--text-sub) !important;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.72rem;
            letter-spacing: 1.2px;
            padding: 22px 24px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.06);
        }
        .table-cyber tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            background: transparent !important;
            transition: all 0.25s ease;
        }
        .table-cyber tbody tr:hover {
            background: rgba(255, 255, 255, 0.02) !important;
        }
        .table-cyber tbody td {
            padding: 18px 24px;
            font-size: 0.92rem;
            vertical-align: middle;
            color: #e2e8f0 !important;
        }

        /* MATERIAL CHIPS/BADGES */
        .neon-badge-stock {
            background: rgba(56, 189, 248, 0.1) !important;
            color: var(--primary-glow) !important;
            border: 1px solid rgba(56, 189, 248, 0.25) !important;
            padding: 6px 14px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-block;
        }

        .badge-status-baik {
            background: rgba(52, 211, 153, 0.1) !important;
            color: var(--emerald-glow) !important;
            border: 1px solid rgba(52, 211, 153, 0.2) !important;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.75rem;
        }

        .badge-status-other {
            background: rgba(251, 191, 36, 0.1) !important;
            color: #fbbf24 !important;
            border: 1px solid rgba(251, 191, 36, 0.2) !important;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.75rem;
        }

        /* CUSTOM CONTROLLER MINI BUTTON GROUP */
        .btn-action-group {
            background: #192048;
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 10px;
            overflow: hidden;
            display: inline-flex;
        }
        .btn-action-item {
            background: transparent;
            border: none;
            color: var(--text-sub);
            padding: 8px 14px;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-action-item:hover { background: rgba(255, 255, 255, 0.05); }
        .btn-action-item.btn-view:hover { color: var(--primary-glow); }
        .btn-action-item.btn-edit:hover { color: #fbbf24; }
        .btn-action-item.btn-delete:hover { color: #f87171; }

        /* PAGINATION GLOW */
        .pagination .page-link {
            background-color: #111635 !important;
            border: 1px solid var(--border-glass) !important;
            color: var(--text-sub) !important;
            padding: 10px 18px;
            font-weight: 600;
            border-radius: 10px;
            margin: 0 3px;
        }
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary-glow), var(--secondary-glow)) !important;
            border: none !important;
            color: #060814 !important;
            font-weight: 700;
            box-shadow: 0 0 20px rgba(56, 189, 248, 0.3);
        }
    </style>
</head>
<body>

<!-- SIDEBAR MENU KIRI (PERSISTEN) -->
<div class="sidebar">
    <h3><i class="fa-solid fa-bolt text-warning me-2"></i>I-CALM Panel</h3>

    <a href="../dashboard/index.php">
        <span><i class="fa-solid fa-chart-pie me-2"></i><span class="menu-text">Dashboard</span></span>
    </a>

    <!-- Menu Monitoring Diaktifkan Otomatis Terbuka saat di Halaman Ini -->
    <button class="dropdown-btn active">
        <span><i class="fa-solid fa-layer-group"></i><span class="menu-text">Monitoring</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container" style="display: block;">
        <a href="../material/index.php" class="active-menu">Material Gudang</a>
        <a href="../ba/index.php">Database BA</a>
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

    <a href="../login/logout.php" class="mt-4">
        <span><i class="fa-solid fa-right-from-bracket text-danger"></i><span class="menu-text text-danger">Logout</span></span>
    </a>
</div>

<!-- KONTEN KANAN -->
<div class="content">
    
    <!-- TOP GLASS NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-cyber">
        <div class="container-fluid px-0">
            <span class="navbar-brand-cyber d-flex align-items-center">
                <i class="fa-solid fa-boxes-stacked text-info me-2"></i> KENDALI LOGISTIK <span style="font-weight: 300; font-size: 0.9rem; color: var(--text-sub); margin-left: 10px;">/ Material Gudang</span>
            </span>
            <div class="d-flex gap-2">
                <a href="../export/material_excel.php" class="btn btn-success btn-sm px-3 fw-semibold" style="border-radius: 10px; box-shadow: 0 0 15px rgba(25, 135, 84, 0.2);"><i class="fa-solid fa-file-excel me-1"></i> Export Excel</a>
            </div>
        </div>
    </nav>

    <!-- WRAPPER DATA UTAMA -->
    <div class="main-body-wrapper">

        <!-- COUNTER HEADERS GRID -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="glass-stat-card">
                    <div class="stat-label">Total Klasifikasi Material</div>
                    <div class="stat-number"><?= number_format($total_data); ?> <span style="font-size: 1.1rem; font-weight: 400; color: var(--text-sub);">Item Terdata</span></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-stat-card">
                    <div class="stat-label">Volume Akumulasi Stok</div>
                    <div class="stat-number" style="color: #34d399;"><?= number_format($total_stok['total'] ?? 0); ?> <span style="font-size: 1.1rem; font-weight: 400; color: var(--text-sub);">Unit Logistik</span></div>
                </div>
            </div>
        </div>

        <!-- FILTER ENGINE -->
        <div class="cyber-search-box mb-4">
            <form method="GET">
                <div class="row g-3">
                    <div class="col-md-10">
                        <div class="input-group input-cyber-group">
                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="cari" class="form-control" placeholder="Cari nama komponen, kode asset, atau spesifikasi material..." value="<?= htmlspecialchars($cari); ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100 fw-bold py-3" style="border-radius: 14px; background: linear-gradient(135deg, #0284c7, #2563eb); border: none; box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);"><i class="fa-solid fa-sliders me-1"></i> Saring Data</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- CONTROL HEADER ACTION -->
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <a href="tambah.php" class="btn btn-info btn-sm fw-bold px-4 py-2 text-dark" style="border-radius: 12px; background: #38bdf8; box-shadow: 0 0 20px rgba(56, 189, 248, 0.4);"><i class="fa-solid fa-plus-circle me-1"></i> Registrasi Material Baru</a>
            <?php if(!empty($cari)) { ?>
                <span class="small text-muted">Hasil filter untuk klausa: <strong class="text-info">"<?= htmlspecialchars($cari) ?>"</strong></span>
            <?php } ?>
        </div>

        <!-- MAIN SYSTEM CORE TABLE -->
        <div class="cyber-table-wrapper table-responsive mb-4">
            <table class="table-cyber">
                <thead>
                    <tr>
                        <th width="70" class="text-center">ID</th>
                        <th>NAMA KELOMPOK MATERIAL GUDANG</th>
                        <th width="100">SATUAN</th>
                        <th width="160">JUMLAH STOK</th>
                        <th width="140">NOMOR RAK</th>
                        <th width="140">STATUS KONDISI</th>
                        <th>LOKASI PENYIMPANAN</th>
                        <th width="180" class="text-center">MANAJEMEN OPSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = $offset + 1;
                    if(mysqli_num_rows($query) > 0){
                        while($d = mysqli_fetch_assoc($query)){
                    ?>
                    <tr>
                        <td class="text-center fw-bold" style="color: #475569 !important;"><?= $no++; ?></td>
                        <td style="font-weight: 700; color: #ffffff !important; font-size:0.95rem;"><?= htmlspecialchars($d['nama_material']); ?></td>
                        <td><span class="small px-2 py-1 rounded fw-semibold" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); color: #cbd5e1;"><?= htmlspecialchars($d['satuan']); ?></span></td>
                        <td>
                            <span class="neon-badge-stock"><?= number_format($d['jumlah']); ?></span>
                        </td>
                        <td style="font-weight: 600;"><i class="fa-solid fa-layer-group text-muted me-1.5 small"></i><?= htmlspecialchars($d['no_rak']); ?></td>
                        <td>
                            <?php
                            if(strtoupper($d['kondisi']) == 'BAIK'){
                                echo "<span class='badge-status-baik'><i class='fa-solid fa-circle-check me-1 small'></i> BAIK</span>";
                            }else{
                                echo "<span class='badge-status-other'><i class='fa-solid fa-triangle-exclamation me-1 small'></i> ".htmlspecialchars(strtoupper($d['kondisi']))."</span>";
                            }
                            ?>
                        </td>
                        <td style="color: #94a3b8 !important;"><i class="fa-solid fa-map-pin text-danger opacity-70 me-1.5 small"></i><?= htmlspecialchars($d['lokasi_penyimpanan']); ?></td>
                        <td class="text-center">
                            <div class="btn-action-group">
                                <a href="detail.php?id=<?= $d['id']; ?>" class="btn-action-item btn-view" title="Detail Aset"><i class="fa-solid fa-expand"></i></a>
                                <a href="edit.php?id=<?= $d['id']; ?>" class="btn-action-item btn-edit" title="Ubah Konfigurasi"><i class="fa-solid fa-user-pen"></i></a>
                                <a href="hapus.php?id=<?= $d['id']; ?>" class="btn-action-item btn-delete" onclick="return confirm('Hapus permanen data material ini dari sistem logistik?')" title="Hapus Data"><i class="fa-solid fa-trash-can"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php
                        }
                    }else{
                    ?>
                    <tr>
                        <td colspan="8" class="text-center py-5" style="color: var(--text-sub) !important;">
                            <i class="fa-solid fa-satellite-dish d-block fs-1 mb-3 text-muted opacity-40"></i> Database kosong atau kata kunci tidak cocok.
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- INFRASTRUKTUR NAVIGATION INDEX -->
        <?php if($total_halaman > 1) { ?>
        <nav class="pb-5">
            <ul class="pagination justify-content-center">
                <?php if($page > 1){ ?>
                <li class="page-item"><a class="page-link" href="?cari=<?= urlencode($cari); ?>&page=<?= $page-1; ?>"><i class="fa-solid fa-chevron-left"></i></a></li>
                <?php } ?>

                <?php
                for($i=1; $i<=$total_halaman; $i++){
                    if($i == 1 || $i == $total_halaman || ($i >= $page-2 && $i <= $page+2)){
                ?>
                    <li class="page-item <?= ($i==$page)?'active':''; ?>">
                        <a class="page-link" href="?cari=<?= urlencode($cari); ?>&page=<?= $i; ?>"><?= $i; ?></a>
                    </li>
                <?php
                    }
                }
                ?>

                <?php if($page < $total_halaman){ ?>
                <li class="page-item"><a class="page-link" href="?cari=<?= urlencode($cari); ?>&page=<?= $page+1; ?>"><i class="fa-solid fa-chevron-right"></i></a></li>
                <?php } ?>
            </ul>
        </nav>
        <?php } ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- JAVASCRIPT ANIMASI INTERAKTIF SIDEBAR DROPDOWN -->
<script>
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
</script>

</body>
</html>