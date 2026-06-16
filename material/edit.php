<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$data = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT * FROM material_gudang WHERE id='$id'")
);

if(!$data){
    header("Location: index.php");
    exit;
}

if(isset($_POST['update'])){
    $nama_material = mysqli_real_escape_string($conn,$_POST['nama_material']);
    $satuan        = mysqli_real_escape_string($conn,$_POST['satuan']);
    $jumlah        = (int)$_POST['jumlah'];
    $no_rak        = mysqli_real_escape_string($conn,$_POST['no_rak']);
    $kondisi       = mysqli_real_escape_string($conn,$_POST['kondisi']);
    $lokasi        = mysqli_real_escape_string($conn,$_POST['lokasi']);
    $keterangan    = mysqli_real_escape_string($conn,$_POST['keterangan']);

    $foto = $data['foto_material'];

    if($_FILES['foto']['name'] != ''){
        $foto = time().'_'.$_FILES['foto']['name'];
        move_uploaded_file(
            $_FILES['foto']['tmp_name'],
            "upload/".$foto
        );
    }

    mysqli_query($conn,"
        UPDATE material_gudang SET
        nama_material='$nama_material',
        satuan='$satuan',
        jumlah='$jumlah',
        no_rak='$no_rak',
        kondisi='$kondisi',
        lokasi_penyimpanan='$lokasi',
        keterangan='$keterangan',
        foto_material='$foto'
        WHERE id='$id'
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
    <title>I-CALM | Edit Konfigurasi Material</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-base: #e2e8f0;            
            --bg-body: #f8fafc;
            --bg-card: rgba(255, 255, 255, 0.6); 
            --primary-brand: #0284c7;       
            --accent-blue: #3b82f6;         
            --text-main: #1e293b;           
            --text-muted: #64748b;          
            --border-glass: rgba(255, 255, 255, 0.7);
            --border-light: rgba(148, 163, 184, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body { 
            background: radial-gradient(circle at top right, #dbeafe 0%, var(--bg-base) 60%, #e0e7ff 100%);
            color: var(--text-main);
            min-height: 100vh;
        }

        /* ========================================================
           SIDEBAR OCEAN BLUE PREMIUM DESIGN (PERSISTEN & SELARAS)
        ========================================================= */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100%;
            background: linear-gradient(135deg, 
                        rgba(15, 32, 67, 0.95) 0%, 
                        rgba(9, 53, 122, 0.9) 50%, 
                        rgba(2, 132, 199, 0.85) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 28px;
            z-index: 1000;
            box-shadow: 5px 0 30px rgba(9, 53, 122, 0.15); 
        }
        
        .sidebar h3 { 
            font-size: 1.4rem; 
            font-weight: 800; 
            padding: 0 24px; 
            margin-bottom: 35px; 
            color: #ffffff;
            display: flex;
            align-items: center;
        }
        .sidebar h3 i { color: #38bdf8 !important; text-shadow: 0 0 12px rgba(56, 189, 248, 0.6); }
        
        .sidebar a, .dropdown-btn { 
            display: flex; align-items: center; justify-content: space-between; color: rgba(255, 255, 255, 0.7); 
            text-decoration: none; padding: 14px 24px; font-size: 0.95rem; font-weight: 600; border: none; background: none; width: 100%; transition: all 0.25s; cursor: pointer;
        }
        .sidebar a:hover, .dropdown-btn:hover { background: rgba(255, 255, 255, 0.08); color: #ffffff; }

        .sidebar .active-menu {
            color: #ffffff !important; 
            background: linear-gradient(90deg, rgba(56, 189, 248, 0.2) 0%, rgba(56, 189, 248, 0.03) 100%) !important; 
            border-left: 4px solid #38bdf8; padding-left: 20px;
        }
        .sidebar .active-menu i { color: #38bdf8 !important; }
        .sidebar a i, .dropdown-btn i { margin-right: 12px; font-size: 1.1rem; width: 20px; text-align: center; color: rgba(255, 255, 255, 0.6); }
        .sidebar a:hover i, .dropdown-btn:hover i { color: #ffffff; }
        .sidebar .menu-text { flex-grow: 1; }
        .dropdown-chevron { font-size: 0.8rem !important; transition: transform 0.2s ease; color: rgba(255, 255, 255, 0.5) !important; }
        .dropdown-btn.active .dropdown-chevron { transform: rotate(180deg); color: #38bdf8 !important; }

        .dropdown-container { display: none; background: rgba(0, 0, 0, 0.15); padding: 4px 0; }
        .dropdown-container a { padding: 11px 24px 11px 56px; font-size: 0.85rem; font-weight: 500; color: rgba(255, 255, 255, 0.6); }
        .dropdown-container a:hover { color: #38bdf8; background: transparent; }

        .sidebar .logout-button {
            margin-top: 30px; background: rgba(239, 68, 68, 0.1); border-radius: 12px; width: calc(100% - 32px); margin-left: 16px; padding: 12px 16px;
        }
        .sidebar .logout-button:hover { background: rgba(239, 68, 68, 0.25) !important; }
        .sidebar .logout-button i, .sidebar .logout-button .menu-text { color: #fca5a5 !important; }

        /* ========================================================
           CONTENT CONTAINER
        ========================================================= */
        .content { margin-left: 260px; }
        
        .navbar-custom { 
            background: rgba(255, 255, 255, 0.45); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            padding: 18px 32px; border-bottom: 1px solid var(--border-glass); position: sticky; top: 0; z-index: 999;
        }
        .navbar-custom .navbar-brand { color: var(--text-main); font-weight: 800; font-size: 1.4rem; letter-spacing: -0.5px;}

        .main-body-wrapper { padding: 40px 32px; }

        /* PROFILE CARD FORM */
        .glass-form-card {
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border-radius: 24px; padding: 32px;
            box-shadow: 0 10px 30px -10px rgba(148, 163, 184, 0.12);
        }

        /* FORM STYLING */
        .form-label-custom {
            font-size: 0.8rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.8px; color: var(--text-muted); margin-bottom: 8px;
        }
        .form-control-custom {
            background: rgba(255, 255, 255, 0.7) !important;
            border: 1px solid rgba(148, 163, 184, 0.25) !important;
            border-radius: 12px !important; padding: 12px 16px;
            color: var(--text-main) !important; font-size: 0.95rem; font-weight: 500;
            transition: all 0.2s;
        }
        .form-control-custom:focus {
            background: #ffffff !important;
            border-color: var(--primary-brand) !important;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15) !important;
        }

        /* PHOTO PREVIEW FRAME */
        .preview-photo-box {
            background: rgba(255, 255, 255, 0.4);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 16px; padding: 16px; text-align: center;
        }
        .img-edit-preview {
            max-width: 100%; max-height: 180px; object-fit: contain;
            border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fa-solid fa-bolt me-2"></i>I-CALM Panel</h3>

    <a href="../dashboard/index.php">
        <span><i class="fa-solid fa-chart-pie me-2"></i><span class="menu-text">Dashboard</span></span>
    </a>

    <button class="dropdown-btn active">
        <span><i class="fa-solid fa-layer-group"></i><span class="menu-text">Monitoring</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container" style="display: block;">
        <a href="../material/index.php" class="active-menu">Material Gudang</a>
        <a href="../ba/index.php">Database BA</a>
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
            <span class="navbar-brand mb-0 h1 d-flex align-items-center">
                <i class="fa-solid fa-pen-to-square text-primary me-2"></i> MODIFIKASI DATA 
                <span class="ms-2" style="font-weight: 400; font-size: 0.95rem; color: var(--text-muted);">/ Material Gudang</span>
            </span>
            <div>
                <a href="index.php" class="btn btn-outline-secondary btn-sm px-3 fw-semibold border-2" style="border-radius: 10px;">
                    <i class="fa-solid fa-arrow-left me-1"></i> Batal
                </a>
            </div>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="glass-form-card">
            
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-4">
                    
                    <div class="col-lg-8">
                        <div class="row g-3">
                            
                            <div class="col-12">
                                <label class="form-label-custom">Nama Kelompok Material Gudang</label>
                                <input type="text" name="nama_material" value="<?= htmlspecialchars($data['nama_material']); ?>" class="form-control form-control-custom" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">Satuan Ukur</label>
                                <input type="text" name="satuan" value="<?= htmlspecialchars($data['satuan']); ?>" class="form-control form-control-custom" placeholder="Contoh: PCS, Unit, Meter">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">Jumlah Stok</label>
                                <input type="number" name="jumlah" value="<?= $data['jumlah']; ?>" class="form-control form-control-custom" min="0" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">Nomor Rak</label>
                                <input type="text" name="no_rak" value="<?= htmlspecialchars($data['no_rak']); ?>" class="form-control form-control-custom">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">Status Kondisi</label>
                                <select name="kondisi" class="form-select form-control-custom">
                                    <option value="BAIK" <?= (strtoupper($data['kondisi']) == 'BAIK') ? 'selected' : ''; ?>>BAIK</option>
                                    <option value="RUSAK" <?= (strtoupper($data['kondisi']) == 'RUSAK') ? 'selected' : ''; ?>>RUSAK</option>
                                    <option value="PERLU CEK" <?= (strtoupper($data['kondisi']) == 'PERLU CEK') ? 'selected' : ''; ?>>PERLU CEK</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label-custom">Lokasi Penyimpanan</label>
                                <input type="text" name="lokasi" value="<?= htmlspecialchars($data['lokasi_penyimpanan']); ?>" class="form-control form-control-custom">
                            </div>

                            <div class="col-12">
                                <label class="form-label-custom">Keterangan / Catatan Tambahan</label>
                                <textarea name="keterangan" class="form-control form-control-custom" rows="4"><?= htmlspecialchars($data['keterangan']); ?></textarea>
                            </div>

                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="row g-3">
                            
                            <div class="col-12">
                                <label class="form-label-custom">Pratinjau Lampiran Foto Saat Ini</label>
                                <div class="preview-photo-box mb-3">
                                    <?php if(!empty($data['foto_material']) && file_exists("upload/".$data['foto_material'])){ ?>
                                        <img src="upload/<?= $data['foto_material']; ?>" class="img-edit-preview" alt="Current Photo">
                                    <?php } else { ?>
                                        <div class="text-muted small py-4">
                                            <i class="fa-solid fa-image d-block fs-2 mb-2 opacity-30"></i>
                                            Belum ada berkas foto diunggah
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label-custom">Ganti Lampiran Foto Baru</label>
                                <input type="file" name="foto" class="form-control form-control-custom">
                                <div class="form-text small text-muted mt-1">Kosongkan jika tidak ingin mengganti berkas gambar.</div>
                            </div>

                        </div>
                    </div>

                </div>

                <div class="mt-4 pt-4 border-top d-flex gap-2" style="border-color: var(--border-light) !important;">
                    <button type="submit" name="update" class="btn btn-warning px-4 py-2 fw-bold text-dark" style="border-radius: 12px; background: #fbbf24; border: none; box-shadow: 0 4px 15px rgba(251, 191, 36, 0.25);">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                    </button>
                    <a href="index.php" class="btn btn-light px-4 py-2 fw-semibold border text-secondary" style="border-radius: 12px; background: #fff;">
                        Batal
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
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