<?php
session_start();

// 1. PROTEKSI LOGIN
if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

// 2. PROSES BACKEND CSV STOK
if (isset($_POST['submit_import'])) {
    if (isset($_FILES['file_csv']) && $_FILES['file_csv']['error'] == 0) {
        
        $filename = $_FILES['file_csv']['tmp_name'];
        
        if (($handle = fopen($filename, "r")) !== FALSE) {
            
            $sukses_insert = 0;
            $gagal_insert = 0;
            $index = 0;
            
            while (($row = fgetcsv($handle, 10000, ",")) !== FALSE) {

                
                // Lewati baris pertama karena merupakan Header/Judul Kolom CSV
               if ($index == 0) {
    $index++;
    continue;
}
                
                // Pemetaan Kolom CSV berdasarkan gambar tabel Stok / Non Stok:
             $nama_material      = mysqli_real_escape_string($conn, trim($row[2] ?? ''));
$satuan             = mysqli_real_escape_string($conn, trim($row[3] ?? ''));
$jumlah_stok        = (float)($row[4] ?? 0);
$kategori_sub       = mysqli_real_escape_string($conn, trim($row[5] ?? ''));
$no_rak             = mysqli_real_escape_string($conn, trim($row[0] ?? ''));
$kondisi            = 'BAIK';
$lokasi_penyimpanan = mysqli_real_escape_string($conn, trim($row[0] ?? ''));
$jenis_kategori     = 'stok';

// karena CSV tidak punya kolom kondisi
$kondisi = "Baik";

$jenis_kategori = "stok";

                // Lewati baris jika nama material kosong
                if (empty(trim($nama_material))) {
                    continue;
                }
                
                // Query Insert Data menyesuaikan kolom database
                $query = "
INSERT INTO material_gudang
(
    nama_material,
    keterangan,
    satuan,
    jumlah,
    no_rak,
    kondisi,
    lokasi_penyimpanan,
    jenis_kategori
)
VALUES
(
    '$nama_material',
    '$kategori_sub',
    '$satuan',
    '$jumlah_stok',
    '$no_rak',
    '$kondisi',
    '$lokasi_penyimpanan',
    '$jenis_kategori'
)";

if (mysqli_query($conn, $query)) {
    $sukses_insert++;
} else {
    $gagal_insert++;
    echo mysqli_error($conn) . "<br>";
}

$index++;
            }
            fclose($handle);
            
            echo "<script>alert('Berhasil mengimport $sukses_insert data CSV Stok! Gagal: $gagal_insert'); window.location='../kategori/stok/stok.php'; </script>";
            exit;
        } else {
            echo "<script>alert('Gagal membuka file CSV.');</script>";
        }
    } else {
        echo "<script>alert('Terjadi kesalahan upload file.');</script>";
    }
}

// 3. AMBIL STATISTIK UNTUK TAMPILAN INFORMASI HALAMAN STOK
$q_stok = mysqli_query($conn, "SELECT COUNT(*) as total_stok FROM material_gudang WHERE jenis_kategori = 'stok'");
$res_stok = mysqli_fetch_assoc($q_stok);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I-CALM | Import Stok</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-base: #f4f7fc;            
            --bg-card: #ffffff; 
            --primary-brand: #0284c7;       
            --text-main: #0f172a;           
            --text-muted: #64748b;          
            --border-glass: rgba(148, 163, 184, 0.15);
            --bg-sidebar: #d0e1f9;
        }

        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body { 
            background: var(--bg-base); color: var(--text-main);
            min-height: 100vh; overflow-x: hidden;
        }

        .sidebar {
            position: fixed; left: 0; top: 0; width: 260px; height: 100%;
            background-color: var(--bg-sidebar); border-right: 1px solid rgba(2, 132, 199, 0.15);
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
        }
        .dropdown-container a:hover { background: #ffffff; color: #0284c7; }

        .sidebar .logout-button { 
            margin-top: auto; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; 
        }
        .sidebar .logout-button i, .sidebar .logout-button span { color: #b91c1c !important; }
        .sidebar .logout-button:hover { background: #fee2e2; transform: none; }

        .content { margin-left: 260px; background: transparent; }
        
        .navbar-custom { 
            background: rgba(255, 255, 255, 0.5); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            padding: 18px 32px; border-bottom: 1px solid var(--border-glass); position: sticky; top: 0; z-index: 999;
        }
        .navbar-custom .navbar-brand { color: var(--text-main); font-weight: 800; font-size: 1.4rem; letter-spacing: -0.5px;}

        .main-body-wrapper { padding: 40px 32px; }

        .cyber-card { background: #ffffff; border: 1px solid var(--border-glass); border-radius: 16px; padding: 30px; }
        .input-cyber-group { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 8px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fa-solid fa-bolt text-primary"></i> I-CALM Panel</h3>
    <a href="../dashboard/index.php"><span class="menu-content-wrapper"><i class="fa-solid fa-chart-pie"></i><span>Dashboard</span></span></a>
    
    <button class="dropdown-btn">
        <span class="menu-content-wrapper"><i class="fa-solid fa-layer-group menu-icon"></i><span>Monitoring</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../material/index.php">Material Gudang</a>
        <a href="../ba/index.php">Database BA</a>
    </div>
    
    <button class="dropdown-btn">
        <span class="menu-content-wrapper"><i class="fa-solid fa-tags menu-icon"></i><span>Kategori</span></span>
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

    <button class="dropdown-btn active">
        <span class="menu-content-wrapper"><i class="fa-solid fa-file-import menu-icon"></i><span>Import</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container" style="display: block;">
        <a href="material.php">Import Material</a>
        <a href="ba.php">Import BA</a>
        <a href="../import/form_stok.php" class="active-menu">Import Stok</a>
        <a href="../import/form_non_stok.php">Import Non Stok</a>
        <a href="../import/form_non_po.php">Import Non PO</a>
        <a href="../import/form_ex_bongkaran.php">Import Ex Bongkaran</a>
        <a href="../import/form_pre_memory.php">Import Pre Memory</a>
        <a href="../import/form_peminjaman.php">Import Peminjaman</a>
        <a href="../import/form_pemakaian.php">Import Pemakaian</a>
    </div>

    <button class="dropdown-btn">
        <span class="menu-content-wrapper"><i class="fa-solid fa-file-export menu-icon"></i><span>Export</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../export/material_excel.php">Export Material</a>
        <a href="../export/ba_excel.php">Export BA</a>
    </div>

    <a href="../login/logout.php" class="logout-button"><span class="menu-content-wrapper"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></span></a>
</div>

<div class="content">
    <nav class="navbar navbar-custom">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center">
                <i class="fa-solid fa-file-csv text-success me-2"></i> DATA IMPORT SYSTEM SINGLE-FILE
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="cyber-card">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h5 class="fw-bold m-0" style="color: var(--text-main);">
                            <i class="fa-solid fa-upload text-primary me-2"></i> Import CSV - Stok
                        </h5>
                        <span class="badge bg-info text-dark fw-bold px-3 py-2" style="border-radius: 8px;">
                            Total Data: <?= number_format($res_stok['total_stok']); ?> Item
                        </span>
                    </div>

                    <div class="alert alert-warning border-0 p-3 mb-4" style="border-radius: 12px; background-color: rgba(245, 158, 11, 0.08); color: #b45309;">
                        <i class="fa-solid fa-circle-info me-2"></i> 
                        <strong>Format File:</strong> Form ini memproses file ekstensi <strong>.csv</strong>. Data Anda akan langsung diproses ke dalam database setelah tombol ditekan.
                    </div>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-2">Pilih File CSV Master Stok</label>
                            <div class="input-cyber-group">
                                <input type="file" name="file_csv" class="form-control border-0 bg-transparent" accept=".csv" required>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="../kategori/stok/stok.php" class="btn btn-light w-100 fw-bold py-2" style="border-radius: 12px; border: 1px solid #cbd5e1;">
                                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Kategori
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" name="submit_import" class="btn btn-success text-white w-100 fw-bold py-2" style="border-radius: 12px; background: linear-gradient(135deg, #10b981, #059669); border: none;">
                                    <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload & Proses
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
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