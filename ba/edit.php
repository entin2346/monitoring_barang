<?php
session_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

if(strtolower($_SESSION['role']) != 'admin'){
    die("Akses ditolak.");
}
include "../config/koneksi.php";

// Pastikan ID ada di URL dan aman
if(!isset($_GET['id']) || empty($_GET['id'])){
    echo "ID tidak ditemukan";
    exit;
}
$id = $_GET['id'];

// Ambil data lama menggunakan Prepared Statement
$stmt_get = $conn->prepare("SELECT * FROM database_ba WHERE id = ?");
$stmt_get->bind_param("s", $id); 
$stmt_get->execute();
$result = $stmt_get->get_result();
$d = $result->fetch_assoc();

if(!$d){
    echo "Data tidak ditemukan";
    exit;
}

// Mengurai berkas lama
$files_lama = [];
if (!empty($d['file_ba'])) {
    $decoded = json_decode($d['file_ba'], true);
    if (is_array($decoded)) {
        $files_lama = $decoded;
    } else {
        $files_lama = [$d['file_ba']];
    }
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

    // Ambil ulang kondisi file_ba terbaru dari DB sebelum disave
    $stmt_check = $conn->prepare("SELECT file_ba FROM database_ba WHERE id = ?");
    $stmt_check->bind_param("s", $id);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result()->fetch_assoc();
    
    $files_current = [];
    if (!empty($res_check['file_ba'])) {
        $dec = json_decode($res_check['file_ba'], true);
        if (is_array($dec)) { $files_current = $dec; } 
        else { $files_current = [$res_check['file_ba']]; }
    }

    $files_final = $files_current; 

    // Proses Validasi & Upload Banyak File Berita Acara (Multiple Upload)
    if(isset($_FILES['file_ba']) && !empty($_FILES['file_ba']['name'][0])){
        $folder = "../uploads/";
        if(!is_dir($folder)){
            mkdir($folder, 0777, true);
        }

        $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];
        $uploaded_new_files = [];

        foreach ($_FILES['file_ba']['name'] as $key => $filename) {
            if ($_FILES['file_ba']['error'][$key] == 0) {
                $file_tmp  = $_FILES['file_ba']['tmp_name'][$key];
                $ext       = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if(in_array($ext, $allowed_ext)){
                    $nama_file_baru = time() . "_" . $key . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $filename);

                    if(move_uploaded_file($file_tmp, $folder . $nama_file_baru)){
                        $uploaded_new_files[] = $nama_file_baru;
                    } else {
                        die("Gagal mengunggah file baru. Periksa izin akses folder uploads.");
                    }
                } else {
                    echo "<script>alert('Format file " . htmlspecialchars($filename) . " tidak diizinkan oleh sistem!'); window.history.back();</script>";
                    exit;
                }
            }
        }

        if (!empty($uploaded_new_files)) {
            $files_final = array_merge($files_current, $uploaded_new_files);
        }
    }

    $file_ba_db = json_encode($files_final);

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
        $tujuan, $kondisi_material, $keterangan, $file_ba_db,
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I-CALM | Edit Database BA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        /* CSS ATURAN BARU DIBAWAH INI ADALAH COPY-PASTE PERSIS DARI INDEX.PHP */
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

        body { 
            background: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .sidebar {
            position: fixed; left: 0; top: 0; width: 260px; height: 100%;
            background-color: var(--bg-sidebar);
            border-right: 1px solid rgba(2, 132, 199, 0.15);
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
            color: #025a9c; 
            background: rgba(2, 132, 199, 0.12); 
            transform: translateX(4px);
        }
        
        .sidebar .menu-content-wrapper { display: flex; align-items: center; gap: 12px; }
        .sidebar a i, .dropdown-btn i.menu-icon { font-size: 1.05rem; width: 20px; text-align: center; color: #1e40af; }
        .sidebar a:hover i, .dropdown-btn:hover i.menu-icon { color: #025a9c; }
        
        .sidebar .active-menu {
            color: #ffffff !important; 
            background: #0284c7 !important; 
            font-weight: 700;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25);
            border-radius: 10px;
            transform: translateX(4px);
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

        .content { margin-left: 260px; position: relative; }
        
        /* ATURAN FORM EDIT-CARD BAWAAN */
        .navbar-cyber { background: #ffffff; padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999; }
        .main-body-wrapper { padding: 40px; }

        .glass-form-card {
            background: var(--bg-card); border: 1px solid var(--border-color);
            border-radius: 16px; padding: 35px; box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        }

        .form-label-custom {
            font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px;
        }
        .form-control-cyber-edit {
            background: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 12px !important; padding: 12px 16px;
            color: var(--text-main) !important; font-size: 0.92rem; font-weight: 500; transition: all 0.2s;
        }
        .form-control-cyber-edit:focus {
            border-color: var(--primary) !important;
            background: #fff !important;
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.1) !important;
        }
        
        select.form-control-cyber-edit option { background-color: #ffffff; color: var(--text-main); }

        .old-file-container { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
        .old-file-card {
            position: relative; background: #ffffff; border: 1px solid #cbd5e1;
            border-radius: 12px; padding: 10px 28px 10px 12px; display: flex; align-items: center; gap: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.03); max-width: 220px;
        }
        .btn-delete-old-file {
            position: absolute; top: -6px; right: -6px; background: #ef4444; color: #ffffff !important;
            border: 2px solid #ffffff; border-radius: 50%; width: 20px; height: 20px;
            display: flex; align-items: center; justify-content: center; font-size: 10px; cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15); transition: transform 0.2s;
        }
        .btn-delete-old-file:hover { transform: scale(1.15); background: #dc2626; }

        .file-list-container { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 12px; }
        .file-badge-item {
            background: #ffffff; border: 1px solid rgba(2, 132, 199, 0.2);
            padding: 6px 12px; border-radius: 8px; font-size: 0.85rem; font-weight: 500;
            display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .file-badge-item i.fa-file { color: #ef4444; }
        .file-badge-item .btn-remove-file { border: none; background: none; color: #64748b; cursor: pointer; padding: 0; font-size: 0.9rem; line-height: 1; }
        .file-badge-item .btn-remove-file:hover { color: #ef4444; }
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
    <button class="dropdown-btn active">
        <span class="menu-content-wrapper">
            <i class="fa-solid fa-layer-group menu-icon"></i>
            <span>Monitoring</span>
        </span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container" style="display: block;">
        <a href="../material/index.php">Material Gudang</a>
        <a href="../ba/index.php" class="active-menu">Database BA</a>
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
        <a href="../kategori/non_po/non_po.php">Non PO</a>
        <a href="../kategori/ex_bongkaran/ex_bongkaran.php">Ex Bongkaran</a>
        <a href="../kategori/pre_memory/pre_memory.php">Pre Memory</a>
        <a href="../kategori/peminjaman/peminjaman.php">Peminjaman</a>
        <a href="../kategori/pemakaian/pemakaian.php">Pemakaian</a>
    </div>
    <button class="dropdown-btn">
        <span class="menu-content-wrapper">
            <i class="fa-solid fa-file-import menu-icon"></i>
            <span>Import</span>
        </span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../import/material.php">Import Material</a>
        <a href="../import/ba.php">Import BA</a>
        <a href="../import/form_stok.php">Import Stok</a>
        <a href="../import/form_non_stok.php">Import Non Stok</a>
        <a href="../import/form_non_po.php">Import Non PO</a>
        <a href="../import/form_ex_bongkaran.php">Import Ex Bongkaran</a>
        <a href="../import/form_pre_memory.php">Import Pre Memory</a>
        <a href="../import/form_peminjaman.php">Import Peminjaman</a>
        <a href="../import/form_pemakaian.php">Import Pemakaian</a>
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
    <nav class="navbar navbar-expand-lg navbar-cyber">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center" style="color: #0f172a; font-weight: 800; font-size: 1.3rem;">
                <i class="fa-solid fa-pen-to-square text-primary me-3"></i> Form Ubah Berita Acara
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="glass-form-card">
            <form action="" method="POST" enctype="multipart/form-data">
                
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label form-label-custom">Jenis Berita Acara</label>
                        <select name="jenis_berita_acara" class="form-control form-control-cyber-edit" required>
                            <option value="MASUK" <?= (strtoupper($d['jenis_berita_acara']) == 'MASUK') ? 'selected' : ''; ?>>MASUK</option>
                            <option value="KELUAR" <?= (strtoupper($d['jenis_berita_acara']) == 'KELUAR') ? 'selected' : ''; ?>>KELUAR</option>
                            <option value="RETURN" <?= (strtoupper($d['jenis_berita_acara']) == 'RETURN') ? 'selected' : ''; ?>>RETURN</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-custom">Tanggal Berita Acara</label>
                        <input type="date" name="tanggal" class="form-control form-control-cyber-edit" value="<?= htmlspecialchars($d['tanggal']); ?>" required>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label form-label-custom">Nama Barang / Material</label>
                        <input type="text" name="nama_barang" class="form-control form-control-cyber-edit" value="<?= htmlspecialchars($d['nama_barang']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-custom">Merk / Jenis</label>
                        <input type="text" name="merk_jenis" class="form-control form-control-cyber-edit" value="<?= htmlspecialchars($d['merk_jenis']); ?>">
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label form-label-custom">Jenis Barang (Gudang/Non-Gudang)</label>
                        <input type="text" name="jenis_barang" class="form-control form-control-cyber-edit" value="<?= htmlspecialchars($d['jenis_barang']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-custom">Sumber Barang (PO/Non-PO/Dll)</label>
                        <input type="text" name="sumber_barang" class="form-control form-control-cyber-edit" value="<?= htmlspecialchars($d['sumber_barang']); ?>">
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label form-label-custom">Satuan</label>
                        <input type="text" name="satuan" class="form-control form-control-cyber-edit" value="<?= htmlspecialchars($d['satuan']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-custom">Jumlah Volume</label>
                        <input type="number" name="jumlah" class="form-control form-control-cyber-edit" value="<?= htmlspecialchars($d['jumlah']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-custom">No Seri / SN</label>
                        <input type="text" name="no_seri" class="form-control form-control-cyber-edit" value="<?= htmlspecialchars($d['no_seri']); ?>">
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label form-label-custom">Asal Barang / Vendor</label>
                        <input type="text" name="asal_barang_vendor" class="form-control form-control-cyber-edit" value="<?= htmlspecialchars($d['asal_barang_vendor']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-custom">Kategori Material</label>
                        <select name="kategori_material" class="form-control form-control-cyber-edit">
                            <option value="STOK" <?= (strtoupper($d['kategori_material']) == 'STOK') ? 'selected' : ''; ?>>STOK</option>
                            <option value="NON STOK" <?= (strtoupper($d['kategori_material']) == 'NON STOK') ? 'selected' : ''; ?>>NON STOK</option>
                            <option value="NON PO" <?= (strtoupper($d['kategori_material']) == 'NON PO') ? 'selected' : ''; ?>>NON PO</option>
                            <option value="EX BONGKARAN" <?= (strtoupper($d['kategori_material']) == 'EX BONGKARAN') ? 'selected' : ''; ?>>EX BONGKARAN</option>
                            <option value="PRE MEMORY" <?= (strtoupper($d['kategori_material']) == 'PRE MEMORY') ? 'selected' : ''; ?>>PRE MEMORY</option>
                            <option value="PEMINJAMAN" <?= (strtoupper($d['kategori_material']) == 'PEMINJAMAN') ? 'selected' : ''; ?>>PEMINJAMAN</option>
                            <option value="PEMAKAIAN" <?= (strtoupper($d['kategori_material']) == 'PEMAKAIAN') ? 'selected' : ''; ?>>PEMAKAIAN</option>
                        </select>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label form-label-custom">Tujuan / Lokasi Distribusi</label>
                        <input type="text" name="tujuan" class="form-control form-control-cyber-edit" value="<?= htmlspecialchars($d['tujuan']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-custom">Kondisi Material</label>
                        <input type="text" name="kondisi_material" class="form-control form-control-cyber-edit" value="<?= htmlspecialchars($d['kondisi_material']); ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label form-label-custom">Keterangan Tambahan</label>
                    <textarea name="keterangan" rows="3" class="form-control form-control-cyber-edit"><?= htmlspecialchars($d['keterangan']); ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label form-label-custom d-block">Berkas Lampiran Saat Ini</label>
                    <?php if(!empty($files_lama)): ?>
                        <div class="old-file-container" id="oldFilesWrapper">
                            <?php foreach($files_lama as $idx => $f_old): ?>
                                <div class="old-file-card" id="old_card_<?= $idx; ?>">
                                    <i class="fa-solid fa-file-lines text-primary" style="font-size: 1.1rem;"></i>
                                    <a href="../uploads/<?= htmlspecialchars($f_old); ?>" target="_blank" class="text-decoration-none text-truncate text-secondary" style="font-size: 0.85rem; font-weight: 600; max-width: 140px;" title="<?= htmlspecialchars($f_old); ?>">
                                        <?= htmlspecialchars($f_old); ?>
                                    </a>
                                    <button type="button" class="btn-delete-old-file" onclick="ajaxDeleteFile('<?= htmlspecialchars($id); ?>', '<?= htmlspecialchars($f_old); ?>', <?= $idx; ?>)">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted" style="font-size: 0.88rem; font-style: italic;"><i class="fa-solid fa-circle-info me-1"></i> Belum ada file berita acara yang dilampirkan.</p>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label class="form-label form-label-custom">Tambah Berkas Lampiran Baru (Bisa pilih banyak file sekaligus)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px;"><i class="fa-solid fa-cloud-arrow-up text-muted"></i></span>
                        <input type="text" id="inputFileDummy" class="form-control form-control-cyber-edit border-start-0 bg-white" placeholder="Klik di sini untuk memilih file baru..." style="border-radius: 0 12px 12px 0 !important; cursor: pointer;" readonly>
                    </div>
                    <input type="file" id="inputFileReal" name="file_ba[]" class="d-none" multiple>
                    
                    <div class="file-list-container" id="previewContainer"></div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-5">
                    <a href="detail.php?id=<?= urlencode($id); ?>" class="btn btn-light px-4 py-2" style="border-radius: 10px; font-weight: 600; font-size: 0.9rem;">Batal</a>
                    <button type="submit" name="update" class="btn btn-primary px-4 py-2" style="border-radius: 10px; font-weight: 600; font-size: 0.9rem; background: var(--primary); border-color: var(--primary);">Simpan Perubahan</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    /* =========================================================================
       SCRIPT SIDEBAR DROPDOWN (SAMA PERSIS DENGAN INDEX.PHP)
       ========================================================================= */
    const dropdownBtns = document.querySelectorAll(".dropdown-btn");
    dropdownBtns.forEach(btn => {
        btn.addEventListener("click", function() {
            this.classList.toggle("active");
            const dropdownContainer = this.nextElementSibling;
            if (dropdownContainer.style.display === "block") {
                dropdownContainer.style.display = "none";
            } else {
                dropdownContainer.style.display = "block";
            }
        });
    });

    /* =========================================================================
       SCRIPT LIVE MULTIPLE FILE BADGES PREVIEW
       ========================================================================= */
    const inputFileDummy = document.getElementById('inputFileDummy');
    const inputFileReal = document.getElementById('inputFileReal');
    const previewContainer = document.getElementById('previewContainer');
    
    let dtContainer = new DataTransfer();

    inputFileDummy.addEventListener('click', () => inputFileReal.click());

    inputFileReal.addEventListener('change', function() {
        const filesSelected = this.files;
        if(filesSelected.length === 0) return;

        for (let i = 0; i < filesSelected.length; i++) {
            const file = filesSelected[i];
            
            let isDuplicate = false;
            for (let j = 0; j < dtContainer.files.length; j++) {
                if (dtContainer.files[j].name === file.name) {
                    isDuplicate = true;
                    break;
                }
            }

            if (!isDuplicate) {
                dtContainer.items.add(file);
            }
        }

        inputFileReal.files = dtContainer.files;
        renderFileBadges();
        inputFileDummy.value = '';
    });

    function renderFileBadges() {
        previewContainer.innerHTML = '';
        
        Array.from(dtContainer.files).forEach((file, index) => {
            const badge = document.createElement('div');
            badge.className = 'file-badge-item';
            badge.innerHTML = `
                <i class="fa-solid fa-file"></i>
                <span class="text-truncate" style="max-width: 150px;" title="${file.name}">${file.name}</span>
                <button type="button" class="btn-remove-file" onclick="removeSelectedFile(${index})">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
            previewContainer.appendChild(badge);
        });
    }

    function removeSelectedFile(index) {
        dtContainer.items.remove(index);
        inputFileReal.files = dtContainer.files;
        renderFileBadges();
    }

    /* =========================================================================
       SCRIPT AJAX HAPUS BERKAS LAMA SECARA ASYNCHRONOUS
       ========================================================================= */
    function ajaxDeleteFile(idData, fileName, elementIndex) {
        if (confirm("Apakah Anda yakin ingin menghapus berkas lampiran ini dari server?")) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "hapus_file_ajax.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === "success") {
                            const cardElement = document.getElementById("old_card_" + elementIndex);
                            if (cardElement) {
                                cardElement.remove();
                            }
                            
                            const remainingCards = document.querySelectorAll("#oldFilesWrapper .old-file-card");
                            if (remainingCards.length === 0) {
                                const wrapper = document.getElementById("oldFilesWrapper");
                                if (wrapper) {
                                    wrapper.innerHTML = `<p class="text-muted" style="font-size: 0.88rem; font-style: italic;"><i class="fa-solid fa-circle-info me-1"></i> Belum ada file berita acara yang dilampirkan.</p>`;
                                }
                            }
                        } else {
                            alert("Gagal menghapus file: " + response.message);
                        }
                    } catch (e) {
                        alert("Terjadi kesalahan sistem saat memproses respon server.");
                    }
                }
            };
            
            xhr.send("id=" + encodeURIComponent(idData) + "&filename=" + encodeURIComponent(fileName));
        }
    }
</script>
</body>
</html>