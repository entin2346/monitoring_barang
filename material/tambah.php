<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

if(isset($_POST['simpan'])){

    // Ambil data form & amankan inputan sesuai kolom tabel database_ba
    $tanggal             = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $nama_barang         = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $merk_jenis          = mysqli_real_escape_string($conn, $_POST['merk_jenis']);
    $jenis_barang        = mysqli_real_escape_string($conn, $_POST['jenis_barang']);
    $sumber_barang       = mysqli_real_escape_string($conn, $_POST['sumber_barang']);
    $satuan              = mysqli_real_escape_string($conn, $_POST['satuan']);
    $jumlah              = (int)$_POST['jumlah'];
    $no_rak              = mysqli_real_escape_string($conn, $_POST['no_rak']); 
    $no_seri             = mysqli_real_escape_string($conn, $_POST['no_seri']);
    $asal_barang_vendor  = mysqli_real_escape_string($conn, $_POST['asal_barang_vendor']);
    $jenis_berita_acara  = mysqli_real_escape_string($conn, $_POST['jenis_berita_acara']);
    $keterangan          = mysqli_real_escape_string($conn, $_POST['keterangan']);

    $foto = '';

    if($_FILES['foto']['name'] != ''){
        $foto = time().'_'.$_FILES['foto']['name'];
        move_uploaded_file(
            $_FILES['foto']['tmp_name'],
            "upload/".$foto
        );
    }

    mysqli_query($conn, "
        INSERT INTO database_ba 
        (
            tanggal,
            nama_barang,
            merk_jenis,
            jenis_barang,
            sumber_barang,
            satuan,
            jumlah,
            no_rak,
            no_seri,
            asal_barang_vendor,
            jenis_berita_acara,
            keterangan,
            foto
        )
        VALUES
        (
            '$tanggal',
            '$nama_barang',
            '$merk_jenis',
            '$jenis_barang',
            '$sumber_barang',
            '$satuan',
            '$jumlah',
            '$no_rak',
            '$no_seri',
            '$asal_barang_vendor',
            '$jenis_berita_acara',
            '$keterangan',
            '$foto'
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
    <title>I-CALM | Registrasi Berita Acara Baru</title>
    
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body { 
            background: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* SIDEBAR STYLE SESUAI INDEX */
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
        .dropdown-btn.active { color: #ffffff !important; background: #0284c7 !important; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); border-radius: 10px; }
        .dropdown-btn.active i.menu-icon { color: #ffffff !important; }
        .dropdown-btn.active .dropdown-chevron { color: #ffffff !important; }
        
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

        /* CONTENT STYLE */
        .content { margin-left: 260px; position: relative; }
        
        .navbar-custom { 
            background: #ffffff;
            padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999;
        }

        .main-body-wrapper { padding: 40px; }

        /* FORM CARD SESUAI STYLE INDEX */
        .glass-form-card {
            background: var(--bg-card); 
            border: 1px solid var(--border-color);
            border-radius: 16px; 
            padding: 32px; 
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.02);
        }

        .form-label { 
            font-weight: 700; 
            color: var(--text-main); 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            letter-spacing: 0.8px; 
            margin-bottom: 10px; 
        }
        
        .form-control, .form-select {
            background: #f8fafc !important; 
            border: 1px solid #cbd5e1 !important;
            border-radius: 10px; 
            padding: 12px 16px; 
            font-size: 0.9rem; 
            color: var(--text-main);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary) !important; 
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.1) !important;
            background: #ffffff !important;
        }

        .btn-primary {
            background: var(--primary);
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 700;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background: #026ca3;
            transform: translateY(-2px);
        }
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
        <a href="../kategori/stok.php">Stok</a>
        <a href="../kategori/non_stok.php">Non Stok</a>
        <a href="../kategori/non_po.php">Non PO</a>
        <a href="../kategori/ex_bongkaran.php">Ex Bongkaran</a>
        <a href="../kategori/pre_memory.php">Pre Memory</a>
        <a href="../kategori/pemakaian.php">Pemakaian</a>
        <a href="../kategori/peminjaman.php">Peminjaman</a>
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
    <nav class="navbar navbar-custom">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center" style="font-weight: 800; font-size: 1.3rem;">
                <i class="fa-solid fa-boxes-stacked text-primary me-2"></i> KENDALI LOGISTIK 
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Registrasi Berita Acara Baru</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="glass-form-card">
            <h4 class="fw-bold mb-4" style="color: var(--text-main);"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Form Entry Data Berita Acara</h4>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-4">
                    
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Record</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Kategori Berita Acara (BA)</label>
                        <select name="jenis_berita_acara" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="MASUK">MASUK</option>
                            <option value="KELUAR">KELUAR</option>
                            <option value="RETURN">RETURN</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Nama Material / Barang</label>
                        <input type="text" name="nama_barang" class="form-control" placeholder="Contoh: Kabel Optik 4 Core" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Merk / Spesifikasi</label>
                        <input type="text" name="merk_jenis" class="form-control" placeholder="Contoh: Supreme / Prysmian">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Jenis Material</label>
                        <input type="text" name="jenis_barang" class="form-control" placeholder="Contoh: Aksesoris Utama / Tool">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Sumber Material</label>
                        <input type="text" name="sumber_barang" class="form-control" placeholder="Contoh: Pembelian / Inventaris Pusat">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="satuan" class="form-control" placeholder="Contoh: Pcs, Meter, Roll">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Jumlah Volume</label>
                        <input type="number" name="jumlah" class="form-control" placeholder="0" min="1" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Nomor Rak</label>
                        <select name="no_rak" class="form-select">
                            <option value="">-- Pilih Nomor Rak --</option>
                            <?php
                            $list_rak = [
                                'A1', 'A2', 'A3', 'B1', 'B2', 'B3', 'B4', 'C1', 'C2', 'C3',
                                'D1', 'D2', 'D3', 'E1', 'E2', 'F1', 'F2', 'G1', 'G2', 'G3',
                                'H1', 'H2', 'H3', 'I1', 'I2', 'J1', 'J2', 'K1', 'K2', 'K3',
                                'M1', 'M2', 'M3', 'PETI', 'RAK ISOLATOR'
                            ];
                            foreach($list_rak as $rak){
                                echo "<option value='$rak'>$rak</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Nomor Seri / Kode Asset</label>
                        <input type="text" name="no_seri" class="form-control" placeholder="Contoh: NS-998231">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Pemasok / Vendor Asal</label>
                        <input type="text" name="asal_barang_vendor" class="form-control" placeholder="Contoh: PT. Logistik Nusantara">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Keterangan Tambahan</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Tuliskan catatan fungsional atau kondisi khusus berkas BA..."></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Dokumentasi / Foto Fisik BA</label>
                        <input type="file" name="foto" class="form-control">
                    </div>
                </div>

                <div class="mt-4 pt-2 d-flex gap-2">
                    <button type="submit" name="simpan" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Data BA
                    </button>
                    <a href="index.php" class="btn btn-light border fw-bold px-4 py-2" style="border-radius: 10px; color: var(--text-muted);">
                        Kembali
                    </a>
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