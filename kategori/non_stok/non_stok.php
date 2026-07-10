<?php
session_start();
if(!isset($_SESSION['login'])){
    // PERBAIKAN: Naik 2 tingkat ke folder login
    header("Location: ../../login/index.php");
    exit;
}
// PERBAIKAN: Naik 2 tingkat untuk mengambil file koneksi database
include "../../config/koneksi.php";

// Ambil parameter pencarian dan bersihkan bug '+' dari URL
$cari = $_GET['cari'] ?? '';
$cari_clean = trim(mysqli_real_escape_string($conn, urldecode($cari)));

// KUNCI FILTER: Diselaraskan dengan logika index.php (Membaca teks kategori ATAU ID > 63)
$whereClause = "
(
    id BETWEEN 64 AND 467
)
OR
(
    id > 467
    AND UPPER(TRIM(jenis_kategori)) IN ('NON STOCK','NON STOK','NON-STOCK')
)";
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
$total_data = mysqli_fetch_assoc($total_query)['total'] ?? 0;
$total_halaman = ceil($total_data / $limit);

// Hitung akumulasi volume stok khusus kategori Non Stok
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
    die(mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Kategori Non Stok</title>
    
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
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fa-solid fa-bolt text-primary"></i> I-CALM Panel</h3>
    
    <a href="../../dashboard/index.php">
        <span class="menu-content-wrapper"><i class="fa-solid fa-chart-pie"></i><span>Dashboard</span></span>
    </a>
    
    <button class="dropdown-btn">
        <span class="menu-content-wrapper"><i class="fa-solid fa-layer-group menu-icon"></i><span>Monitoring</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../../material/index.php">Material Gudang</a>
        <a href="../../ba/index.php">Database BA</a>
    </div>

    <button class="dropdown-btn active">
        <span class="menu-content-wrapper"><i class="fa-solid fa-tags menu-icon"></i><span>Kategori</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container" style="display: block;">
        <a href="../stok/stok.php">Stok</a>
        <a href="non_stok.php" class="active-menu">Non Stok</a>
        <a href="../non_po/non_po.php">Non PO</a>
        <a href="../ex_bongkaran/ex_bongkaran.php">Ex Bongkaran</a>
        <a href="../pre_memory/pre_memory.php">Pre Memory</a>
        <a href="../peminjaman/peminjaman.php">Peminjaman</a>
        <a href="../pemakaian/pemakaian.php">Pemakaian</a>
    </div>

    <button class="dropdown-btn">
        <span class="menu-content-wrapper"><i class="fa-solid fa-file-import menu-icon"></i><span>Import</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../../import/material.php">Import Material</a>
        <a href="../../import/ba.php">Import BA</a>
        <a href="../../import/form_stok.php">Import Stok</a>
        <a href="../../import/form_non_stok.php">Import Non Stok</a>
        <a href="../../import/form_non_po.php">Import Non PO</a>
        <a href="../../import/form_ex_bongkaran.php">Import Ex Bongkaran</a>
        <a href="../../import/form_pre_memory.php">Import Pre Memory</a>
        <a href="../../import/form_peminjaman.php">Import Peminjaman</a>
        <a href="../../import/form_pemakaian.php">Import Pemakaian</a>
    </div>

    <button class="dropdown-btn">
        <span class="menu-content-wrapper"><i class="fa-solid fa-file-export menu-icon"></i><span>Export</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../../export/material_excel.php">Export Material</a>
        <a href="../../export/ba_excel.php">Export BA</a>
    </div>
    
    <a href="../../login/logout.php" class="logout-button">
        <span class="menu-content-wrapper"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></span>
    </a>
</div>

<div class="content">
    <nav class="navbar navbar-custom">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center">
                <i class="fa-solid fa-boxes-stacked text-primary me-2"></i> KENDALI LOGISTIK 
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Kategori: Non Stok</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="glass-stat-card">
                    <div class="stat-label">Total Jenis Material Non Stok</div>
                    <div class="stat-number"><?= number_format($total_data); ?> <span class="fw-normal text-muted" style="font-size: 1.1rem;">Item</span></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-stat-card" style="border-left-color: #10b981;">
                    <div class="stat-label">Volume Akumulasi Fisik</div>
                    <div class="stat-number" style="color: #10b981;"><?= number_format($total_stok); ?> <span class="fw-normal text-muted" style="font-size: 1.1rem;">Unit</span></div>
                </div>
            </div>
        </div>

        <div class="cyber-search-box mb-4 d-flex justify-content-between align-items-center">
            <form method="GET" class="w-70 me-3" style="flex: 1;">
                <div class="input-group input-cyber-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="cari" class="form-control" autocomplete="off" placeholder="Cari material khusus Non Stok..." value="<?= htmlspecialchars($cari_clean); ?>">
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Saring</button>
                </div>
            </form>
            <a href="tambah.php" class="btn btn-success fw-bold py-2 px-4" style="border-radius:12px;"><i class="fa-solid fa-plus me-2"></i>Tambah Data</a>
        </div>

        <div class="cyber-table-wrapper table-responsive mb-4">
            <table class="table-cyber">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>AKSI</th>
                        <th>JENIS BA</th>
                        <th>TANGGAL</th>
                        <th>NAMA BARANG</th>
                        <th>MERK/JENIS</th>
                        <th>JENIS BARANG</th>
                        <th>SUMBER BARANG</th>
                        <th>SATUAN</th>
                        <th>JUMLAH</th>
                        <th>TUJUAN</th>
                        <th>KONDISI</th>
                        <th>VENDOR</th>
                        <th>BERITA ACARA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = $offset + 1;
                    if(mysqli_num_rows($query) > 0){
                        while($d = mysqli_fetch_assoc($query)){
                    ?>
                    <tr>
                        <td><?= str_pad($no++, 2, '0', STR_PAD_LEFT); ?></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="detail.php?id=<?= $d['id']; ?>" class="btn btn-info btn-sm text-white"><i class="fa-solid fa-eye"></i></a>
                                <a href="edit.php?id=<?= $d['id']; ?>" class="btn btn-warning btn-sm text-white"><i class="fa-solid fa-pen"></i></a>
                                <a href="hapus.php?id=<?= $d['id']; ?>" class="btn btn-danger btn-sm text-white" onclick="return confirm('Yakin ingin menghapus data ini?')"><i class="fa-solid fa-trash"></i></a>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($d['jenis_ba'] ?? ''); ?></td>
                        <td><?= !empty($d['tanggal']) ? date('d-m-Y', strtotime($d['tanggal'])) : '-'; ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($d['nama_material'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($d['merk_jenis'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($d['jenis_barang'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($d['sumber_barang'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($d['satuan'] ?? ''); ?></td>
                        <td><span class="neon-badge-stock"><?= number_format((int)($d['jumlah'] ?? 0)); ?></span></td>
                        <td><?= htmlspecialchars($d['tujuan'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($d['kondisi'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($d['vendor'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($d['berita_acara'] ?? ''); ?></td>
                    </tr>
                    <?php } } else { ?>
                    <tr>
                        <td colspan="14" class="text-center py-5" style="color: var(--text-muted) !important;">
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