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
    <title>I-CALM | Database BA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-deep: #060814;
            --bg-dark: #090d16;
            --bg-surface: #0f132a;
            --bg-card: #151b3d;
            --primary-glow: #38bdf8;
            --secondary-glow: #818cf8;
            --emerald-glow: #34d399;
            --rose-glow: #f43f5e;
            --text-main: #f8fafc;
            --text-sub: #94a3b8;
            --border-glass: rgba(255, 255, 255, 0.08);
        }

        /* Custom Scrollbar Bertema Cyber */
        ::-webkit-scrollbar { width: 8px; height: 10px; }
        ::-webkit-scrollbar-track { background: var(--bg-dark); }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 20px; border: 2px solid var(--bg-dark); }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary-glow); }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body { 
            background: radial-gradient(circle at top right, #111638 0%, var(--bg-deep) 60%) !important;
            color: var(--text-main);
            min-height: 100vh;
        }

        /* SIDEBAR PERSISTEN SISI KIRI */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100%;
            background: #111827;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 28px;
            z-index: 1050;
            box-shadow: 10px 0 30px rgba(0,0,0,0.3);
        }
        
        .sidebar h3 { 
            font-size: 1.4rem; 
            font-weight: 800; 
            padding: 0 24px; 
            margin-bottom: 35px; 
            color: #ffffff;
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
        }

        .sidebar a i, .dropdown-btn i { margin-right: 12px; width: 20px; text-align: center; }
        .sidebar .menu-text { flex-grow: 1; }
        .dropdown-container { display: none; background: rgba(0, 0, 0, 0.2); }
        .dropdown-container a { padding: 11px 24px 11px 56px; font-size: 0.85rem; }

        /* CONTENT WRAPPER LAYOUT */
        .content { 
            margin-left: 260px; 
            min-height: 100vh;
        }

        .navbar-cyber {
            background: rgba(15, 19, 42, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-glass);
            padding: 18px 32px;
        }

        .main-body-wrapper {
            padding: 30px 32px;
        }

        /* 4 GRID STAT CARDS */
        .glass-stat-link { text-decoration: none; display: block; }
        .glass-stat-card {
            background: rgba(21, 27, 61, 0.6);
            border: 1px solid var(--border-glass);
            border-radius: 15px;
            padding: 20px;
        }
        .stat-label { font-size: 0.75rem; text-transform: uppercase; color: var(--text-sub); font-weight: 700; margin-bottom: 5px; }
        .stat-number { font-size: 1.8rem; font-weight: 800; color: #ffffff; margin: 0; }

        /* SEARCH AREA */
        .cyber-search-box {
            background: rgba(15, 19, 42, 0.8);
            border: 1px solid var(--border-glass);
            border-radius: 15px;
            padding: 20px;
        }

        /* FIXING UTAMA: MASTER KONTROL RESPONSIVE TABEL */
        .cyber-table-wrapper {
            background: #0f132a !important; 
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            
            /* Mengaktifkan scroll horizontal secara paksa & aman */
            display: block;
            width: 100%;
            overflow-x: auto !important;
            overflow-y: hidden;
        }
        
        .table-cyber-clean {
            width: 100%;
            margin-bottom: 0;
            border-collapse: collapse;
            /* Memberikan ruang horizontal minimal agar 13 kolom tidak berdesakan */
            min-width: 1700px; 
        }
        
        .table-cyber-clean thead th {
            background-color: #111635 !important;
            color: #94a3b8 !important;
            font-weight: 700;
            padding: 16px 14px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid rgba(255,255,255,0.1) !important;
            white-space: nowrap;
        }
        
        .table-cyber-clean tbody tr {
            background-color: #0f132a !important;
            border-bottom: 1px solid rgba(255,255,255,0.06) !important;
        }
        
        .table-cyber-clean tbody tr:hover {
            background-color: #161c3d !important;
        }

        .table-cyber-clean tbody td {
            padding: 14px 14px !important;
            font-size: 13px;
            vertical-align: middle;
            color: #e2e8f0 !important; 
            white-space: nowrap; /* Mengunci kolom pendek agar tetap satu baris lurus */
            background: transparent !important;
        }

        /* Mengontrol kolom teks panjang agar turun ke bawah secara estetik */
        .table-cyber-clean th.max-col-width,
        .table-cyber-clean td.max-col-width {
            white-space: normal !important; 
            word-break: break-word;
            min-width: 260px !important;
            max-width: 340px !important;
        }

        /* STYLE MANAJEMEN OPSI FIXED */
        .btn-action-group-cyber {
            display: inline-flex;
            background-color: rgba(21, 27, 61, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 6px 12px;
            gap: 16px;
            align-items: center;
            justify-content: center;
        }

        .btn-action-item-cyber {
            color: #94a3b8;
            font-size: 1.05rem;
            text-decoration: none;
            transition: all 0.2s ease;
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        .btn-action-item-cyber.btn-view:hover { color: #38bdf8; text-shadow: 0 0 8px rgba(56, 189, 248, 0.5); }
        .btn-action-item-cyber.btn-edit:hover { color: #fbbf24; text-shadow: 0 0 8px rgba(251, 191, 36, 0.5); }
        .btn-action-item-cyber.btn-delete:hover { color: #f43f5e; text-shadow: 0 0 8px rgba(244, 63, 94, 0.5); }

        /* BADGES STYLE */
        .badge-masuk { background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.4); padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 11px; }
        .badge-keluar { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 11px; }
        .badge-return { background: rgba(234, 179, 8, 0.2); color: #facc15; border: 1px solid rgba(234, 179, 8, 0.4); padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 11px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fa-solid fa-bolt text-warning me-2"></i>I-CALM</h3>

    <a href="../dashboard/index.php">
        <span><i class="fa-solid fa-chart-pie me-2"></i><span class="menu-text">Dashboard</span></span>
    </a>

    <button class="dropdown-btn active">
        <span><i class="fa-solid fa-layer-group"></i><span class="menu-text">Monitoring</span></span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="dropdown-container" style="display: block;">
        <a href="../material/index.php">Material Gudang</a>
        <a href="../ba/index.php" class="active-menu">Database BA</a>
    </div>

    <button class="dropdown-btn">
        <span><i class="fa-solid fa-file-import"></i><span class="menu-text">Import</span></span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="dropdown-container">
        <a href="../import/material.php">Import Material</a>
        <a href="../import/ba.php">Import BA</a>
    </div>

    <button class="dropdown-btn">
        <span><i class="fa-solid fa-file-export"></i><span class="menu-text">Export</span></span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="dropdown-container">
        <a href="../export/material_excel.php">Export Material</a>
        <a href="../export/ba_excel.php">Export BA</a>
    </div>

    <a href="../login/logout.php" class="mt-4">
        <span><i class="fa-solid fa-right-from-bracket text-danger"></i><span class="menu-text text-danger">Logout</span></span>
    </a>
</div>

<div class="content">

    <nav class="navbar navbar-expand-lg navbar-cyber">
        <div class="container-fluid px-0">
            <span class="fs-5 fw-bold text-white">
                📄 I-CALM - Database Berita Acara
            </span>
            <div>
                <a href="tambah.php" class="btn btn-info btn-sm fw-bold text-dark me-1" style="background:#38bdf8;">➕ Tambah Data</a>
                <a href="../export/ba_excel.php" class="btn btn-success btn-sm fw-bold me-1">📥 Export Excel</a>
                <a href="../dashboard/index.php" class="btn btn-secondary btn-sm fw-bold">← Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="main-body-wrapper">

        <div class="mb-4">
            <h2 class="fw-bold text-white m-0" style="font-size: 1.8rem;">Dashboard Monitoring</h2>
            <p class="text-muted m-0 small" style="letter-spacing: 0.3px;">Sistem kendali aset distribusi logistik aktif.</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <a href="barang_masuk.php" class="glass-stat-link">
                    <div class="glass-stat-card" style="border-left: 4px solid #22c55e;">
                        <div class="stat-label">Barang Masuk</div>
                        <div class="stat-number text-success"><?= number_format($barang_masuk['total'] ?? 0); ?></div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="barang_keluar.php" class="glass-stat-link">
                    <div class="glass-stat-card" style="border-left: 4px solid #ef4444;">
                        <div class="stat-label">Barang Keluar</div>
                        <div class="stat-number text-danger"><?= number_format($barang_keluar['total'] ?? 0); ?></div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="stok_barang.php" class="glass-stat-link">
                    <div class="glass-stat-card" style="border-left: 4px solid #38bdf8;">
                        <div class="stat-label">Sisa Stok</div>
                        <div class="stat-number" style="color: #38bdf8;"><?= number_format($stok); ?></div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <div class="glass-stat-card" style="border-left: 4px solid #eab308;">
                    <div class="stat-label">Total Data BA</div>
                    <div class="stat-number text-warning"><?= number_format($total_ba['total']); ?></div>
                </div>
            </div>
        </div>

        <div class="cyber-search-box mb-3">
            <form method="GET">
                <div class="row g-2">
                    <div class="col-md-10">
                        <input type="text" name="cari" class="form-control bg-dark text-white border-secondary py-2" placeholder="Cari Nama Barang..." value="<?= htmlspecialchars($cari); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Cari Data</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 px-1">
            <span class="small text-muted">Total Data Ditemukan: <strong class="text-info"><?= number_format($total_data); ?></strong> baris</span>
        </div>

        <div class="cyber-table-wrapper mb-4">
            <table class="table table-cyber-clean">
                <thead>
                    <tr>
                        <th class="text-center">NO</th>
                        <th>TANGGAL</th>
                        <th class="max-col-width">NAMA MATERIAL</th>
                        <th>MERK/JENIS</th>
                        <th>JENIS MATERIAL</th>
                        <th class="max-col-width">SUMBER MATERIAL</th>
                        <th>SATUAN</th>
                        <th>JUMLAH</th>
                        <th>NOMOR SERI</th>
                        <th>PEMASOK/VENDOR</th>
                        <th class="text-center">KATEGORI</th>
                        <th>KETERANGAN</th>
                        <th class="text-center">MANAJEMEN OPSI</th>
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
                        <td class="text-center fw-bold" style="color: #64748b !important;"><?= $no++; ?></td>
                        <td style="color: #94a3b8 !important;"><?= $tanggal; ?></td>
                        
                        <td class="max-col-width">
                            <a href="detail.php?id=<?= $d['id']; ?>" style="text-decoration:none; font-weight:700; color:#38bdf8 !important;">
                                <?= htmlspecialchars($d['nama_barang']); ?>
                            </a>
                        </td>
                        
                        <td style="color: #cbd5e1 !important;"><?= htmlspecialchars($d['merk_jenis']); ?></td>
                        <td style="color: #cbd5e1 !important;"><?= htmlspecialchars($d['jenis_barang']); ?></td>
                        
                        <td class="max-col-width" style="color: #cbd5e1 !important;"><?= htmlspecialchars($d['sumber_barang']); ?></td>
                        
                        <td><?= htmlspecialchars($d['satuan']); ?></td>
                        <td class="fw-bold text-white"><?= number_format($d['jumlah']); ?></td>
                        <td style="color: #34d399 !important; font-family: monospace;"><?= htmlspecialchars($d['no_seri']); ?></td>
                        <td style="color: #cbd5e1 !important;"><?= htmlspecialchars($d['asal_barang_vendor']); ?></td>
                        
                        <td class="text-center">
                            <?php
                            $kategori = strtoupper($d['jenis_berita_acara']);
                            if(strpos($kategori,'MASUK') !== false){ echo "<span class='badge-masuk'>MASUK</span>"; }
                            elseif(strpos($kategori,'KELUAR') !== false || strpos($kategori,'TERPAKAI') !== false){ echo "<span class='badge-keluar'>KELUAR</span>"; }
                            elseif(strpos($kategori,'RETURN') !== false || strpos($kategori,'PENGEMBALIAN') !== false){ echo "<span class='badge-return'>RETURN</span>"; }
                            else{ echo "<span class='badge bg-secondary text-uppercase'>".$kategori."</span>"; }
                            ?>
                        </td>
                        
                        <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($d['keterangan']); ?>">
                            <?= htmlspecialchars($d['keterangan']); ?>
                        </td>
                        
                        <td class="text-center">
                            <div class="btn-action-group-cyber">
                                <a href="detail.php?id=<?= $d['id']; ?>" class="btn-action-item-cyber btn-view" title="Detail">
                                    <i class="fa-solid fa-expand"></i>
                                </a>
                                <a href="edit.php?id=<?= $d['id']; ?>" class="btn-action-item-cyber btn-edit" title="Edit">
                                    <i class="fa-solid fa-user-pen"></i>
                                </a>
                                <a href="hapus.php?id=<?= $d['id']; ?>" class="btn-action-item-cyber btn-delete tombol-hapus" title="Hapus">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php } } else { ?>
                    <tr><td colspan="13" class="text-center py-5" style="color: var(--text-sub) !important;">Data tidak ditemukan</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php if($total_halaman > 1) { ?>
        <nav class="pb-4">
            <ul class="pagination justify-content-center flex-wrap">
                <?php if($page > 1){ ?><li class="page-item"><a class="page-link" style="background:#111635; color:#fff;" href="?cari=<?= urlencode($cari); ?>&page=<?= $page-1; ?>">Prev</a></li><?php } ?>
                <?php for($i=1; $i<=$total_halaman; $i++){ if($i == 1 || $i == $total_halaman || ($i >= $page-2 && $i <= $page+2)){ ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>"><a class="page-link" style="<?= ($page == $i) ? '' : 'background:#111635; color:#94a3b8;'; ?>" href="?cari=<?= urlencode($cari); ?>&page=<?= $i; ?>"><?= $i; ?></a></li>
                <?php } } ?>
                <?php if($page < $total_halaman){ ?><li class="page-item"><a class="page-link" style="background:#111635; color:#fff;" href="?cari=<?= urlencode($cari); ?>&page=<?= $page+1; ?>">Next</a></li><?php } ?>
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

    // Interseptor Hapus Data SweetAlert2
    document.querySelectorAll('.tombol-hapus').forEach(function(btn){
        btn.addEventListener('click', function(e){
            e.preventDefault();
            let url = this.getAttribute('href');
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: 'Data yang sudah dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                background: '#151b3d',
                color: '#fff',
                confirmButtonColor: '#f43f5e',
                cancelButtonColor: '#475569',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Tidak'
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