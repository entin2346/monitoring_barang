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

// AJAX Handler untuk menghapus gambar individu atau berkas BA
if (isset($_POST['action']) && $_POST['action'] === 'delete_image') {
    $img_to_delete = $_POST['image_name'];
    $field = $_POST['field_name']; 
    
    if ($id > 0) {
        $get_current = mysqli_query($conn, "SELECT $field FROM ex_bongkaran WHERE id = $id");
        $current_data = mysqli_fetch_assoc($get_current);
        
        if ($current_data) {
            $success = false;
            // Jika kolom bertipe JSON (foto_nameplate atau foto_material)
            if ($field === 'foto_nameplate' || $field === 'foto_material') {
                $images = json_decode($current_data[$field] ?? '[]', true);
                if (($key = array_search($img_to_delete, $images)) !== false) {
                    unset($images[$key]);
                    $images = array_values($images); 
                    $new_json = mysqli_real_escape_string($conn, json_encode($images));
                    if (mysqli_query($conn, "UPDATE ex_bongkaran SET $field = '$new_json' WHERE id = $id")) {
                        $success = true;
                    }
                }
            } 
            // Jika kolom bertipe teks/file tunggal (BA Pemindahan, Pemanfaatan, Penggantian)
            else if (in_array($field, ['link_ba_pemindahan', 'link_ba_pemanfaatan', 'link_ba_penggantian'])) {
                if (mysqli_query($conn, "UPDATE ex_bongkaran SET $field = '' WHERE id = $id")) {
                    $success = true;
                }
            }

            if ($success) {
                if (!empty($img_to_delete) && file_exists("upload/" . $img_to_delete)) {
                    @unlink("upload/" . $img_to_delete);
                }
                echo json_encode(['status' => 'success']);
                exit;
            }
        }
    }
    echo json_encode(['status' => 'error']);
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM ex_bongkaran WHERE id = $id");
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='ex_bongkaran.php';</script>";
    exit;
}

if (isset($_POST['update'])) {
    $unit = mysqli_real_escape_string($conn, $_POST['unit']);
    $nama_material = mysqli_real_escape_string($conn, $_POST['nama_material']);
    $mtu = mysqli_real_escape_string($conn, $_POST['mtu']);
    $tegangan = mysqli_real_escape_string($conn, $_POST['tegangan']);
    $merk_tipe = mysqli_real_escape_string($conn, $_POST['merk_tipe']);
    $no_seri = mysqli_real_escape_string($conn, $_POST['no_seri']);
    $gardu_induk = mysqli_real_escape_string($conn, $_POST['gardu_induk']);
    $lokasi_asal_eks_bongkaran = mysqli_real_escape_string($conn, $_POST['lokasi_asal_eks_bongkaran']);
    $no_kontrak_penggantian = mysqli_real_escape_string($conn, $_POST['no_kontrak_penggantian']);
    $judul_kontrak_penggantian = mysqli_real_escape_string($conn, $_POST['judul_kontrak_penggantian']);
    $jumlah = (int)$_POST['jumlah'];
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);
    $nilai_buku = (float)$_POST['nilai_buku'];
    $berat = mysqli_real_escape_string($conn, $_POST['berat']);
    $lokasi_penyimpanan = mysqli_real_escape_string($conn, $_POST['lokasi_penyimpanan']);
    $kondisi = mysqli_real_escape_string($conn, $_POST['kondisi']);
    $justifikasi_kondisi = mysqli_real_escape_string($conn, $_POST['justifikasi_kondisi']);
    $kelengkapan_aksesoris = mysqli_real_escape_string($conn, $_POST['kelengkapan_aksesoris']);
    $ket_kelengkapan_aksesoris = mysqli_real_escape_string($conn, $_POST['ket_kelengkapan_aksesoris']);
    $keterangan_ex_bongkaran = mysqli_real_escape_string($conn, $_POST['keterangan_ex_bongkaran']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $ket_waktu_pembongkaran = mysqli_real_escape_string($conn, $_POST['ket_waktu_pembongkaran']);
    $no_at = mysqli_real_escape_string($conn, $_POST['no_at']);
    $nilai_perolehan = (float)$_POST['nilai_perolehan'];
    $techidentno = mysqli_real_escape_string($conn, $_POST['techidentno']);
    $upt = mysqli_real_escape_string($conn, $_POST['upt']);
    $umur_operasi = mysqli_real_escape_string($conn, $_POST['umur_operasi']);
    $umur_simpan = mysqli_real_escape_string($conn, $_POST['umur_simpan']);
    $tahun_pembuatan = mysqli_real_escape_string($conn, $_POST['tahun_pembuatan']);
    $funloct = mysqli_real_escape_string($conn, $_POST['funloct']);
    $katalog_mara = mysqli_real_escape_string($conn, $_POST['katalog_mara']);
    $no_aset = mysqli_real_escape_string($conn, $_POST['no_aset']);
    $link_ba_pemindahan = mysqli_real_escape_string($conn, $_POST['link_ba_pemindahan']);
    $link_hasil_uji = mysqli_real_escape_string($conn, $_POST['link_hasil_uji']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $keterangan_tambahan = mysqli_real_escape_string($conn, $_POST['keterangan_tambahan']);
    $tanggal_update_terakhir = date('Y-m-d H:i:s');

    $target_dir = "upload/";

    // --- PROSES UPDATE FILE BA PEMANFAATAN ---
    $link_ba_pemanfaatan = $data['link_ba_pemanfaatan']; // default pakai yang lama
    if (!empty($_FILES['ba_pemanfaatan_file']['name'])) {
        $file_name_ba_pem = time() . '_ba_pem_' . basename($_FILES['ba_pemanfaatan_file']['name']);
        if (move_uploaded_file($_FILES['ba_pemanfaatan_file']['tmp_name'], $target_dir . $file_name_ba_pem)) {
            // Hapus file lama jika ada
            if (!empty($data['link_ba_pemanfaatan']) && file_exists($target_dir . $data['link_ba_pemanfaatan'])) {
                @unlink($target_dir . $data['link_ba_pemanfaatan']);
            }
            $link_ba_pemanfaatan = mysqli_real_escape_string($conn, $file_name_ba_pem);
        }
    }

    // --- PROSES UPDATE FILE BA PENGGANTIAN ---
    $link_ba_penggantian = $data['link_ba_penggantian']; // default pakai yang lama
    if (!empty($_FILES['ba_penggantian_file']['name'])) {
        $file_name_ba_peng = time() . '_ba_peng_' . basename($_FILES['ba_penggantian_file']['name']);
        if (move_uploaded_file($_FILES['ba_penggantian_file']['tmp_name'], $target_dir . $file_name_ba_peng)) {
            // Hapus file lama jika ada
            if (!empty($data['link_ba_penggantian']) && file_exists($target_dir . $data['link_ba_penggantian'])) {
                @unlink($target_dir . $data['link_ba_penggantian']);
            }
            $link_ba_penggantian = mysqli_real_escape_string($conn, $file_name_ba_peng);
        }
    }

    // --- PROSES TAMBAH FOTO NAMEPLATE BARU ---
    $existing_np = json_decode($data['foto_nameplate'] ?? '[]', true);
    if (!is_array($existing_np)) $existing_np = [];
    
    if (!empty($_FILES['new_foto_nameplate']['name'][0])) {
        foreach ($_FILES['new_foto_nameplate']['name'] as $key => $val) {
            if ($_FILES['new_foto_nameplate']['error'][$key] === 0) {
                $ext = pathinfo($_FILES['new_foto_nameplate']['name'][$key], PATHINFO_EXTENSION);
                $filename = "np_" . uniqid() . "." . $ext;
                if (move_uploaded_file($_FILES['new_foto_nameplate']['tmp_name'][$key], $target_dir . $filename)) {
                    $existing_np[] = $filename;
                }
            }
        }
    }
    $final_np_json = mysqli_real_escape_string($conn, json_encode($existing_np));

    // --- PROSES TAMBAH FOTO MATERIAL BARU ---
    $existing_mat = json_decode($data['foto_material'] ?? '[]', true);
    if (!is_array($existing_mat)) $existing_mat = [];

    if (!empty($_FILES['new_foto_material']['name'][0])) {
        foreach ($_FILES['new_foto_material']['name'] as $key => $val) {
            if ($_FILES['new_foto_material']['error'][$key] === 0) {
                $ext = pathinfo($_FILES['new_foto_material']['name'][$key], PATHINFO_EXTENSION);
                $filename = "mat_" . uniqid() . "." . $ext;
                if (move_uploaded_file($_FILES['new_foto_material']['tmp_name'][$key], $target_dir . $filename)) {
                    $existing_mat[] = $filename;
                }
            }
        }
    }
    $final_mat_json = mysqli_real_escape_string($conn, json_encode($existing_mat));

    $query = "UPDATE ex_bongkaran SET 
              unit='$unit', nama_material='$nama_material', mtu='$mtu', tegangan='$tegangan', 
              merk_tipe='$merk_tipe', no_seri='$no_seri', gardu_induk='$gardu_induk', lokasi_asal_eks_bongkaran='$lokasi_asal_eks_bongkaran', 
              no_kontrak_penggantian='$no_kontrak_penggantian', judul_kontrak_penggantian='$judul_kontrak_penggantian', 
              jumlah=$jumlah, satuan='$satuan', nilai_buku=$nilai_buku, berat='$berat', lokasi_penyimpanan='$lokasi_penyimpanan', 
              kondisi='$kondisi', justifikasi_kondisi='$justifikasi_kondisi', kelengkapan_aksesoris='$kelengkapan_aksesoris', 
              ket_kelengkapan_aksesoris='$ket_kelengkapan_aksesoris', keterangan_ex_bongkaran='$keterangan_ex_bongkaran', 
              status='$status', ket_waktu_pembongkaran='$ket_waktu_pembongkaran', no_at='$no_at', nilai_perolehan=$nilai_perolehan, 
              techidentno='$techidentno', upt='$upt', umur_operasi='$umur_operasi', umur_simpan='$umur_simpan', tahun_pembuatan='$tahun_pembuatan', 
              funloct='$funloct', katalog_mara='$katalog_mara', no_aset='$no_aset',
              foto_nameplate='$final_np_json', foto_material='$final_mat_json',
              link_ba_pemindahan='$link_ba_pemindahan', link_ba_pemanfaatan='$link_ba_pemanfaatan', link_hasil_uji='$link_hasil_uji', 
              link_ba_penggantian='$link_ba_penggantian', keterangan='$keterangan', keterangan_tambahan='$keterangan_tambahan', tanggal_update_terakhir='$tanggal_update_terakhir' 
              WHERE id=$id";

    if (mysqli_query($conn, $query)) {
        header("Location: ex_bongkaran.php");
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
    <title>I-CALM | Edit Ex Bongkaran</title>
    
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
        body { background: var(--bg-body); color: var(--text-main); min-height: 100vh; }

        /* SIDEBAR STYLE */
        .sidebar {
            position: fixed; left: 0; top: 0; width: 260px; height: 100%;
            background-color: var(--bg-sidebar); border-right: 1px solid rgba(2, 132, 199, 0.15);
            padding: 35px 20px; z-index: 1050; display: flex; flex-direction: column; overflow-y: auto;
        }
        .sidebar h3 { font-size: 1.25rem; font-weight: 800; color: #1e3a8a; margin-bottom: 35px; padding-left: 6px; display: flex; align-items: center; gap: 10px; }
        .sidebar a, .dropdown-btn { display: flex; align-items: center; justify-content: space-between; color: #1e3a8a; text-decoration: none; padding: 11px 14px; font-size: 0.9rem; font-weight: 700; border: none; background: transparent; width: 100%; cursor: pointer; border-radius: 10px; margin-bottom: 5px; transition: all 0.2s ease-in-out; }
        .sidebar a:hover, .dropdown-btn:hover { color: #025a9c; background: rgba(2, 132, 199, 0.12); transform: translateX(4px); }
        .sidebar .menu-content-wrapper { display: flex; align-items: center; gap: 12px; }
        .sidebar a i, .dropdown-btn i.menu-icon { font-size: 1.05rem; width: 20px; text-align: center; color: #1e40af; }
        .dropdown-chevron { font-size: 0.75rem !important; transition: transform 0.2s ease; color: #1e40af !important; }
        .dropdown-btn.active .dropdown-chevron { transform: rotate(180deg); color: #ffffff !important; }
        .dropdown-btn.active { color: #ffffff !important; background: #0284c7 !important; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); }
        .dropdown-btn.active i.menu-icon { color: #ffffff !important; }
        .dropdown-container { display: none; padding-left: 12px; margin-bottom: 6px; margin-top: 4px; }
        .dropdown-container a { padding: 9px 14px; font-size: 0.85rem; color: #1e40af; font-weight: 600; background: rgba(255, 255, 255, 0.3); border-radius: 8px; margin-bottom: 3px; }
        .dropdown-container a:hover { background: #ffffff; color: #0284c7; }
        .dropdown-container a.active-menu { color: #ffffff !important; background: #0284c7 !important; font-weight: 700; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); border-radius: 10px; }
        .sidebar .logout-button { margin-top: auto; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; }
        .sidebar .logout-button i, .sidebar .logout-button span { color: #b91c1c !important; }

        /* CONTENT STYLE */
        .content { margin-left: 260px; position: relative; width: calc(100% - 260px); }
        .navbar-custom { background: #ffffff; padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999; }
        .main-body-wrapper { padding: 40px; }
        .glass-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 40px; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04); }
        
        .form-label { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; margin-bottom: 6px; }
        .form-control { border-radius: 10px; border: 1px solid #cbd5e1; padding: 11px 16px; font-size: 0.9rem; background-color: #f8fafc; transition: all 0.2s; }
        .form-control:focus { background-color: #fff; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15); }
        
        /* IMAGE & FILE GALLERY PREVIEW STYLE WITH DELETE BUTTON */
        .image-wrapper { position: relative; display: inline-block; margin-right: 12px; margin-bottom: 12px; }
        .img-edit-preview { width: 110px; height: 110px; object-fit: cover; border-radius: 10px; border: 1px solid #cbd5e1; }
        .btn-delete-img { position: absolute; top: -6px; right: -6px; width: 24px; height: 24px; background: #ef4444; color: #fff; border: none; border-radius: 50%; font-size: 11px; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4); transition: transform 0.2s; z-index: 10; }
        .btn-delete-img:hover { transform: scale(1.15); background: #dc2626; }

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
        <a href="/monitoring_barang/kategori/ex_bongkaran/ex_bongkaran.php" class="active-menu">Ex Bongkaran</a>
        <a href="/monitoring_barang/kategori/pre_memory/pre_memory.php">Pre Memory</a>
        <a href="/monitoring_barang/kategori/peminjaman/peminjaman.php">Peminjaman</a>
    </div>
    
    <a href="/monitoring_barang/login/logout.php" class="logout-button"><span class="menu-content-wrapper"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></span></a>
</div>

<div class="content">
    <nav class="navbar navbar-custom">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center">
                <i class="fa-solid fa-pen-to-square text-primary me-2"></i> KENDALI LOGISTIK 
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Kategori: Ex Bongkaran / Mode Pembaruan Data</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="glass-card">
            <h4 class="fw-bold mb-4" style="color: #0f172a;"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Material Ex Bongkaran</h4>
            
            <?php if(isset($error)): ?><div class="alert alert-danger"><?=$error;?></div><?php endif;?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Unit</label><input type="text" name="unit" class="form-control" value="<?=htmlspecialchars($data['unit']??'');?>" required></div>
                    <div class="col-md-4"><label class="form-label">Nama Material</label><input type="text" name="nama_material" class="form-control" value="<?=htmlspecialchars($data['nama_material']??'');?>" required></div>
                    <div class="col-md-4"><label class="form-label">MTU</label><input type="text" name="mtu" class="form-control" value="<?=htmlspecialchars($data['mtu']??'');?>"></div>
                    
                    <div class="col-md-4"><label class="form-label">Tegangan</label><input type="text" name="tegangan" class="form-control" value="<?=htmlspecialchars($data['tegangan']??'');?>"></div>
                    <div class="col-md-4"><label class="form-label">Merk/Tipe</label><input type="text" name="merk_tipe" class="form-control" value="<?=htmlspecialchars($data['merk_tipe']??'');?>"></div>
                    <div class="col-md-4"><label class="form-label">No Seri</label><input type="text" name="no_seri" class="form-control" value="<?=htmlspecialchars($data['no_seri']??'');?>"></div>
                    
                    <div class="col-md-6"><label class="form-label">Gardu Induk</label><input type="text" name="gardu_induk" class="form-control" value="<?=htmlspecialchars($data['gardu_induk']??'');?>"></div>
                    <div class="col-md-6"><label class="form-label">Lokasi Asal Eks Bongkaran</label><input type="text" name="lokasi_asal_eks_bongkaran" class="form-control" value="<?=htmlspecialchars($data['lokasi_asal_eks_bongkaran']??'');?>"></div>
                    
                    <div class="col-md-6"><label class="form-label">No Kontrak Penggantian</label><input type="text" name="no_kontrak_penggantian" class="form-control" value="<?=htmlspecialchars($data['no_kontrak_penggantian']??'');?>"></div>
                    <div class="col-md-6"><label class="form-label">Judul Kontrak Penggantian</label><input type="text" name="judul_kontrak_penggantian" class="form-control" value="<?=htmlspecialchars($data['judul_kontrak_penggantian']??'');?>"></div>
                    
                    <div class="col-md-3"><label class="form-label">Jumlah</label><input type="number" name="jumlah" class="form-control" value="<?=(int)($data['jumlah']??0);?>" required min="0"></div>
                    <div class="col-md-3"><label class="form-label">Satuan</label><input type="text" name="satuan" class="form-control" value="<?=htmlspecialchars($data['satuan']??'');?>" required></div>
                    <div class="col-md-3"><label class="form-label">Nilai Buku (Rp)</label><input type="number" name="nilai_buku" step="any" class="form-control" value="<?=(float)($data['nilai_buku']??0);?>"></div>
                    <div class="col-md-3"><label class="form-label">Berat (Kg)</label><input type="text" name="berat" class="form-control" value="<?=htmlspecialchars($data['berat']??'');?>"></div>
                    
                    <div class="col-md-4"><label class="form-label">Lokasi Penempatan Material</label><input type="text" name="lokasi_penyimpanan" class="form-control" value="<?=htmlspecialchars($data['lokasi_penyimpanan']??'');?>"></div>
                    <div class="col-md-4"><label class="form-label">Kondisi</label><input type="text" name="kondisi" class="form-control" value="<?=htmlspecialchars($data['kondisi']??'');?>"></div>
                    <div class="col-md-4"><label class="form-label">Status</label><input type="text" name="status" class="form-control" value="<?=htmlspecialchars($data['status']??'');?>"></div>
                    
                    <div class="col-md-6"><label class="form-label">Justifikasi Kondisi</label><textarea name="justifikasi_kondisi" class="form-control" rows="2"><?=htmlspecialchars($data['justifikasi_kondisi']??'');?></textarea></div>
                    <div class="col-md-6"><label class="form-label">Kelengkapan Aksesoris</label><textarea name="kelengkapan_aksesoris" class="form-control" rows="2"><?=htmlspecialchars($data['kelengkapan_aksesoris']??'');?></textarea></div>
                    <div class="col-md-6"><label class="form-label">Ket Kelengkapan Aksesoris</label><textarea name="ket_kelengkapan_aksesoris" class="form-control" rows="2"><?=htmlspecialchars($data['ket_kelengkapan_aksesoris']??'');?></textarea></div>
                    <div class="col-md-6"><label class="form-label">Keterangan Ex Bongkaran</label><textarea name="keterangan_ex_bongkaran" class="form-control" rows="2"><?=htmlspecialchars($data['keterangan_ex_bongkaran']??'');?></textarea></div>
                    
                    <div class="col-md-4"><label class="form-label">Ket Waktu Pembongkaran</label><input type="text" name="ket_waktu_pembongkaran" class="form-control" value="<?=htmlspecialchars($data['ket_waktu_pembongkaran']??'');?>"></div>
                    <div class="col-md-4"><label class="form-label">No AT</label><input type="text" name="no_at" class="form-control" value="<?=htmlspecialchars($data['no_at']??'');?>"></div>
                    <div class="col-md-4"><label class="form-label">Nilai Perolehan (Rp)</label><input type="number" name="nilai_perolehan" step="any" class="form-control" value="<?=(float)($data['nilai_perolehan']??0);?>"></div>
                    
                    <div class="col-md-4"><label class="form-label">Techidentno</label><input type="text" name="techidentno" class="form-control" value="<?=htmlspecialchars($data['techidentno']??'');?>"></div>
                    <div class="col-md-4"><label class="form-label">UPT</label><input type="text" name="upt" class="form-control" value="<?=htmlspecialchars($data['upt']??'');?>"></div>
                    <div class="col-md-4"><label class="form-label">Tahun Pembuatan</label><input type="text" name="tahun_pembuatan" class="form-control" value="<?=htmlspecialchars($data['tahun_pembuatan']??'');?>"></div>
                    <div class="col-md-4"><label class="form-label">Umur Operasi</label><input type="text" name="umur_operasi" class="form-control" value="<?=htmlspecialchars($data['umur_operasi']??'');?>"></div>
                    <div class="col-md-4"><label class="form-label">Umur Simpan</label><input type="text" name="umur_simpan" class="form-control" value="<?=htmlspecialchars($data['umur_simpan']??'');?>"></div>
                    <div class="col-md-4"><label class="form-label">Funloct</label><input type="text" name="funloct" class="form-control" value="<?=htmlspecialchars($data['funloct']??'');?>"></div>
                    
                    <div class="col-md-6"><label class="form-label">Katalog Mara</label><input type="text" name="katalog_mara" class="form-control" value="<?=htmlspecialchars($data['katalog_mara']??'');?>"></div>
                    <div class="col-md-6"><label class="form-label">No Aset</label><input type="text" name="no_aset" class="form-control" value="<?=htmlspecialchars($data['no_aset']??'');?>"></div>
                    
                    <!-- KELOLA FOTO NAMEPLATE + FITUR TAMBAH FOTO MULTIPLE -->
                    <div class="col-md-6">
                        <label class="form-label">Kelola Foto Nameplate</label>
                        <div class="p-3 border rounded bg-light mb-2 d-flex flex-wrap">
                            <?php 
                            $np_images = json_decode($data['foto_nameplate'] ?? '[]', true);
                            if (!empty($np_images) && is_array($np_images)): 
                                foreach ($np_images as $img):
                                    if (file_exists("upload/" . $img)):
                            ?>
                                        <div class="image-wrapper" id="div-<?=md5($img);?>">
                                            <img src="upload/<?=$img;?>" class="img-edit-preview">
                                            <button type="button" class="btn-delete-img" onclick="deleteGalleryImage('<?=$img;?>', 'foto_nameplate', '<?=md5($img);?>')" title="Hapus foto ini">×</button>
                                        </div>
                            <?php 
                                    endif;
                                endforeach;
                            else:
                                echo '<span class="text-muted small">Tidak ada foto tersimpan</span>';
                            endif; 
                            ?>
                        </div>
                        <input type="file" name="new_foto_nameplate[]" class="form-control" multiple accept="image/*">
                        <div class="form-text text-muted" style="font-size:0.75rem;">Bisa memilih lebih dari 1 file foto sekaligus.</div>
                    </div>
                    
                    <!-- KELOLA FOTO MATERIAL + FITUR TAMBAH FOTO MULTIPLE -->
                    <div class="col-md-6">
                        <label class="form-label">Kelola Foto Material</label>
                        <div class="p-3 border rounded bg-light mb-2 d-flex flex-wrap">
                            <?php 
                            $mat_images = json_decode($data['foto_material'] ?? '[]', true);
                            if (!empty($mat_images) && is_array($mat_images)): 
                                foreach ($mat_images as $img):
                                    if (file_exists("upload/" . $img)):
                            ?>
                                        <div class="image-wrapper" id="div-<?=md5($img);?>">
                                            <img src="upload/<?=$img;?>" class="img-edit-preview">
                                            <button type="button" class="btn-delete-img" onclick="deleteGalleryImage('<?=$img;?>', 'foto_material', '<?=md5($img);?>')" title="Hapus foto ini">×</button>
                                        </div>
                            <?php 
                                    endif;
                                endforeach;
                            else:
                                echo '<span class="text-muted small">Tidak ada foto tersimpan</span>';
                            endif; 
                            ?>
                        </div>
                        <input type="file" name="new_foto_material[]" class="form-control" multiple accept="image/*">
                        <div class="form-text text-muted" style="font-size:0.75rem;">Bisa memilih lebih dari 1 file foto sekaligus.</div>
                    </div>

                    <!-- KELOLA DOKUMEN BA (PEMINDAHAN, PEMANFAATAN, PENGGANTIAN) -->
                    <!-- BA Pemindahan (Teks) -->
                    <div class="col-md-6">
                        <label class="form-label">Link BA Pemindahan</label>
                        <div class="input-group">
                            <input type="text" name="link_ba_pemindahan" id="input-ba-pemindahan" class="form-control" value="<?=htmlspecialchars($data['link_ba_pemindahan']??'');?>" placeholder="Tautan cloud/drive Berita Acara Pemindahan">
                            <?php if(!empty($data['link_ba_pemindahan'])): ?>
                                <button type="button" class="btn btn-danger" onclick="deleteGalleryImage('<?=htmlspecialchars($data['link_ba_pemindahan']);?>', 'link_ba_pemindahan', 'ba-pemindahan-wrapper')">×</button>
                            <?php endif; ?>
                        </div>
                        <div class="form-text text-muted small" id="ba-pemindahan-wrapper-text">
                            <?php if(!empty($data['link_ba_pemindahan'])): ?>
                                File aktif: <a href="<?=htmlspecialchars($data['link_ba_pemindahan']);?>" target="_blank"><?=htmlspecialchars($data['link_ba_pemindahan']);?></a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- BA Pemanfaatan (File Upload) -->
                    <div class="col-md-6">
                        <label class="form-label">Link BA Pemanfaatan</label>
                        <div class="input-group">
                            <input type="file" name="ba_pemanfaatan_file" class="form-control">
                            <?php if(!empty($data['link_ba_pemanfaatan'])): ?>
                                <button type="button" class="btn btn-danger" id="btn-del-ba-pemanfaatan" onclick="deleteGalleryImage('<?=htmlspecialchars($data['link_ba_pemanfaatan']);?>', 'link_ba_pemanfaatan', 'ba-pemanfaatan-wrapper')">×</button>
                            <?php endif; ?>
                        </div>
                        <div class="form-text text-muted small" id="ba-pemanfaatan-wrapper-text">
                            <?php if(!empty($data['link_ba_pemanfaatan'])): ?>
                                File aktif: <a href="upload/<?=htmlspecialchars($data['link_ba_pemanfaatan']);?>" target="_blank"><?=htmlspecialchars($data['link_ba_pemanfaatan']);?></a>
                            <?php else: ?>
                                Unggah dokumen Berita Acara Pemanfaatan komponen di lapangan.
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Link Hasil Uji (Teks) -->
                    <div class="col-md-6">
                        <label class="form-label">Link Hasil Uji</label>
                        <input type="text" name="link_hasil_uji" class="form-control" value="<?=htmlspecialchars($data['link_hasil_uji']??'');?>">
                    </div>

                    <!-- BA Penggantian (File Upload) -->
                    <div class="col-md-6">
                        <label class="form-label">Link BA Penggantian</label>
                        <div class="input-group">
                            <input type="file" name="ba_penggantian_file" class="form-control">
                            <?php if(!empty($data['link_ba_penggantian'])): ?>
                                <button type="button" class="btn btn-danger" id="btn-del-ba-penggantian" onclick="deleteGalleryImage('<?=htmlspecialchars($data['link_ba_penggantian']);?>', 'link_ba_penggantian', 'ba-penggantian-wrapper')">×</button>
                            <?php endif; ?>
                        </div>
                        <div class="form-text text-muted small" id="ba-penggantian-wrapper-text">
                            <?php if(!empty($data['link_ba_penggantian'])): ?>
                                File aktif: <a href="upload/<?=htmlspecialchars($data['link_ba_penggantian']);?>" target="_blank"><?=htmlspecialchars($data['link_ba_penggantian']);?></a>
                            <?php else: ?>
                                Unggah dokumen Berita Acara Penggantian komponen di lapangan.
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6"><label class="form-label">Keterangan</label><textarea name="keterangan" class="form-control" rows="2"><?=htmlspecialchars($data['keterangan']??'');?></textarea></div>
                    <div class="col-md-6"><label class="form-label">Keterangan Tambahan</label><textarea name="keterangan_tambahan" class="form-control" rows="2"><?=htmlspecialchars($data['keterangan_tambahan']??'');?></textarea></div>
                </div>
                
                <div class="mt-4 gap-2 d-flex">
                    <button type="submit" name="update" class="btn btn-warning text-white px-4 fw-semibold" style="border-radius:10px;">Update</button>
                    <a href="ex_bongkaran.php" class="btn btn-light px-4 border" style="border-radius:10px;">Batal</a>
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
            const container = this.nextElementSibling;
            this.classList.toggle('active');
            container.style.display = container.style.display === "block" ? "none" : "block";
        });
    });

    function deleteGalleryImage(imageName, fieldName, elementId) {
        if (confirm("Apakah Anda yakin ingin menghapus data / berkas ini?")) {
            const formData = new FormData();
            formData.append('action', 'delete_image');
            formData.append('image_name', imageName);
            formData.append('field_name', fieldName);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Jika yang dihapus adalah foto nameplate atau foto material
                    if (fieldName === 'foto_nameplate' || fieldName === 'foto_material') {
                        const targetEl = document.getElementById('div-' + elementId);
                        if(targetEl) targetEl.remove();
                    } 
                    // Jika yang dihapus adalah Link BA Pemindahan
                    else if (fieldName === 'link_ba_pemindahan') {
                        document.getElementById('input-ba-pemindahan').value = '';
                        const textEl = document.getElementById('ba-pemindahan-wrapper-text');
                        if (textEl) textEl.innerHTML = '';
                        const btnEl = document.querySelector('[onclick*="link_ba_pemindahan"]');
                        if (btnEl) btnEl.remove();
                    }
                    // Jika yang dihapus adalah Link BA Pemanfaatan
                    else if (fieldName === 'link_ba_pemanfaatan') {
                        const textEl = document.getElementById('ba-pemanfaatan-wrapper-text');
                        if (textEl) textEl.innerHTML = 'Unggah dokumen Berita Acara Pemanfaatan komponen di lapangan.';
                        const btnEl = document.getElementById('btn-del-ba-pemanfaatan');
                        if (btnEl) btnEl.remove();
                    }
                    // Jika yang dihapus adalah Link BA Penggantian
                    else if (fieldName === 'link_ba_penggantian') {
                        const textEl = document.getElementById('ba-penggantian-wrapper-text');
                        if (textEl) textEl.innerHTML = 'Unggah dokumen Berita Acara Penggantian komponen di lapangan.';
                        const btnEl = document.getElementById('btn-del-ba-penggantian');
                        if (btnEl) btnEl.remove();
                    }
                } else {
                    alert('Gagal menghapus berkas dari sistem.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan koneksi.');
            });
        }
    }
</script>
</body>
</html>