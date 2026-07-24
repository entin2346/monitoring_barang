<?php
session_start();
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

if(strtolower($_SESSION['role']) != 'admin'){
    die("Akses ditolak.");
}


include "../config/koneksi.php";

// --- FITUR AJAX UNTUK HAPUS FOTO/DOKUMEN SATU PER SATU ---
if (isset($_POST['action']) && $_POST['action'] === 'delete_single_photo') {
    $material_id = (int)$_POST['material_id'];
    $photo_to_delete = mysqli_real_escape_string($conn, $_POST['photo_name']);

    // Ambil data lampiran saat ini dari DB
    $q = mysqli_query($conn, "SELECT foto_barang FROM material_gudang WHERE id='$material_id'");
    $res = mysqli_fetch_assoc($q);

    if ($res) {
        $current_photos = explode(',', $res['foto_barang']);
        // Cari dan hapus berkas dari array
        $updated_photos = array_filter($current_photos, function($val) use ($photo_to_delete) {
            return trim($val) !== trim($photo_to_delete);
        });

        $new_string_foto = implode(',', $updated_photos);

        // Update database
        $update_q = mysqli_query($conn, "UPDATE material_gudang SET foto_barang='$new_string_foto' WHERE id='$material_id'");

        if ($update_q) {
            // Hapus file fisik dari server jika ada
            if (file_exists("upload/" . $photo_to_delete)) {
                unlink("upload/" . $photo_to_delete);
            }
            echo json_encode(['status' => 'success']);
            exit;
        }
    }
    echo json_encode(['status' => 'error']);
    exit;
}
// -------------------------------------------------

// 1. Ambil ID dari GET atau POST
$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

// Menggunakan COALESCE bersama NULLIF untuk mengatasi string kosong ('')
$query_select = mysqli_query($conn, "
    SELECT mg.*, 
           COALESCE(NULLIF(mg.sumber_barang, ''), ba.sumber_barang) AS sumber_barang,
           COALESCE(NULLIF(mg.keterangan, ''), ba.keterangan) AS keterangan
    FROM material_gudang mg
    LEFT JOIN database_ba ba ON mg.nama_material = ba.nama_barang
    WHERE mg.id='$id'
");
$data = mysqli_fetch_assoc($query_select);

if(!$data){
    header("Location: index.php");
    exit;
}

if(isset($_POST['update'])){
    $nama_material  = mysqli_real_escape_string($conn, $_POST['nama_material']);
    $jenis_kategori = mysqli_real_escape_string($conn, $_POST['jenis_kategori']);
    $satuan         = mysqli_real_escape_string($conn, $_POST['satuan']);
    $jumlah         = (int)$_POST['jumlah'];
    $no_rak         = mysqli_real_escape_string($conn, $_POST['no_rak']);
    $kondisi        = mysqli_real_escape_string($conn, $_POST['kondisi']);
    $lokasi         = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $sumber_barang  = mysqli_real_escape_string($conn, $_POST['sumber_barang']); 
    $keterangan     = mysqli_real_escape_string($conn, $_POST['keterangan']);

    $string_foto = $data['foto_barang'];

    // 2. Cek apakah ada file-file baru yang diunggah
    $files_uploaded = false;
    if (isset($_FILES['foto']) && is_array($_FILES['foto']['name'])) {
        foreach ($_FILES['foto']['name'] as $name) {
            if (!empty($name)) {
                $files_uploaded = true;
                break;
            }
        }
    }

    if ($files_uploaded) {
        $files = $_FILES['foto'];
        $uploaded_photos = array();
        $is_valid = true;

        if ($is_valid) {
            if (!is_dir("upload")) {
                mkdir("upload", 0755, true);
            }

            foreach ($files['name'] as $key => $filename) {
                if (!empty($filename) && $files['error'][$key] === 0) {
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    // Amankan nama file dengan enkripsi waktu unik
                    $new_filename = time() . '_' . uniqid() . '.' . $ext;

                    if (move_uploaded_file($files['tmp_name'][$key], "upload/" . $new_filename)) {
                        $uploaded_photos[] = $new_filename;
                    }
                }
            }

            if (!empty($uploaded_photos)) {
                $string_foto_baru = implode(',', $uploaded_photos);
                if (!empty($data['foto_barang'])) {
                    $string_foto = $data['foto_barang'] . ',' . $string_foto_baru;
                } else {
                    $string_foto = $string_foto_baru;
                }
            }
        }
    }

    // 3. Eksekusi UPDATE query
    $query_update = "UPDATE material_gudang SET
                        nama_material='$nama_material',
                        jenis_kategori='$jenis_kategori',
                        satuan='$satuan',
                        jumlah='$jumlah',
                        no_rak='$no_rak',
                        kondisi='$kondisi',
                        lokasi_penyimpanan='$lokasi',
                        sumber_barang='$sumber_barang',
                        keterangan='$keterangan',
                        foto_barang='$string_foto'
                     WHERE id='$id'";

    if(mysqli_query($conn, $query_update)){
        header("Location: index.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Material - I-CALM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
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
        
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary); }

        body { background: var(--bg-body); color: var(--text-main); font-family: 'Plus Jakarta Sans', sans-serif; overflow-x: hidden; }
        
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
        
        .content { margin-left: 260px; padding: 40px; }
        .glass-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 32px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        
        .preview-photo-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 14px; padding: 4px; }
        .preview-photo-box { 
            position: relative; background: #f8fafc; border: 1px solid var(--border-color); 
            border-radius: 12px; padding: 6px; height: 110px; display: flex; flex-direction: column; align-items: center; justify-content: center; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.03); transition: transform 0.2s;
        }
        .preview-photo-box:hover { transform: translateY(-2px); }
        .img-edit-preview { max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px; cursor: pointer; }
        
        .doc-icon-preview { font-size: 2rem; color: var(--primary); margin-bottom: 4px; }
        .doc-name-text { font-size: 0.65rem; text-align: center; color: var(--text-main); max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding: 0 2px; }

        .btn-delete-photo {
            position: absolute; top: -6px; right: -6px; width: 22px; height: 22px;
            background: #ef4444; color: #fff; border: none; border-radius: 50%;
            font-size: 11px; font-weight: bold; display: flex; align-items: center; justify-content: center;
            cursor: pointer; box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4); z-index: 10; transition: background 0.2s;
        }
        .btn-delete-photo:hover { background: #dc2626; }

        .form-label-custom { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-muted); margin-bottom: 8px; }
        .form-control-custom { background: #f8fafc !important; border: 1px solid rgba(148, 163, 184, 0.25) !important; border-radius: 12px !important; padding: 12px 16px; color: var(--text-main) !important; }
        .form-control-custom:focus { border-color: var(--primary) !important; box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.1) !important; background-color: #ffffff !important; }
    </style>
</head>
<body>

<!-- SIDEBAR DISINKRONKAN DENGAN INDEX -->
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
        <a href="../material/index.php" class="active-menu">Material Gudang</a>
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Ubah Informasi Komponen</h3>
        <a href="detail.php?id=<?= $id; ?>" class="btn btn-outline-secondary px-3 fw-semibold border-2" style="border-radius: 10px;">Batal</a>
    </div>

    <div class="glass-card">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="material_id" value="<?= $data['id']; ?>">
            
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-custom">Nama Kelompok Material Gudang</label>
                            <input type="text" name="nama_material" value="<?= htmlspecialchars($data['nama_material']); ?>" class="form-control form-control-custom" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Kategori Kelompok</label>
                           <select name="jenis_kategori" class="form-select form-control-custom" required>
    <?php 
    // SINKRONISASI LOGIKA DENGAN INDEX.PHP BERDASARKAN ID BARANG
    $is_stock_id = ((int)$data['id'] <= 63);
    $is_nonstock_id = ((int)$data['id'] > 63);
    
    $curr_kat = strtolower(trim($data['jenis_kategori'] ?? '')); 
    ?>
    <option value="Stok" <?= ($is_stock_id || $curr_kat == 'stok' || $curr_kat == 'material stock' || $curr_kat == 'stock') ? 'selected' : ''; ?>>Stok</option>
    <option value="Non Stock" <?= ($is_nonstock_id || $curr_kat == 'non stock' || $curr_kat == 'non-stock' || $curr_kat == 'non stok' || $curr_kat == 'non-stok') ? 'selected' : ''; ?>>Non Stock</option>
    <option value="Non PO" <?= ($curr_kat == 'non po' || $curr_kat == 'non-po') ? 'selected' : ''; ?>>Non PO</option>
    <option value="Ex Bongkaran" <?= ($curr_kat == 'ex bongkaran' || $curr_kat == 'ex-bongkaran') ? 'selected' : ''; ?>>Ex Bongkaran</option>
    <option value="Pre Memory" <?= ($curr_kat == 'pre memory' || $curr_kat == 'pre-memory') ? 'selected' : ''; ?>>Pre Memory</option>
</select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Satuan Ukur</label>
                            <input type="text" name="satuan" value="<?= htmlspecialchars($data['satuan']); ?>" class="form-control form-control-custom">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Jumlah Stok</label>
                            <input type="number" name="jumlah" value="<?= $data['jumlah']; ?>" class="form-control form-control-custom" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Nomor Rak</label>
                            <select name="no_rak" class="form-select form-control-custom">
                                <option value="">-- Pilih Nomor Rak --</option>
                                <?php 
                                $list_rak = ['A1', 'A2', 'A3', 'B1', 'B2', 'B3', 'B4', 'C1', 'C2', 'C3', 'D1', 'D2', 'D3', 'E1', 'E2', 'F1', 'F2', 'G1', 'G2', 'G3', 'H1', 'H2', 'H3', 'I1', 'I2', 'J1', 'J2', 'K1', 'K2', 'K3', 'M1', 'M2', 'M3', 'PETI', 'RAK ISOLATOR'];
                                $curr_rak = strtoupper(trim($data['no_rak'] ?? ''));
                                foreach($list_rak as $rak) {
                                    $selected = ($curr_rak == strtoupper($rak)) ? 'selected' : '';
                                    echo "<option value='$rak' $selected>$rak</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Status Kondisi</label>
                            <select name="kondisi" class="form-select form-control-custom">
                                <option value="BAIK" <?= (strtoupper($data['kondisi'] ?? '') == 'BAIK') ? 'selected' : ''; ?>>BAIK</option>
                                <option value="RUSAK" <?= (strtoupper($data['kondisi'] ?? '') == 'RUSAK') ? 'selected' : ''; ?>>RUSAK</option>
                                <option value="PERLU CEK" <?= (strtoupper($data['kondisi'] ?? '') == 'PERLU CEK') ? 'selected' : ''; ?>>PERLU CEK</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">Lokasi Penyimpanan</label>
                            <input type="text" name="lokasi" value="<?= htmlspecialchars($data['lokasi_penyimpanan']); ?>" class="form-control form-control-custom">
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">Sumber Barang / Asal Material</label>
                            <input type="text" name="sumber_barang" value="<?= htmlspecialchars($data['sumber_barang'] ?? ''); ?>" class="form-control form-control-custom" placeholder="Contoh: Pengadaan PO 2026 / Vendor X">
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">Keterangan / Catatan Tambahan</label>
                            <textarea name="keterangan" class="form-control form-control-custom" rows="4"><?= htmlspecialchars($data['keterangan'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label-custom">Pratinjau Seluruh Berkas Saat Ini</label>
                            <div class="card bg-light border-0 rounded-4 p-3 shadow-sm">
                                <div class="preview-photo-container" id="photo_wrapper">
                                    <?php 
                                    if(!empty($data['foto_barang'])){
                                        $array_foto = explode(',', $data['foto_barang']);
                                        $has_photo = false;
                                        
                                        foreach($array_foto as $item_foto){
                                            $item_foto = trim($item_foto);
                                            if(!empty($item_foto) && file_exists("upload/".$item_foto)){
                                                $has_photo = true;
                                                echo '<div class="preview-photo-box" id="box_'.md5($item_foto).'">';
                                                echo '<button type="button" class="btn-delete-photo" onclick="deletePhoto(\''.$item_foto.'\')">&times;</button>';
                                                
                                                $ext = strtolower(pathinfo($item_foto, PATHINFO_EXTENSION));
                                                $img_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                                                
                                                if(in_array($ext, $img_exts)){
                                                    echo '<img src="upload/'.$item_foto.'" class="img-edit-preview" alt="Barang" onclick="openPreview(\'upload/'.$item_foto.'\', true)">';
                                                } else {
                                                    $icon = 'fa-file-lines';
                                                    if($ext == 'pdf') $icon = 'fa-file-pdf text-danger';
                                                    elseif(in_array($ext, ['xls', 'xlsx', 'csv'])) $icon = 'fa-file-excel text-success';
                                                    elseif(in_array($ext, ['doc', 'docx'])) $icon = 'fa-file-word text-primary';
                                                    elseif(in_array($ext, ['zip', 'rar'])) $icon = 'fa-file-zipper text-warning';
                                                    
                                                    echo '<i class="fa-solid '.$icon.' doc-icon-preview" onclick="openPreview(\'upload/'.$item_foto.'\', false)"></i>';
                                                    echo '<span class="doc-name-text" title="'.htmlspecialchars($item_foto).'">'.htmlspecialchars($item_foto).'</span>';
                                                }
                                                echo '</div>';
                                            }
                                        }
                                        if(!$has_photo){ echo '<div id="no_photo_msg" class="text-muted small py-4 text-center w-100"><i class="fa-solid fa-folder-open d-block fs-2 mb-2 opacity-30"></i>Belum ada berkas</div>'; }
                                    } else { ?>
                                        <div id="no_photo_msg" class="text-muted small py-4 text-center w-100">
                                            <i class="fa-solid fa-folder-open d-block fs-2 mb-2 opacity-30"></i>Belum ada berkas terunggah
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">Tambah / Sisipkan Berkas Baru</label>
                            <input type="file" name="foto[]" class="form-control form-control-custom" multiple>
                            <div class="form-text small text-muted mt-1">Dapat mengunggah file gambar maupun dokumen (PDF, Excel, Word, ZIP, RAR, dsb). Berkas baru ditambahkan tanpa menghapus berkas lama.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 d-flex gap-2 border-top" style="border-color: var(--border-color) !important;">
                <button type="submit" name="update" class="btn btn-primary px-4 py-2 fw-bold" style="border-radius: 10px;">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                </button>
                <a href="detail.php?id=<?= $id; ?>" class="btn btn-light px-4 py-2 fw-semibold border text-secondary" style="border-radius: 10px;">Batal</a>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-transparent border-0 text-end">
        <button type="button" class="btn-close btn-close-white ms-auto mb-2" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-body p-0 text-center">
            <img src="" id="modalPreviewImg" style="max-width: 100%; max-height: 80vh; object-fit: contain; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function openPreview(fileSrc, isImage) {
        if(isImage) {
            document.getElementById('modalPreviewImg').src = fileSrc;
            var myModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
            myModal.show();
        } else {
            window.open(fileSrc, '_blank');
        }
    }

    function deletePhoto(photoName) {
        if (confirm('Apakah Anda yakin ingin menghapus berkas ini?')) {
            var materialId = document.getElementById('material_id').value;
            var boxes = document.getElementsByClassName('preview-photo-box');
            var targetBox = null;
            for(var i=0; i<boxes.length; i++){
                if(boxes[i].innerHTML.includes("'" + photoName + "'")){
                    targetBox = boxes[i];
                    break;
                }
            }

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        if (targetBox) {
                            targetBox.remove();
                        }
                        var remainingBoxes = document.getElementsByClassName('preview-photo-box');
                        if (remainingBoxes.length === 0) {
                            document.getElementById('photo_wrapper').innerHTML = '<div id="no_photo_msg" class="text-muted small py-4 text-center w-100"><i class="fa-solid fa-folder-open d-block fs-2 mb-2 opacity-30"></i>Belum ada berkas terunggah</div>';
                        }
                    } else {
                        alert('Gagal menghapus berkas dari server.');
                    }
                }
            };
            xhr.send("action=delete_single_photo&material_id=" + materialId + "&photo_name=" + encodeURIComponent(photoName));
        }
    }

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