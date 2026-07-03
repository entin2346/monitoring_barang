<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}
include "../config/koneksi.php";

$cari = $_GET['cari'] ?? '';
$cari_clean = trim(mysqli_real_escape_string($conn, urldecode($cari)));

// Kunci filter untuk kategori STOK
$whereClause = "(jenis_kategori LIKE '%stok%' OR jenis_kategori LIKE '%stock%') AND jenis_kategori NOT LIKE '%non%'";
if ($cari_clean !== '') {
    $whereClause .= " AND (nama_material LIKE '%$cari_clean%')";
}

$limit = 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM material_gudang WHERE $whereClause");
$total_data = mysqli_fetch_assoc($total_query)['total'];

$stok_query = mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM material_gudang WHERE $whereClause");
$total_stok = mysqli_fetch_assoc($stok_query)['total'] ?? 0;

$query = mysqli_query($conn, "SELECT * FROM material_gudang WHERE $whereClause ORDER BY nama_material ASC LIMIT $offset, $limit");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>I-CALM | Kategori Stok</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --bg-body: #f4f7fc; --bg-card: #ffffff; --primary: #0284c7; --text-main: #0f172a; --text-muted: #64748b; --border-color: rgba(148, 163, 184, 0.12); --bg-sidebar: #d0e1f9; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: var(--bg-body); color: var(--text-main); min-height: 100vh; }
        .sidebar { position: fixed; left: 0; top: 0; width: 260px; height: 100%; background-color: var(--bg-sidebar); border-right: 1px solid rgba(2, 132, 199, 0.15); padding: 35px 20px; z-index: 1050; display: flex; flex-direction: column; overflow-y: auto; }
        .sidebar h3 { font-size: 1.25rem; font-weight: 800; color: #1e3a8a; margin-bottom: 35px; display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
        .sidebar a, .dropdown-btn { display: flex; align-items: center; justify-content: space-between; color: #1e3a8a; text-decoration: none; padding: 11px 14px; font-size: 0.9rem; font-weight: 700; border: none; background: transparent; width: 100%; cursor: pointer; border-radius: 10px; margin-bottom: 5px; transition: all 0.2s; }
        .sidebar a:hover, .dropdown-btn:hover { color: #025a9c; background: rgba(2, 132, 199, 0.12); transform: translateX(4px); }
        .sidebar .menu-content-wrapper { display: flex; align-items: center; gap: 12px; }
        .sidebar a i, .dropdown-btn i.menu-icon { font-size: 1.05rem; width: 20px; text-align: center; color: #1e40af; }
        .sidebar .active-menu { color: #ffffff !important; background: #0284c7 !important; font-weight: 700; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); }
        .sidebar .active-menu i { color: #ffffff !important; }
        .dropdown-chevron { font-size: 0.75rem !important; transition: transform 0.2s ease; color: #1e40af !important; }
        .dropdown-btn.active .dropdown-chevron { transform: rotate(180deg); }
        .dropdown-container { display: none; padding-left: 12px; margin-bottom: 6px; margin-top: 4px; }
        .dropdown-container a { padding: 9px 14px; font-size: 0.85rem; color: #1e40af; font-weight: 600; background: rgba(255, 255, 255, 0.2); border-radius: 8px; margin-bottom: 3px; }
        .dropdown-container a:hover { background: #ffffff; color: #0284c7; }
        .sidebar .logout-button { margin-top: auto; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; flex-shrink: 0; }
        .sidebar .logout-button i, .sidebar .logout-button span { color: #b91c1c !important; }
        .content { margin-left: 260px; padding: 0; }
        .navbar-custom { background: #ffffff; padding: 20px 40px; border-bottom: 1px solid var(--border-color); }
        .main-body-wrapper { padding: 40px; }
        .glass-stat-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 26px; border-left: 5px solid var(--primary); }
        .stat-label { font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted); }
        .stat-number { font-size: 2rem; font-weight: 800; }
        .cyber-search-box { background: #ffffff; border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; }
        .input-cyber-group { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; overflow: hidden; }
        .cyber-table-wrapper { border: 1px solid var(--border-color); border-radius: 16px; overflow: hidden; }
        .table-cyber { width: 100%; border-collapse: separate; border-spacing: 0; }
        .table-cyber th { background: #f8fafc; padding: 16px 22px; font-size: 0.72rem; text-transform: uppercase; font-weight: 700; border-bottom: 1px solid var(--border-color); }
        .table-cyber td { padding: 15px 22px; font-size: 0.88rem; border-bottom: 1px solid var(--border-color); }
        .badge-kat { padding: 5px 10px; font-size: 0.78rem; font-weight: 700; border-radius: 6px; text-transform: uppercase; }
        .kat-stock { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
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
        <a href="../material/index.php">Semua Material Gudang</a>
        <a href="../ba/index.php">Database BA</a>
    </div>

    <button class="dropdown-btn active">
        <span class="menu-content-wrapper"><i class="fa-solid fa-tags menu-icon"></i><span>Kategori</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container" style="display: block;">
        <a href="stok.php" class="active-menu">Stok</a>
        <a href="non_stok.php">Non Stok</a>
        <a href="non_po.php">Non PO</a>
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
            <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-boxes-stacked text-primary me-2"></i> KENDALI LOGISTIK / Kategori: Stok</span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="row g-3 mb-4">
            <div class="col-md-6"><div class="glass-stat-card"><div class="stat-label">Total Item</div><div class="stat-number"><?= number_format($total_data); ?></div></div></div>
            <div class="col-md-6"><div class="glass-stat-card" style="border-left-color: #10b981;"><div class="stat-label">Total Volume Stok</div><div class="stat-number" style="color: #10b981;"><?= number_format($total_stok); ?></div></div></div>
        </div>

        <div class="cyber-search-box mb-4">
            <form method="GET">
                <div class="row g-3">
                    <div class="col-md-10">
                        <div class="input-group input-cyber-group">
                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="cari" class="form-control" placeholder="Cari nama material di kategori Stok..." value="<?= htmlspecialchars($cari_clean); ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Saring</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="cyber-table-wrapper table-responsive">
            <table class="table-cyber">
                <thead>
                    <tr>
                        <th width="60" class="text-center">NO</th>
                        <th>NAMA MATERIAL GUDANG</th>
                        <th>KATEGORI</th>
                        <th>SATUAN</th>
                        <th>JUMLAH STOK</th>
                        <th>NOMOR RAK</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = $offset + 1;
                    if(mysqli_num_rows($query) > 0){
                        while($d = mysqli_fetch_assoc($query)){
                    ?>
                    <tr>
                        <td class="text-center fw-bold"><?= $no++; ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($d['nama_material']); ?></td>
                        <td><span class="badge-kat kat-stock">Stok</span></td>
                        <td><?= htmlspecialchars($d['satuan']); ?></td>
                        <td><span class="badge bg-primary px-3 py-2"><?= number_format($d['jumlah']); ?></span></td>
                        <td><?= htmlspecialchars($d['no_rak']); ?></td>
                    </tr>
                    <?php } } else { echo "<tr><td colspan='6' class='text-center py-4 text-muted'>Data kosong atau tidak ditemukan.</td></tr>"; } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.dropdown-btn').forEach(button => {
        button.addEventListener('click', function() {
            this.classList.toggle('active');
            const container = this.nextElementSibling;
            container.style.display = container.style.display === "block" ? "none" : "block";
        });
    });
</script>
</body>
</html>