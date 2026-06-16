<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

// Pastikan ID ada di URL dan aman
if(!isset($_GET['id']) || empty($_GET['id'])){
    echo "ID tidak ditemukan";
    exit;
}
$id = $_GET['id'];

// Ambil data lama menggunakan Prepared Statement (Aman dari SQL Injection)
$stmt_get = $conn->prepare("SELECT * FROM database_ba WHERE id = ?");
$stmt_get->bind_param("s", $id); 
$stmt_get->execute();
$result = $stmt_get->get_result();
$d = $result->fetch_assoc();

if(!$d){
    echo "Data tidak ditemukan";
    exit;
}

if(isset($_POST['update'])){
    $jenis_berita_acara = $_POST['jenis_berita_acara'];
    $tanggal            = $_POST['tanggal'];
    $nama_barang        = $_POST['nama_barang'];
    $merk_jenis         = $_POST['merk_jenis'];
    $jenis_barang       = $_POST['jenis_barang'];
    $sumber_barang      = $_POST['sumber_barang'];
    $satuan             = $_POST['satuan'];
    $jumlah             = (int)$_POST['jumlah'];
    $no_seri            = $_POST['no_seri'];
    $asal_barang_vendor = $_POST['asal_barang_vendor'];
    $kategori_material  = $_POST['kategori_material'];
    $tujuan             = $_POST['tujuan'];
    $kondisi_material   = $_POST['kondisi_material'];
    $keterangan         = $_POST['keterangan'];

    $nama_file_baru = $d['file_ba']; // Default menggunakan berkas yang sudah ada

    // Proses Validasi & Upload File Berita Acara
    if(isset($_FILES['file_ba']) && $_FILES['file_ba']['error'] == 0){
        $folder = "../uploads/";
        if(!is_dir($folder)){
            mkdir($folder, 0777, true);
        }

        $filename  = $_FILES['file_ba']['name'];
        $file_tmp  = $_FILES['file_ba']['tmp_name'];
        $ext       = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Hanya izinkan dokumen resmi & gambar, mencegah file berbahaya
        $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];

        if(in_array($ext, $allowed_ext)){
            // Modifikasi nama file agar unik dan aman
            $nama_file_baru = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $filename);

            if(move_uploaded_file($file_tmp, $folder . $nama_file_baru)){
                // Hapus file lama jika ada agar penyimpanan server tidak penuh
                if(!empty($d['file_ba']) && file_exists($folder . $d['file_ba'])){
                    unlink($folder . $d['file_ba']);
                }
            } else {
                die("Gagal mengunggah file baru. Periksa izin akses folder uploads.");
            }
        } else {
            echo "<script>alert('Format file tidak diizinkan oleh sistem!'); window.history.back();</script>";
            exit;
        }
    }

    // Eksekusi Pembaruan Data via Prepared Statement
    $sql_update = "UPDATE database_ba SET 
                    jenis_berita_acara = ?, tanggal = ?, nama_barang = ?, merk_jenis = ?, 
                    jenis_barang = ?, sumber_barang = ?, satuan = ?, jumlah = ?, 
                    no_seri = ?, asal_barang_vendor = ?, kategori_material = ?, 
                    tujuan = ?, kondisi_material = ?, keterangan = ?, file_ba = ? 
                   WHERE id = ?";
                   
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param(
        "sssssssissssssss", 
        $jenis_berita_acara, $tanggal, $nama_barang, $merk_jenis,
        $jenis_barang, $sumber_barang, $satuan, $jumlah,
        $no_seri, $asal_barang_vendor, $kategori_material,
        $tujuan, $kondisi_material, $keterangan, $nama_file_baru,
        $id
    );

    if($stmt_update->execute()){
        echo "<script>
            alert('Data Berita Acara berhasil diperbarui!');
            window.location='detail.php?id=" . urlencode($id) . "';
        </script>";
    } else {
        die("Error Query Database: " . $stmt_update->error);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Edit Database BA</title>
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
            SIDEBAR OCEAN BLUE PREMIUM DESIGN (PERSISTEN)
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
            CONTENT AREA
        ========================================================= */
        .content { margin-left: 260px; }
        
        .navbar-custom { 
            background: rgba(255, 255, 255, 0.45); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            padding: 18px 32px; border-bottom: 1px solid var(--border-glass); position: sticky; top: 0; z-index: 999;
        }
        .navbar-custom .navbar-brand { color: var(--text-main); font-weight: 800; font-size: 1.4rem; letter-spacing: -0.5px;}

        .main-body-wrapper { padding: 40px 32px; }

        /* GLASS CARD PANELS */
        .glass-form-card {
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border-radius: 24px; padding: 32px;
            box-shadow: 0 10px 30px -10px rgba(148, 163, 184, 0.12);
        }

        /* LIGHT INPUT CONTROLS */
        .form-label-custom {
            font-size: 0.8rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.8px; color: var(--text-muted); margin-bottom: 8px;
        }
        .form-control-custom {
            background: rgba(255, 255, 255, 0.7) !important;
            border: 1px solid rgba(148, 163, 184, 0.25) !important;
            border-radius: 12px !important; padding: 12px 16px;
            color: var(--text-main) !important; font-size: 0.95rem; font-weight: 500;
            transition: all 0.2s;
        }
        .form-control-custom:focus {
            background: #ffffff !important;
            border-color: var(--primary-brand) !important;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15) !important;
        }

        .preview-file-box {
            background: rgba(255, 255, 255, 0.4);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 16px; padding: 16px;
        }
        
        select.form-control-custom option {
            background-color: #ffffff;
            color: var(--text-main);
        }
    </style>
</head>
<body>

<!-- SIDEBAR COMPONENT -->
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
        <a href="../material/index.php">Material Gudang</a>
        <a href="../ba/index.php" class="active-menu">Database BA</a>
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

<!-- MAIN CONTENT AREA -->
<div class="content">
    
    <nav class="navbar navbar-custom">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center">
                <i class="fa-solid fa-pen-to-square text-primary me-2"></i> MODIFIKASI DATA 
                <span class="ms-2" style="font-weight: 400; font-size: 0.95rem; color: var(--text-muted);">/ Database BA</span>
            </span>
            <div>
                <a href="detail.php?id=<?= urlencode($id); ?>" class="btn btn-outline-secondary btn-sm px-3 fw-semibold border-2" style="border-radius: 10px;">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Detail
                </a>
            </div>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="glass-form-card">
            
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-4">
                    
                    <!-- Kiri: Input Form Utama -->
                    <div class="col-lg-8">
                        <div class="row g-3">
                            
                            <div class="col-md-6">
                                <label class="form-label-custom">Jenis Berita Acara</label>
                                <input type="text" name="jenis_berita_acara" value="<?= htmlspecialchars($d['jenis_berita_acara'] ?? ''); ?>" class="form-control form-control-custom" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">Tanggal Dokumen</label>
                                <input type="date" name="tanggal" value="<?= htmlspecialchars($d['tanggal'] ?? ''); ?>" class="form-control form-control-custom" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label-custom">Nama Barang / Material</label>
                                <input type="text" name="nama_barang" value="<?= htmlspecialchars($d['nama_barang'] ?? ''); ?>" class="form-control form-control-custom" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">Merk / Jenis</label>
                                <input type="text" name="merk_jenis" value="<?= htmlspecialchars($d['merk_jenis'] ?? ''); ?>" class="form-control form-control-custom">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">Jenis Barang</label>
                                <input type="text" name="jenis_barang" value="<?= htmlspecialchars($d['jenis_barang'] ?? ''); ?>" class="form-control form-control-custom">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">Sumber Barang</label>
                                <input type="text" name="sumber_barang" value="<?= htmlspecialchars($d['sumber_barang'] ?? ''); ?>" class="form-control form-control-custom">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label-custom">Satuan</label>
                                <input type="text" name="satuan" value="<?= htmlspecialchars($d['satuan'] ?? ''); ?>" class="form-control form-control-custom">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label-custom">Jumlah Volume</label>
                                <input type="number" name="jumlah" value="<?= htmlspecialchars($d['jumlah'] ?? 0); ?>" class="form-control form-control-custom" min="0" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">Nomor Seri Komponen</label>
                                <input type="text" name="no_seri" value="<?= htmlspecialchars($d['no_seri'] ?? ''); ?>" class="form-control form-control-custom" style="font-family: monospace;">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">Pemasok / Asal Material (Vendor)</label>
                                <input type="text" name="asal_barang_vendor" value="<?= htmlspecialchars($d['asal_barang_vendor'] ?? ''); ?>" class="form-control form-control-custom">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">Kategori Material</label>
                                <select name="kategori_material" class="form-select form-control-custom">
                                    <?php 
                                    $kategori_list = ["Material Gardu", "Material Proteksi", "Material Kabel", "Material Trafo", "Alat Kerja", "Alat Uji", "Lainnya"];
                                    foreach($kategori_list as $kat){
                                        $selected = (($d['kategori_material'] ?? '') == $kat) ? 'selected' : '';
                                        echo "<option value='$kat' $selected>$kat</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">Kondisi Fisik Material</label>
                                <select name="kondisi_material" class="form-select form-control-custom">
                                    <option value="BAIK" <?= (($d['kondisi_material'] ?? '') == 'BAIK') ? 'selected' : ''; ?>>BAIK</option>
                                    <option value="RUSAK" <?= (($d['kondisi_material'] ?? '') == 'RUSAK') ? 'selected' : ''; ?>>RUSAK</option>
                                    <option value="PERBAIKAN" <?= (($d['kondisi_material'] ?? '') == 'PERBAIKAN') ? 'selected' : ''; ?>>PERBAIKAN</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label-custom">Lokasi / Unit Tujuan</label>
                                <input type="text" name="tujuan" value="<?= htmlspecialchars($d['tujuan'] ?? ''); ?>" class="form-control form-control-custom">
                            </div>

                            <div class="col-12">
                                <label class="form-label-custom">Keterangan Deskriptif</label>
                                <textarea name="keterangan" class="form-control form-control-custom" rows="4"><?= htmlspecialchars($d['keterangan'] ?? ''); ?></textarea>
                            </div>

                        </div>
                    </div>

                    <!-- Kanan: Preview File Lampiran -->
                    <div class="col-lg-4">
                        <div class="row g-3">
                            
                            <div class="col-12">
                                <label class="form-label-custom">Berkas Lampiran Saat Ini</label>
                                <div class="preview-file-box mb-3">
                                    <?php if(!empty($d['file_ba'])){ ?>
                                        <div class="d-flex align-items-center gap-2 p-2 rounded bg-white bg-opacity-50 border">
                                            <i class="fa-solid fa-file-pdf fs-3 text-danger"></i>
                                            <div class="text-truncate" style="font-size: 0.85rem;">
                                                <span class="d-block fw-bold text-dark text-truncate" title="<?= htmlspecialchars($d['file_ba']); ?>"><?= htmlspecialchars($d['file_ba']); ?></span>
                                                <a href="../uploads/<?= urlencode($d['file_ba']); ?>" target="_blank" class="text-primary text-decoration-none fw-semibold">📄 Lihat Dokumen</a>
                                            </div>
                                        </div>
                                    <?php } else { ?>
                                        <div class="text-muted small py-4 text-center">
                                            <i class="fa-solid fa-folder-open d-block fs-2 mb-2 opacity-30"></i>
                                            Belum ada file lampiran
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label-custom">Ganti Lampiran File Baru</label>
                                <input type="file" name="file_ba" class="form-control form-control-custom" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
                                <div class="form-text small text-muted mt-1">Format berkas: PDF, Gambar, atau Dokumen Office. Kosongkan jika tidak ingin diubah.</div>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- Action Button Form -->
                <div class="mt-4 pt-4 border-top d-flex gap-2" style="border-color: var(--border-light) !important;">
                    <button type="submit" name="update" class="btn btn-warning px-4 py-2 fw-bold text-dark" style="border-radius: 12px; background: #fbbf24; border: none; box-shadow: 0 4px 15px rgba(251, 191, 36, 0.25);">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                    </button>
                    <a href="detail.php?id=<?= urlencode($id); ?>" class="btn btn-light px-4 py-2 fw-semibold border text-secondary" style="border-radius: 12px; background: #fff;">
                        Batal
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- SCRIPT DROPDOWN INTERAKTIF SIDEBAR -->
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