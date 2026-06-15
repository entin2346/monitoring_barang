<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

$jumlah_import = 0;
$sukses_import = false;

if(isset($_POST['import'])){
    if($_FILES['file']['error'] == 0){
        $file = $_FILES['file']['tmp_name'];
        $handle = fopen($file, "r");

        // Lewati judul & header file CSV
        fgetcsv($handle, 10000, ",");
        fgetcsv($handle, 10000, ",");

        while(($data = fgetcsv($handle, 10000, ",")) !== FALSE){
            // Lewati jika baris kosong
            if(count(array_filter($data)) == 0){
                continue;
            }

            $nama_material = trim($data[1] ?? '');
            if($nama_material == ''){
                continue;
            }

            $nama_material = mysqli_real_escape_string($conn, $nama_material);
            $satuan        = mysqli_real_escape_string($conn, trim($data[2] ?? ''));
            $jumlah        = (int)($data[3] ?? 0);
            $no_rak        = mysqli_real_escape_string($conn, trim($data[4] ?? ''));
            $kondisi       = mysqli_real_escape_string($conn, trim($data[5] ?? ''));
            $lokasi        = mysqli_real_escape_string($conn, trim($data[6] ?? ''));
            $keterangan    = mysqli_real_escape_string($conn, trim($data[7] ?? ''));

            mysqli_query($conn,"
                INSERT INTO material_gudang 
                (nama_material, satuan, jumlah, no_rak, kondisi, lokasi_penyimpanan, keterangan) 
                VALUES 
                ('$nama_material', '$satuan', '$jumlah', '$no_rak', '$kondisi', '$lokasi', '$keterangan')
            ");

            $jumlah_import++;
        }
        fclose($handle);
        $sukses_import = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I-CALM | Import Material Futuristic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            --amber-glow: #fbbf24;
            --text-main: #f8fafc;
            --text-sub: #94a3b8;
            --border-glass: rgba(255, 255, 255, 0.06);
            --border-color: rgba(255, 255, 255, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body { 
            background: radial-gradient(circle at top right, #111638 0%, var(--bg-deep) 60%);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* SIDEBAR NAVIGATION PERSISTEN */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100%;
            background: #111827;
            border-right: 1px solid var(--border-color);
            padding-top: 28px;
            z-index: 1000;
            box-shadow: 10px 0 30px rgba(0,0,0,0.2);
        }
        
        .sidebar h3 { 
            font-size: 1.4rem; 
            font-weight: 800; 
            padding: 0 24px; 
            margin-bottom: 35px; 
            background: linear-gradient(135deg, #fff 30%, var(--primary-glow));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
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
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
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
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.3);
        }

        .sidebar a i, .dropdown-btn i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .sidebar .menu-text { flex-grow: 1; }
        .dropdown-chevron { font-size: 0.8rem !important; transition: transform 0.2s ease; }
        .dropdown-btn.active .dropdown-chevron { transform: rotate(180deg); color: var(--primary-glow); }

        .dropdown-container {
            display: none;
            background: rgba(0, 0, 0, 0.2); 
            padding: 4px 0;
        }
        .dropdown-container a { padding: 11px 24px 11px 56px; font-size: 0.85rem; font-weight: 500; }

        /* CONTENT WORKSPACE WRAPPER */
        .content { 
            margin-left: 260px; 
            min-height: 100vh;
        }

        .navbar-cyber {
            background: rgba(15, 19, 42, 0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-glass);
            padding: 18px 32px;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .navbar-brand-cyber {
            font-weight: 800;
            font-size: 1.3rem;
            background: linear-gradient(135deg, #ffffff 40%, var(--primary-glow));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .main-body-wrapper {
            padding: 40px 32px;
            max-width: 1000px;
        }

        /* METRICS STATUS MINI CARDS */
        .glass-mini-card {
            background: rgba(21, 27, 61, 0.4);
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .mini-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        /* CORE GLASSMORPHISM FILE UPLOAD CONTAINER */
        .cyber-import-container {
            background: rgba(15, 19, 42, 0.6);
            border: 1px solid var(--border-glass);
            border-radius: 24px;
            padding: 35px;
            backdrop-filter: blur(10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
        }

        .cyber-alert-info {
            background: rgba(56, 189, 248, 0.05);
            border: 1px solid rgba(56, 189, 248, 0.15);
            border-radius: 16px;
            padding: 20px;
            color: #e2e8f0;
        }

        .upload-drag-zone {
            border: 2px dashed rgba(251, 191, 36, 0.3);
            background: rgba(251, 191, 36, 0.02);
            border-radius: 20px;
            padding: 45px 30px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
        }
        .upload-drag-zone:hover {
            border-color: var(--amber-glow);
            background: rgba(251, 191, 36, 0.05);
            box-shadow: 0 0 25px rgba(251, 191, 36, 0.08);
        }

        /* CUSTOM FILE CONTROL OVERLAY */
        .file-input-cyber {
            background: #192048 !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            color: #fff !important;
            border-radius: 12px;
            padding: 12px;
            max-width: 450px;
            margin: 20px auto 0 auto;
        }
        .file-input-cyber::file-selector-button {
            background: var(--amber-glow);
            color: #060814;
            border: none;
            padding: 6px 16px;
            border-radius: 8px;
            font-weight: 700;
            margin-right: 12px;
            transition: 0.2s;
        }
        .file-input-cyber::file-selector-button:hover {
            background: #f59e0b;
        }

        .btn-action-submit {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border: none; color: #060814; font-weight: 800;
            padding: 14px 35px; border-radius: 12px;
            box-shadow: 0 0 20px rgba(251, 191, 36, 0.3);
            transition: all 0.3s;
        }
        .btn-action-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 30px rgba(251, 191, 36, 0.5);
            color: #060814;
        }
    </style>
</head>
<body>

<!-- SIDEBAR NAVIGATION MENU (LEFT FIXED) -->
<div class="sidebar">
    <h3><i class="fa-solid fa-bolt text-warning me-2"></i>I-CALM Panel</h3>

    <a href="../dashboard/index.php">
        <span><i class="fa-solid fa-chart-pie me-2"></i><span class="menu-text">Dashboard</span></span>
    </a>

    <button class="dropdown-btn">
        <span><i class="fa-solid fa-layer-group"></i><span class="menu-text">Monitoring</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../material/index.php">Material Gudang</a>
        <a href="../ba/index.php">Database BA</a>
    </div>

    <button class="dropdown-btn active">
        <span><i class="fa-solid fa-file-import"></i><span class="menu-text">Import</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container" style="display: block;">
        <a href="../import/material.php" class="active-menu">Import Material</a>
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

    <a href="../login/logout.php" class="mt-4">
        <span><i class="fa-solid fa-right-from-bracket text-danger"></i><span class="menu-text text-danger">Logout</span></span>
    </a>
</div>

<!-- CONTAINER LAYOUT (RIGHT SIDE) -->
<div class="content">

    <!-- TOP CONTROL HEADER NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-cyber">
        <div class="container-fluid px-0">
            <span class="navbar-brand-cyber d-flex align-items-center">
                <i class="fa-solid fa-cloud-arrow-up text-warning me-2"></i> SYSTEM LOADER <span style="font-weight: 300; font-size: 0.9rem; color: var(--text-sub); margin-left: 10px;">/ Bulk Import Material</span>
            </span>
        </div>
    </nav>

    <!-- MAIN BODY INNER WRAPPER -->
    <div class="main-body-wrapper">
        
        <?php
        $total = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM material_gudang"));
        ?>

        <!-- MINI ENGINE METRICS -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="glass-mini-card">
                    <div class="mini-card-icon" style="background: rgba(56, 189, 248, 0.1); color: var(--primary-glow);">
                        <i class="fa-solid fa-cubes"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-sub); font-weight: 700; letter-spacing: 0.5px;">Total Material Terdaftar</div>
                        <div style="font-size: 1.4rem; font-weight: 800; color: #fff;"><?= number_format($total['total']); ?> Record</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-mini-card">
                    <div class="mini-card-icon" style="background: rgba(52, 211, 153, 0.1); color: var(--emerald-glow);">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-sub); font-weight: 700; letter-spacing: 0.5px;">Status Sinkronisasi</div>
                        <div style="font-size: 1.4rem; font-weight: 800; color: var(--emerald-glow);">UPT MAKASSAR READY</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAIN UTILITY CONTAINER -->
        <div class="cyber-import-container">
            <h4 class="fw-bold mb-1" style="color: #fff;"><i class="fa-solid fa-box-open text-warning me-2"></i>Unggah Log Basis Data</h4>
            <p class="text-muted small mb-4">Pastikan struktur berkas penampung tabel eksternal sesuai dengan aturan pemetaan parser.</p>

            <!-- CRISP SCHEDULER DIRECTION LOG -->
            <div class="cyber-alert-info mb-4">
                <div class="d-flex gap-2 align-items-center mb-2">
                    <i class="fa-solid fa-triangle-exclamation text-warning"></i>
                    <strong style="font-size: 0.9rem; letter-spacing: 0.3px;">STANDARISASI STRUKTUR PARAMETER CSV:</strong>
                </div>
                <ul class="mb-0 small" style="padding-left: 20px; color: #94a3b8; line-height: 1.6;">
                    <li>Ekstensi berkas wajib bertipe komparasi sekuensial <strong class="text-white">.CSV (Comma Separated Values)</strong>.</li>
                    <li>Sistem otomatis melewati baris <strong class="text-warning">Judul (Row 1)</strong> dan <strong class="text-warning">Header Tabel (Row 2)</strong> pada template default.</li>
                    <li>Baris data yang tidak memiliki nilai/kosong pada entitas <strong class="text-danger">Nama Material</strong> akan otomatis diabaikan demi integritas data.</li>
                    <li>Eksekusi query bersifat *Auto-Commit*, rekaman log akan langsung dipetakan ke kluster tabel produksi.</li>
                </ul>
            </div>

            <!-- UPLOAD FORM LAYER -->
            <form method="POST" enctype="multipart/form-data">
                <div class="upload-drag-zone mb-4">
                    <i class="fa-solid fa-cloud-circle-arrow-up fa-3x text-warning mb-3 opacity-75"></i>
                    <h5 class="fw-bold text-white mb-1">Pilih Berkas Spreadsheet CSV</h5>
                    <p class="text-muted small mb-0">Klik tombol di bawah ini untuk mencari lokasi penyimpanan lokal data Anda</p>
                    
                    <input type="file" name="file" class="form-control file-input-cyber" accept=".csv" required>
                </div>

                <div class="d-flex justify-content-end gap-3">
                    <a href="material.php" class="btn btn-outline-secondary px-4 py-3 fw-bold border-opacity-20 text-light" style="border-radius: 12px;"><i class="fa-solid fa-circle-arrow-left me-1"></i> Kembali</a>
                    <button type="submit" name="import" class="btn btn-action-submit"><i class="fa-solid fa-server me-1"></i> Eksekusi Unggahan Data</button>
                </div>
            </form>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Handler Menu Dropdown Sidebar
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

    // Elegant SweetAlert Success Trigger
    <?php if($sukses_import): ?>
        Swal.fire({
            title: 'Sinkronisasi Sukses!',
            text: 'Berhasil mengintegrasikan sebanyak <?= $jumlah_import; ?> baris entitas baru ke sistem.',
            icon: 'success',
            background: '#151b3d',
            color: '#fff',
            confirmButtonColor: '#38bdf8',
            confirmButtonText: 'Buka Log Material'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location = 'material.php';
            }
        });
    <?php endif; ?>
</script>
</body>
</html>