<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}
include "../../config/koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = mysqli_query($conn, "SELECT * FROM material_gudang WHERE id = $id");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='non_stok.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Detail Material Non Stok</title>
    
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
        .sidebar { position: fixed; left: 0; top: 0; width: 260px; height: 100%; background-color: var(--bg-sidebar); border-right: 1px solid rgba(2, 132, 199, 0.15); padding: 35px 20px; z-index: 1050; display: flex; flex-direction: column; overflow-y: auto; }
        .sidebar h3 { font-size: 1.25rem; font-weight: 800; color: #1e3a8a; margin-bottom: 35px; padding-left: 6px; display: flex; align-items: center; gap: 10px; }
        .sidebar a, .dropdown-btn { display: flex; align-items: center; justify-content: space-between; color: #1e3a8a; text-decoration: none; padding: 11px 14px; font-size: 0.9rem; font-weight: 700; border: none; background: transparent; width: 100%; cursor: pointer; border-radius: 10px; margin-bottom: 5px; transition: all 0.2s ease-in-out; }
        .sidebar a:hover, .dropdown-btn:hover { color: #025a9c; background: rgba(2, 132, 199, 0.12); transform: translateX(4px); }
        .sidebar .menu-content-wrapper { display: flex; align-items: center; gap: 12px; }
        .sidebar a i, .dropdown-btn i.menu-icon { font-size: 1.05rem; width: 20px; text-align: center; color: #1e40af; }
        .sidebar .active-menu { color: #ffffff !important; background: #0284c7 !important; font-weight: 700; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); border-radius: 10px; }
        .sidebar .active-menu i { color: #ffffff !important; }
        .dropdown-chevron { font-size: 0.75rem !important; transition: transform 0.2s ease; color: #1e40af !important; }
        .dropdown-btn.active .dropdown-chevron { transform: rotate(180deg); }
        .dropdown-container { display: none; padding-left: 12px; margin-bottom: 6px; margin-top: 4px; }
        .dropdown-container a { padding: 9px 14px; font-size: 0.85rem; color: #1e40af; font-weight: 600; background: rgba(255, 255, 255, 0.2); border-radius: 8px; margin-bottom: 3px; }
        .dropdown-container a:hover { background: #ffffff; color: #0284c7; }
        .sidebar .logout-button { margin-top: auto; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; }
        .sidebar .logout-button i, .sidebar .logout-button span { color: #b91c1c !important; }

        /* CONTENT STYLE & GRID FONT */
        .content { margin-left: 260px; position: relative; }
        .navbar-custom { background: #ffffff; padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999; }
        .main-body-wrapper { padding: 40px; }
        
        .glass-form-card { background: var(--bg-card); border: 1px solid #e2e8f0; border-radius: 20px; padding: 40px; width: 100%; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04); }
        .detail-label { font-weight: 700; color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        .detail-value { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 12px 16px; font-size: 0.95rem; color: var(--text-main); font-weight: 600; min-height: 48px; display: flex; align-items: center; }

        .btn-back-custom { border-radius: 8px; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 10px 24px; font-size: 0.9rem; font-weight: 600; color: #475569; text-decoration: none; transition: all 0.2s; display: inline-flex; align-items: center; }
        .btn-back-custom:hover { background: #e2e8f0; color: #1e293b; }

        /* Media Thumbnail & Attachments Box */
        .attachment-card { border: 1px solid #e2e8f0; border-radius: 12px; background: #f8fafc; overflow: hidden; height: 100%; transition: transform 0.2s; }
        .attachment-card:hover { transform: translateY(-2px); }
        .img-preview { width: 100%; height: 160px; object-fit: cover; border-bottom: 1px solid #e2e8f0; }
        .file-icon-box { height: 160px; display: flex; align-items: center; justify-content: center; background: #edf2f7; border-bottom: 1px solid #e2e8f0; color: #64748b; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fa-solid fa-bolt text-primary"></i> I-CALM Panel</h3>
    <a href="/monitoring_barang/dashboard/index.php"><span class="menu-content-wrapper"><i class="fa-solid fa-chart-pie"></i><span>Dashboard</span></span></a>
    
    <button class="dropdown-btn"><span class="menu-content-wrapper"><i class="fa-solid fa-layer-group menu-icon"></i><span>Monitoring</span></span><i class="fa-solid fa-chevron-down dropdown-chevron"></i></button>
    <div class="dropdown-container"><a href="/monitoring_barang/material/index.php">Material Gudang</a><a href="/monitoring_barang/ba/index.php">Database BA</a></div>
    
    <button class="dropdown-btn active active-category-btn" style="background-color: #0284c7; color: white;"><span class="menu-content-wrapper"><i class="fa-solid fa-tags menu-icon" style="color: white !important;"></i><span>Kategori</span></span><i class="fa-solid fa-chevron-down dropdown-chevron"></i></button>
    <div class="dropdown-container" style="display: block;"><a href="/monitoring_barang/kategori/stok/stok.php">Stok</a><a href="/monitoring_barang/kategori/non_stok/non_stok.php" class="active-menu">Non Stok</a><a href="/monitoring_barang/kategori/non_po/non_po.php">Non PO</a><a href="/monitoring_barang/kategori/ex_bongkaran/ex_bongkaran.php">Ex Bongkaran</a><a href="/monitoring_barang/kategori/pre_memory/pre_memory.php">Pre Memory</a><a href="/monitoring_barang/kategori/peminjaman/peminjaman.php">Peminjaman</a><a href="/monitoring_barang/kategori/pemakaian/pemakaian.php">Pemakaian</a></div>
    
    <button class="dropdown-btn"><span class="menu-content-wrapper"><i class="fa-solid fa-file-import menu-icon"></i><span>Import</span></span><i class="fa-solid fa-chevron-down dropdown-chevron"></i></button>
    <div class="dropdown-container"><a href="/monitoring_barang/import/material.php">Import Material</a><a href="/monitoring_barang/import/ba.php">Import BA</a><a href="/monitoring_barang/import/form_stok.php">Import Stok</a><a href="/monitoring_barang/import/form_non_stok.php">Import Non Stok</a><a href="/monitoring_barang/import/form_non_po.php">Import Non PO</a><a href="/monitoring_barang/import/form_ex_bongkaran.php">Import Ex Bongkaran</a><a href="/monitoring_barang/import/form_pre_memory.php">Import Pre Memory</a><a href="/monitoring_barang/import/form_peminjaman.php">Import Peminjaman</a><a href="/monitoring_barang/import/form_pemakaian.php">Import Pemakaian</a></div>
    
    <button class="dropdown-btn"><span class="menu-content-wrapper"><i class="fa-solid fa-file-export menu-icon"></i><span>Export</span></span><i class="fa-solid fa-chevron-down dropdown-chevron"></i></button>
    <div class="dropdown-container"><a href="/monitoring_barang/export/material_excel.php">Export Material</a><a href="/monitoring_barang/export/ba_excel.php">Export BA</a></div>
    
    <a href="/monitoring_barang/login/logout.php" class="logout-button"><span class="menu-content-wrapper"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></span></a>
</div>

<div class="content">
    <nav class="navbar navbar-custom">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center" style="font-weight: 800; font-size: 1.3rem;">
                <i class="fa-solid fa-boxes-stacked text-primary me-2"></i> KENDALI LOGISTIK 
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Kategori: Non Stok / Detail Data</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="glass-form-card">
            <h4 class="fw-bold mb-4" style="color: #0f172a; letter-spacing: -0.02em;"><i class="fa-solid fa-circle-info text-primary me-2"></i>Rincian Informasi Material Non Stok</h4>
            
            <div class="row g-4">
                
                <div class="col-md-6">
                    <div class="detail-label">Nama Kelompok Material / Barang</div>
                    <div class="detail-value"><?= htmlspecialchars($data['nama_material'] ?? '-'); ?></div>
                </div>

                <div class="col-md-3">
                    <div class="detail-label">Satuan</div>
                    <div class="detail-value"><?= htmlspecialchars($data['satuan'] ?? '-'); ?></div>
                </div>

                <div class="col-md-3">
                    <div class="detail-label">Jumlah Volume</div>
                    <div class="detail-value"><?= number_format(abs((int)($data['jumlah'] ?? 0))); ?></div>
                </div>

                <div class="col-md-4">
                    <div class="detail-label">Nomor Rak</div>
                    <div class="detail-value"><?= htmlspecialchars($data['no_rak'] ?? '-'); ?></div>
                </div>

                <div class="col-md-4">
                    <div class="detail-label">Status Kondisi</div>
                    <div class="detail-value">
                        <span class="badge <?= (strtoupper($data['kondisi'] ?? '') == 'BAIK') ? 'bg-success' : 'bg-warning'; ?> px-3 py-2 fs-7">
                            <?= htmlspecialchars(strtoupper($data['kondisi'] ?? '-')); ?>
                        </span>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-label">Lokasi Penyimpanan</div>
                    <div class="detail-value"><?= htmlspecialchars($data['lokasi_penyimpanan'] ?? '-'); ?></div>
                </div>

                <!-- Bagian Render Lampiran Files / Dokumen / Foto -->
                <div class="col-12 mt-4">
                    <div class="detail-label">Lampiran Dokumentasi & Berkas</div>
                    <div class="row g-3">
                        <?php 
                        $files = json_decode($data['lampiran_files'] ?? '[]', true);
                        if (!empty($files) && is_array($files)): 
                            foreach ($files as $file): 
                                $file_path = "upload/" . $file;
                                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                $is_image = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                        ?>
                                <div class="col-sm-6 col-md-4 col-lg-3">
                                    <div class="attachment-card">
                                        <?php if ($is_image && file_exists($file_path)): ?>
                                            <a href="<?= $file_path; ?>" target="_blank">
                                                <img src="<?= $file_path; ?>" class="img-preview" alt="Foto Dokumentasi">
                                            </a>
                                        <?php else: ?>
                                            <div class="file-icon-box">
                                                <i class="fa-regular fa-file-lines fa-3x"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="p-2 text-center border-top">
                                            <a href="<?= file_exists($file_path) ? $file_path : '#'; ?>" target="_blank" class="btn btn-sm btn-outline-primary w-100" <?= !file_exists($file_path) ? 'disabled style="pointer-events: none; opacity: 0.5;"' : ''; ?>>
                                                <i class="fa-solid fa-download me-1"></i> Lihat / Unduh
                                            </a>
                                        </div>
                                    </div>
                                </div>
                        <?php 
                            endforeach; 
                        else: 
                        ?>
                            <div class="col-12">
                                <div class="alert alert-light border text-muted py-3 px-4">
                                    <i class="fa-solid fa-paperclip me-2"></i> Tidak ada lampiran berkas maupun foto untuk data material ini.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <div class="mt-4 pt-3 border-top d-flex gap-2">
                <a href="non_stok.php" class="btn-back-custom">
                    <i class="fa-solid fa-arrow-left-long me-2"></i> Kembali Ke Halaman Utama
                </a>
            </div>
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