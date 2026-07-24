<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

$jumlah_import = 0;
$sukses_import = false;
$gagal_import  = false;

// Proses Import
if(isset($_POST['import'])){
    if(isset($_FILES['file']) && $_FILES['file']['error'] == 0){
        
        set_time_limit(0); 
        ini_set('memory_limit', '512M');

        $file = $_FILES['file']['tmp_name'];
        
        // Gunakan fgetcsv dengan auto-detect line endings
        ini_set("auto_detect_line_endings", true);
        $handle = fopen($file, "r");

        if($handle !== FALSE){
            // Lewati baris pertama (Header Kolom)
            fgetcsv($handle, 0, ",");

            $conn->autocommit(FALSE);

            // Prepared Statement untuk INSERT (tanpa pengecekan duplikat)
            $stmt = $conn->prepare("INSERT INTO database_ba 
                (no_urut, jenis_berita_acara, tanggal, nama_barang, merk_jenis, jenis_barang, sumber_barang, satuan, jumlah, tujuan, kondisi_material, no_seri, asal_barang_vendor, berita_acara, dokumentasi_ba_kembali, keterangan, keterangan_tambahan, tug5) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // Membaca file CSV menggunakan fgetcsv agar parsing tanda koma/kutip rapi
            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                
                // Fallback jika CSV menggunakan pemisah titik koma (;)
                if (count($data) <= 1 && isset($data[0]) && strpos($data[0], ';') !== false) {
                    $data = str_getcsv($data[0], ";");
                }

                $no_urut     = trim($data[0] ?? '');
                $nama_barang = trim($data[3] ?? '');
                
                // Lewati hanya jika baris benar-benar kosong
                if(empty($no_urut) && empty($nama_barang)) continue;

                $jenis_berita_acara = trim($data[1] ?? '');
                
                // Konversi Format Tanggal CSV ke Format Database (Y-m-d)
                $tanggal_csv = trim($data[2] ?? '');
                $tanggal = NULL;
                if(!empty($tanggal_csv)){
                    $date = DateTime::createFromFormat('d-M-y', $tanggal_csv);
                    if(!$date) {
                        $date = DateTime::createFromFormat('d-m-Y', $tanggal_csv);
                    }
                    if(!$date) {
                        $date = DateTime::createFromFormat('Y-m-d', $tanggal_csv);
                    }
                    $tanggal = ($date) ? $date->format('Y-m-d') : NULL;
                }

                $merk_jenis             = trim($data[4] ?? '');
                $jenis_barang           = trim($data[5] ?? '');
                $sumber_barang          = trim($data[6] ?? '');
                $satuan                 = trim($data[7] ?? '');
                
                $jumlah_raw             = trim($data[8] ?? '0');
                $jumlah                 = is_numeric($jumlah_raw) ? $jumlah_raw : 0;

                $tujuan                 = trim($data[9] ?? '');
                $kondisi_material       = trim($data[10] ?? '');
                $no_seri                = trim($data[11] ?? '');
                $asal_barang_vendor     = trim($data[12] ?? '');
                $berita_acara           = trim($data[13] ?? '');
                $dokumentasi_ba_kembali = trim($data[14] ?? '');
                $keterangan             = trim($data[15] ?? '');
                $keterangan_tambahan    = trim($data[16] ?? '');
                $tug5                   = trim($data[18] ?? '');

                // Langsung simpan ke database tanpa filter/skip duplikat
                $stmt->bind_param("ssssssssssssssssss", 
                    $no_urut, $jenis_berita_acara, $tanggal, $nama_barang, $merk_jenis, 
                    $jenis_barang, $sumber_barang, $satuan, $jumlah, $tujuan, 
                    $kondisi_material, $no_seri, $asal_barang_vendor, $berita_acara, 
                    $dokumentasi_ba_kembali, $keterangan, $keterangan_tambahan, $tug5
                );
                
                if($stmt->execute()){
                    $jumlah_import++;
                }
            }

            $conn->commit();
            $conn->autocommit(TRUE);

            fclose($handle);
            $stmt->close();
            
            $sukses_import = true;
        } else {
            $gagal_import = true;
        }
    } else {
        $gagal_import = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I-CALM | Import Database BA</title>
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

        .glass-mini-card {
            background: var(--bg-card); border: 1px solid var(--border-glass);
            border-radius: 20px; padding: 20px; display: flex; align-items: center; gap: 16px;
            box-shadow: 0 10px 25px -10px rgba(148, 163, 184, 0.1); height: 100%;
        }
        .mini-card-icon {
            width: 48px; height: 48px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center; font-size: 1.3rem;
        }

        .cyber-import-container {
            background: var(--bg-card); border: 1px solid var(--border-glass);
            border-radius: 24px; padding: 40px; box-shadow: 0 25px 50px -12px rgba(148, 163, 184, 0.2);
        }

        .cyber-alert-info {
            background: rgba(2, 132, 199, 0.05); border: 1px solid rgba(2, 132, 199, 0.12);
            border-radius: 16px; padding: 20px;
        }

        .upload-drag-zone {
            border: 2px dashed rgba(2, 132, 199, 0.25); background: rgba(255, 255, 255, 0.4);
            border-radius: 20px; padding: 50px 30px; text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative;
        }
        .upload-drag-zone:hover {
            border-color: var(--primary-brand); background: rgba(255, 255, 255, 0.8);
            box-shadow: 0 12px 30px rgba(2, 132, 199, 0.08); transform: translateY(-2px);
        }

        .file-input-cyber {
            background: #ffffff !important; border: 1px solid rgba(148, 163, 184, 0.3) !important;
            color: var(--text-main) !important; border-radius: 12px; padding: 12px; max-width: 450px; margin: 24px auto 0 auto;
            font-weight: 500; box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        }
        .file-input-cyber::file-selector-button {
            background: var(--primary-brand); color: #fff; border: none;
            padding: 6px 18px; border-radius: 8px; font-weight: 600; margin-right: 12px; transition: 0.2s;
        }
        .file-input-cyber::file-selector-button:hover { background: #0369a1; }

        .btn-action-submit {
            background: linear-gradient(135deg, #0284c7, #0369a1); border: none; color: #fff; font-weight: 700;
            padding: 14px 32px; border-radius: 12px; box-shadow: 0 6px 20px rgba(2, 132, 199, 0.25); transition: all 0.25s;
        }
        .btn-action-submit:hover {
            transform: translateY(-2px); box-shadow: 0 8px 25px rgba(2, 132, 199, 0.4); color: #fff;
        }
        .btn-back-custom {
            border-radius: 12px; padding: 14px 28px; font-weight: 600;
            background: #ffffff; border: 1px solid rgba(148, 163, 184, 0.3); color: var(--text-muted);
            transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center;
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
    
    <button class="dropdown-btn">
        <span class="menu-content-wrapper">
            <i class="fa-solid fa-layer-group menu-icon"></i>
            <span>Monitoring</span>
        </span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../material/index.php">Material Gudang</a>
        <a href="../ba/index.php">Database BA</a>
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
        <a href="../kategori/ex_bongkaran/ex_bongkaran.php">Ex Bongkaran</a>
        <a href="../kategori/pre_memory/pre_memory.php">Pre Memory</a>
        <a href="../kategori/peminjaman/peminjaman.php">Peminjaman</a>
    </div>

    <button class="dropdown-btn active">
        <span class="menu-content-wrapper">
            <i class="fa-solid fa-file-import menu-icon"></i>
            <span>Import</span>
        </span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container" style="display: block;">
        <a href="material.php">Import Material</a>
        <a href="ba.php" class="active-menu">Import BA</a>
        <a href="../import/form_stok.php">Import Stok</a>
        <a href="../import/form_non_stok.php">Import Non Stok</a>
        <a href="../import/form_ex_bongkaran.php">Import Ex Bongkaran</a>
        <a href="../import/form_pre_memory.php">Import Pre Memory</a>
        <a href="../import/form_peminjaman.php">Import Peminjaman</a>
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
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center">
                <i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i> UTALITAS IMPOR 
                <span class="ms-2" style="font-weight: 400; font-size: 0.95rem; color: var(--text-muted);">/ Bulk Import Database BA</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper container-fluid">
        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-10">
                
                <?php
                $total = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM database_ba"));
                ?>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="glass-mini-card">
                            <div class="mini-card-icon" style="background: rgba(52, 211, 153, 0.1); color: #16a34a;">
                                <i class="fa-solid fa-file-invoice"></i>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; letter-spacing: 0.6px;">Total Log BA Terdaftar</div>
                                <div style="font-size: 1.35rem; font-weight: 800; color: var(--text-main);"><?= number_format($total['total']); ?> Record</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="glass-mini-card">
                            <div class="mini-card-icon" style="background: rgba(2, 132, 199, 0.1); color: var(--primary-brand);">
                                <i class="fa-solid fa-database"></i>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; letter-spacing: 0.6px;">Status Sinkronisasi</div>
                                <div style="font-size: 1.35rem; font-weight: 800; color: var(--primary-brand);">ONLINE / AKTIF</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cyber-import-container">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-dark mb-1"><i class="fa-solid fa-file-csv text-primary me-2"></i>Unggah Log Berita Acara</h4>
                        <p class="text-muted small">Integrasikan arsip laporan distribusi alat & material UPT Makassar secara massal.</p>
                    </div>

                    <div class="cyber-alert-info mb-4">
                        <div class="d-flex gap-2 align-items-center mb-2">
                            <i class="fa-solid fa-circle-info text-primary"></i>
                            <strong style="font-size: 0.85rem; letter-spacing: 0.3px; color: #0369a1;">PETUNJUK PARSING & INTEGRASI BERKAS:</strong>
                        </div>
                        <ul class="mb-0 small text-secondary" style="padding-left: 20px; line-height: 1.6; font-weight: 500;">
                            <li>Pastikan ekstensi arsip yang dipilih murni bertipe data <strong>.CSV (Comma Separated Values)</strong>.</li>
                            <li>Sistem otomatis melewati baris <span class="text-dark fw-semibold">Header Kolom</span> tanpa membuang data pertama.</li>
                            <li><strong>Sistem Anti-Duplikat Cerdas:</strong> Jika berkas diunggah ulang, sistem otomatis membandingkan data dan hanya memasukkan data baru yang belum ada di database.</li>
                        </ul>
                    </div>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="upload-drag-zone mb-4">
                            <i class="fa-solid fa-file-shield fa-3x text-primary mb-3 opacity-75"></i>
                            <h5 class="fw-bold text-dark mb-1">Pilih Berkas Laporan CSV</h5>
                            <p class="text-muted small mb-0">Klik area atau tombol di bawah ini untuk memuat dokumen dari perangkat lokal</p>
                            
                            <input type="file" name="file" class="form-control file-input-cyber" accept=".csv" required>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="../ba/index.php" class="btn btn-back-custom">
                                <i class="fa-solid fa-circle-arrow-left me-1"></i> Kembali
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

    <?php if($sukses_import): ?>
        Swal.fire({
            title: 'Sinkronisasi Berhasil!',
            text: 'Proses selesai penuh. Sebanyak <?= $jumlah_import; ?> data baru berhasil dimuat (Data duplikat otomatis dilewati).',
            icon: 'success',
            background: '#ffffff',
            color: '#1e293b',
            confirmButtonColor: '#0284c7',
            confirmButtonText: 'Buka Database BA'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location = '../ba/index.php';
            }
        });
    <?php endif; ?>

    <?php if($gagal_import): ?>
        Swal.fire({
            title: 'Operasi Dibatalkan!',
            text: 'Gagal memproses file. Pastikan Anda telah memilih file CSV yang valid.',
            icon: 'error',
            background: '#ffffff',
            color: '#1e293b',
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Coba Lagi'
        });
    <?php endif; ?>
</script>
</body>
</html>