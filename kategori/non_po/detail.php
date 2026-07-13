<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}
include "../../config/koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = mysqli_query($conn, "SELECT * FROM non_po WHERE id = $id");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='non_po.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Detail Material Non PO</title>
    
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

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: var(--bg-body); color: var(--text-main); min-height: 100vh; overflow-x: auto; }

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
            border-radius: 8px; margin-bottom: 3px;
        }
        .dropdown-container a:hover { background: #ffffff; color: #0284c7; }
        .sidebar .logout-button { margin-top: auto; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; }
        .sidebar .logout-button i, .sidebar .logout-button span { color: #b91c1c !important; }

        /* CONTENT & LANDSCAPE LAYOUT STYLE */
        .content { margin-left: 260px; position: relative; min-width: 320px; }
        .navbar-custom { background: #ffffff; padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999; }
        .main-body-wrapper { padding: 40px; }
        
        .glass-detail-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 40px; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04); }
        
        /* Landscape Info Block Components */
        .info-label { font-weight: 700; color: #475569; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .info-value-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px; font-size: 0.9rem; color: var(--text-main); min-height: 48px; display: flex; align-items: center; word-break: break-all; }
        
        .badge-jumlah { background: rgba(2, 132, 199, 0.08) !important; color: var(--primary) !important; font-weight: 700; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem; border: 1px solid rgba(2, 132, 199, 0.15); }

        .btn-back-custom { border-radius: 8px; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 10px 24px; font-size: 0.9rem; font-weight: 600; color: #475569; text-decoration: none; transition: all 0.2s; display: inline-flex; align-items: center; }
        .btn-back-custom:hover { background: #e2e8f0; color: #1e293b; }
        
        /* Attachment Elements */
        .attachment-container { display: flex; flex-wrap: wrap; gap: 12px; width: 100%; }
        .img-thumbnail-custom { width: 120px; height: 120px; object-fit: cover; border-radius: 8px; border: 1px solid #cbd5e1; transition: transform 0.2s; cursor: pointer; }
        .img-thumbnail-custom:hover { transform: scale(1.05); }
        .file-download-box { display: inline-flex; align-items: center; gap: 8px; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 8px 14px; border-radius: 8px; text-decoration: none; color: #334155; font-size: 0.85rem; font-weight: 600; transition: all 0.2s; }
        .file-download-box:hover { background: #e2e8f0; color: #0f172a; }

        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; border-right: none; border-bottom: 1px solid rgba(2, 132, 199, 0.15); padding: 20px; }
            .content { margin-left: 0; }
            .main-body-wrapper { padding: 20px; }
            .navbar-custom { padding: 20px; }
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
        <a href="/monitoring_barang/kategori/non_po/non_po.php" class="active-menu">Non PO</a>
        <a href="/monitoring_barang/kategori/ex_bongkaran/ex_bongkaran.php">Ex Bongkaran</a>
        <a href="/monitoring_barang/kategori/pre_memory/pre_memory.php">Pre Memory</a>
        <a href="/monitoring_barang/kategori/peminjaman/peminjaman.php">Peminjaman</a>
        <a href="/monitoring_barang/kategori/pemakaian/pemakaian.php">Pemakaian</a>
    </div>

    <button class="dropdown-btn"><span class="menu-content-wrapper"><i class="fa-solid fa-file-import menu-icon"></i><span>Import</span></span><i class="fa-solid fa-chevron-down dropdown-chevron"></i></button>
    <div class="dropdown-container"><a href="/monitoring_barang/import/material.php">Import Material</a><a href="/monitoring_barang/import/ba.php">Import BA</a><a href="/monitoring_barang/import/form_stok.php">Import Stok</a><a href="/monitoring_barang/import/form_non_stok.php">Import Non Stok</a><a href="/monitoring_barang/import/form_non_po.php">Import Non PO</a><a href="/monitoring_barang/import/form_ex_bongkaran.php">Import Ex Bongkaran</a><a href="/monitoring_barang/import/form_pre_memory.php">Import Pre Memory</a><a href="/monitoring_barang/import/form_peminjaman.php">Import Peminjaman</a><a href="/monitoring_barang/import/form_pemakaian.php">Import Pemakaian</a></div>

    <button class="dropdown-btn"><span class="menu-content-wrapper"><i class="fa-solid fa-file-export menu-icon"></i><span>Export</span></span><i class="fa-solid fa-chevron-down dropdown-chevron"></i></button>
    <div class="dropdown-container"><a href="/monitoring_barang/export/material_excel.php">Export Material</a><a href="/monitoring_barang/export/ba_excel.php">Export BA</a></div>
    
    <a href="/monitoring_barang/login/logout.php" class="logout-button"><span class="menu-content-wrapper"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></span></a>
</div>

<div class="content">
    <nav class="navbar navbar-custom">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center">
                <i class="fa-solid fa-boxes-stacked text-primary me-2"></i> KENDALI LOGISTIK 
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Kategori: Non PO / Informasi Detail</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="glass-detail-card">
            <h4 class="fw-bold mb-4" style="color: #0f172a; letter-spacing: -0.02em;"><i class="fa-solid fa-circle-info text-primary me-2"></i>Lembar Detail Item Non PO</h4>
            
            <!-- Tampilan berbentuk baris kolom melebar (Landscape Grid) -->
            <div class="row g-4">
                
                <div class="col-md-4">
                    <div class="info-label">Jenis BA</div>
                    <div class="info-value-box"><?= htmlspecialchars($data['jenis_ba'] ?? '-'); ?></div>
                </div>

                <div class="col-md-4">
                    <div class="info-label">Tanggal Sistem</div>
                    <div class="info-value-box"><?= !empty($data['tanggal']) ? date('d-m-Y', strtotime($data['tanggal'])) : '-'; ?></div>
                </div>

                <div class="col-md-4">
                    <div class="info-label">Satuan</div>
                    <div class="info-value-box"><?= htmlspecialchars($data['satuan'] ?? '-'); ?></div>
                </div>

                <div class="col-md-8">
                    <div class="info-label">Nama Barang / Material</div>
                    <div class="info-value-box fw-semibold"><?= htmlspecialchars($data['nama_material'] ?? '-'); ?></div>
                </div>

                <div class="col-md-4">
                    <div class="info-label">Jumlah Volume</div>
                    <div class="info-value-box">
                        <span class="badge-jumlah"><?= number_format((int)($data['jumlah'] ?? 0)); ?></span>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-label">Merk / Jenis</div>
                    <div class="info-value-box"><?= htmlspecialchars($data['merk_jenis'] ?? '-'); ?></div>
                </div>

                <div class="col-md-4">
                    <div class="info-label">Jenis Barang</div>
                    <div class="info-value-box"><?= htmlspecialchars($data['jenis_barang'] ?? '-'); ?></div>
                </div>

                <div class="col-md-4">
                    <div class="info-label">Sumber Barang</div>
                    <div class="info-value-box"><?= htmlspecialchars($data['sumber_barang'] ?? '-'); ?></div>
                </div>

                <div class="col-md-4">
                    <div class="info-label">Tujuan</div>
                    <div class="info-value-box"><?= htmlspecialchars($data['tujuan'] ?? '-'); ?></div>
                </div>

                <div class="col-md-4">
                    <div class="info-label">Kondisi</div>
                    <div class="info-value-box"><?= htmlspecialchars($data['kondisi'] ?? $data['kondisi_material'] ?? '-'); ?></div>
                </div>

                <div class="col-md-4">
                    <div class="info-label">Vendor</div>
                    <div class="info-value-box"><?= htmlspecialchars($data['vendor'] ?? $data['asal_vendor'] ?? '-'); ?></div>
                </div>

                <div class="col-md-12">
                    <div class="info-label">Berita Acara</div>
                    <div class="info-value-box"><?= htmlspecialchars($data['berita_acara'] ?? '-'); ?></div>
                </div>

                <!-- Bagian Preview Banyak Berkas & Foto Resmi -->
                <div class="col-12">
                    <div class="info-label">Berkas / Foto Terlampir Saat Ini</div>
                    <div class="info-value-box py-3" style="min-height: 80px;">
                        <div class="attachment-container">
                            <?php 
                            if (!empty($data['lampiran_files'])) {
                                $files = json_decode($data['lampiran_files'], true);
                                if (is_array($files) && count($files) > 0) {
                                    foreach ($files as $file) {
                                        $file_path = "upload/" . $file;
                                        if (file_exists($file_path)) {
                                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                            $allowed_img = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                                            
                                            if (in_array($ext, $allowed_img)) {
                                                echo '<a href="'.$file_path.'" target="_blank">
                                                        <img src="'.$file_path.'" class="img-thumbnail-custom" alt="Lampiran Foto">
                                                      </a>';
                                            } else {
                                                echo '<a href="'.$file_path.'" class="file-download-box" target="_blank">
                                                        <i class="fa-solid fa-file-lines text-primary fs-5"></i>
                                                        <span>'.htmlspecialchars($file).'</span>
                                                      </a>';
                                            }
                                        }
                                    }
                                } else {
                                    echo '<span class="text-muted small"><i class="fa-solid fa-paperclip me-1"></i> Belum ada file atau foto dokumentasi yang diunggah.</span>';
                                }
                            } else {
                                echo '<span class="text-muted small"><i class="fa-solid fa-paperclip me-1"></i> Belum ada file atau foto dokumentasi yang diunggah.</span>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

            </div>

            <div class="mt-4 pt-2">
                <a href="non_po.php" class="btn-back-custom"><i class="fa-solid fa-arrow-left me-2"></i>Kembali ke List</a>
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
            if (container.style.display === "block") {
                container.style.display = "none";
            } else {
                container.style.display = "block";
            }
        });
    });
</script>
</body>
</html>