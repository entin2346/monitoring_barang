<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}
include "../../config/koneksi.php";

$cari = $_GET['cari'] ?? '';
$cari_clean = trim(mysqli_real_escape_string($conn, urldecode($cari)));

// Kunci filter untuk kategori PEMINJAMAN
$whereClause = "1=1";

if ($cari_clean != '') {
    $whereClause .= " AND (
        nama_material LIKE '%$cari_clean%'
        OR peminjam LIKE '%$cari_clean%'
    )";
}

$limit = 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1){
    $page = 1;
}

$offset = ($page - 1) * $limit;

$total_query = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM peminjaman
WHERE $whereClause
");

$total_data = mysqli_fetch_assoc($total_query)['total'];

$total_halaman = ceil($total_data/$limit);

$stok_query = mysqli_query($conn,"
SELECT SUM(jumlah) AS total
FROM peminjaman
WHERE $whereClause
");

$total_stok = mysqli_fetch_assoc($stok_query)['total'];

$query = mysqli_query($conn,"
SELECT *
FROM peminjaman
WHERE $whereClause
ORDER BY nama_material ASC
LIMIT $offset,$limit
");

if(!$query){
    die(mysqli_error($conn));
}

if(!$query){
    die("Query Gagal: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Kategori Peminjaman</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --bg-body: #f4f7fc; --bg-card: #ffffff; --primary: #0284c7; --text-main: #0f172a; --text-muted: #64748b; --border-color: rgba(148, 163, 184, 0.12); --bg-sidebar: #d0e1f9; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: var(--bg-body); color: var(--text-main); min-height: 100vh; overflow-x: hidden; }
        .sidebar { position: fixed; left: 0; top: 0; width: 260px; height: 100%; background-color: var(--bg-sidebar); border-right: 1px solid rgba(2, 132, 199, 0.15); padding: 35px 20px; z-index: 1050; display: flex; flex-direction: column; overflow-y: auto; }
        .sidebar h3 { font-size: 1.25rem; font-weight: 800; color: #1e3a8a; margin-bottom: 35px; display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
        .sidebar a, .dropdown-btn { display: flex; align-items: center; justify-content: space-between; color: #1e3a8a; text-decoration: none; padding: 11px 14px; font-size: 0.9rem; font-weight: 700; border: none; background: transparent; width: 100%; cursor: pointer; border-radius: 10px; margin-bottom: 5px; transition: all 0.2s; }
        .sidebar a:hover, .dropdown-btn:hover { color: #025a9c; background: rgba(2, 132, 199, 0.12); transform: translateX(4px); }
        .sidebar .menu-content-wrapper { display: flex; align-items: center; gap: 12px; }
        .sidebar a i, .dropdown-btn i.menu-icon { font-size: 1.05rem; width: 20px; text-align: center; color: #1e40af; }
        
        .dropdown-container .active-menu { 
            color: #ffffff !important; 
            background: #0284c7 !important; 
            font-weight: 700; 
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); 
            border-radius: 10px; 
        }
        .dropdown-container .active-menu i { 
            color: #ffffff !important; 
        }
        
        .dropdown-chevron { font-size: 0.75rem !important; transition: transform 0.2s ease; color: #1e40af !important; }
        .dropdown-btn.active .dropdown-chevron { transform: rotate(180deg); color: #ffffff !important; }
        .dropdown-btn.active { color: #ffffff !important; background: #0284c7 !important; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); }
        .dropdown-btn.active i.menu-icon { color: #ffffff !important; }
        
        .dropdown-container { display: none; padding-left: 12px; margin-bottom: 6px; margin-top: 4px; }
        .dropdown-container a { padding: 9px 14px; font-size: 0.85rem; color: #1e40af; font-weight: 600; background: rgba(255, 255, 255, 0.2); border-radius: 8px; margin-bottom: 3px; }
        .dropdown-container a:hover { background: #ffffff; color: #0284c7; }
        .sidebar .logout-button { margin-top: auto; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; flex-shrink: 0; }
        .sidebar .logout-button i, .sidebar .logout-button span { color: #b91c1c !important; }
        .content { margin-left: 260px; position: relative; width: calc(100% - 260px); }
        .navbar-custom { background: #ffffff; padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999; }
        .navbar-custom .navbar-brand { color: var(--text-main); font-weight: 800; font-size: 1.3rem; }
        .main-body-wrapper { padding: 40px; }
        .glass-stat-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 26px; border-left: 5px solid var(--primary); }
        .stat-label { font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted); }
        .stat-number { font-size: 2rem; font-weight: 800; }
        .cyber-search-box { background: #ffffff; border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; }
        .input-cyber-group { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; overflow: hidden; }
        
        .cyber-table-wrapper { border: 1px solid var(--border-color); border-radius: 16px; overflow-x: auto; background: #fff; width: 100%; display: block; }
        .table-cyber { width: 100%; border-collapse: separate; border-spacing: 0; margin: 0; white-space: nowrap; }
        .table-cyber th { background: #f8fafc; padding: 12px 16px; font-size: 0.72rem; text-transform: uppercase; font-weight: 700; border: 1px solid #e2e8f0; text-align: center; vertical-align: middle; }
        .table-cyber td { padding: 12px 16px; font-size: 0.85rem; border: 1px solid #e2e8f0; vertical-align: middle; }
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
        <a href="/monitoring_barang/kategori/peminjaman/peminjaman.php" class="active-menu">Peminjaman</a>
        <a href="/monitoring_barang/kategori/pemakaian/pemakaian.php">Pemakaian</a>
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
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Kategori: Peminjaman</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="glass-stat-card">
                    <div class="stat-label">Total Item</div>
                    <div class="stat-number"><?= number_format((float)$total_data); ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-stat-card" style="border-left-color: #f59e0b;">
                    <div class="stat-label">Total Volume Peminjaman</div>
                    <div class="stat-number" style="color: #f59e0b;"><?= number_format((float)$total_stok); ?></div>
                </div>
            </div>
        </div>

        <div class="cyber-search-box mb-4">
            <form method="GET">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="input-group input-cyber-group">
                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="cari" class="form-control" placeholder="Cari nama material atau peminjam..." value="<?= htmlspecialchars($cari_clean); ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2" style="border-radius: 12px; background: linear-gradient(135deg, #0284c7, #2563eb); border: none; height:100%;">Saring</button>
                    </div>
                 <?php if(strtolower($_SESSION['role']) == 'admin'){ ?>

<div class="col-md-2">
    <a href="tambah.php" class="btn btn-success w-100 fw-bold py-2 d-flex align-items-center justify-content-center"
       style="border-radius: 12px; background: linear-gradient(135deg, #10b981, #059669); border: none; height:100%;">
        <i class="fa-solid fa-plus me-1"></i> Tambah
    </a>
</div>

<?php } ?>
                </div>
            </form>
        </div>

        <div class="cyber-table-wrapper table-responsive mb-4">
            <table class="table-cyber">
                <thead>
                    <tr>
                        <th rowspan="2" width="50">NO</th>
                        <th rowspan="2">MATERIAL</th>
                        <th rowspan="2">ASAL MATERIAL</th>
                        <th rowspan="2">TANGGAL PENGAMBILAN</th>
                        <th rowspan="2">PEMINJAM MATERIAL</th>
                        <th rowspan="2">JUMLAH</th>
                        <th rowspan="2">SATUAN</th>
                        <th colspan="2">PENGEMBALIAN MATERIAL</th>
                        <th colspan="2">LINK BA</th>
                        <th rowspan="2">DOKUMENTASI</th>
                        <th rowspan="2">KETERANGAN</th>
                        <th rowspan="2" width="120">AKSI</th>
                    </tr>
                    <tr>
                        <th>STATUS</th>
                        <th>JUMLAH DIKEMBALIKAN</th>
                        <th>LINK BA PENGAMBILAN MATERIAL</th>
                        <th>LINK BA PENGEMBALIAN MATERIAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = $offset + 1;
                    if(mysqli_num_rows($query) > 0){
                        while($d = mysqli_fetch_assoc($query)){
                    ?>
                    <tr>
                        <td class="text-center fw-bold"><?= str_pad($no++, 2, '0', STR_PAD_LEFT); ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($d['nama_material'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['asal_material'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['tanggal_pengambilan'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['peminjam'] ?? '-'); ?></td>
                        <td><span class="badge bg-warning text-dark px-2 py-1"><?= number_format((float)($d['jumlah'] ?? 0)); ?></span></td>
                        <td><?= htmlspecialchars($d['satuan'] ?? '-'); ?></td>
                        
                        <td>
                            <?php
                            $status = strtoupper(trim($d['status_kembali'] ?? ''));
                            if ($status == 'SUDAH') {
                                echo '<span class="badge bg-success">SUDAH</span>';
                            } else {
                                echo '<span class="badge bg-warning text-dark">BELUM</span>';
                            }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($d['jumlah_dikembalikan'] ?? '-'); ?></td>
                        
                        <td>
                            <?php if(!empty($d['link_ba_ambil'])): ?>
                                <a href="<?= htmlspecialchars($d['link_ba_ambil']); ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-link"></i> BA Ambil</a>
                            <?php else: ?> - <?php endif; ?>
                        </td>
                        <td>
                            <?php if(!empty($d['link_ba_kembali'])): ?>
                                <a href="<?= htmlspecialchars($d['link_ba_kembali']); ?>" target="_blank" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-link"></i> BA Kembali</a>
                            <?php else: ?> - <?php endif; ?>
                        </td>
                        
                        <td>
                            <?php if(!empty($d['dokumentasi'])): ?>
                                <a href="<?= htmlspecialchars($d['dokumentasi']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-image"></i> Lihat</a>
                            <?php else: ?> - <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($d['keterangan'] ?? '-'); ?></td>
                     <td class="text-center">
    <div class="btn-group gap-1">

        <!-- Semua user boleh melihat detail -->
        <a href="detail.php?id=<?= $d['id']; ?>"
           class="btn btn-xs btn-outline-info rounded-2 p-1 px-2"
           style="font-size:0.75rem;"
           title="Detail">
            <i class="fa-solid fa-eye"></i>
        </a>

        <?php if(strtolower($_SESSION['role']) == 'admin'){ ?>

            <a href="edit.php?id=<?= $d['id']; ?>"
               class="btn btn-xs btn-outline-warning rounded-2 p-1 px-2"
               style="font-size:0.75rem;"
               title="Edit">
                <i class="fa-solid fa-pen-to-square"></i>
            </a>

            <a href="hapus.php?id=<?= $d['id']; ?>"
               class="btn btn-xs btn-outline-danger rounded-2 p-1 px-2"
               style="font-size:0.75rem;"
               title="Hapus"
               onclick="return confirm('Apakah Anda yakin ingin menghapus data peminjaman ini?');">
                <i class="fa-solid fa-trash-can"></i>
            </a>

        <?php } ?>

    </div>
</td>
                    </tr>
                    <?php } } else { ?>
                    <tr>
                        <td colspan="14" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-satellite-dish d-block fs-1 mb-3 opacity-25"></i> Data kosong atau tidak ditemukan.
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php if($total_halaman > 1): ?>
        <nav aria-label="Navigasi Halaman" class="mb-4">
            <ul class="pagination justify-content-center gap-2">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link border-0 rounded-3 px-3 py-2 text-dark bg-white shadow-sm" href="?cari=<?= urlencode($cari_clean); ?>&page=<?= $page - 1; ?>"><i class="fa-solid fa-chevron-left"></i></a>
                </li>
                <?php for($i = 1; $i <= $total_halaman; $i++): ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link border-0 rounded-3 px-3 py-2 shadow-sm <?= ($page == $i) ? 'bg-primary text-white font-weight-bold' : 'bg-white text-dark'; ?>" href="?cari=<?= urlencode($cari_clean); ?>&page=<?= $i; ?>"><?= $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $total_halaman) ? 'disabled' : ''; ?>">
                    <a class="page-link border-0 rounded-3 px-3 py-2 text-dark bg-white shadow-sm" href="?cari=<?= urlencode($cari_clean); ?>&page=<?= $page + 1; ?>"><i class="fa-solid fa-chevron-right"></i></a>
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
            this.classList.toggle('active');
            const container = this.nextElementSibling;
            container.style.display = container.style.display === "block" ? "none" : "block";
        });
    });
</script>
</body>
</html>