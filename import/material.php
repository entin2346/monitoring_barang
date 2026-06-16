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

        // Prepared Statement untuk bulk import yang aman dan cepat
        $stmt = $conn->prepare("INSERT INTO material_gudang 
            (nama_material, satuan, jumlah, no_rak, kondisi, lokasi_penyimpanan, keterangan) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");

        while(($data = fgetcsv($handle, 10000, ",")) !== FALSE){
            if(count(array_filter($data)) == 0){
                continue;
            }

            $nama_material = trim($data[1] ?? '');
            if($nama_material == ''){
                continue;
            }

            $satuan     = trim($data[2] ?? '');
            $jumlah     = (int)($data[3] ?? 0);
            $no_rak     = trim($data[4] ?? '');
            $kondisi    = trim($data[5] ?? '');
            $lokasi     = trim($data[6] ?? '');
            $keterangan = trim($data[7] ?? '');

            $stmt->bind_param("ssissss", $nama_material, $satuan, $jumlah, $no_rak, $kondisi, $lokasi, $keterangan);
            $stmt->execute();

            $jumlah_import++;
        }
        fclose($handle);
        $stmt->close();
        $sukses_import = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Import Material</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-base: #e2e8f0;            
            --bg-body: #f8fafc;
            --bg-card: rgba(255, 255, 255, 0.65); 
            --primary-brand: #0284c7;       
            --accent-blue: #3b82f6;         
            --text-main: #1e293b;           
            --text-muted: #64748b;          
            --border-glass: rgba(255, 255, 255, 0.7);
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
            overflow-x: hidden;
        }

        /* ========================================================
            SIDEBAR NAVIGATION (PERSISTEN)
        ========================================================= */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100%;
            background: linear-gradient(135deg, rgba(15, 32, 67, 0.95) 0%, rgba(9, 53, 122, 0.9) 50%, rgba(2, 132, 199, 0.85) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 28px;
            z-index: 1000;
            box-shadow: 5px 0 30px rgba(9, 53, 122, 0.15); 
        }
        
        .sidebar h3 { 
            font-size: 1.4rem; font-weight: 800; padding: 0 24px; margin-bottom: 35px; color: #ffffff;
            display: flex; align-items: center;
        }
        .sidebar h3 i { color: #38bdf8 !important; text-shadow: 0 0 12px rgba(56, 189, 248, 0.6); }
        
        .sidebar a, .dropdown-btn { 
            display: flex; align-items: center; justify-content: space-between; color: rgba(255, 255, 255, 0.7); 
            text-decoration: none; padding: 14px 24px; font-size: 0.95rem; font-weight: 600; border: none; background: none; width: 100%; transition: all 0.25s; cursor: pointer;
        }
        .sidebar a:hover, .dropdown-btn:hover { background: rgba(255, 255, 255, 0.08); color: #ffffff; }

        .sidebar .active-menu {
            color: #ffffff !important; 
            background: linear-gradient(90deg, rgba(56, 189, 248, 0.2) 0%, rgba(56, 189, 248, 0.03) 100%) !important; 
            border-left: 4px solid #38bdf8; padding-left: 20px;
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
            margin-top: 30px; background: rgba(239, 68, 68, 0.1); border-radius: 12px; width: calc(100% - 32px); margin-left: 16px; padding: 12px 16px;
        }
        .sidebar .logout-button:hover { background: rgba(239, 68, 68, 0.25) !important; }
        .sidebar .logout-button i, .sidebar .logout-button .menu-text { color: #fca5a5 !important; }

        /* ========================================================
            LAYOUT UTAMA & CENTRALIZED CONTAINER
        ========================================================= */
        .content { margin-left: 260px; }
        
        .navbar-custom { 
            background: rgba(255, 255, 255, 0.45); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            padding: 18px 32px; border-bottom: 1px solid var(--border-glass); position: sticky; top: 0; z-index: 999;
        }
        .navbar-custom .navbar-brand { color: var(--text-main); font-weight: 800; font-size: 1.4rem; letter-spacing: -0.5px;}

        .main-body-wrapper { padding: 40px 32px; }

        /* METRICS MINI CARD */
        .glass-mini-card {
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            border-radius: 20px; padding: 20px;
            display: flex; align-items: center; gap: 16px;
            box-shadow: 0 10px 25px -10px rgba(148, 163, 184, 0.1);
            height: 100%;
        }
        .mini-card-icon {
            width: 48px; height: 48px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center; font-size: 1.3rem;
        }

        /* CARD PANEL UTAMA (CENTERED STYLING) */
        .cyber-import-container {
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            border-radius: 24px; padding: 40px;
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 25px 50px -12px rgba(148, 163, 184, 0.2);
        }

        .cyber-alert-info {
            background: rgba(2, 132, 199, 0.05);
            border: 1px solid rgba(2, 132, 199, 0.12);
            border-radius: 16px; padding: 20px;
        }

        /* AREA UNGGAH BERKAS (DRAG & DROP ZONE) */
        .upload-drag-zone {
            border: 2px dashed rgba(2, 132, 199, 0.25);
            background: rgba(255, 255, 255, 0.4);
            border-radius: 20px; padding: 50px 30px; text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative;
        }
        .upload-drag-zone:hover {
            border-color: var(--primary-brand);
            background: rgba(255, 255, 255, 0.8);
            box-shadow: 0 12px 30px rgba(2, 132, 199, 0.08);
            transform: translateY(-2px);
        }

        .file-input-cyber {
            background: #ffffff !important;
            border: 1px solid rgba(148, 163, 184, 0.3) !important;
            color: var(--text-main) !important;
            border-radius: 12px; padding: 12px; max-width: 450px; margin: 24px auto 0 auto;
            font-weight: 500; box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        }
        .file-input-cyber::file-selector-button {
            background: var(--primary-brand); color: #fff; border: none;
            padding: 6px 18px; border-radius: 8px; font-weight: 600; margin-right: 12px;
            transition: 0.2s;
        }
        .file-input-cyber::file-selector-button:hover { background: #0369a1; }

        /* BUTTON ACTIONS */
        .btn-action-submit {
            background: linear-gradient(135deg, #0284c7, #0369a1);
            border: none; color: #fff; font-weight: 700;
            padding: 14px 32px; border-radius: 12px;
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.25);
            transition: all 0.25s;
        }
        .btn-action-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(2, 132, 199, 0.4);
        }
        .btn-back-custom {
            border-radius: 12px; padding: 14px 28px; font-weight: 600;
            background: #ffffff; border: 1px solid rgba(148, 163, 184, 0.3); color: var(--text-muted);
            transition: all 0.2s;
        }
        .btn-back-custom:hover {
            background: #f8fafc; color: var(--text-main); border-color: rgba(148, 163, 184, 0.5);
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fa-solid fa-bolt me-2"></i>I-CALM Panel</h3>

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

    <a href="../login/logout.php" class="logout-button">
        <span><i class="fa-solid fa-right-from-bracket"></i><span class="menu-text">Logout</span></span>
    </a>
</div>

<div class="content">

    <nav class="navbar navbar-custom">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center">
                <i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i> UTALITAS IMPOR 
                <span class="ms-2" style="font-weight: 400; font-size: 0.95rem; color: var(--text-muted);">/ Bulk Import Material</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper container-fluid">
        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-10">
                
                <?php
                $total = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM material_gudang"));
                ?>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="glass-mini-card">
                            <div class="mini-card-icon" style="background: rgba(2, 132, 199, 0.1); color: var(--primary-brand);">
                                <i class="fa-solid fa-cubes"></i>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; letter-spacing: 0.6px;">Total Material Terdaftar</div>
                                <div style="font-size: 1.35rem; font-weight: 800; color: var(--text-main);"><?= number_format($total['total']); ?> Record</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="glass-mini-card">
                            <div class="mini-card-icon" style="background: rgba(22, 163, 74, 0.1); color: #16a34a;">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; letter-spacing: 0.6px;">Status Sinkronisasi</div>
                                <div style="font-size: 1.35rem; font-weight: 800; color: #16a34a;">UPT MAKASSAR READY</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cyber-import-container">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-dark mb-1"><i class="fa-solid fa-box-open text-primary me-2"></i>Unggah Log Basis Data</h4>
                        <p class="text-muted small">Pastikan struktur berkas penampung tabel eksternal sesuai dengan aturan pemetaan parser.</p>
                    </div>

                    <div class="cyber-alert-info mb-4">
                        <div class="d-flex gap-2 align-items-center mb-2">
                            <i class="fa-solid fa-circle-info text-primary"></i>
                            <strong style="font-size: 0.85rem; letter-spacing: 0.3px; color: #0369a1;">STANDARISASI STRUKTUR PARAMETER CSV:</strong>
                        </div>
                        <ul class="mb-0 small text-secondary" style="padding-left: 20px; line-height: 1.6; font-weight: 500;">
                            <li>Ekstensi berkas wajib bertipe komparasi sekuensial <strong>.CSV (Comma Separated Values)</strong>.</li>
                            <li>Sistem otomatis melewati baris <span class="text-dark fw-semibold">Judul (Row 1)</span> dan <span class="text-dark fw-semibold">Header Tabel (Row 2)</span> pada template default.</li>
                            <li>Baris data tanpa nilai pada entitas <span class="text-danger fw-semibold">Nama Material</span> akan diabaikan otomatis demi integritas database.</li>
                            <li>Eksekusi query menggunakan <em>Prepared Statement</em> untuk menjaga performa transaksi massal yang aman.</li>
                        </ul>
                    </div>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="upload-drag-zone mb-4">
                            <i class="fa-solid fa-cloud-arrow-up fa-3x text-primary mb-3 opacity-75"></i>
                            <h5 class="fw-bold text-dark mb-1">Pilih Berkas Spreadsheet CSV</h5>
                            <p class="text-muted small mb-0">Klik tombol di bawah ini untuk mencari lokasi penyimpanan data lokal Anda</p>
                            
                            <input type="file" name="file" class="form-control file-input-cyber" accept=".csv" required>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="material.php" class="btn btn-back-custom">
                                Kembali
                            </a>
                            <button type="submit" name="import" class="btn btn-action-submit">
                                <i class="fa-solid fa-server me-1"></i> Eksekusi Unggahan Data
                            </button>
                        </div>
                    </form>
                </div>

            </div>
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
            background: '#ffffff',
            color: '#1e293b',
            confirmButtonColor: '#0284c7',
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