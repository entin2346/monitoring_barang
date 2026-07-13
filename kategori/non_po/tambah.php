<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}
include "../../config/koneksi.php";

if (isset($_POST['submit'])) {
    $jenis_ba = mysqli_real_escape_string($conn, $_POST['jenis_ba']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $nama_material = mysqli_real_escape_string($conn, $_POST['nama_material']);
    $jenis_kategori = 'Non PO'; // Otomatis dikunci ke Non PO
    $merk_jenis = mysqli_real_escape_string($conn, $_POST['merk_jenis']);
    $jenis_barang = mysqli_real_escape_string($conn, $_POST['jenis_barang']);
    $sumber_barang = mysqli_real_escape_string($conn, $_POST['sumber_barang']);
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);
    $jumlah = (int)$_POST['jumlah'];
    $tujuan = mysqli_real_escape_string($conn, $_POST['tujuan']);
    $kondisi = mysqli_real_escape_string($conn, $_POST['kondisi']);
    $vendor = mysqli_real_escape_string($conn, $_POST['vendor']);
    $berita_acara = mysqli_real_escape_string($conn, $_POST['berita_acara']);

    // Array untuk menampung berkas yang diunggah
    $uploaded_files = [];

    // Proses unggah banyak berkas/foto sekaligus
    if(!empty($_FILES['files']['name'][0])) {
        $target_dir = "upload/";
        // Buat folder jika belum tersedia
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        foreach($_FILES['files']['name'] as $key => $val) {
            if($_FILES['files']['name'][$key] != '') {
                $file_name = time() . '_' . basename($_FILES['files']['name'][$key]);
                $target_file = $target_dir . $file_name;
                if(move_uploaded_file($_FILES['files']['tmp_name'][$key], $target_file)) {
                    $uploaded_files[] = $file_name;
                }
            }
        }
    }
    
    // Mengonversi array nama file ke JSON string
    $lampiran_files = mysqli_real_escape_string($conn, json_encode($uploaded_files));

    $query = "INSERT INTO non_po (jenis_ba, tanggal, nama_material, jenis_kategori, merk_jenis, jenis_barang, sumber_barang, satuan, jumlah, tujuan, kondisi, vendor, berita_acara, lampiran_files) 
              VALUES ('$jenis_ba', '$tanggal', '$nama_material', '$jenis_kategori', '$merk_jenis', '$jenis_barang', '$sumber_barang', '$satuan', $jumlah, '$tujuan', '$kondisi', '$vendor', '$berita_acara', '$lampiran_files')";

    if (mysqli_query($conn, $query)) {
        header("Location: non_po.php");
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
    <title>I-CALM | Tambah Material Non PO</title>
    
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

        /* CONTENT & FORM STYLE */
        .content { margin-left: 260px; position: relative; min-width: 320px; }
        .navbar-custom { background: #ffffff; padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999; }
        .main-body-wrapper { padding: 40px; }
        
        .glass-form-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 40px; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04); }
        .form-label { font-weight: 700; color: #1e293b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; }
        .form-control, .form-select { background: #f8fafc !important; border: 1px solid #cbd5e1 !important; border-radius: 10px; padding: 12px 16px; font-size: 0.9rem; color: var(--text-main); transition: all 0.2s ease; }
        .form-control:focus, .form-select:focus { border-color: var(--primary) !important; box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.12) !important; background: #ffffff !important; }

        .btn-submit-custom { background: #0284c7; border: none; border-radius: 8px; padding: 10px 24px; font-size: 0.9rem; font-weight: 700; color: white; transition: all 0.2s; }
        .btn-submit-custom:hover { background: #0369a1; }
        .btn-back-custom { border-radius: 8px; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 10px 24px; font-size: 0.9rem; font-weight: 600; color: #475569; text-decoration: none; transition: all 0.2s; }
        .btn-back-custom:hover { background: #e2e8f0; color: #1e293b; }

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
        <a href="/monitoring_barang/kategori/non_po/non_po.php" class="active-menu">Non PO</a>
        <a href="/monitoring_barang/kategori/ex_bongkaran/ex_bongkaran.php">Ex Bongkaran</a>
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
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Kategori: Non PO / Registrasi Baru</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="glass-form-card">
            <h4 class="fw-bold mb-4" style="color: #0f172a; letter-spacing: -0.02em;"><i class="fa-regular fa-square-plus text-primary me-2"></i>Form Entry Data Non PO Baru</h4>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger mb-4"><?= $error; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row g-4">
                    
                    <div class="col-md-3">
                        <label class="form-label">Jenis BA</label>
                        <input type="text" name="jenis_ba" class="form-control" placeholder="Contoh: BA Penerimaan" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tanggal Sistem</label>
                        <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="satuan" class="form-control" placeholder="Contoh: Pcs, Unit, Meter" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Jumlah Volume</label>
                        <input type="number" name="jumlah" class="form-control" placeholder="0" min="0" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Nama Barang / Material</label>
                        <input type="text" name="nama_material" class="form-control" placeholder="Masukkan nama kelompok material" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Merk / Jenis</label>
                        <input type="text" name="merk_jenis" class="form-control" placeholder="Masukkan merk">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Jenis Barang</label>
                        <input type="text" name="jenis_barang" class="form-control" placeholder="Masukkan jenis spesifik barang">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Sumber Barang</label>
                        <input type="text" name="sumber_barang" class="form-control" placeholder="Masukkan asal sumber barang">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tujuan</label>
                        <input type="text" name="tujuan" class="form-control" placeholder="Masukkan alokasi tujuan barang">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Kondisi</label>
                        <input type="text" name="kondisi" class="form-control" placeholder="Contoh: BAIK / RUSAK">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Vendor</label>
                        <input type="text" name="vendor" class="form-control" placeholder="Masukkan nama vendor penyuplai">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Nomor Berita Acara</label>
                        <input type="text" name="berita_acara" class="form-control" placeholder="Masukkan nomor berita acara">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Unggah Dokumentasi / Berkas Lampiran (Bisa Banyak File & Foto)</label>
                        <input type="file" name="files[]" class="form-control" multiple>
                        <div class="form-text text-muted small mt-1">Anda bisa memilih lebih dari satu file gambar atau berkas sekaligus dengan menahan tombol Ctrl/Shift sewaktu memilih berkas.</div>
                    </div>

                </div>

                <div class="mt-4 pt-2 d-flex gap-2">
                    <button type="submit" name="submit" class="btn-submit-custom">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Data Non PO
                    </button>
                    <a href="non_po.php" class="btn-back-custom">Kembali</a>
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