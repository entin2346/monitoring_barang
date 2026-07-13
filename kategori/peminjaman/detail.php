<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../../login/index.php");
    exit;
}
include "../../config/koneksi.php";

$id = $_GET['id'] ?? 0;
// Sesuaikan query pencarian data peminjaman
$query = mysqli_query($conn, "SELECT * FROM peminjaman WHERE id = '$id'");
$d = mysqli_fetch_assoc($query);

if(!$d){
    echo "<script>alert('Data tidak ditemukan!'); window.location='peminjaman.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Detail Peminjaman</title>
    
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

        /* SIDEBAR STYLE */
        .sidebar {
            position: fixed; left: 0; top: 0; width: 260px; height: 100%;
            background-color: var(--bg-sidebar); border-right: 1px solid rgba(2, 132, 199, 0.15);
            padding: 35px 20px; z-index: 1050; display: flex; flex-direction: column; overflow-y: auto;
        }
        .sidebar h3 { font-size: 1.25rem; font-weight: 800; color: #1e3a8a; margin-bottom: 35px; display: flex; align-items: center; gap: 10px; }
        .sidebar a, .dropdown-btn { display: flex; align-items: center; justify-content: space-between; color: #1e3a8a; text-decoration: none; padding: 11px 14px; font-size: 0.9rem; font-weight: 700; border: none; background: transparent; width: 100%; cursor: pointer; border-radius: 10px; margin-bottom: 5px; transition: all 0.2s ease-in-out; }
        .sidebar a:hover, .dropdown-btn:hover { color: #025a9c; background: rgba(2, 132, 199, 0.12); transform: translateX(4px); }
        .sidebar .menu-content-wrapper { display: flex; align-items: center; gap: 12px; }
        .sidebar a i, .dropdown-btn i.menu-icon { font-size: 1.05rem; width: 20px; text-align: center; color: #1e40af; }
        
        .sidebar .dropdown-btn.active { color: #ffffff !important; background: #0284c7 !important; font-weight: 700; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); border-radius: 10px; }
        .sidebar .dropdown-btn.active i { color: #ffffff !important; }
        .dropdown-chevron { font-size: 0.75rem !important; transition: transform 0.2s ease; color: #1e40af !important; }
        .dropdown-btn.active .dropdown-chevron { transform: rotate(180deg); color: #ffffff !important; }
        
        .dropdown-container { display: none; padding-left: 12px; margin-bottom: 6px; margin-top: 4px; }
        .dropdown-container a { padding: 9px 14px; font-size: 0.85rem; color: #1e40af; font-weight: 600; background: rgba(255, 255, 255, 0.2); border-radius: 8px; margin-bottom: 3px; }
        .dropdown-container a:hover { background: #ffffff; color: #0284c7; }
        .dropdown-container a.active-menu { color: #ffffff !important; background: #0284c7 !important; font-weight: 700; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); border-radius: 10px; }
        
        .sidebar .logout-button { margin-top: auto; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; padding: 11px 14px; text-decoration: none; display: flex; align-items: center; }
        .sidebar .logout-button i, .sidebar .logout-button span { color: #b91c1c !important; }

        /* CONTENT STYLE */
        .content { margin-left: 260px; position: relative; width: calc(100% - 260px); }
        .navbar-custom { background: #ffffff; padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999; }
        .main-body-wrapper { padding: 40px; }
        
        /* CARD PANJANG LAYOUT */
        .glass-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 40px; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04); width: 100%; }
        
        /* DETAIL FIELD STYLE */
        .detail-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; margin-bottom: 8px; }
        .detail-value { border-radius: 10px; border: 1px solid #cbd5e1; padding: 13px 18px; font-size: 0.9rem; background-color: #f8fafc; color: var(--text-main); font-weight: 600; min-height: 50px; display: flex; align-items: center; }
        
        /* PHOTO CONTAINER & BERKAS */
        .img-thumbnail-custom { width: 100%; max-width: 140px; height: 100px; object-fit: cover; border-radius: 10px; border: 1px solid #cbd5e1; transition: transform 0.2s; cursor: pointer; }
        .img-thumbnail-custom:hover { transform: scale(1.05); }
        .file-download-box { display: flex; align-items: center; gap: 10px; background: #fff; padding: 10px 15px; border-radius: 8px; border: 1px solid #cbd5e1; text-decoration: none; color: #1e293b; font-size: 0.85rem; font-weight: 600; transition: background 0.2s; }
        .file-download-box:hover { background: #f1f5f9; color: #0284c7; }

        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; border-right: none; padding: 20px; }
            .content { margin-left: 0; width: 100%; }
            .main-body-wrapper { padding: 20px; }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fa-solid fa-bolt text-primary"></i> I-CALM Panel</h3>
    <a href="/monitoring_barang/dashboard/index.php"><span class="menu-content-wrapper"><i class="fa-solid fa-chart-pie"></i><span>Dashboard</span></span></a>
    
    <button class="dropdown-btn"><span class="menu-content-wrapper"><i class="fa-solid fa-layer-group menu-icon"></i><span>Monitoring</span></span><i class="fa-solid fa-chevron-down dropdown-chevron"></i></button>
    <div class="dropdown-container"><a href="/monitoring_barang/material/index.php">Material Gudang</a><a href="/monitoring_barang/ba/index.php">Database BA</a></div>

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
    
    <a href="/monitoring_barang/login/logout.php" class="logout-button"><span class="menu-content-wrapper"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></span></a>
</div>

<div class="content">
    <nav class="navbar navbar-custom">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center">
                <i class="fa-solid fa-boxes-stacked text-primary me-2"></i> KENDALI LOGISTIK 
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Kategori: Peminjaman / Detail Rincian</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="glass-card">
            <h4 class="fw-bold mb-4" style="color: #0f172a;"><i class="fa-solid fa-magnifying-glass-chart text-primary me-2"></i>Detail Rincian Peminjaman Material</h4>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="detail-label">Material (Nama Material)</div>
                    <div class="detail-value text-primary fw-bold"><?= htmlspecialchars($d['nama_material'] ?? '-'); ?></div>
                </div>
                
                <div class="col-md-6">
                    <div class="detail-label">Asal Material</div>
                    <div class="detail-value"><?= htmlspecialchars($d['asal_material'] ?? '-'); ?></div>
                </div>
                
                <div class="col-md-6">
                    <div class="detail-label">Tanggal Pengambilan</div>
                    <div class="detail-value"><?= htmlspecialchars($d['tanggal_pengambilan'] ?? '-'); ?></div>
                </div>
                
                <div class="col-md-6">
                    <div class="detail-label">Peminjam Material</div>
                    <div class="detail-value text-danger"><?= htmlspecialchars($d['peminjam'] ?? '-'); ?></div>
                </div>
                
                <div class="col-md-4">
                    <div class="detail-label">Jumlah Pinjam</div>
                    <div class="detail-value"><?= number_format((float)($d['jumlah'] ?? 0)); ?></div>
                </div>
                
                <div class="col-md-4">
                    <div class="detail-label">Satuan</div>
                    <div class="detail-value"><?= htmlspecialchars($d['satuan'] ?? '-'); ?></div>
                </div>

                <div class="col-md-4">
                    <div class="detail-label">Status Pengembalian</div>
                    <div class="detail-value">
                        <span class="badge <?= (strtoupper(trim($d['status_kembali'] ?? '')) == 'SUDAH') ? 'bg-success' : 'bg-warning text-dark'; ?> px-3 py-2 fs-6" style="font-size:0.8rem !important;">
                            <?= htmlspecialchars(strtoupper($d['status_kembali'] ?? 'BELUM')); ?>
                        </span>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-label">Jumlah Dikembalikan</div>
                    <div class="detail-value"><?= htmlspecialchars($d['jumlah_dikembalikan'] ?? '-'); ?></div>
                </div>
                
                <div class="col-md-4">
                    <div class="detail-label">Link BA Pengambilan Material</div>
                    <div class="detail-value">
                        <?= !empty($d['link_ba_ambil']) ? "<a href='".htmlspecialchars($d['link_ba_ambil'])."' target='_blank' class='btn btn-sm btn-outline-primary w-100 fw-bold'><i class='fa-solid fa-arrow-up-right-from-square me-1'></i> Buka Tautan BA</a>" : "<span class='text-muted fw-normal'>-</span>"; ?>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="detail-label">Link BA Pengembalian Material</div>
                    <div class="detail-value">
                        <?= !empty($d['link_ba_kembali']) ? "<a href='".htmlspecialchars($d['link_ba_kembali'])."' target='_blank' class='btn btn-sm btn-outline-success w-100 fw-bold'><i class='fa-solid fa-arrow-up-right-from-square me-1'></i> Buka Tautan BA</a>" : "<span class='text-muted fw-normal'>-</span>"; ?>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="detail-label">Dokumentasi Lampiran (Berkas &amp; Gambar)</div>
                    <div class="detail-value d-flex flex-wrap gap-2 py-3" style="min-height: 120px;">
                        <?php 
                        $docs = json_decode($d['dokumentasi'] ?? '[]', true);
                        if (!empty($docs) && is_array($docs)): 
                            foreach ($docs as $file): 
                                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])):
                        ?>
                                    <a href="upload/<?= $file; ?>" target="_blank" title="Klik untuk memperbesar">
                                        <img src="upload/<?= $file; ?>" class="img-thumbnail-custom" alt="Foto Dokumentasi">
                                    </a>
                        <?php 
                                else: 
                        ?>
                                    <a href="upload/<?= $file; ?>" target="_blank" class="file-download-box">
                                        <i class="fa-solid fa-file-lines text-primary fs-4"></i>
                                        <span><?= htmlspecialchars($file); ?></span>
                                    </a>
                        <?php 
                                endif;
                            endforeach; 
                        else: 
                        ?>
                            <span class="text-muted fw-normal italic">Tidak ada berkas dokumentasi terunggah.</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="detail-label">Keterangan Tambahan</div>
                    <div class="detail-value align-items-start py-3" style="min-height: 100px;">
                        <?= !empty($d['keterangan']) ? nl2br(htmlspecialchars($d['keterangan'])) : '<span class="text-muted fw-normal">Tidak ada catatan keterangan tambahan.</span>'; ?>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 gap-2 d-flex">
                <a href="edit.php?id=<?= $d['id']; ?>" class="btn btn-warning px-4 fw-bold text-white" style="border-radius:10px;"><i class="fa-solid fa-pen-to-square me-1"></i> Edit Data</a>
                <a href="peminjaman.php" class="btn btn-light px-4 border" style="border-radius:10px;">Kembali</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.dropdown-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const container = this.nextElementSibling;
            this.classList.toggle('active');
            container.style.display = container.style.display === "block" ? "none" : "block";
        });
    });
</script>
</body>
</html>