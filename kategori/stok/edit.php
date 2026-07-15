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
$get_data = mysqli_query($conn, "SELECT * FROM material_gudang WHERE id = $id");
$data = mysqli_fetch_assoc($get_data);

if(!$data){
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='stok.php';</script>";
    exit;
}

$existing_files = json_decode($data['lampiran_files'] ?? '[]', true);

// Proses hapus berkas (DIUBAH KE FOLDER upload/)
if(isset($_GET['delete_file'])) {
    $file_to_delete = $_GET['delete_file'];
    if(($key = array_search($file_to_delete, $existing_files)) !== false){
        unset($existing_files[$key]);
        $existing_files = array_values($existing_files);
        
        if(file_exists("upload/" . $file_to_delete)) {
            unlink("upload/" . $file_to_delete);
        }
        
        $json_updated = mysqli_real_escape_string($conn, json_encode($existing_files));
        mysqli_query($conn, "UPDATE material_gudang SET lampiran_files = '$json_updated' WHERE id = $id");
        echo "<script>alert('Berkas dihapus!'); window.location.href='edit.php?id=$id';</script>";
        exit;
    }
}

// Proses update data
if(isset($_POST['update'])){
    $nama_material = mysqli_real_escape_string($conn, $_POST['nama_material']);
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);
    $jumlah = (int)$_POST['jumlah'];
    $no_rak = mysqli_real_escape_string($conn, $_POST['no_rak']);
    $kondisi = mysqli_real_escape_string($conn, $_POST['kondisi']);
    $lokasi_penyimpanan = mysqli_real_escape_string($conn, $_POST['lokasi_penyimpanan']);

    if(!empty($_FILES['files']['name'][0])) {
        // DIUBAH KE FOLDER upload/
        $target_dir = "upload/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        foreach($_FILES['files']['name'] as $key => $val) {
            if($_FILES['files']['name'][$key] != '') {
                $file_name = time() . '_' . basename($_FILES['files']['name'][$key]);
                $target_file = $target_dir . $file_name;
                if(move_uploaded_file($_FILES['files']['tmp_name'][$key], $target_file)) {
                    $existing_files[] = $file_name;
                }
            }
        }
    }
    
    $lampiran_files = mysqli_real_escape_string($conn, json_encode($existing_files));

    $query = "UPDATE material_gudang SET 
                nama_material = '$nama_material', 
                satuan = '$satuan', 
                jumlah = '$jumlah', 
                no_rak = '$no_rak', 
                kondisi = '$kondisi', 
                lokasi_penyimpanan = '$lokasi_penyimpanan',
                lampiran_files = '$lampiran_files' 
              WHERE id = $id";
              
    if(mysqli_query($conn, $query)){
        echo "<script>alert('Data berhasil diperbarui!'); window.location.href='stok.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data: ".mysqli_error($conn)."');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Edit Stok</title>
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
        
        /* Layout Sidebar */
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

        .content { margin-left: 260px; position: relative; }
        .navbar-custom { background: #ffffff; padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999; }
        .main-body-wrapper { padding: 40px; }

        /* Full Width Card Desain */
        .glass-form-card { background: var(--bg-card); border: 1px solid #e2e8f0; border-radius: 20px; padding: 40px; width: 100%; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04); }
        .form-label { font-weight: 700; color: #1e293b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; }
        .form-control, .form-select { background: #f8fafc !important; border: 1px solid #cbd5e1 !important; border-radius: 10px; padding: 12px 16px; font-size: 0.9rem; color: var(--text-main); transition: all 0.2s ease; }
        .form-control:focus, .form-select:focus { border-color: var(--primary) !important; box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.12) !important; background: #ffffff !important; }

        .btn-submit-ba { background: #0284c7; border: none; border-radius: 8px; padding: 10px 24px; font-size: 0.9rem; font-weight: 700; color: white; transition: all 0.2s; }
        .btn-submit-ba:hover { background: #0369a1; }
        .btn-back-custom { border-radius: 8px; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 10px 24px; font-size: 0.9rem; font-weight: 600; color: #475569; text-decoration: none; transition: all 0.2s; }
        .btn-back-custom:hover { background: #e2e8f0; color: #1e293b; }

        /* Komponen Pratinjau Gambar X Melayang */
        .gallery-item-wrapper { position: relative; width: 100%; height: 140px; border-radius: 10px; overflow: hidden; border: 1px solid #cbd5e1; }
        .img-gallery-edit { width: 100%; height: 100%; object-fit: cover; }
        .btn-delete-image-overlay { position: absolute; top: 6px; right: 6px; width: 26px; height: 26px; background: rgba(239, 68, 68, 0.9); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #ffffff; text-decoration: none; font-size: 0.8rem; box-shadow: 0 2px 6px rgba(0,0,0,0.2); transition: all 0.2s; }
        .btn-delete-image-overlay:hover { background: #dc2626; transform: scale(1.1); color: #ffffff; }

        .file-badge { display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 1px solid #cbd5e1; padding: 10px 14px; border-radius: 10px; margin-bottom: 6px; font-size: 0.85rem; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fa-solid fa-bolt text-primary"></i> I-CALM Panel</h3>
    <a href="/monitoring_barang/dashboard/index.php"><span class="menu-content-wrapper"><i class="fa-solid fa-chart-pie"></i><span>Dashboard</span></span></a>
    <button class="dropdown-btn"><span class="menu-content-wrapper"><i class="fa-solid fa-layer-group menu-icon"></i><span>Monitoring</span></span><i class="fa-solid fa-chevron-down dropdown-chevron"></i></button>
    <div class="dropdown-container"><a href="/monitoring_barang/material/index.php">Material Gudang</a><a href="/monitoring_barang/ba/index.php">Database BA</a></div>
    <button class="dropdown-btn active active-category-btn" style="background-color: #0284c7; color: white;"><span class="menu-content-wrapper"><i class="fa-solid fa-tags menu-icon" style="color: white !important;"></i><span>Kategori</span></span><i class="fa-solid fa-chevron-down dropdown-chevron"></i></button>
    <div class="dropdown-container" style="display: block;"><a href="/monitoring_barang/kategori/stok/stok.php" class="active-menu">Stok</a><a href="/monitoring_barang/kategori/non_stok/non_stok.php">Non Stok</a><a href="/monitoring_barang/kategori/non_po/non_po.php">Non PO</a><a href="/monitoring_barang/kategori/ex_bongkaran/ex_bongkaran.php">Ex Bongkaran</a><a href="/monitoring_barang/kategori/pre_memory/pre_memory.php">Pre Memory</a><a href="/monitoring_barang/kategori/peminjaman/peminjaman.php">Peminjaman</a><a href="/monitoring_barang/kategori/pemakaian/pemakaian.php">Pemakaian</a></div>
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
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Kategori: Stok / Edit Data</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="glass-form-card">
            <h4 class="fw-bold mb-4" style="color: #0f172a; letter-spacing: -0.02em;"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Form Modifikasi Data Stok</h4>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-4">
                    
                    <div class="col-md-6">
                        <label class="form-label">Nama Kelompok Material / Barang</label>
                        <input type="text" name="nama_material" class="form-control" value="<?= htmlspecialchars($data['nama_material']); ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="satuan" class="form-control" value="<?= htmlspecialchars($data['satuan']); ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Jumlah Volume</label>
                        <input type="number" name="jumlah" class="form-control" value="<?= $data['jumlah']; ?>" min="0" required>
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
                                $selected = ($data['no_rak'] == $rak) ? 'selected' : '';
                                echo "<option value='$rak' $selected>$rak</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status Kondisi</label>
                        <select name="kondisi" class="form-select" required>
                            <option value="BAIK" <?= ($data['kondisi'] == 'BAIK') ? 'selected' : ''; ?>>BAIK</option>
                            <option value="RUSAK" <?= ($data['kondisi'] == 'RUSAK') ? 'selected' : ''; ?>>RUSAK</option>
                            <option value="PERLU PERBAIKAN" <?= ($data['kondisi'] == 'PERLU PERBAIKAN') ? 'selected' : ''; ?>>PERLU PERBAIKAN</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Lokasi Penyimpanan</label>
                        <input type="text" name="lokasi_penyimpanan" class="form-control" value="<?= htmlspecialchars($data['lokasi_penyimpanan']); ?>" required>
                    </div>

                    <!-- Blok Pratinjau Lampiran/Gambar Visual dengan Tombol X -->
                    <div class="col-12">
                        <div class="mt-2 border-top pt-3">
                            <label class="form-label d-block mb-3">Lampiran Dokumentasi Saat Ini</label>
                            
                            <?php 
                            if(!empty($existing_files)): 
                                $imgs = []; $docs = [];
                                foreach($existing_files as $file) {
                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                    if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) { $imgs[] = $file; } 
                                    else { $docs[] = $file; }
                                }
                                
                                if(!empty($imgs)):
                            ?>
                                <div class="row g-3 mb-3">
                                    <?php foreach($imgs as $img): ?>
                                        <div class="col-md-3 col-sm-6">
                                            <div class="gallery-item-wrapper">
                                                <img src="upload/<?= $img; ?>" class="img-gallery-edit" alt="Foto Dokumentasi">
                                                <!-- Tombol X Melayang di atas foto -->
                                                <a href="edit.php?id=<?= $id; ?>&delete_file=<?= urlencode($img); ?>" class="btn-delete-image-overlay" title="Hapus Gambar ini" onclick="return confirm('Apakah Anda yakin ingin menghapus gambar ini?');">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php 
                                endif; 
                                if(!empty($docs)):
                            ?>
                                <div class="list-group col-md-6 mb-3">
                                    <?php foreach($docs as $doc): ?>
                                        <div class="file-badge">
                                            <span class="text-truncate me-2 fw-600"><i class="fa-solid fa-file-pdf text-danger me-2 fs-5"></i><?= htmlspecialchars($doc); ?></span>
                                            <a href="edit.php?id=<?= $id; ?>&delete_file=<?= urlencode($doc); ?>" class="text-danger" onclick="return confirm('Hapus lampiran dokumen ini?');"><i class="fa-solid fa-xmark"></i></a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php 
                                endif;
                            else: 
                            ?>
                                <div class="text-muted small p-3 bg-light rounded border mb-3"><i class="fa-solid fa-box-open me-2"></i>Belum ada lampiran terunggah.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Unggah Tambahan Berkas / Foto Baru</label>
                        <input type="file" name="files[]" class="form-control" multiple>
                    </div>
                </div>

                <div class="mt-4 pt-2 d-flex gap-2">
                    <button type="submit" name="update" class="btn-submit-ba">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                    </button>
                    <a href="stok.php" class="btn-back-custom">Kembali Ke Halaman Utama</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
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