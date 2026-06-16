<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

if(isset($_POST['simpan'])){

    $jenis_berita_acara = mysqli_real_escape_string($conn, $_POST['jenis_berita_acara']);
    $tanggal            = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $nama_barang        = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $merk_jenis         = mysqli_real_escape_string($conn, $_POST['merk_jenis']);
    $jenis_barang       = mysqli_real_escape_string($conn, $_POST['jenis_barang']);
    $sumber_barang      = mysqli_real_escape_string($conn, $_POST['sumber_barang']);
    $satuan             = mysqli_real_escape_string($conn, $_POST['satuan']);
    $jumlah             = (int)$_POST['jumlah'];
    $no_seri            = mysqli_real_escape_string($conn, $_POST['no_seri']);
    $asal_barang_vendor = mysqli_real_escape_string($conn, $_POST['asal_barang_vendor']);
    $kategori_material  = mysqli_real_escape_string($conn, $_POST['kategori_material']);
    $tujuan             = mysqli_real_escape_string($conn, $_POST['tujuan']);
    $kondisi_material   = mysqli_real_escape_string($conn, $_POST['kondisi_material']);
    $keterangan         = mysqli_real_escape_string($conn, $_POST['keterangan']);

    mysqli_query($conn, "
        INSERT INTO database_ba
        (
            jenis_berita_acara,
            tanggal,
            nama_barang,
            merk_jenis,
            jenis_barang,
            sumber_barang,
            satuan,
            jumlah,
            no_seri,
            asal_barang_vendor,
            kategori_material,
            tujuan,
            kondisi_material,
            keterangan
        )
        VALUES
        (
            '$jenis_berita_acara',
            '$tanggal',
            '$nama_barang',
            '$merk_jenis',
            '$jenis_barang',
            '$sumber_barang',
            '$satuan',
            '$jumlah',
            '$no_seri',
            '$asal_barang_vendor',
            '$kategori_material',
            '$tujuan',
            '$kondisi_material',
            '$keterangan'
        )
    ");

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Data BA | I-CALM</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-base: #e6eef8;            
            --bg-card: rgba(255, 255, 255, 0.7); 
            --primary-brand: #0284c7;       
            --text-main: #0f172a;           
            --text-muted: #475569;          
            --border-glass: rgba(255, 255, 255, 0.8);
        }

        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body { 
            background: radial-gradient(circle at top right, #dbeafe 0%, var(--bg-base) 50%, #eff6ff 100%);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            position: fixed; left: 0; top: 0; width: 260px; height: 100%;
            background: linear-gradient(135deg, rgba(11, 27, 60, 0.98) 0%, rgba(7, 43, 102, 0.96) 60%, rgba(2, 110, 168, 0.95) 100%);
            backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px);
            border-right: 1px solid rgba(255, 255, 255, 0.08); padding-top: 28px; z-index: 1000;
            box-shadow: 10px 0 40px rgba(7, 43, 102, 0.15); 
        }
        
        .sidebar h3 { 
            font-size: 1.35rem; font-weight: 800; padding: 0 24px; margin-bottom: 35px; letter-spacing: -0.5px; color: #ffffff;
            display: flex; align-items: center; gap: 10px;
        }
        .sidebar h3 i { color: #38bdf8 !important; text-shadow: 0 0 15px rgba(56, 189, 248, 0.7); }
        
        .sidebar a, .dropdown-btn { 
            display: flex; align-items: center; justify-content: space-between; color: rgba(255, 255, 255, 0.65); 
            text-decoration: none; padding: 13px 24px; font-size: 0.9rem; font-weight: 600; border: none; background: none; width: 100%; transition: all 0.25s; cursor: pointer;
        }
        .sidebar a:hover, .dropdown-btn:hover { background: rgba(255, 255, 255, 0.06); color: #ffffff; }

        .sidebar .active-menu {
            color: #ffffff !important; 
            background: linear-gradient(90deg, rgba(56, 189, 248, 0.15) 0%, rgba(56, 189, 248, 0.02) 100%) !important; 
            border-left: 4px solid #38bdf8; padding-left: 20px;
        }
        .sidebar .active-menu i { color: #38bdf8 !important; }
        .sidebar a i, .dropdown-btn i { font-size: 1.05rem; width: 22px; text-align: center; color: rgba(255, 255, 255, 0.5); margin-right: 10px;}
        
        .sidebar .menu-text { flex-grow: 1; }
        .dropdown-chevron { font-size: 0.75rem !important; transition: transform 0.2s ease; color: rgba(255, 255, 255, 0.4) !important; }
        .dropdown-btn.active .dropdown-chevron { transform: rotate(180deg); color: #38bdf8 !important; }

        .dropdown-container { display: none; background: rgba(0, 0, 0, 0.12); padding: 4px 0; }
        .dropdown-container a { padding: 10px 24px 10px 56px; font-size: 0.85rem; font-weight: 500; color: rgba(255, 255, 255, 0.55); }
        .dropdown-container a:hover { color: #38bdf8; background: transparent; }

        .sidebar .logout-button {
            margin-top: 40px; background: rgba(239, 68, 68, 0.08); border-radius: 12px; width: calc(100% - 32px); margin-left: 16px; padding: 12px 16px;
        }
        .sidebar .logout-button:hover { background: rgba(239, 68, 68, 0.2) !important; }
        .sidebar .logout-button i, .sidebar .logout-button .menu-text { color: #fca5a5 !important; }

        /* Content Layout */
        .content { margin-left: 260px; background: transparent; }
        
        .navbar-custom { 
            background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            padding: 18px 32px; border-bottom: 1px solid var(--border-glass); position: sticky; top: 0; z-index: 999;
        }

        .main-body-wrapper { padding: 35px 32px; }

        /* Glassmorphism Card Form */
        .glass-form-card {
            background: var(--bg-card); border: 1px solid var(--border-glass); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border-radius: 24px; padding: 35px; box-shadow: 0 15px 35px rgba(148, 163, 184, 0.06);
        }

        .form-label { font-weight: 700; color: var(--text-main); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        
        /* Input Premium Styling */
        .form-control, .form-select {
            background: #ffffff !important; border: 1px solid rgba(148, 163, 184, 0.25) !important;
            border-radius: 12px; padding: 12px 16px; font-size: 0.95rem; font-weight: 500; color: var(--text-main); transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-brand) !important; box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15) !important;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fa-solid fa-bolt"></i>I-CALM Panel</h3>
    <a href="../dashboard/index.php">
        <span><i class="fa-solid fa-chart-pie"></i><span class="menu-text">Dashboard</span></span>
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

<div class="content">
    <nav class="navbar navbar-custom">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center" style="font-weight: 800; font-size: 1.3rem;">
                <i class="fa-solid fa-folder-plus text-primary me-2"></i> KENDALI LOGISTIK 
                <span class="ms-2" style="font-weight: 400; font-size: 0.95rem; color: var(--text-muted);">/ Registrasi Berita Acara Baru</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="glass-form-card">
            <h4 class="fw-extrabold mb-4" style="letter-spacing: -0.5px; font-weight: 800;"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Form Entry Data Berita Acara</h4>
            <hr class="mb-4 opacity-10">

            <form method="POST">
                <div class="row g-4">
                    
                    <div class="col-md-4">
                        <label class="form-label">Jenis Berita Acara (BA)</label>
                        <select name="jenis_berita_acara" class="form-select" required>
                            <option value="MASUK">MASUK</option>
                            <option value="KELUAR">KELUAR</option>
                            <option value="PENGEMBALIAN">PENGEMBALIAN</option>
                            <option value="RETURN">RETURN</option>
                            <option value="PERBAIKAN">PERBAIKAN</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tanggal Record</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Kategori Material</label>
                        <select name="kategori_material" class="form-select">
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Material Gardu">Material Gardu</option>
                            <option value="Material Proteksi">Material Proteksi</option>
                            <option value="Material Kabel">Material Kabel</option>
                            <option value="Material Trafo">Material Trafo</option>
                            <option value="Alat Kerja">Alat Kerja</option>
                            <option value="Alat Uji">Alat Uji</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Nama Barang / Deskripsi Teknis</label>
                        <input type="text" name="nama_barang" class="form-control" placeholder="Contoh: Kubikel Schneider SM6" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Merk / Spesifikasi</label>
                        <input type="text" name="merk_jenis" class="form-control" placeholder="Contoh: Schneider / Siemens">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Jenis Barang</label>
                        <input type="text" name="jenis_barang" class="form-control" placeholder="Contoh: Pasif / Mekanik">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Sumber Barang</label>
                        <input type="text" name="sumber_barang" class="form-control" placeholder="Contoh: Pengadaan Investasi">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="satuan" class="form-control" placeholder="Contoh: Unit, Pcs, Meter">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Jumlah / Volume</label>
                        <input type="number" name="jumlah" class="form-control" placeholder="0" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nomor Seri (S/N)</label>
                        <input type="text" name="no_seri" class="form-control" placeholder="Tulis nomor seri pabrikan jika ada">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Pemasok / Asal Material</label>
                        <input type="text" name="asal_barang_vendor" class="form-control" placeholder="Contoh: PT. PLN Tarakan">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tujuan Distribusi</label>
                        <input type="text" name="tujuan" class="form-control" placeholder="Contoh: ULTG Makassar">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Kondisi Fisik Material</label>
                        <select name="kondisi_material" class="form-select">
                            <option value="BAIK">BAIK</option>
                            <option value="RUSAK">RUSAK</option>
                            <option value="PERBAIKAN">PERBAIKAN</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Keterangan Tambahan</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Tuliskan detail info lapangan, lokasi penyimpanan sekunder, atau nomor nota dinas terkait..."></textarea>
                    </div>
                </div>

                <div class="mt-4 pt-2 d-flex gap-2">
                    <button type="submit" name="simpan" class="btn btn-primary fw-bold px-4 py-2" style="border-radius: 12px; background: linear-gradient(135deg, #0284c7, #2563eb); border: none; box-shadow: 0 4px 15px rgba(37, 99, 235, 0.25);">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Data BA
                    </button>
                    <a href="index.php" class="btn btn-light fw-bold px-4 py-2" style="border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.25); color: var(--text-muted);">
                        Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Sidebar Dropdown Menu Controller
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