<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../../login/index.php");
    exit;
}
include "../../config/koneksi.php";

$cari = $_GET['cari'] ?? '';
$cari_clean = trim(mysqli_real_escape_string($conn, urldecode($cari)));

// Filter Pencarian
$whereClause = "1=1";
if ($cari_clean !== '') {
    $whereClause .= " AND (nama_material LIKE '%$cari_clean%' OR lokasi_penyimpanan LIKE '%$cari_clean%' OR peminjam LIKE '%$cari_clean%')";
}

// Mutasi Halaman (Pagination)
$limit = 25;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

// Total Data & Total Stok dari tabel ex_bongkaran
$total_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM ex_bongkaran WHERE $whereClause");
$total_data = mysqli_fetch_assoc($total_query)['total'] ?? 0;
$total_halaman = ceil($total_data / $limit);

$stok_query = mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM ex_bongkaran WHERE $whereClause");
$total_stok = mysqli_fetch_assoc($stok_query)['total'] ?? 0;

// =========================================================================
// 📌 DI SINI KODE TERSEBUT DITAMBAHKAN / DIGUNAKAN:
// =========================================================================
$query = mysqli_query($conn, "SELECT * FROM ex_bongkaran WHERE $whereClause ORDER BY id DESC LIMIT $offset, $limit");

if(!$query){
    die("Query Gagal: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Kategori Ex Bongkaran</title>
    
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

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary); }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: var(--bg-body); color: var(--text-main); min-height: 100vh; overflow-x: auto; }

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
        
        .sidebar a:hover, .dropdown-btn:hover { color: #025a9c; background: rgba(2, 132, 199, 0.12); transform: translateX(4px); }
        .sidebar .menu-content-wrapper { display: flex; align-items: center; gap: 12px; }
        .sidebar a i, .dropdown-btn i.menu-icon { font-size: 1.05rem; width: 20px; text-align: center; color: #1e40af; }
        
        .sidebar .dropdown-btn.active { 
            color: #ffffff !important; 
            background: #0284c7 !important; 
            font-weight: 700; 
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); 
            border-radius: 10px; 
        }
        .sidebar .dropdown-btn.active i { color: #ffffff !important; }
        
        .dropdown-chevron { font-size: 0.75rem !important; transition: transform 0.2s ease; color: #1e40af !important; }
        .dropdown-btn.active .dropdown-chevron { transform: rotate(180deg); }
        
        .dropdown-container { display: none; padding-left: 12px; margin-bottom: 6px; margin-top: 4px; }
        .dropdown-container a { padding: 9px 14px; font-size: 0.85rem; color: #1e40af; font-weight: 600; background: rgba(255, 255, 255, 0.2); border-radius: 8px; margin-bottom: 3px; }
        .dropdown-container a:hover { background: #ffffff; color: #0284c7; }
        
        .dropdown-container a.active-menu {
            color: #ffffff !important; 
            background: #0284c7 !important; 
            font-weight: 700;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); 
            border-radius: 10px;
        }

        .sidebar .logout-button { margin-top: auto; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; }
        .sidebar .logout-button i, .sidebar .logout-button span { color: #b91c1c !important; }

        .content { margin-left: 260px; position: relative; width: calc(100% - 260px); }
        .navbar-custom { background: #ffffff; padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999; }
        .navbar-custom .navbar-brand { color: var(--text-main); font-weight: 800; font-size: 1.3rem; }
        .main-body-wrapper { padding: 40px; }

        .glass-stat-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 26px; border-left: 5px solid var(--primary); }
        .stat-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-muted); font-weight: 700; margin-bottom: 8px; }
        .stat-number { font-size: 2rem; font-weight: 800; color: var(--text-main); margin: 0; }

        .cyber-search-box { background: #ffffff; border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; }

        .cyber-table-wrapper { border: 1px solid var(--border-color); border-radius: 16px; overflow-x: auto; background: #ffffff; width: 100%; display: block; }
        .table-cyber { width: 100%; border-collapse: separate; border-spacing: 0; margin: 0; white-space: nowrap; }
        .table-cyber thead th { background: #f8fafc !important; color: #334155 !important; font-weight: 700; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.5px; padding: 16px 22px; border-bottom: 1px solid var(--border-color); }
        .table-cyber tbody tr:not(:last-child) td { border-bottom: 1px solid var(--border-color); }
        .table-cyber tbody tr:hover td { background: #f8fafc; }
        .table-cyber tbody td { padding: 15px 22px; font-size: 0.88rem; vertical-align: middle; color: var(--text-main) !important; }

        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; border-right: none; border-bottom: 1px solid rgba(2, 132, 199, 0.15); padding: 20px; }
            .content { margin-left: 0; width: 100%; }
            .main-body-wrapper { padding: 20px; }
            .navbar-custom { padding: 20px; }
        }
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
        <a href="/monitoring_barang/kategori/ex_bongkaran/ex_bongkaran.php" class="active-menu">Ex Bongkaran</a>
        <a href="/monitoring_barang/kategori/pre_memory/pre_memory.php">Pre Memory</a>
        <a href="/monitoring_barang/kategori/peminjaman/peminjaman.php">Peminjaman</a>
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
        <a href="/monitoring_barang/import/form_ex_bongkaran.php">Import Ex Bongkaran</a>
        <a href="/monitoring_barang/import/form_pre_memory.php">Import Pre Memory</a>
        <a href="/monitoring_barang/import/form_peminjaman.php">Import Peminjaman</a>
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
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Kategori: Ex Bongkaran</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="glass-stat-card">
                    <div class="stat-label">Total Klasifikasi Ex Bongkaran</div>
                    <div class="stat-number"><?= number_format((float)$total_data); ?> <span class="fw-normal text-muted" style="font-size: 1.1rem;">Item</span></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-stat-card" style="border-left-color: #10b981;">
                    <div class="stat-label">Volume Akumulasi Stok</div>
                    <div class="stat-number" style="color: #10b981;"><?= number_format((float)$total_stok); ?> <span class="fw-normal text-muted" style="font-size: 1.1rem;">Unit</span></div>
                </div>
            </div>
        </div>

        <div class="cyber-search-box mb-4 d-flex justify-content-between align-items-center gap-3">
            <form method="GET" class="d-flex gap-2" style="flex: 1;">
                <div class="input-group" style="flex: 1;">
                    <span class="input-group-text bg-white border-end-0 px-3" style="border-radius: 12px 0 0 12px; border-color: #cbd5e1; height: 46px; color: #64748b;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="cari" class="form-control border-start-0 ps-1 pr-3" autocomplete="off" placeholder="Cari material ex bongkaran..." value="<?= htmlspecialchars($cari_clean); ?>" style="border-radius: 0 12px 12px 0; border-color: #cbd5e1; height: 46px; background-color: #fff;">
                </div>
                <button type="submit" class="btn btn-primary px-4 fw-bold d-flex align-items-center gap-2" style="border-radius: 12px; background-color: #0284c7; border: none; height: 46px; white-space: nowrap;">
                    <i class="fa-solid fa-sliders"></i> Saring
                </button>
            </form>
            <a href="../../material/tambah.php" class="btn btn-success fw-bold px-4 d-flex align-items-center gap-2" style="border-radius:12px;background-color:#059669;border:none;height:46px;white-space:nowrap;">
                <i class="fa-solid fa-plus"></i> Tambah
            </a>
        </div>

        <div class="cyber-table-wrapper mb-4">
            <table class="table-cyber">
                <thead>
                    <tr>
                        <th class="text-center">NO</th>
                        <th>UNIT</th>
                        <th>NAMA MATERIAL</th>
                        <th>MTU</th>
                        <th>TEGANGAN</th>
                        <th>MERK/TIPE</th>
                        <th>NO SERI</th>
                        <th>GARDU INDUK</th>
                        <th>LOKASI ASAL EKS. BONGKARAN</th>
                        <th>NO. KONTRAK PENGGANTIAN</th>
                        <th>JUDUL KONTRAK PENGGANTIAN</th>
                        <th>JUMLAH</th>
                        <th>SATUAN</th>
                        <th>NILAI BUKU (Rp)</th>
                        <th>BERAT (Kg)</th>
                        <th>LOKASI PENEMPATAN MATERIAL</th>
                        <th>KONDISI</th>
                        <th>JUSTIFIKASI KONDISI</th>
                        <th>KELENGKAPAN AKSESORIS</th>
                        <th>KETERANGAN KELENGKAPAN AKSESORIS</th>
                        <th>KETERANGAN EX BONGKARAN</th>
                        <th>STATUS</th>
                        <th>KETERANGAN WAKTU PEMBONGKARAN</th>
                        <th>TANGGAL UPDATE TERAKHIR</th>
                        <th>NO AT</th>
                        <th>NILAI PEROLEHAN</th>
                        <th>TECHIDENTNO</th>
                        <th>UPT</th>
                        <th>UMUR OPERASI</th>
                        <th>UMUR SIMPAN</th>
                        <th>TAHUN PEMBUATAN</th>
                        <th>FUNLOCT</th>
                        <th>KATALOG MARA</th>
                        <th>NO ASET</th>
                        <th>FOTO NAMEPLATE</th>
                        <th>FOTO MATERIAL</th>
                        <th>LINK BA PEMINDAHAN</th>
                        <th>LINK BA PEMANFAATAN</th>
                        <th>LINK HASIL UJI</th>
                        <th>LINK BA PENGGANTIAN</th>
                        <th>KETERANGAN</th>
                        <th>KETERANGAN TAMBAHAN</th>
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
                        <td class="text-center fw-bold"><?= str_pad($no++, 2, '0', STR_PAD_LEFT); ?></td>
                        <td><?= htmlspecialchars($d['unit'] ?? '-'); ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($d['nama_material'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['mtu'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['tegangan'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['merk_tipe'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['no_seri'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['gardu_induk'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['lokasi_asal_eks_bongkaran'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['no_kontrak_penggantian'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['judul_kontrak_penggantian'] ?? '-'); ?></td>
                        <td class="fw-bold text-primary"><?= number_format((float)($d['jumlah'] ?? 0)); ?></td>
                        <td><?= htmlspecialchars($d['satuan'] ?? '-'); ?></td>
                        <td>Rp <?= number_format((float)($d['nilai_buku'] ?? 0)); ?></td>
                        <td><?= htmlspecialchars($d['berat'] ?? '-'); ?> Kg</td>
                        <td><?= htmlspecialchars($d['lokasi_penyimpanan'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['kondisi'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['justifikasi_kondisi'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['kelengkapan_aksesoris'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['ket_kelengkapan_aksesoris'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['keterangan_ex_bongkaran'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['status'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['ket_waktu_pembongkaran'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['tanggal_update_terakhir'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['no_at'] ?? '-'); ?></td>
                        <td>Rp <?= number_format((float)($d['nilai_perolehan'] ?? 0)); ?></td>
                        <td><?= htmlspecialchars($d['techidentno'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['upt'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['umur_operasi'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['umur_simpan'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['tahun_pembuatan'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['funloct'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['katalog_mara'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['no_aset'] ?? '-'); ?></td>
                        
                        <!-- FOTO NAMEPLATE -->
                        <td>
                            <?php if (!empty($d['foto_nameplate']) && $d['foto_nameplate'] !== '-'): ?>
                                <a href="<?= htmlspecialchars($d['foto_nameplate']); ?>" target="_blank" class="btn btn-outline-success btn-sm px-2 py-1" style="border-radius: 6px; font-size: 0.8rem; font-weight: 600;">
                                    <i class="fa-solid fa-link me-1"></i> Nameplate
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>

                        <!-- FOTO MATERIAL -->
                        <td>
                            <?php if (!empty($d['foto_material']) && $d['foto_material'] !== '-'): ?>
                                <a href="<?= htmlspecialchars($d['foto_material']); ?>" target="_blank" class="btn btn-outline-success btn-sm px-2 py-1" style="border-radius: 6px; font-size: 0.8rem; font-weight: 600;">
                                    <i class="fa-solid fa-link me-1"></i> Material
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>

                        <!-- LINK BA PEMINDAHAN -->
                        <td>
                            <?php if (!empty($d['link_ba_pemindahan']) && $d['link_ba_pemindahan'] !== '-'): ?>
                                <a href="<?= htmlspecialchars($d['link_ba_pemindahan']); ?>" target="_blank" class="btn btn-outline-success btn-sm px-2 py-1" style="border-radius: 6px; font-size: 0.8rem; font-weight: 600;">
                                    <i class="fa-solid fa-link me-1"></i> BA Pemindahan
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>

                        <!-- LINK BA PEMANFAATAN -->
                        <td>
                            <?php if (!empty($d['link_ba_pemanfaatan']) && $d['link_ba_pemanfaatan'] !== '-'): ?>
                                <a href="<?= htmlspecialchars($d['link_ba_pemanfaatan']); ?>" target="_blank" class="btn btn-outline-success btn-sm px-2 py-1" style="border-radius: 6px; font-size: 0.8rem; font-weight: 600;">
                                    <i class="fa-solid fa-link me-1"></i> BA Pemanfaatan
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>

                        <!-- LINK HASIL UJI -->
                        <td>
                            <?php if (!empty($d['link_hasil_uji']) && $d['link_hasil_uji'] !== '-'): ?>
                                <a href="<?= htmlspecialchars($d['link_hasil_uji']); ?>" target="_blank" class="btn btn-outline-success btn-sm px-2 py-1" style="border-radius: 6px; font-size: 0.8rem; font-weight: 600;">
                                    <i class="fa-solid fa-link me-1"></i> Hasil Uji
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>

                        <!-- LINK BA PENGGANTIAN -->
                        <td>
                            <?php 
                            $link_ba_penggantian = $d['link_ba_penggantian_mtu'] ?? $d['link_ba_penggantian'] ?? '';
                            if (!empty($link_ba_penggantian) && $link_ba_penggantian !== '-'): 
                            ?>
                                <a href="<?= htmlspecialchars($link_ba_penggantian); ?>" target="_blank" class="btn btn-outline-success btn-sm px-2 py-1" style="border-radius: 6px; font-size: 0.8rem; font-weight: 600;">
                                    <i class="fa-solid fa-link me-1"></i> BA Penggantian
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>

                        <td><?= htmlspecialchars($d['keterangan'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($d['keterangan_tambahan'] ?? '-'); ?></td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="../../material/edit.php?id=<?= $d['id']; ?>" class="btn btn-warning btn-sm text-white" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="../../material/hapus.php?id=<?= $d['id']; ?>" class="btn btn-danger btn-sm text-white" title="Hapus" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php } } else { ?>
                    <tr>
                        <td colspan="43" class="text-center py-5" style="color: var(--text-muted) !important;">
                            <i class="fa-solid fa-satellite-dish d-block fs-1 mb-3 text-muted opacity-25"></i> Data Ex Bongkaran tidak ditemukan.
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
            this.classList.toggle('active');
            const container = this.nextElementSibling;
            container.style.display = container.style.display === "block" ? "none" : "block";
        });
    });
</script>
</body>
</html>