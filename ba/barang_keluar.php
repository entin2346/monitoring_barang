<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

$cari = $_GET['cari'] ?? '';
$cari = mysqli_real_escape_string($conn, $cari);

$query = mysqli_query($conn,"
    SELECT *
    FROM database_ba
    WHERE UPPER(jenis_berita_acara) LIKE 'KELUAR%'
    AND (
        nama_barang LIKE '%$cari%'
        OR merk_jenis LIKE '%$cari%'
        OR jenis_barang LIKE '%$cari%'
        OR sumber_barang LIKE '%$cari%'
        OR no_seri LIKE '%$cari%'
        OR asal_barang_vendor LIKE '%$cari%'
        OR kategori_material LIKE '%$cari%'
        OR keterangan LIKE '%$cari%'
    )
    ORDER BY tanggal DESC, id DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Material Keluar | I-CALM</title>

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
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    body { 
        background: var(--bg-body);
        color: var(--text-main);
        min-height: 100vh;
        overflow-x: hidden;
    }

    /* SIDEBAR PANEL ARSITEKTUR (MENGIKUTI INDEX.PHP) */
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

    /* LAYOUT UTAMA KONTEN */
    .content { margin-left: 260px; position: relative; min-height: 100vh; }
    
    /* NAVBAR ATAS */
    .navbar-custom { 
        background: #ffffff;
        padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999;
    }

    /* JUDUL MATERIAL KELUAR YANG SUPER TEBAL & MENCOLOK */
    .brand-mencolok-keluar {
        font-weight: 800 !important;
        font-size: 1.3rem !important;
        letter-spacing: 0.5px;
        background: linear-gradient(135deg, #ef4444, #f97316);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-shadow: 0px 2px 10px rgba(239, 68, 68, 0.15);
    }
    
    /* TOMBOL KEMBALI WARNA BIRU MENCOLOK */
    .btn-kembali-biru {
        background: linear-gradient(135deg, #0284c7, #2563eb) !important;
        color: #ffffff !important;
        font-weight: 800 !important;
        font-size: 0.85rem !important;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 10px 24px !important;
        border: none !important;
        border-radius: 30px !important;
        box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4) !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    .btn-kembali-biru:hover {
        transform: translateY(-2px) scale(1.03);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.55) !important;
        background: linear-gradient(135deg, #0369a1, #1d4ed8) !important;
    }
    .btn-kembali-biru:active {
        transform: translateY(1px);
    }

    /* WORKSPACE BODY */
    .main-body-wrapper { padding: 40px; }

    /* BOX PENCARIAN */
    .cyber-search-box {
        background: #ffffff; border: 1px solid var(--border-color); border-radius: 16px; padding: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.01);
    }
    .input-cyber-group { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; overflow: hidden; }
    .input-cyber-group input { background: transparent !important; border: none !important; color: var(--text-main) !important; padding: 14px 14px; font-weight: 500; font-size: 0.92rem; }
    .input-cyber-group input::placeholder { color: #94a3b8; }
    .input-cyber-group .input-group-text { background: transparent; border: none; color: #64748b; padding-left: 18px; }

    /* TABEL CONTAINER */
    .cyber-table-card {
        background: #ffffff; border: 1px solid var(--border-color); border-radius: 16px; 
        box-shadow: 0 4px 16px rgba(0,0,0,0.02); overflow-x: auto; width: 100%;
    }
    
    /* GAYA TABEL */
    .table-cyber { width: 100%; border-collapse: separate; border-spacing: 0; margin: 0; }
    .table-cyber thead th { 
        background: #f8fafc !important; color: var(--text-muted) !important; 
        font-weight: 700; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.3px; 
        padding: 16px 20px; 
        border-bottom: 1px solid var(--border-color); white-space: nowrap;
    }
    .table-cyber tbody tr:hover { background-color: #f8fafc !important; }
    .table-cyber tbody td { 
        padding: 15px 20px !important; 
        font-size: 0.88rem; vertical-align: middle; color: var(--text-main) !important; 
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap; 
    }

    /* PENGATURAN TEKS KREATIVE WRAPPING */
    .col-text-wrap {
        white-space: normal !important; 
        word-break: break-word;
        min-width: 250px !important; max-width: 330px !important;
    }
    .col-nowrap {
        white-space: nowrap !important;
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
    
    <nav class="navbar navbar-custom">
        <div class="container-fluid d-flex justify-content-between align-items-center px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center brand-mencolok-keluar">
                <i class="fa-solid fa-square-minus me-2" style="color: #ef4444; -webkit-text-fill-color: initial;"></i> MATERIAL KELUAR
            </span>
            
            <a href="index.php" class="btn btn-kembali-biru">
                <i class="fa-solid fa-chevron-left"></i> Kembali
            </a>
        </div>
    </nav>

    <div class="container-fluid main-body-wrapper">
        
        <div class="cyber-search-box mb-4">
            <form method="GET" action="">
                <div class="row g-2">
                    <div class="col-md-10">
                        <div class="input-group input-cyber-group">
                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass text-muted" style="font-size: 0.85rem;"></i></span>
                            <input type="text" name="cari" class="form-control" placeholder="Cari berdasarkan nama material, merk, tipe, vendor..." value="<?= htmlspecialchars($cari); ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-dark fw-bold w-100" style="border-radius: 12px; background: #0f172a; border: none; height: 100%; font-size: 0.9rem;">
                            Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="cyber-table-card">
            <table class="table-cyber">
                <thead>
                    <tr>
                        <th width="70" class="text-center">NO</th>
                        <th>TANGGAL RECORD</th>
                        <th class="col-text-wrap">NAMA MATERIAL</th>
                        <th>MERK / JENIS</th>
                        <th>JENIS MATERIAL</th>
                        <th class="col-text-wrap">SUMBER MATERIAL</th>
                        <th class="text-center">SATUAN</th>
                        <th class="text-center">JUMLAH</th>
                        <th>NO SERI</th>
                        <th>PEMASOK/VENDOR</th>
                        <th class="text-center">KATEGORI MATERIAL</th>
                        <th>KETERANGAN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if(mysqli_num_rows($query) > 0){
                        while($d = mysqli_fetch_assoc($query)){
                    ?>
                    <tr>
                        <td class="text-center fw-bold col-nowrap" style="color: var(--text-muted) !important; font-size:0.85rem;"><?= str_pad($no++, 2, '0', STR_PAD_LEFT); ?></td>
                        <td class="col-nowrap fw-semibold text-muted"><i class="fa-regular fa-calendar me-1" style="font-size: 0.75rem;"></i> <?= !empty($d['tanggal']) ? date('d-m-Y', strtotime($d['tanggal'])) : '-'; ?></td>
                        <td class="fw-bold text-dark col-text-wrap"><?= htmlspecialchars($d['nama_barang'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($d['merk_jenis'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($d['jenis_barang'] ?? ''); ?></td>
                        <td class="col-text-wrap text-muted"><?= htmlspecialchars($d['sumber_barang'] ?? ''); ?></td>
                        <td class="text-center col-nowrap"><span class="badge bg-light text-secondary border px-2 py-1" style="border-radius:6px; font-size:0.75rem; font-weight:600;"><?= htmlspecialchars($d['satuan'] ?? ''); ?></span></td>
                        <td class="text-center col-nowrap fw-bold text-danger" style="font-size:0.85rem;"><?= number_format($d['jumlah'] ?? 0); ?></td>
                        <td class="fw-bold text-primary" style="font-family: monospace; font-size: 0.9rem;"><?= htmlspecialchars($d['no_seri'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($d['asal_barang_vendor'] ?? ''); ?></td>
                        <td class="text-center col-nowrap"><span class="badge bg-danger-subtle text-danger border-0 px-2 py-1" style="border-radius:6px; font-size:0.75rem; font-weight:600;"><?= htmlspecialchars($d['kategori_material'] ?? ''); ?></span></td>
                        <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text-muted)" title="<?= htmlspecialchars($d['keterangan'] ?? ''); ?>"><?= htmlspecialchars($d['keterangan'] ?? ''); ?></td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='12' class='text-center py-5 fw-bold text-muted'><i class='fa-solid fa-box-open d-block fs-1 mb-3 opacity-25'></i> Data tidak ditemukan</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    /* TOGGLE DROP DOWN CONTROLLER (MENGIKUTI INDEX.PHP) */
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