<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

// Pastikan ID disaring dengan aman
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = mysqli_query($conn, "SELECT * FROM material_gudang WHERE id='$id'");
$data = mysqli_fetch_assoc($query);

// Jika data tidak ditemukan, kembalikan ke index
if(!$data){
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Profil Detail Material</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-base: #e2e8f0;            
            --bg-body: #f8fafc;
            --bg-card: rgba(255, 255, 255, 0.6); 
            --primary-brand: #0284c7;       
            --accent-blue: #3b82f6;         
            --text-main: #1e293b;           
            --text-muted: #64748b;          
            --border-glass: rgba(255, 255, 255, 0.7);
            --border-light: rgba(148, 163, 184, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body { 
            background: radial-gradient(circle at top right, #dbeafe 0%, var(--bg-base) 60%, #e0e7ff 100%);
            color: var(--text-main);
            min-height: 100vh;
        }

        /* ========================================================
           SIDEBAR OCEAN BLUE PREMIUM DESIGN (PERSISTEN & SELARAS)
        ========================================================= */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100%;
            background: linear-gradient(135deg, 
                        rgba(15, 32, 67, 0.95) 0%, 
                        rgba(9, 53, 122, 0.9) 50%, 
                        rgba(2, 132, 199, 0.85) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 28px;
            z-index: 1000;
            box-shadow: 5px 0 30px rgba(9, 53, 122, 0.15); 
        }
        
        .sidebar h3 { 
            font-size: 1.4rem; 
            font-weight: 800; 
            padding: 0 24px; 
            margin-bottom: 35px; 
            color: #ffffff;
            display: flex;
            align-items: center;
        }

        .sidebar h3 i {
            color: #38bdf8 !important;
            text-shadow: 0 0 12px rgba(56, 189, 248, 0.6);
        }
        
        .sidebar a, .dropdown-btn { 
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: rgba(255, 255, 255, 0.7); 
            text-decoration: none; 
            padding: 14px 24px; 
            font-size: 0.95rem;
            font-weight: 600;
            border: none;
            background: none;
            width: 100%;
            transition: all 0.25s;
            cursor: pointer;
        }
        
        .sidebar a:hover, .dropdown-btn:hover { 
            background: rgba(255, 255, 255, 0.08); 
            color: #ffffff;
        }

        .sidebar .active-menu {
            color: #ffffff !important; 
            background: linear-gradient(90deg, rgba(56, 189, 248, 0.2) 0%, rgba(56, 189, 248, 0.03) 100%) !important; 
            border-left: 4px solid #38bdf8; 
            padding-left: 20px;
        }

        .sidebar .active-menu i { color: #38bdf8 !important; }
        .sidebar a i, .dropdown-btn i { margin-right: 12px; font-size: 1.1rem; width: 20px; text-align: center; color: rgba(255, 255, 255, 0.6); }
        .sidebar a:hover i, .dropdown-btn:hover i { color: #ffffff; }
        .sidebar .menu-text { flex-grow: 1; }
        .dropdown-chevron { font-size: 0.8rem !important; transition: transform 0.2s ease; color: rgba(255, 255, 255, 0.5) !important; }
        .dropdown-btn.active .dropdown-chevron { transform: rotate(180deg); color: #38bdf8 !important; }

        .dropdown-container { display: none; background: rgba(0, 0, 0, 0.15); padding: 4px 0; }
        .dropdown-container a { padding: 11px 24px 11px 56px; font-size: 0.85rem; font-weight: 500; color: rgba(255, 255, 255, 0.6); }
        .dropdown-container a:hover { color: #38bdf8; background: transparent; }

        .sidebar .logout-button {
            margin-top: 30px;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 12px;
            width: calc(100% - 32px);
            margin-left: 16px;
            padding: 12px 16px;
        }
        .sidebar .logout-button:hover { background: rgba(239, 68, 68, 0.25) !important; }
        .sidebar .logout-button i, .sidebar .logout-button .menu-text { color: #fca5a5 !important; }

        /* ========================================================
           CONTENT CONTAINER MODERISASI
        ========================================================= */
        .content { margin-left: 260px; }
        
        .navbar-custom { 
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            padding: 18px 32px; 
            border-bottom: 1px solid var(--border-glass);
            position: sticky;
            top: 0;
            z-index: 999;
        }
        .navbar-custom .navbar-brand { color: var(--text-main); font-weight: 800; font-size: 1.4rem; letter-spacing: -0.5px;}

        .main-body-wrapper { padding: 40px 32px; }

        /* DETAIL CARD LAYOUT */
        .glass-profile-card {
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 10px 30px -10px rgba(148, 163, 184, 0.12), 0 1px 1px rgba(255,255,255,0.8) inset;
        }

        /* METADATA LIST ITEMS */
        .info-item {
            background: rgba(255, 255, 255, 0.4);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            padding: 18px 20px;
            height: 100%;
        }
        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            font-weight: 700;
            margin-bottom: 6px;
        }
        .info-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-main);
        }

        /* PHOTO THUMBNAIL WRAPPER */
        .photo-frame-container {
            background: rgba(255, 255, 255, 0.5);
            border: 2px dashed rgba(148, 163, 184, 0.25);
            border-radius: 20px;
            padding: 16px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 280px;
            height: 100%;
        }
        .img-material-premium {
            max-width: 100%;
            max-height: 280px;
            object-fit: contain;
            border-radius: 14px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.06);
        }

        /* BADGES */
        .badge-stock {
            background: rgba(2, 132, 199, 0.08);
            color: var(--primary-brand);
            border: 1px solid rgba(2, 132, 199, 0.15);
            padding: 4px 12px;
            border-radius: 8px;
            font-weight: 700;
        }
        .badge-status-baik {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
            padding: 4px 12px;
            border-radius: 8px;
            font-weight: 700;
        }
        .badge-status-other {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.2);
            padding: 4px 12px;
            border-radius: 8px;
            font-weight: 700;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fa-solid fa-bolt me-2"></i>I-CALM Panel</h3>

    <a href="../dashboard/index.php">
        <span><i class="fa-solid fa-chart-pie me-2"></i><span class="menu-text">Dashboard</span></span>
    </a>

    <button class="dropdown-btn active">
        <span><i class="fa-solid fa-layer-group"></i><span class="menu-text">Monitoring</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container" style="display: block;">
        <a href="../material/index.php" class="active-menu">Material Gudang</a>
        <a href="../ba/index.php">Database BA</a>
    </div>

    <button class="dropdown-btn">
        <span><i class="fa-solid fa-file-import"></i><span class="menu-text">Import</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../import/material.php">Import Material</a>
        <a href="../import/ba.php">Import BA</a>
    </div>

    <button class="dropdown-btn">
        <span><i class="fa-solid fa-file-export"></i><span class="menu-text">Export</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../export/material_excel.php">Export Material</a>
        <a href="../export/ba_excel.php">Export BA</a>
    </div>

    <a href="../login/logout.php" class="logout-button">
        <span><i class="fa-solid fa-right-from-bracket"></i><span class="menu-text">Logout</span></span>
    </a>
</div>

<div class="content">
    
    <nav class="navbar navbar-custom">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center">
                <i class="fa-solid fa-circle-info text-primary me-2"></i> RINCIAN SPESIFIKASI 
                <span class="ms-2" style="font-weight: 400; font-size: 0.95rem; color: var(--text-muted);">/ Data Material Logistik</span>
            </span>
            <div>
                <a href="index.php" class="btn btn-outline-secondary btn-sm px-3 fw-semibold border-2" style="border-radius: 10px;">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke List
                </a>
            </div>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="glass-profile-card">
            
            <div class="mb-4 pb-3 border-bottom" style="border-color: var(--border-light) !important;">
                <span class="text-uppercase small fw-bold tracking-wider text-primary">Identifikasi Komponen</span>
                <h2 class="fw-extrabold mt-1 mb-0" style="color: var(--text-main); font-weight: 800;"><?= htmlspecialchars($data['nama_material']); ?></h2>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="row g-3">
                        
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label"><i class="fa-solid fa-calculator me-1"></i> Volume / Jumlah Stok</div>
                                <div class="info-value">
                                    <span class="badge-stock"><?= number_format($data['jumlah']); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label"><i class="fa-solid fa-weight-scale me-1"></i> Satuan Ukur</div>
                                <div class="info-value"><?= htmlspecialchars($data['satuan'] ?: '-'); ?></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label"><i class="fa-solid fa-layer-group me-1"></i> Penomoran Rak</div>
                                <div class="info-value" style="color: var(--primary-brand);"><i class="fa-solid fa-box-open me-1.5 small text-muted"></i><?= htmlspecialchars($data['no_rak'] ?: '-'); ?></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label"><i class="fa-solid fa-heart-pulse me-1"></i> Kondisi Fisik</div>
                                <div class="info-value">
                                    <?php
                                    if(strtoupper($data['kondisi']) == 'BAIK'){
                                        echo "<span class='badge-status-baik'><i class='fa-solid fa-circle-check me-1 small'></i> BAIK</span>";
                                    } else {
                                        echo "<span class='badge-status-other'><i class='fa-solid fa-triangle-exclamation me-1 small'></i> ".htmlspecialchars(strtoupper($data['kondisi'] ?: 'TIDAK DIKETAHUI'))."</span>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="info-item">
                                <div class="info-label"><i class="fa-solid fa-map-location-dot me-1"></i> Titik Lokasi Penyimpanan</div>
                                <div class="info-value text-secondary"><i class="fa-solid fa-map-pin text-danger me-2"></i><?= htmlspecialchars($data['lokasi_penyimpanan'] ?: '-'); ?></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="info-item" style="background: rgba(255,255,255,0.25);">
                                <div class="info-label"><i class="fa-solid fa-note-sticky me-1"></i> Catatan Tambahan / Keterangan</div>
                                <div class="info-value text-muted" style="font-weight: 400; font-size: 0.95rem; line-height: 1.6;">
                                    <?= nl2br(htmlspecialchars($data['keterangan'] ?: 'Tidak ada deskripsi tambahan untuk material ini.')); ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="photo-frame-container">
                        <div class="info-label w-100 text-start mb-3 px-2"><i class="fa-solid fa-image me-1"></i> Lampiran Visual</div>
                        
                        <?php if(!empty($data['foto_material']) && file_exists("upload/".$data['foto_material'])){ ?>
                            <img src="upload/<?= $data['foto_material']; ?>" class="img-material-premium" alt="Foto Material">
                        <?php } else { ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fa-solid fa-image-notch d-block fs-1 mb-2 opacity-30"></i>
                                <span class="small fw-semibold">Dokumentasi Foto Kosong</span>
                            </div>
                        <?php } ?>
                    </div>
                </div>

            </div>

            <div class="mt-4 pt-3 d-flex gap-2 border-top" style="border-color: var(--border-light) !important;">
                <a href="edit.php?id=<?= $data['id']; ?>" class="btn btn-warning btn-sm px-4 py-2 fw-bold text-dark" style="border-radius: 10px; background: #fbbf24; border:none;">
                    <i class="fa-solid fa-user-pen me-1"></i> Ubah Konfigurasi
                </a>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
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
</script>

</body>
</html>