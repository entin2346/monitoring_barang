<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}
include "../config/koneksi.php";

// Ambil parameter pencarian dan bersihkan bug '+' dari URL
$cari = $_GET['cari'] ?? '';
$cari_clean = trim(mysqli_real_escape_string($conn, urldecode($cari)));

// KUNCI FILTER: Hanya menarik data kategori NON PO
$whereClause = "(jenis_kategori LIKE '%non%po%')";
if ($cari_clean !== '') {
    $whereClause .= " AND (nama_material LIKE '%$cari_clean%')";
}

// Fitur Mutasi Halaman (Pagination)
$limit = 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1){ $page = 1; }
$offset = ($page - 1) * $limit;

// Hitung total item hasil filter
$total_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM material_gudang WHERE $whereClause");
$total_data = mysqli_fetch_assoc($total_query)['total'];
$total_halaman = ceil($total_data / $limit);

// Hitung akumulasi volume stok khusus Non PO
$stok_query = mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM material_gudang WHERE $whereClause");
$total_stok = mysqli_fetch_assoc($stok_query)['total'] ?? 0;

// Ambil data dari database dengan pengurutan No Rak khusus
$query = mysqli_query($conn, "
    SELECT * FROM material_gudang 
    WHERE $whereClause 
    ORDER BY 
        CASE
            WHEN no_rak='A1' THEN 1 WHEN no_rak='A2' THEN 2 WHEN no_rak='A3' THEN 3
            WHEN no_rak='B1' THEN 4 WHEN no_rak='B2' THEN 5 WHEN no_rak='B3' THEN 6 WHEN no_rak='B4' THEN 7
            WHEN no_rak='C1' THEN 8 WHEN no_rak='C2' THEN 9 WHEN no_rak='C3' THEN 10
            WHEN no_rak='D1' THEN 11 WHEN no_rak='D2' THEN 12 WHEN no_rak='D3' THEN 13
            WHEN no_rak='E1' THEN 14 WHEN no_rak='E2' THEN 15 WHEN no_rak='E3' THEN 16
            WHEN no_rak='F1' THEN 17 WHEN no_rak='F2' THEN 18
            WHEN no_rak='G1' THEN 19 WHEN no_rak='G2' THEN 20 WHEN no_rak='G3' THEN 21
            WHEN no_rak='H1' THEN 22 WHEN no_rak='H2' THEN 23 WHEN no_rak='H3' THEN 24
            WHEN no_rak='I1' THEN 25 WHEN no_rak='I2' THEN 26
            WHEN no_rak='J1' THEN 27 WHEN no_rak='J2' THEN 28
            WHEN no_rak='K1' THEN 29 WHEN no_rak='K2' THEN 30 WHEN no_rak='K3' THEN 31
            WHEN no_rak='M1' THEN 32 WHEN no_rak='M2' THEN 33 WHEN no_rak='M3' THEN 34
            WHEN no_rak='PETI' THEN 35 WHEN no_rak='RAK ISOLATOR' THEN 36
            ELSE 999
        END ASC,
        nama_material ASC 
    LIMIT $offset, $limit
");

if(!$query){
    die(mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Kategori Non PO</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: var(--bg-body); color: var(--text-main); min-height: 100vh; overflow-x: hidden; }

        .sidebar {
            position: fixed; left: 0; top: 0; width: 260px; height: 100%;
            background-color: var(--bg-sidebar); border-right: 1px solid rgba(2, 132, 199, 0.15);
            padding: 35px 20px; z-index: 1050; display: flex; flex-direction: column; overflow-y: auto;
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
            color: #025a9c; background: rgba(2, 132, 199, 0.12); transform: translateX(4px);
        }
        
        .sidebar .menu-content-wrapper { display: flex; align-items: center; gap: 12px; }
        .sidebar a i, .dropdown-btn i.menu-icon { font-size: 1.05rem; width: 20px; text-align: center; color: #1e40af; }
        
        .sidebar .active-menu {
            color: #ffffff !important; background: #0284c7 !important; font-weight: 700;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); border-radius: 10px;
        }
        .sidebar .active-menu i { color: #ffffff !important; }

        .dropdown-chevron { font-size: 0.75rem !important; transition: transform 0.2s ease; color: #1e40af !important; }
        .dropdown-btn.active .dropdown-chevron { transform: rotate(180deg); }
        
        .dropdown-container { display: none; padding-left: 12px; margin-bottom: 6px; margin-top: 4px; }
        .dropdown-container a { 
            padding: 9px 14px; font-size: 0.85rem; color: #1e40af; font-weight: 600; background: rgba(255, 255, 255, 0.2);
            border-radius: 8px; margin-bottom: 3px;
        }
        .dropdown-container a:hover { background: #ffffff; color: #0284c7; }

        .sidebar .logout-button { margin-top: auto; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; }
        .sidebar .logout-button i, .sidebar .logout-button span { color: #b91c1c !important; }

        .content { margin-left: 260px; position: relative; }
        .navbar-custom { background: #ffffff; padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999; }
        .navbar-custom .navbar-brand { color: var(--text-main); font-weight: 800; font-size: 1.3rem; }
        .main-body-wrapper { padding: 40px; }

        .glass-stat-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 26px; border-left: 5px solid var(--primary); }
        .stat-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-muted); font-weight: 700; margin-bottom: 8px; }
        .stat-number { font-size: 2rem; font-weight: 800; color: var(--text-main); margin: 0; }

        .cyber-search-box { background: #ffffff; border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; }
        .input-cyber-group { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; overflow: hidden; }
        .input-cyber-group input { background: transparent !important; border: none !important; color: var(--text-main) !important; padding: 12px 18px; }
        .input-cyber-group .input-group-text { background: transparent; border: none; color: #64748b; padding-left: 18px; }

        .cyber-table-wrapper { border: 1px solid var(--border-color); border-radius: 16px; overflow: hidden; }
        .table-cyber { width: 100%; border-collapse: separate; border-spacing: 0; margin: 0; }
        .table-cyber thead th { background: #f8fafc !important; color: #334155 !important; font-weight: 700; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.5px; padding: 16px 22px; border-bottom: 1px solid var(--border-color); }
        .table-cyber tbody tr:not(:last-child) td { border-bottom: 1px solid var(--border-color); }
        .table-cyber tbody tr:hover td { background: #f8fafc; }
        .table-cyber tbody td { padding: 15px 22px; font-size: 0.88rem; vertical-align: middle; color: var(--text-main) !important; }

        .neon-badge-stock { background: rgba(2, 132, 199, 0.06) !important; color: var(--primary) !important; border: 1px solid rgba(2, 132, 199, 0.1) !important; border-radius: 8px; padding: 5px 12px; font-weight: 700; font-size: 0.8rem; display: inline-block; }
        .badge-status-baik { background: rgba(16, 185, 129, 0.1) !important; color: #10b981 !important; border: 1px solid rgba(16, 185, 129, 0.2) !important; padding: 6px 12px; border-radius: 8px; font-weight: 700; }
        .badge-status-other { background: rgba(245, 158, 11, 0.1) !important; color: #f59e0b !important; border: 1px solid rgba(245, 158, 11, 0.2) !important; padding: 6px 12px; border-radius: 8px; font-weight: 700; }
        
        .badge-kat { display: inline-block; padding: 5px 10px; font-size: 0.78rem; font-weight: 700; border-radius: 6px; text-transform: uppercase; }
        .kat-nonpo { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fa-solid fa-bolt text-primary"></i> I-CALM Panel</h3>
    
    <a href="../dashboard/index.php">
        <span class="menu-content-wrapper"><i class="fa-solid fa-chart-pie"></i><span>Dashboard</span></span>
    </a>
    
    <button class="dropdown-btn">
        <span class="menu-content-wrapper"><i class="fa-solid fa-layer-group menu-icon"></i><span>Monitoring</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../material/index.php">Material Gudang</a>
        <a href="../ba/index.php">Database BA</a>
    </div>

    <button class="dropdown-btn active">
        <span class="menu-content-wrapper"><i class="fa-solid fa-tags menu-icon"></i><span>Kategori</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container" style="display: block;">
        <a href="stok.php">Stok</a>
        <a href="non_stok.php">Non Stok</a>
        <a href="non_po.php" class="active-menu">Non PO</a>
        <a href="ex_bongkaran.php">Ex Bongkaran</a>
        <a href="pre_memory.php">Pre Memory</a>
        <a href="pemakaian.php">Pemakaian</a>
        <a href="peminjaman.php">Peminjaman</a>
    </div>

    <button class="dropdown-btn">
        <span class="menu-content-wrapper"><i class="fa-solid fa-file-import menu-icon"></i><span>Import</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../import/material.php">Import Material</a>
        <a href="../import/ba.php">Import BA</a>
    </div>

    <button class="dropdown-btn">
        <span class="menu-content-wrapper"><i class="fa-solid fa-file-export menu-icon"></i><span>Export</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../export/material_excel.php">Export Material</a>
        <a href="../export/ba_excel.php">Export BA</a>
    </div>
    
    <a href="../login/logout.php" class="logout-button">
        <span class="menu-content-wrapper"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></span>
    </a>
</div>

<div class="content">
    <nav class="navbar navbar-custom">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center">
                <i class="fa-solid fa-boxes-stacked text-primary me-2"></i> KENDALI LOGISTIK 
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Kategori: Non PO</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="glass-stat-card">
                    <div class="stat-label">Total Klasifikasi Non PO</div>
                    <div class="stat-number"><?= number_format($total_data); ?> <span class="fw-normal text-muted" style="font-size: 1.1rem;">Item</span></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-stat-card" style="border-left-color: #10b981;">
                    <div class="stat-label">Volume Akumulasi Stok</div>
                    <div class="stat-number" style="color: #10b981;"><?= number_format($total_stok); ?> <span class="fw-normal text-muted" style="font-size: 1.1rem;">Unit</span></div>
                </div>
            </div>
        </div>

        <div class="cyber-search-box mb-4">
            <form method="GET">
                <div class="row g-3">
                    <div class="col-md-10">
                        <div class="input-group input-cyber-group">
                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="cari" class="form-control" autocomplete="off" placeholder="Cari material khusus Non PO..." value="<?= htmlspecialchars($cari_clean); ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2" style="border-radius: 12px; background: linear-gradient(135deg, #0284c7, #2563eb); border: none; height:100%;"><i class="fa-solid fa-sliders me-1"></i> Saring</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="cyber-table-wrapper table-responsive mb-4">
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
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = $offset + 1;
                    if(mysqli_num_rows($query) > 0){
                        while($d = mysqli_fetch_assoc($query)){
                    ?>
                    <tr>
                        <td class="text-center fw-bold" style="color: var(--text-muted) !important; font-size:0.85rem;"><?= str_pad($no++, 2, '0', STR_PAD_LEFT); ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($d['nama_material']); ?></td>
                        <td><span class="badge-kat kat-nonpo">Non PO</span></td>
                        <td><span class="small px-2 py-1 rounded fw-semibold" style="background: rgba(0,0,0,0.03); border: 1px solid var(--border-color); color: var(--text-muted);"><?= htmlspecialchars($d['satuan']); ?></span></td>
                        <td><span class="neon-badge-stock"><?= number_format(abs((int)$d['jumlah'])); ?></span></td>
                        <td style="font-weight: 600;"><i class="fa-solid fa-layer-group text-muted me-2 small"></i><?= htmlspecialchars($d['no_rak']); ?></td>
                        <td>
                            <?php
                            if(strtoupper($d['kondisi'] ?? '') == 'BAIK'){ echo "<span class='badge-status-baik'><i class='fa-solid fa-circle-check me-1 small'></i> BAIK</span>"; }
                            else { echo "<span class='badge-status-other'><i class='fa-solid fa-triangle-exclamation me-1 small'></i> ".htmlspecialchars(strtoupper($d['kondisi'] ?? '-'), ENT_QUOTES, 'UTF-8')."</span>"; }
                            ?>
                        </td>
                        <td><i class="fa-solid fa-map-pin text-danger opacity-70 me-2 small"></i><?= htmlspecialchars($d['lokasi_penyimpanan']); ?></td>
                    </tr>
                    <?php } } else { ?>
                    <tr>
                        <td colspan="8" class="text-center py-5" style="color: var(--text-muted) !important;">
                            <i class="fa-solid fa-satellite-dish d-block fs-1 mb-3 text-muted opacity-25"></i> Data kosong atau tidak ditemukan.
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.dropdown-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            this.classList.toggle('active');
            const container = this.nextElementSibling;
            container.style.display = container.style.display === "block" ? "none" : "block";
        });
    });
</script>
</body>
</html>