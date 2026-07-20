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

if (isset($_POST['submit'])) {
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

    // Folder tujuan upload berkas
    $target_dir = "upload/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Proses Upload BA Pemanfaatan (Single File)
    $link_ba_pemanfaatan = "";
    if (!empty($_FILES['ba_pemanfaatan_file']['name'])) {
        $file_name_ba_pem = time() . '_ba_pem_' . basename($_FILES['ba_pemanfaatan_file']['name']);
        if (move_uploaded_file($_FILES['ba_pemanfaatan_file']['tmp_name'], $target_dir . $file_name_ba_pem)) {
            $link_ba_pemanfaatan = mysqli_real_escape_string($conn, $target_dir . $file_name_ba_pem);
        }
    }

    // Proses Upload BA Penggantian (Single File)
    $link_ba_penggantian_mtu = "";
    if (!empty($_FILES['ba_penggantian_file']['name'])) {
        $file_name_ba_peng = time() . '_ba_peng_' . basename($_FILES['ba_penggantian_file']['name']);
        if (move_uploaded_file($_FILES['ba_penggantian_file']['tmp_name'], $target_dir . $file_name_ba_peng)) {
            $link_ba_penggantian_mtu = mysqli_real_escape_string($conn, $target_dir . $file_name_ba_peng);
        }
    }

    // Proses multi-upload Foto Nameplate
    $foto_nameplate = "";
    if (!empty($_FILES['foto_nameplate_files']['name'][0])) {
        $file_name = time() . '_np_' . basename($_FILES['foto_nameplate_files']['name'][0]);
        if (move_uploaded_file($_FILES['foto_nameplate_files']['tmp_name'][0], $target_dir . $file_name)) {
            $foto_nameplate = mysqli_real_escape_string($conn, $target_dir . $file_name);
        }
    }

    // Proses multi-upload Foto Material
    $foto_material = "";
    if (!empty($_FILES['foto_material_files']['name'][0])) {
        $file_name = time() . '_mat_' . basename($_FILES['foto_material_files']['name'][0]);
        if (move_uploaded_file($_FILES['foto_material_files']['tmp_name'][0], $target_dir . $file_name)) {
            $foto_material = mysqli_real_escape_string($conn, $target_dir . $file_name);
        }
    }

    // Query INSERT disesuaikan kolom 'link_ba_penggantian_mtu'
    $query = "INSERT INTO ex_bongkaran (
                unit, nama_material, mtu, tegangan, merk_tipe, no_seri, gardu_induk, 
                lokasi_asal_eks_bongkaran, no_kontrak_penggantian, judul_kontrak_penggantian, 
                jumlah, satuan, nilai_buku, berat, lokasi_penyimpanan, kondisi, 
                justifikasi_kondisi, kelengkapan_aksesoris, ket_kelengkapan_aksesoris, 
                keterangan_ex_bongkaran, status, ket_waktu_pembongkaran, no_at, 
                nilai_perolehan, techidentno, upt, umur_operasi, umur_simpan, 
                tahun_pembuatan, funloct, katalog_mara, no_aset, foto_nameplate, 
                foto_material, link_ba_pemindahan, link_ba_pemanfaatan, link_hasil_uji, 
                link_ba_penggantian_mtu, keterangan, keterangan_tambahan, tanggal_update_terakhir
              ) VALUES (
                '$unit', '$nama_material', '$mtu', '$tegangan', '$merk_tipe', '$no_seri', '$gardu_induk', 
                '$lokasi_asal_eks_bongkaran', '$no_kontrak_penggantian', '$judul_kontrak_penggantian', 
                $jumlah, '$satuan', $nilai_buku, '$berat', '$lokasi_penyimpanan', '$kondisi', 
                '$justifikasi_kondisi', '$kelengkapan_aksesoris', '$ket_kelengkapan_aksesoris', 
                '$keterangan_ex_bongkaran', '$status', '$ket_waktu_pembongkaran', '$no_at', 
                $nilai_perolehan, '$techidentno', '$upt', '$umur_operasi', '$umur_simpan', 
                '$tahun_pembuatan', '$funloct', '$katalog_mara', '$no_aset', '$foto_nameplate', 
                '$foto_material', '$link_ba_pemindahan', '$link_ba_pemanfaatan', '$link_hasil_uji', 
                '$link_ba_penggantian_mtu', '$keterangan', '$keterangan_tambahan', '$tanggal_update_terakhir'
              )";

    if (mysqli_query($conn, $query)) {
        header("Location: ex_bongkaran.php");
        exit;
    } else {
        $error = "Gagal menambah data: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Tambah Ex Bongkaran</title>
    
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
        .dropdown-container a.active-menu {
            color: #ffffff !important; background: #0284c7 !important; font-weight: 700;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); border-radius: 10px;
        }
        
        .sidebar .logout-button { margin-top: auto; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; }
        .sidebar .logout-button i, .sidebar .logout-button span { color: #b91c1c !important; }

        /* CONTENT & FORM STYLE */
        .content { margin-left: 260px; position: relative; width: calc(100% - 260px); }
        .navbar-custom { background: #ffffff; padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999; }
        .main-body-wrapper { padding: 40px; }
        
        .glass-form-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 40px; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04); }
        .form-label { font-weight: 700; color: #1e293b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; }
        .form-control, .form-select { background: #f8fafc !important; border: 1px solid #cbd5e1 !important; border-radius: 10px; padding: 12px 16px; font-size: 0.9rem; color: var(--text-main); transition: all 0.2s ease; }
        .form-control:focus, .form-select:focus { border-color: var(--primary) !important; box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.12) !important; background: #ffffff !important; }

        .btn-submit-custom { background: #059669; border: none; border-radius: 8px; padding: 10px 24px; font-size: 0.9rem; font-weight: 700; color: white; transition: all 0.2s; }
        .btn-submit-custom:hover { background: #047857; }
        .btn-back-custom { border-radius: 8px; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 10px 24px; font-size: 0.9rem; font-weight: 600; color: #475569; text-decoration: none; transition: all 0.2s; }
        .btn-back-custom:hover { background: #e2e8f0; color: #1e293b; }

        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; border-right: none; border-bottom: 1px solid rgba(2, 132, 199, 0.15); padding: 20px; }
            .content { margin-left: 0; width: 100%; }
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
        <a href="/monitoring_barang/kategori/non_po/non_po.php">Non PO</a>
        <a href="/monitoring_barang/kategori/ex_bongkaran/ex_bongkaran.php" class="active-menu">Ex Bongkaran</a>
        <a href="/monitoring_barang/kategori/pre_memory/pre_memory.php">Pre Memory</a>
        <a href="/monitoring_barang/kategori/peminjaman/peminjaman.php">Peminjaman</a>
        <a href="/monitoring_barang/kategori/pemakaian/pemakaian.php">Pemakaian</a>
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
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Kategori: Ex Bongkaran / Registrasi Baru</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="glass-form-card">
            <h4 class="fw-bold mb-4" style="color: #0f172a; letter-spacing: -0.02em;"><i class="fa-regular fa-square-plus text-success me-2"></i>Form Entry Data Ex Bongkaran</h4>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger mb-4"><?= $error; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row g-4">
                    
                    <!-- Spesifikasi Utama -->
                    <div class="col-md-4">
                        <label class="form-label">Unit</label>
                        <input type="text" name="unit" class="form-control" placeholder="Masukkan unit kerja" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Nama Material</label>
                        <input type="text" name="nama_material" class="form-control" placeholder="Nama lengkap item material" required>
                    </div>

                    <!-- Keterangan Teknis -->
                    <div class="col-md-3">
                        <label class="form-label">MTU</label>
                        <input type="text" name="mtu" class="form-control" placeholder="MTU">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tegangan</label>
                        <input type="text" name="tegangan" class="form-control" placeholder="Contoh: 150 kV">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Merk / Tipe</label>
                        <input type="text" name="merk_tipe" class="form-control" placeholder="Merk & tipe komponen">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">No Seri</label>
                        <input type="text" name="no_seri" class="form-control" placeholder="Nomor seri pabrikan">
                    </div>

                    <!-- Data Wilayah/Gardu -->
                    <div class="col-md-6">
                        <label class="form-label">Gardu Induk</label>
                        <input type="text" name="gardu_induk" class="form-control" placeholder="Nama Gardu Induk">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Lokasi Asal Eks Bongkaran (Gardu Induk)</label>
                        <input type="text" name="lokasi_asal_eks_bongkaran" class="form-control" placeholder="Detail lokasi asal">
                    </div>

                    <!-- Kontrak Penggantian -->
                    <div class="col-md-6">
                        <label class="form-label">No Kontrak Penggantian</label>
                        <input type="text" name="no_kontrak_penggantian" class="form-control" placeholder="Nomor kontrak">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Judul Kontrak Penggantian</label>
                        <input type="text" name="judul_kontrak_penggantian" class="form-control" placeholder="Judul proyek/pekerjaan kontrak">
                    </div>

                    <!-- Kuantitas & Akuntansi -->
                    <div class="col-md-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="jumlah" class="form-control" placeholder="0" min="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="satuan" class="form-control" placeholder="Contoh: Unit, Pcs" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nilai Buku (Rp)</label>
                        <input type="number" name="nilai_buku" step="any" class="form-control" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Berat (Kg)</label>
                        <input type="text" name="berat" class="form-control" placeholder="Estimasi berat">
                    </div>

                    <!-- Lokasi & Kondisi -->
                    <div class="col-md-4">
                        <label class="form-label">Lokasi Penempatan Material</label>
                        <input type="text" name="lokasi_penyimpanan" class="form-control" placeholder="Lokasi gudang/posisi simpan">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kondisi</label>
                        <input type="text" name="kondisi" class="form-control" placeholder="Contoh: Rusak / Baik">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <input type="text" name="status" class="form-control" placeholder="Status penanganan saat ini">
                    </div>

                    <!-- Detail & Analisis Kondisi -->
                    <div class="col-md-6">
                        <label class="form-label">Justifikasi Kondisi</label>
                        <textarea name="justifikasi_kondisi" class="form-control" rows="2" placeholder="Alasan atau uraian kondisi fisik teknis"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kelengkapan Aksesoris</label>
                        <textarea name="kelengkapan_aksesoris" class="form-control" rows="2" placeholder="Daftar kelengkapan aksesoris pendukung"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Keterangan Kelengkapan Aksesoris</label>
                        <textarea name="ket_kelengkapan_aksesoris" class="form-control" rows="2" placeholder="Catatan tambahan kelengkapan aksesoris"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Keterangan Ex Bongkaran (Penggantian/Uprating)</label>
                        <textarea name="keterangan_ex_bongkaran" class="form-control" rows="2" placeholder="Alasan dibongkar / detail penambahan daya"></textarea>
                    </div>

                    <!-- Waktu & Inventarisasi Aset -->
                    <div class="col-md-4">
                        <label class="form-label">Keterangan Waktu Pembongkaran</label>
                        <input type="text" name="ket_waktu_pembongkaran" class="form-control" placeholder="Contoh: Q1 2026 atau Triwulan II">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">No AT</label>
                        <input type="text" name="no_at" class="form-control" placeholder="Nomor AT">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nilai Perolehan (Rp)</label>
                        <input type="number" name="nilai_perolehan" step="any" class="form-control" value="0">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Techidentno</label>
                        <input type="text" name="techidentno" class="form-control" placeholder="Technical Identification Number">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">UPT</label>
                        <input type="text" name="upt" class="form-control" placeholder="Nama UPT terkait">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tahun Pembuatan</label>
                        <input type="text" name="tahun_pembuatan" class="form-control" placeholder="Tahun manufaktur pabrik">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Umur Operasi</label>
                        <input type="text" name="umur_operasi" class="form-control" placeholder="Durasi masa guna aktif">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Umur Simpan</label>
                        <input type="text" name="umur_simpan" class="form-control" placeholder="Durasi mengendap di gudang">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Funloct</label>
                        <input type="text" name="funloct" class="form-control" placeholder="Functional Location">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Katalog Mara</label>
                        <input type="text" name="katalog_mara" class="form-control" placeholder="Kode penomoran sistem MARA">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">No Aset</label>
                        <input type="text" name="no_aset" class="form-control" placeholder="Nomor kode inventaris aset">
                    </div>

                    <!-- Dokumen Pendukung & Tautan Dokumen Eksternal -->
                    <div class="col-md-6">
                        <label class="form-label">Link BA Pemindahan</label>
                        <input type="text" name="link_ba_pemindahan" class="form-control" placeholder="Tautan cloud/drive Berita Acara Pemindahan">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link BA Pemanfaatan</label>
                        <input type="file" name="ba_pemanfaatan_file" class="form-control">
                        <div class="form-text text-muted small">Unggah dokumen Berita Acara Pemanfaatan komponen di lapangan.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link Hasil Uji</label>
                        <input type="text" name="link_hasil_uji" class="form-control" placeholder="Tautan lembar sertifikasi hasil pengujian">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link BA Penggantian</label>
                        <input type="file" name="ba_penggantian_file" class="form-control">
                        <div class="form-text text-muted small">Unggah dokumen Berita Acara Penggantian komponen di lapangan.</div>
                    </div>

                    <!-- Upload Foto -->
                    <div class="col-md-6">
                        <label class="form-label">Upload Foto Nameplate</label>
                        <input type="file" name="foto_nameplate_files[]" class="form-control">
                        <div class="form-text text-muted small">Pilih file foto nameplate.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Upload Foto Material</label>
                        <input type="file" name="foto_material_files[]" class="form-control">
                        <div class="form-text text-muted small">Pilih file foto material.</div>
                    </div>

                    <!-- Catatan Tambahan -->
                    <div class="col-md-6">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan logistik"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Keterangan Tambahan</label>
                        <textarea name="keterangan_tambahan" class="form-control" rows="2" placeholder="Catatan ekstra lain jika diperlukan"></textarea>
                    </div>

                </div>

                <div class="mt-4 pt-2 d-flex gap-2">
                    <button type="submit" name="submit" class="btn-submit-custom">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Data Ex Bongkaran
                    </button>
                    <a href="ex_bongkaran.php" class="btn-back-custom">Kembali</a>
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