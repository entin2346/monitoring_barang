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
    $nama_material = mysqli_real_escape_string($conn, $_POST['nama_material']);
    $jenis_kategori = 'Pemakaian'; // Dikunci otomatis ke Pemakaian
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);
    $jumlah = (int)$_POST['jumlah'];
    
    // Logika pemilihan nomor rak (jika pilih input baru, ambil dari no_rak_baru)
    if (!empty($_POST['no_rak_baru'])) {
        $no_rak = mysqli_real_escape_string($conn, $_POST['no_rak_baru']);
    } else {
        $no_rak = mysqli_real_escape_string($conn, $_POST['no_rak']);
    }
    
    $tanggal = date('Y-m-d H:i:s');

    // --- PROSES UNGGAH MULTIPLE FOTO / BERKAS ---
    $arr_dokumentasi = [];
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

    $query = "INSERT INTO material_gudang (nama_material, jenis_kategori, satuan, jumlah, no_rak, tanggal, dokumentasi) 
              VALUES ('$nama_material', '$jenis_kategori', '$satuan', $jumlah, '$no_rak', '$tanggal', '$dokumentasi_json')";

    if (mysqli_query($conn, $query)) {
        header("Location: pemakaian.php");
        exit;
    } else {
        $error = "Gagal menambah data: " . mysqli_error($conn);
    }
}

// Mengambil daftar nomor rak unik yang sudah ada di database untuk dijadikan list dropdown
$rak_query = mysqli_query($conn, "SELECT DISTINCT no_rak FROM material_gudang WHERE no_rak IS NOT NULL AND no_rak != '' ORDER BY no_rak ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Tambah Material Pemakaian</title>
    
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
        
        /* CARD PANJANG LAYOUT */
        .glass-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 40px; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04); width: 100%; }
        
        /* INPUT STYLE */
        .form-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; margin-bottom: 8px; }
        .form-control, .form-select { border-radius: 10px; border: 1px solid #cbd5e1; padding: 13px 18px; font-size: 0.9rem; background-color: #f8fafc; transition: all 0.2s; color: var(--text-main); }
        .form-control:focus, .form-select:focus { background-color: #fff; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15); }

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
        <a href="/monitoring_barang/kategori/peminjaman/peminjaman.php">Peminjaman</a>
        <a href="/monitoring_barang/kategori/pemakaian/pemakaian.php" class="active-menu">Pemakaian</a>
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
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Kategori: Pemakaian / Tambah Data</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="glass-card">
            <h4 class="fw-bold mb-4" style="color: #0f172a;"><i class="fa-solid fa-plus text-success me-2"></i>Tambah Material Pemakaian Baru</h4>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger" style="border-radius:10px;"><?= $error; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Material</label>
                        <input type="text" name="nama_material" class="form-control" required placeholder="Masukkan nama material lengkap" autocomplete="off">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="satuan" class="form-control" required placeholder="Contoh: Pcs, Meter, Roll, Set" autocomplete="off">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Jumlah Terpakai</label>
                        <input type="number" name="jumlah" class="form-control" required min="1" value="1">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Nomor Rak</label>
                        <select name="no_rak" id="no_rak" class="form-select" onchange="toggleRakBaru()">
                            <option value="">-- Pilih Nomor Rak --</option>
                            <?php while($rak = mysqli_fetch_assoc($rak_query)): ?>
                                <option value="<?= htmlspecialchars($rak['no_rak']); ?>"><?= htmlspecialchars($rak['no_rak']); ?></option>
                            <?php endwhile; ?>
                            <option value="INPUT_BARU">* Input Rak Baru *</option>
                        </select>
                        
                        <!-- Input tambahan tersembunyi jika rak belum ada di list -->
                        <div id="wrapper_rak_baru" class="mt-2" style="display: none;">
                            <input type="text" name="no_rak_baru" class="form-control" placeholder="Ketik nomor rak baru di sini..." autocomplete="off">
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label">Lampiran Dokumentasi (Bisa beberapa foto / file berkas sekaligus)</label>
                        <input type="file" name="dokumentasi[]" class="form-control" multiple accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                        <div class="form-text text-muted" style="font-size: 0.75rem;">Mendukung format gambar (JPG, PNG) atau dokumen kerja (PDF, DOCX). Tekan Ctrl sambil memilih berkas untuk memilih lebih dari satu.</div>
                    </div>
                </div>
                
                <div class="mt-4 gap-2 d-flex">
                    <button type="submit" name="submit" class="btn btn-primary px-4 fw-bold" style="border-radius:10px; background-color: #0284c7; border:none;"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan Data</button>
                    <a href="pemakaian.php" class="btn btn-light px-4 border" style="border-radius:10px;">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Fungsi untuk menampilkan text input jika memilih opsi * Input Rak Baru *
    function toggleRakBaru() {
        var selectRak = document.getElementById("no_rak");
        var wrapperRakBaru = document.getElementById("wrapper_rak_baru");
        if (selectRak.value === "INPUT_BARU") {
            wrapperRakBaru.style.display = "block";
        } else {
            wrapperRakBaru.style.display = "none";
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