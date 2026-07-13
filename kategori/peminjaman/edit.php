<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../../login/index.php");
    exit;
}
include "../../config/koneksi.php";

$id = $_GET['id'] ?? 0;
// Menggunakan tabel peminjaman agar sinkron dengan file tambah.php
$query = mysqli_query($conn, "SELECT * FROM peminjaman WHERE id = '$id'");
$d = mysqli_fetch_assoc($query);

if(!$d){
    echo "<script>alert('Data tidak ditemukan!'); window.location='peminjaman.php';</script>";
    exit;
}

$arr_dokumentasi = json_decode($d['dokumentasi'] ?? '[]', true);
if (!is_array($arr_dokumentasi)) {
    $arr_dokumentasi = [];
}

// --- FITUR PROSES HAPUS LAMPIRAN SECARA INSTAN ---
if (isset($_GET['delete_file'])) {
    $file_to_delete = $_GET['delete_file'];
    if (($key = array_search($file_to_delete, $arr_dokumentasi)) !== false) {
        unset($arr_dokumentasi[$key]);
        $arr_dokumentasi = array_values($arr_dokumentasi); // Reset indeks array
        
        // Hapus fisik file dari folder upload jika ada
        if (file_exists("upload/" . $file_to_delete)) {
            unlink("upload/" . $file_to_delete);
        }
        
        $json_updated = mysqli_real_escape_string($conn, json_encode($arr_dokumentasi));
        mysqli_query($conn, "UPDATE peminjaman SET dokumentasi = '$json_updated' WHERE id = '$id'");
        echo "<script>alert('Lampiran berhasil dihapus!'); window.location.href='edit.php?id=$id';</script>";
        exit;
    }
}

if(isset($_POST['ubah'])){
    $nama_material = mysqli_real_escape_string($conn, $_POST['nama_material']);
    $asal_material = mysqli_real_escape_string($conn, $_POST['asal_material']);
    $tanggal_pengambilan = mysqli_real_escape_string($conn, $_POST['tanggal_pengambilan']);
    $peminjam = mysqli_real_escape_string($conn, $_POST['peminjam']);
    $jumlah = (int)$_POST['jumlah'];
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);
    $status_kembali = mysqli_real_escape_string($conn, $_POST['status_kembali']);
    $jumlah_dikembalikan = mysqli_real_escape_string($conn, $_POST['jumlah_dikembalikan']);
    $link_ba_ambil = mysqli_real_escape_string($conn, $_POST['link_ba_ambil']);
    $link_ba_kembali = mysqli_real_escape_string($conn, $_POST['link_ba_kembali']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

    // --- PROSES UNGGAH BANYAK FOTO / BERKAS BARU (MENGGABUNGKAN KEDALAM ARRAY) ---
    if (!empty($_FILES['dokumentasi']['name'][0])) {
        if (!is_dir('upload')) {
            mkdir('upload', 0777, true);
        }
        
        foreach ($_FILES['dokumentasi']['name'] as $key => $val) {
            if ($_FILES['dokumentasi']['error'][$key] === 0) {
                $ext = pathinfo($_FILES['dokumentasi']['name'][$key], PATHINFO_EXTENSION);
                $filename = "doc_" . uniqid() . "." . $ext;
                if (move_uploaded_file($_FILES['dokumentasi']['tmp_name'][$key], "upload/" . $filename)) {
                    $arr_dokumentasi[] = $filename;
                }
            }
        }
    }
    
    $dokumentasi_json = mysqli_real_escape_string($conn, json_encode($arr_dokumentasi));

    $update = "UPDATE peminjaman SET 
               nama_material = '$nama_material', 
               asal_material = '$asal_material', 
               tanggal_pengambilan = '$tanggal_pengambilan', 
               peminjam = '$peminjam', 
               jumlah = '$jumlah', 
               satuan = '$satuan', 
               status_kembali = '$status_kembali', 
               jumlah_dikembalikan = '$jumlah_dikembalikan', 
               link_ba_ambil = '$link_ba_ambil', 
               link_ba_kembali = '$link_ba_kembali', 
               dokumentasi = '$dokumentasi_json', 
               keterangan = '$keterangan' 
               WHERE id = '$id'";
               
    if(mysqli_query($conn, $update)){
        echo "<script>alert('Data peminjaman berhasil diperbarui!'); window.location='peminjaman.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal memperbarui data: " . mysqli_error($conn) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Edit Peminjaman</title>
    
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
        .sidebar {
            position: fixed; left: 0; top: 0; width: 260px; height: 100%;
            background-color: var(--bg-sidebar); border-right: 1px solid rgba(2, 132, 199, 0.15);
            padding: 35px 20px; z-index: 1050; display: flex; flex-direction: column; overflow-y: auto;
        }
        .sidebar h3 { font-size: 1.25rem; font-weight: 800; color: #1e3a8a; margin-bottom: 35px; display: flex; align-items: center; gap: 10px; }
        .sidebar a, .dropdown-btn { display: flex; align-items: center; justify-content: space-between; color: #1e3a8a; text-decoration: none; padding: 11px 14px; font-size: 0.9rem; font-weight: 700; border: none; background: transparent; width: 100%; cursor: pointer; border-radius: 10px; margin-bottom: 5px; transition: all 0.2s ease-in-out; }
        .sidebar a:hover, .dropdown-btn:hover { color: #025a9c; background: rgba(2, 132, 199, 0.12); transform: translateX(4px); }
        .sidebar .menu-content-wrapper { display: flex; align-items: center; gap: 12px; }
        .sidebar a i, .dropdown-btn i.menu-icon { font-size: 1.05rem; width: 20px; text-align: center; color: #1e40af; }
        
        .sidebar .dropdown-btn.active { color: #ffffff !important; background: #0284c7 !important; font-weight: 700; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); border-radius: 10px; }
        .sidebar .dropdown-btn.active i { color: #ffffff !important; }
        .dropdown-chevron { font-size: 0.75rem !important; transition: transform 0.2s ease; color: #1e40af !important; }
        .dropdown-btn.active .dropdown-chevron { transform: rotate(180deg); color: #ffffff !important; }
        
        .dropdown-container { display: none; padding-left: 12px; margin-bottom: 6px; margin-top: 4px; }
        .dropdown-container a { padding: 9px 14px; font-size: 0.85rem; color: #1e40af; font-weight: 600; background: rgba(255, 255, 255, 0.2); border-radius: 8px; margin-bottom: 3px; }
        .dropdown-container a:hover { background: #ffffff; color: #0284c7; }
        .dropdown-container a.active-menu { color: #ffffff !important; background: #0284c7 !important; font-weight: 700; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); border-radius: 10px; }
        
        .sidebar .logout-button { margin-top: auto; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; padding: 11px 14px; text-decoration: none; display: flex; align-items: center; }
        .sidebar .logout-button i, .sidebar .logout-button span { color: #b91c1c !important; }

        /* CONTENT STYLE */
        .content { margin-left: 260px; position: relative; width: calc(100% - 260px); }
        .navbar-custom { background: #ffffff; padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999; }
        .main-body-wrapper { padding: 40px; }
        
        /* CARD STYLE LAYOUT */
        .glass-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 40px; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04); width: 100%; }
        
        /* INPUT & LABEL STYLE */
        .form-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; margin-bottom: 8px; }
        .form-control, .form-select { border-radius: 10px; border: 1px solid #cbd5e1; padding: 13px 18px; font-size: 0.9rem; background-color: #f8fafc; transition: all 0.2s; color: var(--text-main); }
        .form-control:focus, .form-select:focus { background-color: #fff; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15); }
        
        /* OVERLAY PREVIEW UNTUK GAMBAR DAN TOMBOL X */
        .gallery-item-wrapper { position: relative; display: inline-block; width: 100px; height: 100px; border-radius: 10px; overflow: hidden; border: 1px solid #cbd5e1; margin-right: 10px; margin-bottom: 10px; }
        .preview-thumb { width: 100%; height: 100%; object-fit: cover; cursor: pointer; }
        .btn-delete-overlay { position: absolute; top: 4px; right: 4px; width: 22px; height: 22px; background: rgba(239, 68, 68, 0.9); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #ffffff; text-decoration: none; font-size: 0.7rem; box-shadow: 0 2px 4px rgba(0,0,0,0.2); transition: all 0.2s; }
        .btn-delete-overlay:hover { background: #dc2626; transform: scale(1.1); color: #ffffff; }

        /* BADGE UNTUK FILE DOKUMEN */
        .file-badge { display: inline-flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 14px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.85rem; margin-right: 6px; margin-bottom: 6px; text-decoration: none; color: #334155; font-weight: 500; }
        .file-badge .btn-delete-file { color: #ef4444; text-decoration: none; font-size: 0.9rem; margin-left: 8px; }
        .file-badge .btn-delete-file:hover { color: #b91c1c; }

        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; border-right: none; padding: 20px; }
            .content { margin-left: 0; width: 100%; }
            .main-body-wrapper { padding: 20px; }
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
        <a href="/monitoring_barang/kategori/non_po/non_po.php">Non PO</a>
        <a href="/monitoring_barang/kategori/ex_bongkaran/ex_bongkaran.php">Ex Bongkaran</a>
        <a href="/monitoring_barang/kategori/pre_memory/pre_memory.php">Pre Memory</a>
        <a href="/monitoring_barang/kategori/peminjaman/peminjaman.php" class="active-menu">Peminjaman</a>
        <a href="/monitoring_barang/kategori/pemakaian/pemakaian.php">Pemakaian</a>
    </div>

    <button class="dropdown-btn">
        <span class="menu-content-wrapper"><i class="fa-solid fa-file-import menu-icon"></i><span>Import</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="/monitoring_barang/import/material.php">Import Material</a>
        <a href="/monitoring_barang/import/ba.php">Import BA</a>
        <a href="/monitoring_barang/import/form_stok.php">Import Stok</a>
        <a href="/monitoring_barang/import/form_non_stok.php">Import Non Stok</a>
        <a href="/monitoring_barang/import/form_non_po.php">Import Non PO</a>
        <a href="/monitoring_barang/import/form_ex_bongkaran.php">Import Ex Bongkaran</a>
        <a href="/monitoring_barang/import/form_pre_memory.php">Import Pre Memory</a>
        <a href="/monitoring_barang/import/form_peminjaman.php">Import Peminjaman</a>
        <a href="/monitoring_barang/import/form_pemakaian.php">Import Pemakaian</a>
    </div>

    <button class="dropdown-btn">
        <span class="menu-content-wrapper"><i class="fa-solid fa-file-export menu-icon"></i><span>Export</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="/monitoring_barang/export/material_excel.php">Export Material</a>
        <a href="/monitoring_barang/export/ba_excel.php">Export BA</a>
    </div>
    
    <a href="/monitoring_barang/login/logout.php" class="logout-button"><span class="menu-content-wrapper"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></span></a>
</div>

<div class="content">
    <nav class="navbar navbar-custom">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center">
                <i class="fa-solid fa-boxes-stacked text-primary me-2"></i> KENDALI LOGISTIK 
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Kategori: Peminjaman / Edit Data</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="glass-card">
            <h4 class="fw-bold mb-4" style="color: #0f172a;"><i class="fa-solid fa-pen-to-square text-warning me-2"></i>Form Edit Peminjaman Material</h4>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Material (Nama Material)</label>
                        <input type="text" name="nama_material" class="form-control" value="<?= htmlspecialchars($d['nama_material'] ?? ''); ?>" required autocomplete="off">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Asal Material</label>
                        <input type="text" name="asal_material" class="form-control" value="<?= htmlspecialchars($d['asal_material'] ?? ''); ?>" autocomplete="off">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Pengambilan</label>
                        <input type="date" name="tanggal_pengambilan" class="form-control" value="<?= htmlspecialchars($d['tanggal_pengambilan'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Peminjam Material</label>
                        <input type="text" name="peminjam" class="form-control" value="<?= htmlspecialchars($d['peminjam'] ?? ''); ?>" autocomplete="off" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Jumlah / Volume Pinjam</label>
                        <input type="number" name="jumlah" class="form-control" min="1" value="<?= $d['jumlah'] ?? 1; ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="satuan" class="form-control" value="<?= htmlspecialchars($d['satuan'] ?? ''); ?>" autocomplete="off" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Status Pengembalian</label>
                        <select name="status_kembali" class="form-select">
                            <option value="BELUM" <?= (strtoupper($d['status_kembali'] ?? '') == 'BELUM') ? 'selected' : ''; ?>>BELUM</option>
                            <option value="SUDAH" <?= (strtoupper($d['status_kembali'] ?? '') == 'SUDAH') ? 'selected' : ''; ?>>SUDAH</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Jumlah Dikembalikan</label>
                        <input type="text" name="jumlah_dikembalikan" class="form-control" value="<?= htmlspecialchars($d['jumlah_dikembalikan'] ?? ''); ?>" autocomplete="off">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Link BA Pengambilan Material</label>
                        <input type="url" name="link_ba_ambil" class="form-control" value="<?= htmlspecialchars($d['link_ba_ambil'] ?? ''); ?>" autocomplete="off">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Link BA Pengembalian Material</label>
                        <input type="url" name="link_ba_kembali" class="form-control" value="<?= htmlspecialchars($d['link_ba_kembali'] ?? ''); ?>" autocomplete="off">
                    </div>

                    <!-- PRATINJAU BERKAS DENGAN TOMBOL HAPUS (X) MELAYANG -->
                    <div class="col-md-12">
                        <label class="form-label">Lampiran Saat Ini</label>
                        <div class="p-3 border bg-light mb-2" style="border-radius:10px;">
                            <?php 
                            if(!empty($arr_dokumentasi)):
                                $imgs = []; $docs = [];
                                foreach($arr_dokumentasi as $file) {
                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                    if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) { $imgs[] = $file; } 
                                    else { $docs[] = $file; }
                                }
                                
                                // Render Khusus Gambar
                                if(!empty($imgs)):
                                    foreach($imgs as $img):
                                        $filepath = "upload/" . $img;
                                        if(file_exists($filepath)):
                            ?>
                                            <div class="gallery-item-wrapper">
                                                <img src="<?= $filepath; ?>" class="preview-thumb" onclick="window.open(this.src, '_blank')" title="Klik untuk memperbesar">
                                                <a href="edit.php?id=<?= $id; ?>&delete_file=<?= urlencode($img); ?>" class="btn-delete-overlay" title="Hapus Gambar" onclick="return confirm('Apakah Anda yakin ingin menghapus gambar ini?');">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </a>
                                            </div>
                            <?php 
                                        endif;
                                    endforeach;
                                endif;

                                // Render Khusus Dokumen Non-Gambar (PDF/Word)
                                if(!empty($docs)):
                                    echo '<div class="d-block mt-2">';
                                    foreach($docs as $doc):
                                        $filepath = "upload/" . $doc;
                                        if(file_exists($filepath)):
                                            $ext = strtolower(pathinfo($doc, PATHINFO_EXTENSION));
                                            $icon = "fa-file-lines";
                                            if($ext == 'pdf') $icon = "fa-file-pdf text-danger";
                                            if(in_array($ext, ['doc', 'docx'])) $icon = "fa-file-word text-primary";
                            ?>
                                            <div class="file-badge">
                                                <a href="<?= $filepath; ?>" target="_blank" class="text-decoration-none text-dark">
                                                    <i class="fa-solid <?= $icon; ?> me-1"></i> <?= htmlspecialchars($doc); ?>
                                                </a>
                                                <a href="edit.php?id=<?= $id; ?>&delete_file=<?= urlencode($doc); ?>" class="btn-delete-file" title="Hapus dokumen" onclick="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?');">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </a>
                                            </div>
                            <?php 
                                        endif;
                                    endforeach;
                                    echo '</div>';
                                endif;
                            else:
                            ?>
                                <span class="text-muted fs-7"><i class="fa-solid fa-circle-minus me-1"></i> Belum ada lampiran berkas terunggah.</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Tambah Lampiran Dokumentasi Baru</label>
                        <input type="file" name="dokumentasi[]" class="form-control" multiple accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                        <div class="form-text text-muted" style="font-size: 0.75rem;">Berkas yang dipilih akan ditambahkan ke lampiran lama tanpa menghapusnya. Tekan Ctrl untuk memilih banyak berkas sekaligus.</div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Masukkan catatan opsional atau rincian tambahan di sini..."><?= htmlspecialchars($d['keterangan'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="mt-4 gap-2 d-flex">
                    <button type="submit" name="ubah" class="btn btn-warning px-4 fw-bold text-white" style="border-radius:10px; background-color: #f59e0b; border:none;"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan</button>
                    <a href="peminjaman.php" class="btn btn-light px-4 border" style="border-radius:10px;">Batal</a>
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