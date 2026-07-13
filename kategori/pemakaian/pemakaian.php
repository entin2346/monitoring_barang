<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: /monitoring_barang/login/index.php");
    exit;
}
include "../../config/koneksi.php";

// Ambil parameter pencarian dan bersihkan bug '+' dari URL
$cari = $_GET['cari'] ?? '';
$cari_clean = trim(mysqli_real_escape_string($conn, urldecode($cari)));

// KUNCI FILTER: Hanya menarik data kategori PEMAKAIAN
$whereClause = "(jenis_kategori LIKE '%pemakaian%')";
if ($cari_clean !== '') {
    $whereClause .= " AND (
        nama_material LIKE '%$cari_clean%' OR
        merk_jenis LIKE '%$cari_clean%' OR
        vendor LIKE '%$cari_clean%' OR
        tujuan LIKE '%$cari_clean%' OR
        sumber_barang LIKE '%$cari_clean%'
    )";
}

// Fitur Mutasi Halaman (Pagination)
$limit = 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1){ $page = 1; }
$offset = ($page - 1) * $limit;

// Hitung total item hasil filter
$total_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM material_gudang WHERE $whereClause");
$total_data = mysqli_fetch_assoc($total_query)['total'] ?? 0;
$total_halaman = ceil($total_data / $limit);

// Hitung akumulasi volume stok khusus Pemakaian
$stok_query = mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM material_gudang WHERE $whereClause");
$total_stok = mysqli_fetch_assoc($stok_query)['total'] ?? 0;

// Ambil data dari database dengan pembatasan halaman
$query = mysqli_query($conn, "
    SELECT * FROM material_gudang 
    WHERE $whereClause 
    ORDER BY tanggal DESC, nama_material ASC
    LIMIT $offset, $limit
");

if(!$query){
    die("Query Gagal: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Kategori Pemakaian</title>
    
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
        body { background: var(--bg-body); color: var(--text-main); min-height: 100vh; overflow-x: hidden; }

        /* SIDEBAR STYLE */
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
        .sidebar a:hover i, .dropdown-btn:hover i.menu-icon { color: #025a9c; }
        
        .sidebar .active-menu {
            color: #ffffff !important; background: #0284c7 !important; font-weight: 700;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); border-radius: 10px; transform: translateX(4px);
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

        .sidebar .logout-button { margin-top: auto; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; }
        .sidebar .logout-button i, .sidebar .logout-button span { color: #b91c1c !important; }
        .sidebar .logout-button:hover { background: #fee2e2; transform: none; }

        /* CONTENT WRAPPER */
        .content { margin-left: 260px; position: relative; min-width: 0; }
        .navbar-custom { background: #ffffff; padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999; }
        .navbar-custom .navbar-brand { color: var(--text-main); font-weight: 800; font-size: 1.3rem; }
        .main-body-wrapper { padding: 40px; }

        .glass-stat-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 26px; border-left: 5px solid var(--primary); }
        .stat-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-muted); font-weight: 700; margin-bottom: 8px; }
        .stat-number { font-size: 2rem; font-weight: 800; color: var(--text-main); margin: 0; }

        .cyber-search-box { background: #ffffff; border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; }
        
        /* SCROLLABLE TABLE CSS */
        .cyber-table-wrapper { 
            border: 1px solid var(--border-color); 
            border-radius: 16px; 
            overflow-x: auto; 
            background: #ffffff;
            width: 100%;
        }
        .table-cyber { width: 100%; border-collapse: separate; border-spacing: 0; margin: 0; }
        .table-cyber thead th { 
            background: #f8fafc !important; 
            color: #334155 !important; 
            font-weight: 700; 
            text-transform: uppercase; 
            font-size: 0.72rem; 
            letter-spacing: 0.5px; 
            padding: 16px 22px; 
            border-bottom: 1px solid var(--border-color); 
            white-space: nowrap;
        }
        .table-cyber tbody tr:not(:last-child) td { border-bottom: 1px solid var(--border-color); }
        .table-cyber tbody tr:hover td { background: #f8fafc; }
        .table-cyber tbody td { 
            padding: 15px 22px; 
            font-size: 0.88rem; 
            vertical-align: middle; 
            color: var(--text-main) !important; 
            white-space: nowrap;
        }

        .neon-badge-stock { background: rgba(2, 132, 199, 0.06) !important; color: var(--primary) !important; border: 1px solid rgba(2, 132, 199, 0.1) !important; border-radius: 8px; padding: 5px 12px; font-weight: 700; font-size: 0.8rem; display: inline-block; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fa-solid fa-bolt text-primary"></i> I-CALM Panel</h3>
    
    <a href="/monitoring_barang/dashboard/index.php">
        <span class="menu-content-wrapper"><i class="fa-solid fa-chart-pie"></i><span>Dashboard</span></span>
    </a>
    
    <button class="dropdown-btn">
        <span class="menu-content-wrapper"><i class="fa-solid fa-layer-group menu-icon"></i><span>Monitoring</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="/monitoring_barang/material/index.php">Material Gudang</a>
        <a href="/monitoring_barang/ba/index.php">Database BA</a>
    </div>

    <button class="dropdown-btn active">
        <span class="menu-content-wrapper"><i class="fa-solid fa-tags menu-icon"></i><span>Kategori</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container" style="display: block;">
        <a href="/monitoring_barang/kategori/stok/stok.php">Stok</a>
        <a href="/monitoring_barang/kategori/non_stok/non_stok.php">Non Stok</a>
        <a href="/monitoring_barang/kategori/non_po/non_po.php">Non PO</a>
        <a href="/monitoring_barang/kategori/ex_bongkaran/ex_bongkaran.php">Ex Bongkaran</a>
        <a href="/monitoring_barang/kategori/pre_memory/pre_memory.php">Pre Memory</a>
        <a href="/monitoring_barang/kategori/peminjaman/peminjaman.php">Peminjaman</a>
        <a href="/monitoring_barang/kategori/pemakaian/pemakaian.php" class="active-menu">Pemakaian</a>
    </div>

    <button class="dropdown-btn">
        <span class="menu-content-wrapper"><i class="fa-solid fa-file-import menu-icon"></i><span>Import</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="/monitoring_barang/import/material.php">Import Material</a>
        <a href="/monitoring_barang/import/ba.php">Import BA</a>
        <a href="/monitoring_barang/import/form_stok.php">Import Stok</a>
        <a href="/monitoring_barang/import/form_non_stok.php">Import Non Stok</a>
        <a href="/monitoring_barang/import/form_non_po.php">Import Non PO</a>
        <a href="/monitoring_barang/import/form_ex_bongkaran.php">Import Ex Bongkaran</a>
        <a href="/monitoring_barang/import/form_pre_memory.php">Import Pre Memory</a>
        <a href="/monitoring_barang/import/form_peminjaman.php">Import Peminjaman</a>
        <a href="/monitoring_barang/import/form_pemakaian.php">Import Pemakaian</a>
    </div>

    <button class="dropdown-btn">
        <span class="menu-content-wrapper"><i class="fa-solid fa-file-export menu-icon"></i><span>Export</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="/monitoring_barang/export/material_excel.php">Export Material</a>
        <a href="/monitoring_barang/export/ba_excel.php">Export BA</a>
    </div>
    
    <a href="/monitoring_barang/login/logout.php" class="logout-button">
        <span class="menu-content-wrapper"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></span>
    </a>
</div>

<div class="content">
    <nav class="navbar navbar-custom">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center">
                <i class="fa-solid fa-boxes-stacked text-primary me-2"></i> KENDALI LOGISTIK 
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Kategori: Pemakaian</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="glass-stat-card">
                    <div class="stat-label">Total Jenis Material Pemakaian</div>
                    <div class="stat-number"><?= number_format((float)$total_data); ?> <span class="fw-normal text-muted" style="font-size: 1.1rem;">Item</span></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-stat-card" style="border-left-color: #10b981;">
                    <div class="stat-label">Volume Akumulasi Terpakai</div>
                    <div class="stat-number" style="color: #10b981;"><?= number_format((float)$total_stok); ?> <span class="fw-normal text-muted" style="font-size: 1.1rem;">Unit</span></div>
                </div>
            </div>
        </div>

        <!-- Bagian Input Saring & Tambah Sesuai Ukuran Gambar -->
        <div class="cyber-search-box mb-4 d-flex justify-content-between align-items-center gap-3">
            <form method="GET" class="d-flex gap-2" style="flex: 1;">
                <div class="input-group" style="flex: 1;">
                    <span class="input-group-text bg-white border-end-0 px-3" style="border-radius: 12px 0 0 12px; border-color: #cbd5e1; height: 46px; color: #64748b;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="cari" class="form-control border-start-0 ps-1 pr-3" autocomplete="off" placeholder="Cari material khusus Pemakaian..." value="<?= htmlspecialchars($cari_clean); ?>" style="border-radius: 0 12px 12px 0; border-color: #cbd5e1; height: 46px; background-color: #fff;">
                </div>
                <button type="submit" class="btn btn-primary px-4 fw-bold d-flex align-items-center gap-2" style="border-radius: 12px; background-color: #0d6efd; border: none; height: 46px; white-space: nowrap;">
                    <i class="fa-solid fa-sliders"></i> Saring
                </button>
            </form>
            <a href="tambah.php" class="btn btn-success fw-bold px-4 d-flex align-items-center gap-2" style="border-radius: 12px; background-color: #059669; border: none; height: 46px; white-space: nowrap;">
                <i class="fa-solid fa-plus"></i> Tambah
            </a>
        </div>

        <div class="cyber-table-wrapper mb-4">
            <table class="table-cyber">
                <thead>
                    <tr>
                        <th width="60" class="text-center">NO</th>
                        <th>NAMA MATERIAL GUDANG</th>
                        <th>KATEGORI</th>
                        <th>SATUAN</th>
                        <th>JUMLAH TERPAKAI</th>
                        <th>NOMOR RAK</th>
                        <th width="120" class="text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = $offset + 1;
                    if(mysqli_num_rows($query) > 0){
                        while($d = mysqli_fetch_assoc($query)){
                    ?>
                    <tr>
                        <td class="text-center"><?= str_pad($no++, 2, '0', STR_PAD_LEFT); ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($d['nama_material'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($d['jenis_kategori'] ?? 'Pemakaian'); ?></td>
                        <td><?= htmlspecialchars($d['satuan'] ?? ''); ?></td>
                        <td><span class="neon-badge-stock"><?= number_format((float)($d['jumlah'] ?? 0)); ?></span></td>
                        <td><?= htmlspecialchars($d['no_rak'] ?? $d['sumber_barang'] ?? '-'); ?></td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="detail.php?id=<?= $d['id']; ?>" class="btn btn-info btn-sm text-white" title="Detail"><i class="fa-solid fa-eye"></i></a>
                                <a href="edit.php?id=<?= $d['id']; ?>" class="btn btn-warning btn-sm text-white" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                <a href="hapus.php?id=<?= $d['id']; ?>" class="btn btn-danger btn-sm text-white" title="Hapus" onclick="return confirm('Yakin ingin menghapus data ini?')"><i class="fa-solid fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php } } else { ?>
                    <tr>
                        <td colspan="7" class="text-center py-5" style="color: var(--text-muted) !important;">
                            <i class="fa-solid fa-satellite-dish d-block fs-1 mb-3 text-muted opacity-25"></i> Data kosong atau tidak ditemukan.
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        
        <?php if($total_halaman > 1): ?>
        <nav class="d-flex justify-content-center mt-4">
            <ul class="pagination">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?= $page-1; ?>&cari=<?= urlencode($cari_clean); ?>">&laquo;</a>
                </li>
                <?php for($i=1; $i<=$total_halaman; $i++): ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?= $i; ?>&cari=<?= urlencode($cari_clean); ?>"><?= $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $total_halaman) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?= $page+1; ?>&cari=<?= urlencode($cari_clean); ?>">&raquo;</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
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
</script>
</body>
</html>