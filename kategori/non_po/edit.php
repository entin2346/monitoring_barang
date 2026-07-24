<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../../login/index.php"); // Diperbaiki agar mengarah ke folder login yang benar
    exit;
}
include "../../config/koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$result = mysqli_query($conn, "SELECT * FROM non_po WHERE id = $id");
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='non_po.php';</script>";
    exit;
}

if (isset($_POST['update'])) {
    $jenis_ba = mysqli_real_escape_string($conn, $_POST['jenis_ba']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $nama_material = mysqli_real_escape_string($conn, $_POST['nama_material']);
    $merk_jenis = mysqli_real_escape_string($conn, $_POST['merk_jenis']);
    $jenis_barang = mysqli_real_escape_string($conn, $_POST['jenis_barang']);
    $sumber_barang = mysqli_real_escape_string($conn, $_POST['sumber_barang']);
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);
    $jumlah = (int)$_POST['jumlah'];
    $tujuan = mysqli_real_escape_string($conn, $_POST['tujuan']);
    $kondisi = mysqli_real_escape_string($conn, $_POST['kondisi']);
    $vendor = mysqli_real_escape_string($conn, $_POST['vendor']);
    $berita_acara = mysqli_real_escape_string($conn, $_POST['berita_acara']);

    // Mengatur berkas lama setelah proses penghapusan via tanda 'X'
    $berkas_tersisa = [];
    if (!empty($_POST['berkas_tersisa_json'])) {
        $berkas_tersisa = json_decode($_POST['berkas_tersisa_json'], true);
        if (!is_array($berkas_tersisa)) {
            $berkas_tersisa = [];
        }
    }

    // Mengolah unggahan berkas tambahan baru
    if (isset($_FILES['lampiran_baru']) && count($_FILES['lampiran_baru']['name']) > 0 && !empty($_FILES['lampiran_baru']['name'][0])) {
        $total_files = count($_FILES['lampiran_baru']['name']);
        for ($i = 0; $i < $total_files; $i++) {
            $file_name = $_FILES['lampiran_baru']['name'][$i];
            $file_tmp = $_FILES['lampiran_baru']['tmp_name'][$i];
            $file_error = $_FILES['lampiran_baru']['error'][$i];

            if ($file_error === 0) {
                $unique_name = time() . '_' . uniqid() . '_' . $file_name;
                $target_dir = "upload/";
                
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                if (move_uploaded_file($file_tmp, $target_dir . $unique_name)) {
                    $berkas_tersisa[] = $unique_name;
                }
            }
        }
    }

    $lampiran_files_json = mysqli_real_escape_string($conn, json_encode(array_values($berkas_tersisa)));

    $query = "UPDATE non_po SET 
              jenis_ba = '$jenis_ba', 
              tanggal = '$tanggal', 
              nama_material = '$nama_material', 
              merk_jenis = '$merk_jenis', 
              jenis_barang = '$jenis_barang', 
              sumber_barang = '$sumber_barang', 
              satuan = '$satuan', 
              jumlah = $jumlah, 
              tujuan = '$tujuan', 
              kondisi = '$kondisi', 
              vendor = '$vendor', 
              berita_acara = '$berita_acara',
              lampiran_files = '$lampiran_files_json'
              WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        header("Location: non_po.php");
        exit;
    } else {
        $error = "Gagal memperbarui data: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Perbarui Data Non PO</title>
    
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

        /* CONTENT & FORM LAYOUT */
        .content { margin-left: 260px; position: relative; min-width: 320px; }
        .navbar-custom { background: #ffffff; padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999; }
        .main-body-wrapper { padding: 40px; }
        
        .glass-edit-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 40px; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04); }
        
        .form-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #475569; margin-bottom: 8px; }
        .form-control { border: 1px solid #cbd5e1; border-radius: 10px; padding: 12px 16px; font-size: 0.9rem; background: #f8fafc; transition: all 0.2s; color: var(--text-main); }
        .form-control:focus { background: #ffffff; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.1); }
        
        /* Attachment Wrapper with X Button */
        .attachment-grid { display: flex; flex-wrap: wrap; gap: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; min-height: 55px; align-items: center; }
        .file-item-wrapper { position: relative; display: inline-block; }
        
        .img-preview-box { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #cbd5e1; }
        .doc-preview-box { display: inline-flex; align-items: center; gap: 8px; background: #ffffff; border: 1px solid #cbd5e1; padding: 8px 32px 8px 12px; border-radius: 8px; font-size: 0.85rem; color: #334155; font-weight: 600; max-width: 240px; }
        
        .btn-remove-file { position: absolute; top: -8px; right: -8px; background: #ef4444; color: #ffffff; border: none; width: 22px; height: 22px; border-radius: 50%; font-size: 11px; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4); transition: transform 0.1s; z-index: 10; }
        .btn-remove-file:hover { transform: scale(1.15); background: #dc2626; }

        .btn-save-custom { border-radius: 8px; background: var(--primary); border: none; padding: 10px 24px; font-size: 0.9rem; font-weight: 600; color: #ffffff; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2); transition: all 0.2s; }
        .btn-save-custom:hover { background: #026ca3; transform: translateY(-1px); }
        .btn-cancel-custom { border-radius: 8px; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 10px 24px; font-size: 0.9rem; font-weight: 600; color: #475569; text-decoration: none; transition: all 0.2s; }
        .btn-cancel-custom:hover { background: #e2e8f0; color: #1e293b; }

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
        <a href="/monitoring_barang/kategori/ex_bongkaran/ex_bongkaran.php">Ex Bongkaran</a>
        <a href="/monitoring_barang/kategori/pre_memory/pre_memory.php">Pre Memory</a>
        <a href="/monitoring_barang/kategori/peminjaman/peminjaman.php">Peminjaman</a>
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
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Kategori: Non PO / Perbarui Data</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="glass-edit-card">
            <h4 class="fw-bold mb-4" style="color: #0f172a; letter-spacing: -0.02em;"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Form Edit Data Non PO</h4>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger mb-4" style="border-radius: 10px;"><?= $error; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" id="formEditNonPO">
                
                <!-- Input hidden untuk array berkas tersisa -->
                <?php 
                $file_list = [];
                if(!empty($data['lampiran_files'])) {
                    $file_list = json_decode($data['lampiran_files'], true);
                    if(!is_array($file_list)) { $file_list = []; }
                }
                ?>
                <input type="hidden" name="berkas_tersisa_json" id="berkas_tersisa_json" value='<?= json_encode(array_values($file_list)); ?>'>

                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Jenis BA</label>
                        <input type="text" name="jenis_ba" class="form-control" value="<?= htmlspecialchars($data['jenis_ba'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Sistem</label>
                        <input type="date" name="tanggal" class="form-control" value="<?= !empty($data['tanggal']) ? date('Y-m-d', strtotime($data['tanggal'])) : date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="satuan" class="form-control" value="<?= htmlspecialchars($data['satuan'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jumlah Volume</label>
                        <input type="number" name="jumlah" class="form-control" value="<?= (int)($data['jumlah'] ?? 0); ?>" required min="0">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Nama Kelompok Material / Barang</label>
                        <input type="text" name="nama_material" class="form-control" value="<?= htmlspecialchars($data['nama_material'] ?? ''); ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Merk / Jenis</label>
                        <input type="text" name="merk_jenis" class="form-control" value="<?= htmlspecialchars($data['merk_jenis'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Jenis Barang</label>
                        <input type="text" name="jenis_barang" class="form-control" value="<?= htmlspecialchars($data['jenis_barang'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sumber Barang</label>
                        <input type="text" name="sumber_barang" class="form-control" value="<?= htmlspecialchars($data['sumber_barang'] ?? ''); ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tujuan</label>
                        <input type="text" name="tujuan" class="form-control" value="<?= htmlspecialchars($data['tujuan'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status Kondisi</label>
                        <input type="text" name="kondisi" class="form-control" value="<?= htmlspecialchars($data['kondisi'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Lokasi / Vendor</label>
                        <input type="text" name="vendor" class="form-control" value="<?= htmlspecialchars($data['vendor'] ?? ''); ?>">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Berita Acara</label>
                        <input type="text" name="berita_acara" class="form-control" value="<?= htmlspecialchars($data['berita_acara'] ?? ''); ?>">
                    </div>

                    <!-- KONTROL FILE DENGAN TOMBOL HAPUS X -->
                    <div class="col-12">
                        <label class="form-label">Berkas / Foto Terlampir Saat Ini (Klik 'X' Untuk Menghapus)</label>
                        <div class="attachment-grid" id="attachmentGridContainer">
                            <?php 
                            if (count($file_list) > 0) {
                                foreach ($file_list as $file) {
                                    $file_path = "upload/" . $file;
                                    if (file_exists($file_path)) {
                                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                        $allowed_img = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                                        
                                        echo '<div class="file-item-wrapper" data-filename="'.htmlspecialchars($file).'">';
                                        echo '<button type="button" class="btn-remove-file" onclick="removeAttachmentFile(this)"><i class="fa-solid fa-xmark"></i></button>';
                                        
                                        if (in_array($ext, $allowed_img)) {
                                            echo '<img src="'.$file_path.'" class="img-preview-box" alt="Lampiran">';
                                        } else {
                                            echo '<div class="doc-preview-box">
                                                    <i class="fa-solid fa-file-lines text-primary fs-5"></i>
                                                    <span class="text-truncate" style="max-width: 150px;">'.htmlspecialchars($file).'</span>
                                                  </div>';
                                        }
                                        echo '</div>';
                                    }
                                }
                            } else {
                                echo '<span class="text-muted small" id="noFileMsg"><i class="fa-solid fa-paperclip me-1"></i> Belum ada file atau foto dokumentasi yang diunggah.</span>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Unggah Lampiran Tambahan Baru</label>
                        <input type="file" name="lampiran_baru[]" class="form-control" multiple>
                        <small class="text-muted d-block mt-1">Gunakan tombol Ctrl / Shift untuk memilih beberapa berkas sekaligus.</small>
                    </div>
                </div>

                <div class="d-flex gap-2 pt-2">
                    <button type="submit" name="update" class="btn-save-custom"><i class="fa-solid fa-floppy-disk me-2"></i>Perbarui Data Non PO</button>
                    <a href="non_po.php" class="btn-cancel-custom">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function removeAttachmentFile(buttonElement) {
        if (confirm("Apakah Anda yakin ingin melepas lampiran berkas ini?")) {
            const wrapper = buttonElement.closest('.file-item-wrapper');
            const filenameToRemove = wrapper.getAttribute('data-filename');
            
            const jsonInput = document.getElementById('berkas_tersisa_json');
            let fileArray = JSON.parse(jsonInput.value);
            
            fileArray = fileArray.filter(file => file !== filenameToRemove);
            jsonInput.value = JSON.stringify(fileArray);
            wrapper.remove();
            
            const container = document.getElementById('attachmentGridContainer');
            if (container.querySelectorAll('.file-item-wrapper').length === 0) {
                container.innerHTML = '<span class="text-muted small" id="noFileMsg"><i class="fa-solid fa-paperclip me-1"></i> Belum ada file atau foto dokumentasi yang diunggah.</span>';
            }
        }
    }

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