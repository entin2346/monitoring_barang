<?php
session_start();

// 1. PROTEKSI LOGIN
if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

// 2. PROSES BACKEND CSV EX BONGKARAN
if (isset($_POST['submit_import'])) {
    if (isset($_FILES['file_csv']) && $_FILES['file_csv']['error'] == 0) {
        
        $filename = $_FILES['file_csv']['tmp_name'];
        
        if (($handle = fopen($filename, "r")) !== FALSE) {
            
            $sukses_insert = 0;
            $gagal_insert = 0;
            $index = 0;
            $error_logs = []; 
            
            // Otomatis mendeteksi pemisah koma (,) atau titik koma (;)
            $first_line = fgets($handle);
            $separator = (strpos($first_line, ';') !== FALSE) ? ';' : ',';
            rewind($handle);
            
            while (($row = fgetcsv($handle, 10000, $separator)) !== FALSE) {
                
                // Lewati baris pertama jika merupakan Header/Judul Kolom CSV
                if ($index == 0) {
                    $index++;
                    continue;
                }
                
                // PEMETAAN KOLOM CSV 
                $unit                       = mysqli_real_escape_string($conn, $row[1] ?? '');  
                $nama_material              = mysqli_real_escape_string($conn, $row[2] ?? '');  
                $mtu                        = mysqli_real_escape_string($conn, $row[3] ?? '');  
                $tegangan                   = mysqli_real_escape_string($conn, $row[4] ?? '');  
                $merk_tipe                  = mysqli_real_escape_string($conn, $row[5] ?? '');  
                $no_seri                    = mysqli_real_escape_string($conn, $row[6] ?? '');  
                $gardu_induk                = mysqli_real_escape_string($conn, $row[7] ?? '');  
                $lokasi_asal_eks_bongkaran  = mysqli_real_escape_string($conn, $row[8] ?? '');  
                $no_kontrak_penggantian     = mysqli_real_escape_string($conn, $row[9] ?? '');  
                $judul_kontrak_penggantian  = mysqli_real_escape_string($conn, $row[10] ?? ''); 
                $jumlah                     = mysqli_real_escape_string($conn, $row[11] ?? '0');  
                $satuan                     = mysqli_real_escape_string($conn, $row[12] ?? ''); 
                $nilai_buku                 = mysqli_real_escape_string($conn, $row[13] ?? '0');  
                $berat                      = mysqli_real_escape_string($conn, $row[14] ?? ''); 
                $lokasi_penyimpanan         = mysqli_real_escape_string($conn, $row[15] ?? ''); 
                $kondisi                    = mysqli_real_escape_string($conn, $row[16] ?? ''); 
                $justifikasi_kondisi        = mysqli_real_escape_string($conn, $row[17] ?? ''); 
                $kelengkapan_aksesoris      = mysqli_real_escape_string($conn, $row[18] ?? ''); 
                $ket_kelengkapan_aksesoris  = mysqli_real_escape_string($conn, $row[19] ?? ''); 
                $keterangan_ex_bongkaran    = mysqli_real_escape_string($conn, $row[20] ?? ''); 
                $status                     = mysqli_real_escape_string($conn, $row[21] ?? ''); 
                $ket_waktu_pembongkaran     = mysqli_real_escape_string($conn, $row[22] ?? ''); 
                $tanggal_update_terakhir    = mysqli_real_escape_string($conn, $row[23] ?? ''); 
                $no_at                      = mysqli_real_escape_string($conn, $row[24] ?? ''); 
                $nilai_perolehan            = mysqli_real_escape_string($conn, $row[25] ?? '0');  
                $techidentno                = mysqli_real_escape_string($conn, $row[26] ?? ''); 
                $upt                        = mysqli_real_escape_string($conn, $row[27] ?? ''); 
                $umur_operasi               = mysqli_real_escape_string($conn, $row[28] ?? ''); 
                $umur_simpan                = mysqli_real_escape_string($conn, $row[29] ?? ''); 
                $tahun_pembuatan            = mysqli_real_escape_string($conn, $row[30] ?? ''); 
                $funloct                    = mysqli_real_escape_string($conn, $row[31] ?? ''); 
                $katalog_mara               = mysqli_real_escape_string($conn, $row[32] ?? ''); 
                $no_aset                    = mysqli_real_escape_string($conn, $row[33] ?? ''); 
                $foto_nameplate             = mysqli_real_escape_string($conn, $row[34] ?? ''); 
                $foto_material              = mysqli_real_escape_string($conn, $row[35] ?? ''); 
                $link_ba_pemindahan         = mysqli_real_escape_string($conn, $row[36] ?? ''); 
                $link_ba_pemanfaatan        = mysqli_real_escape_string($conn, $row[37] ?? ''); 
                $link_hasil_uji             = mysqli_real_escape_string($conn, $row[38] ?? ''); 
                $link_ba_penggantian_mtu    = mysqli_real_escape_string($conn, $row[39] ?? ''); 
                $keterangan                 = mysqli_real_escape_string($conn, $row[40] ?? ''); 
                $keterangan_tambahan        = mysqli_real_escape_string($conn, $row[41] ?? ''); 
                
                $jenis_kategori             = 'ex_bongkaran'; 

                if (empty(trim($nama_material))) {
                    continue;
                }
                
                // Kirim langsung sebagai string aman ke database bertipe VARCHAR
                $query = "INSERT INTO material_gudang 
                          (unit, nama_material, mtu, tegangan, merk_tipe, no_seri, gardu_induk, lokasi_asal_eks_bongkaran, no_kontrak_penggantian, judul_kontrak_penggantian, jumlah, satuan, nilai_buku, berat, lokasi_penyimpanan, kondisi, justifikasi_kondisi, kelengkapan_aksesoris, ket_kelengkapan_aksesoris, keterangan_ex_bongkaran, status, ket_waktu_pembongkaran, tanggal_update_terakhir, no_at, nilai_perolehan, techidentno, upt, umur_operasi, umur_simpan, tahun_pembuatan, funloct, katalog_mara, no_aset, foto_nameplate, foto_material, link_ba_pemindahan, link_ba_pemanfaatan, link_hasil_uji, link_ba_penggantian_mtu, keterangan, keterangan_tambahan, jenis_kategori) 
                          VALUES 
                          ('$unit', '$nama_material', '$mtu', '$tegangan', '$merk_tipe', '$no_seri', '$gardu_induk', '$lokasi_asal_eks_bongkaran', '$no_kontrak_penggantian', '$judul_kontrak_penggantian', '$jumlah', '$satuan', '$nilai_buku', '$berat', '$lokasi_penyimpanan', '$kondisi', '$justifikasi_kondisi', '$kelengkapan_aksesoris', '$ket_kelengkapan_aksesoris', '$keterangan_ex_bongkaran', '$status', '$ket_waktu_pembongkaran', '$tanggal_update_terakhir', '$no_at', '$nilai_perolehan', '$techidentno', '$upt', '$umur_operasi', '$umur_simpan', '$tahun_pembuatan', '$funloct', '$katalog_mara', '$no_aset', '$foto_nameplate', '$foto_material', '$link_ba_pemindahan', '$link_ba_pemanfaatan', '$link_hasil_uji', '$link_ba_penggantian_mtu', '$keterangan', '$keterangan_tambahan', '$jenis_kategori')";
                          
                if (mysqli_query($conn, $query)) {
                    $sukses_insert++;
                } else {
                    $gagal_insert++;
                    $error_logs[] = "Baris ke-" . ($index + 1) . ": " . mysqli_error($conn);
                }
                $index++;
            }
            fclose($handle);
            
            if ($gagal_insert > 0) {
                $detail_error = mysqli_real_escape_string($conn, $error_logs[0]);
                echo "<script>alert('Berhasil mengimport $sukses_insert data CSV Ex Bongkaran! Gagal: $gagal_insert.\\nDetail Error: $detail_error'); window.location='../kategori/ex_bongkaran.php';</script>";
            } else {
                echo "<script>alert('Berhasil mengimport seluruh data ($sukses_insert) CSV Ex Bongkaran!'); window.location='../kategori/ex_bongkaran.php';</script>";
            }
            exit;
        } else {
            echo "<script>alert('Gagal membuka file CSV.');</script>";
        }
    } else {
        echo "<script>alert('Terjadi kesalahan upload file.');</script>";
    }
}

// 3. AMBIL STATISTIK UNTUK INFORMASI HALAMAN
$q_ex = mysqli_query($conn, "SELECT COUNT(*) as total_ex FROM material_gudang WHERE jenis_kategori = 'ex_bongkaran'");
$res_ex = mysqli_fetch_assoc($q_ex);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I-CALM | Import Ex Bongkaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --bg-body: #f4f7fc; --bg-card: #ffffff; --primary: #0284c7; --text-main: #0f172a; --text-muted: #64748b; --border-color: rgba(148, 163, 184, 0.12); --bg-sidebar: #d0e1f9; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: var(--bg-body); color: var(--text-main); min-height: 100vh; overflow-x: hidden; }
        .sidebar { position: fixed; left: 0; top: 0; width: 260px; height: 100%; background-color: var(--bg-sidebar); border-right: 1px solid rgba(2, 132, 199, 0.15); padding: 35px 20px; z-index: 1050; display: flex; flex-direction: column; overflow-y: auto; }
        .sidebar h3 { font-size: 1.25rem; font-weight: 800; color: #1e3a8a; margin-bottom: 35px; padding-left: 6px; display: flex; align-items: center; gap: 10px; }
        .sidebar a, .dropdown-btn { display: flex; align-items: center; justify-content: space-between; color: #1e3a8a; text-decoration: none; padding: 11px 14px; font-size: 0.9rem; font-weight: 700; border: none; background: transparent; width: 100%; cursor: pointer; border-radius: 10px; margin-bottom: 5px; transition: all 0.2s ease-in-out; }
        .sidebar a:hover, .dropdown-btn:hover { color: #025a9c; background: rgba(2, 132, 199, 0.12); transform: translateX(4px); }
        .sidebar .menu-content-wrapper { display: flex; align-items: center; gap: 12px; }
        .sidebar a i, .dropdown-btn i.menu-icon { font-size: 1.05rem; width: 20px; text-align: center; color: #1e40af; }
        .sidebar .dropdown-btn.active { color: #ffffff !important; background: #0284c7 !important; font-weight: 700; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); border-radius: 10px; }
        .sidebar .dropdown-btn.active i { color: #ffffff !important; }
        .dropdown-chevron { font-size: 0.75rem !important; transition: transform 0.2s ease; color: #1e40af !important; }
        .dropdown-container { display: none; padding-left: 12px; margin-bottom: 6px; margin-top: 4px; }
        .dropdown-container a { padding: 9px 14px; font-size: 0.85rem; color: #1e40af; font-weight: 600; background: rgba(255, 255, 255, 0.4); border-radius: 8px; margin-bottom: 5px; }
        .dropdown-container a:hover { background: #ffffff; color: #0284c7; }
        .dropdown-container .active-sub { background: #ffffff !important; color: #0284c7 !important; font-weight: 700; box-shadow: 0 2px 6px rgba(0,0,0,0.03); }
        .sidebar .logout-button { margin-top: auto; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; }
        .sidebar .logout-button i, .sidebar .logout-button span { color: #b91c1c !important; }
        .content { margin-left: 260px; position: relative; }
        .navbar-custom { background: #ffffff; padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999; }
        .navbar-custom .navbar-brand { color: var(--text-main); font-weight: 800; font-size: 1.3rem; }
        .main-body-wrapper { padding: 40px; }
        .cyber-card { background: #ffffff; border: 1px solid var(--border-color); border-radius: 16px; padding: 30px; }
        .input-cyber-group { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 8px; }
    </style>
</head>
<body>
<div class="sidebar">
    <h3><i class="fa-solid fa-bolt text-primary"></i> I-CALM Panel</h3>
    <a href="../dashboard/index.php"><span class="menu-content-wrapper"><i class="fa-solid fa-chart-pie"></i><span>Dashboard</span></span></a>
    <button class="dropdown-btn">
        <span class="menu-content-wrapper"><i class="fa-solid fa-layer-group menu-icon"></i><span>Monitoring</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../material/index.php">Material Gudang</a>
        <a href="../ba/index.php">Database BA</a>
    </div>
    <button class="dropdown-btn">
        <span class="menu-content-wrapper"><i class="fa-solid fa-tags menu-icon"></i><span>Kategori</span></span>
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
    <button class="dropdown-btn active">
        <span class="menu-content-wrapper"><i class="fa-solid fa-file-excel menu-icon"></i><span>Import</span></span>
        <i class="fa-solid fa-chevron-up dropdown-chevron"></i>
    </button>
     <div class="dropdown-container" style="display: block;">
        <a href="../import/material.php">Import Material</a>
        <a href="../import/ba.php">Import BA</a>
        <a href="../import/form_stok.php">Import Stok</a>
        <a href="../import/form_non_stok.php">Import Non Stok</a>
        <a href="../import/form_non_po.php" class="active-menu">Import Non PO</a>
        <a href="../import/form_ex_bongkaran.php">Import Ex Bongkaran</a>
        <a href="../import/form_pre_memory.php">Import Pre Memory</a>
        <a href="../import/form_peminjaman.php">Import Peminjaman</a>
        <a href="../import/form_pemakaian.php">Import Pemakaian</a>
    </div>
    <a href="../login/logout.php" class="logout-button"><span class="menu-content-wrapper"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></span></a>
</div>

<div class="content">
    <nav class="navbar navbar-custom">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center">
                <i class="fa-solid fa-file-csv text-success me-2"></i> DATA IMPORT SYSTEM SINGLE-FILE
            </span>
        </div>
    </nav>
    <div class="main-body-wrapper">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="cyber-card">
                    <h5 class="fw-bold mb-4"><i class="fa-solid fa-upload text-primary me-2"></i> Import CSV - Ex Bongkaran Final</h5>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted mb-2">Pilih File CSV Master Ex Bongkaran</label>
                            <div class="input-cyber-group">
                                <input type="file" name="file_csv" class="form-control border-0 bg-transparent" accept=".csv" required>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="../kategori/ex_bongkaran.php" class="btn btn-light w-100 fw-bold py-2" style="border-radius: 12px; border: 1px solid #cbd5e1;">Kembali</a>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" name="submit_import" class="btn btn-success text-white w-100 fw-bold py-2" style="border-radius: 12px;">Upload & Proses</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.dropdown-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const container = this.nextElementSibling;
            container.style.display = window.getComputedStyle(container).display === "block" ? "none" : "block";
        });
    });
</script>
</body>
</html>