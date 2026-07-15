<?php
session_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['login'])){
    header("Location: ../../login/index.php");
    exit;
}

if(strtolower($_SESSION['role']) != 'admin'){
    die("Akses ditolak.");
}
include "../../config/koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$result = mysqli_query($conn, "SELECT * FROM material_gudang WHERE id = $id");
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='non_stok.php';</script>";
    exit;
}

// Mengurai daftar berkas yang sudah ada di database
$existing_files = json_decode($data['lampiran_files'] ?? '[]', true);

if (isset($_POST['update'])) {
    $nama_material = mysqli_real_escape_string($conn, $_POST['nama_material']);
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);
    $jumlah = (int)$_POST['jumlah'];
    $no_rak = mysqli_real_escape_string($conn, $_POST['no_rak']);
    $kondisi = mysqli_real_escape_string($conn, $_POST['kondisi']);
    $lokasi_penyimpanan = mysqli_real_escape_string($conn, $_POST['lokasi_penyimpanan']);

    // 1. Proses Hapus Berkas yang ditandai (tombol X)
    if (isset($_POST['removed_files']) && is_array($_POST['removed_files'])) {
        foreach ($_POST['removed_files'] as $file_to_remove) {
            // Hapus file fisik dari folder upload jika ada
            $file_path = "upload/" . $file_to_remove;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            // Buang nama file dari array yang akan disimpan kembali
            if (($key = array_search($file_to_remove, $existing_files)) !== false) {
                unset($existing_files[$key]);
            }
        }
    }

    // 2. Proses Unggah Berkas Baru jika ada
    if (!empty($_FILES['files']['name'][0])) {
        $target_dir = "upload/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        foreach ($_FILES['files']['name'] as $key => $val) {
            if ($_FILES['files']['name'][$key] != '') {
                $file_name = time() . '_' . basename($_FILES['files']['name'][$key]);
                $target_file = $target_dir . $file_name;
                if (move_uploaded_file($_FILES['files']['tmp_name'][$key], $target_file)) {
                    $existing_files[] = $file_name;
                }
            }
        }
    }

    // Re-index array agar urutan index JSON rapi kembali
    $updated_files = array_values($existing_files);
    $lampiran_files = mysqli_real_escape_string($conn, json_encode($updated_files));

    $query = "UPDATE material_gudang SET 
              nama_material = '$nama_material', 
              satuan = '$satuan', 
              jumlah = $jumlah, 
              no_rak = '$no_rak',
              kondisi = '$kondisi',
              lokasi_penyimpanan = '$lokasi_penyimpanan',
              lampiran_files = '$lampiran_files'
              WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        header("Location: non_stok.php");
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
    <title>I-CALM | Edit Material Non Stok</title>
    
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

        /* CONTENT STYLE */
        .content { margin-left: 260px; position: relative; }
        .navbar-custom { background: #ffffff; padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999; }
        .main-body-wrapper { padding: 40px; }
        
        .glass-form-card { background: var(--bg-card); border: 1px solid #e2e8f0; border-radius: 20px; padding: 40px; width: 100%; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04); }
        .form-label { font-weight: 700; color: #1e293b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; }
        .form-control, .form-select { background: #f8fafc !important; border: 1px solid #cbd5e1 !important; border-radius: 10px; padding: 12px 16px; font-size: 0.9rem; color: var(--text-main); transition: all 0.2s ease; }
        .form-control:focus, .form-select:focus { border-color: var(--primary) !important; box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.12) !important; background: #ffffff !important; }

        .btn-submit-ba { background: #0284c7; border: none; border-radius: 8px; padding: 10px 24px; font-size: 0.9rem; font-weight: 700; color: white; transition: all 0.2s; }
        .btn-submit-ba:hover { background: #0369a1; }
        .btn-back-custom { border-radius: 8px; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 10px 24px; font-size: 0.9rem; font-weight: 600; color: #475569; text-decoration: none; transition: all 0.2s; }
        .btn-back-custom:hover { background: #e2e8f0; color: #1e293b; }

        /* Style File Manager & Tombol X Hapus */
        .attachment-wrapper { position: relative; border: 1px solid #e2e8f0; border-radius: 12px; background: #f8fafc; overflow: hidden; height: 100%; }
        .img-preview { width: 100%; height: 140px; object-fit: cover; }
        .file-icon-box { height: 140px; display: flex; align-items: center; justify-content: center; background: #edf2f7; color: #64748b; }
        
        .btn-delete-file { position: absolute; top: 8px; right: 8px; width: 28px; height: 28px; background: rgba(239, 68, 68, 0.9); border: none; border-radius: 50%; color: white; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; z-index: 10; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
        .btn-delete-file:hover { background: #dc2626; transform: scale(1.1); }
        
        /* Ketika dihapus via Javascript, beri efek redup */
        .attachment-wrapper.marked-remove { opacity: 0.35; filter: grayscale(1); border-color: #ef4444; }
        .attachment-wrapper.marked-remove .btn-delete-file { background: #64748b; }
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
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Kategori: Non Stok / Perbarui Data</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="glass-form-card">
            <h4 class="fw-bold mb-4" style="color: #0f172a; letter-spacing: -0.02em;"><i class="fa-regular fa-pen-to-square text-primary me-2"></i>Form Edit Data Non Stok</h4>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger mb-4"><?= $error; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row g-4">
                    
                    <div class="col-md-6">
                        <label class="form-label">Nama Kelompok Material / Barang</label>
                        <input type="text" name="nama_material" class="form-control" value="<?= htmlspecialchars($data['nama_material'] ?? ''); ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="satuan" class="form-control" value="<?= htmlspecialchars($data['satuan'] ?? ''); ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Jumlah Volume</label>
                        <input type="number" name="jumlah" class="form-control" value="<?= abs((int)($data['jumlah'] ?? 0)); ?>" min="0" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Nomor Rak</label>
                        <select name="no_rak" class="form-select" required>
                            <option value="">-- Pilih Nomor Rak --</option>
                            <?php
                            $list_rak = [
                                'A1', 'A2', 'A3', 'B1', 'B2', 'B3', 'B4', 'C1', 'C2', 'C3',
                                'D1', 'D2', 'D3', 'E1', 'E2', 'PETI', 'RAK ISOLATOR'
                            ];
                            foreach($list_rak as $rak){
                                $selected = (strtoupper($data['no_rak'] ?? '') == $rak) ? 'selected' : '';
                                echo "<option value='$rak' $selected>$rak</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status Kondisi</label>
                        <select name="kondisi" class="form-select" required>
                            <option value="BAIK" <?= (strtoupper($data['kondisi'] ?? '') == 'BAIK') ? 'selected' : ''; ?>>BAIK</option>
                            <option value="RUSAK" <?= (strtoupper($data['kondisi'] ?? '') == 'RUSAK') ? 'selected' : ''; ?>>RUSAK</option>
                            <option value="PERLU PERBAIKAN" <?= (strtoupper($data['kondisi'] ?? '') == 'PERLU PERBAIKAN') ? 'selected' : ''; ?>>PERLU PERBAIKAN</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Lokasi Penyimpanan</label>
                        <input type="text" name="lokasi_penyimpanan" class="form-control" value="<?= htmlspecialchars($data['lokasi_penyimpanan'] ?? ''); ?>" required>
                    </div>

                    <!-- Review Berkas Aktif & Tombol X Hapus -->
                    <div class="col-12 mt-4">
                        <label class="form-label">Berkas / Foto Terlampir Saat Ini (Klik 'X' untuk menghapus)</label>
                        <div class="row g-3">
                            <?php 
                            if (!empty($existing_files)): 
                                foreach ($existing_files as $index => $file): 
                                    $file_path = "upload/" . $file;
                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                    $is_image = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            ?>
                                    <div class="col-sm-6 col-md-4 col-lg-3" id="file-box-<?= $index; ?>">
                                        <div class="attachment-wrapper">
                                            <!-- Checkbox tersembunyi untuk mengirim data file apa saja yang akan dihapus -->
                                            <input type="checkbox" name="removed_files[]" value="<?= htmlspecialchars($file); ?>" id="check-delete-<?= $index; ?>" class="d-none">
                                            
                                            <!-- Tombol Silang (X) -->
                                            <button type="button" class="btn-delete-file" onclick="toggleRemoveFile(<?= $index; ?>)" title="Hapus Berkas">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>

                                            <?php if ($is_image && file_exists($file_path)): ?>
                                                <img src="<?= $file_path; ?>" class="img-preview" alt="Preview Foto">
                                            <?php else: ?>
                                                <div class="file-icon-box">
                                                    <i class="fa-regular fa-file-code fa-2x"></i>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="p-2 text-center small text-truncate border-top bg-white">
                                                <?= htmlspecialchars($file); ?>
                                            </div>
                                        </div>
                                    </div>
                            <?php 
                                endforeach; 
                            else: 
                            ?>
                                <div class="col-12">
                                    <div class="alert alert-light border text-muted py-2 px-3 small">
                                        <i class="fa-solid fa-paperclip me-1"></i> Belum ada file atau foto dokumentasi yang diunggah.
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Input Upload File & Foto Baru -->
                    <div class="col-12 mt-3">
                        <label class="form-label">Unggah Lampiran Tambahan Baru</label>
                        <input type="file" name="files[]" class="form-control" multiple>
                        <div class="form-text text-muted small mt-1">Gunakan tombol Ctrl / Shift untuk memilih beberapa berkas sekaligus.</div>
                    </div>

                </div>

                <div class="mt-4 pt-2 d-flex gap-2">
                    <button type="submit" name="update" class="btn-submit-ba">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Perbarui Data Non Stok
                    </button>
                    <a href="non_stok.php" class="btn-back-custom">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Fungsi interaksi tombol silang (X) untuk menghapus berkas lampiran secara visual dan logis
    function toggleRemoveFile(index) {
        const checkbox = document.getElementById('check-delete-' + index);
        const container = document.getElementById('file-box-' + index).querySelector('.attachment-wrapper');
        
        checkbox.checked = !checkbox.checked;
        if(checkbox.checked) {
            container.classList.add('marked-remove');
        } else {
            container.classList.remove('marked-remove');
        }
    }

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