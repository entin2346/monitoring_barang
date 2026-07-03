<?php
session_start();
if(!isset($_SESSION['login'])){ header("Location: ../login/index.php"); exit; }
include "../config/koneksi.php";

if(!isset($_GET['id'])){ echo "ID tidak ditemukan"; exit; }
$id = mysqli_real_escape_string($conn, $_GET['id']);
$data = mysqli_query($conn,"SELECT * FROM database_ba WHERE id='$id'");
$d = mysqli_fetch_assoc($data);
if(!$d){ echo "Data tidak ditemukan"; exit; }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail BA | I-CALM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        /* CSS ATURAN SAMA PERSIS DENGAN INDEX.PHP */
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

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }

        body { 
            background: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

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

        .content { margin-left: 260px; position: relative; }
        
        /* NAVBAR & CARD LAYOUT STYLE */
        .navbar-cyber { background: #ffffff; padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999; }
        .main-body-wrapper { padding: 40px; }

        .glass-detail-card {
            background: var(--bg-card); border: 1px solid var(--border-color);
            border-radius: 16px; padding: 35px; box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        }

        .table-detail th { background-color: #f8fafc; color: var(--text-muted); font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; width: 25%; }
        .table-detail td { color: var(--text-main); font-weight: 500; font-size: 0.92rem; }
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
    <nav class="navbar navbar-expand-lg navbar-cyber">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center" style="color: #0f172a; font-weight: 800; font-size: 1.3rem;">
                <i class="fa-solid fa-circle-info text-primary opacity-75 me-2"></i> INFORMASI RINCI DATA 
                <span class="ms-2" style="font-weight: 400; font-size: 0.95rem; color: var(--text-muted);">/ Database BA</span>
            </span>
            <div>
                <a href="index.php" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-semibold border-2" style="border-radius: 10px;">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke List
                </a>
            </div>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="glass-detail-card">
            <table class="table table-bordered table-detail align-middle mb-0">
                <tr>
                    <th>Jenis Berita Acara</th>
                    <td>
                        <?php if(strtoupper($d['jenis_berita_acara']) == 'MASUK'): ?>
                            <span class="badge bg-success-subtle text-success px-2 py-1 fw-bold">MASUK</span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger px-2 py-1 fw-bold">KELUAR</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Tanggal Dokumen</th>
                    <td><?= date('d F Y', strtotime($d['tanggal'])); ?></td>
                </tr>
                <tr>
                    <th>Nama Barang / Material</th>
                    <td class="fw-bold text-primary"><?= htmlspecialchars($d['nama_barang']); ?></td>
                </tr>
                <tr>
                    <th>Merk / Jenis</th>
                    <td><?= htmlspecialchars($d['merk_jenis'] ?: '-'); ?></td>
                </tr>
                <tr>
                    <th>Jenis Barang</th>
                    <td><?= htmlspecialchars($d['jenis_barang'] ?: '-'); ?></td>
                </tr>
                <tr>
                    <th>Sumber Material</th>
                    <td><?= htmlspecialchars($d['sumber_barang'] ?: '-'); ?></td>
                </tr>
                <tr>
                    <th>Satuan</th>
                    <td><?= htmlspecialchars($d['satuan'] ?: '-'); ?></td>
                </tr>
                <tr>
                    <th>Jumlah Volume</th>
                    <td class="fw-bold"><?= number_format($d['jumlah']); ?></td>
                </tr>
                <tr>
                    <th>Nomor Seri Komponen</th>
                    <td style="font-family: monospace; font-size: 0.95rem;"><?= htmlspecialchars($d['no_seri'] ?: '-'); ?></td>
                </tr>
                <tr>
                    <th>Pemasok / Vendor</th>
                    <td><?= htmlspecialchars($d['asal_barang_vendor'] ?: '-'); ?></td>
                </tr>
                <tr>
                    <th>Kategori Material</th>
                    <td><span class="badge bg-secondary-subtle text-secondary px-2 py-1 fw-semibold"><?= htmlspecialchars($d['kategori_material']); ?></span></td>
                </tr>
                <tr>
                    <th>Kondisi Fisik</th>
                    <td>
                        <?php if(strtoupper($d['kondisi_material']) == 'BAIK'): ?>
                            <span class="badge bg-success text-white px-2 py-1">BAIK</span>
                        <?php elseif(strtoupper($d['kondisi_material']) == 'RUSAK'): ?>
                            <span class="badge bg-danger text-white px-2 py-1">RUSAK</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark px-2 py-1">PERBAIKAN</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Lokasi / Unit Tujuan</th>
                    <td><?= htmlspecialchars($d['tujuan'] ?: '-'); ?></td>
                </tr>
                <tr>
                    <th>Keterangan Deskriptif</th>
                    <td><?= nl2br(htmlspecialchars($d['keterangan'] ?: '-')); ?></td>
                </tr>
                <tr>
                    <th>Berkas Lampiran</th>
                    <td>
                        <div class="d-flex flex-wrap gap-3">
                            <?php 
                            if(!empty($d['file_ba'])) {
                                $files = json_decode($d['file_ba'], true);
                                if(!is_array($files)) { $files = [$d['file_ba']]; }
                                
                                foreach($files as $file) {
                                    $file_path = "../uploads/" . $file;
                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            ?>
                                    <div style="max-width: 240px; width: 100%;">
                                        <?php if(in_array($ext, ['jpg','jpeg','png'])): ?>
                                            <div class="border rounded p-2 text-center bg-light">
                                                <img src="<?= $file_path; ?>" class="img-fluid rounded mb-2 img-thumbnail" style="max-height: 120px; cursor: pointer;" onclick="openPreviewImg('<?= $file_path; ?>')">
                                                <p class="mb-0 text-truncate small fw-semibold" title="<?= htmlspecialchars($file); ?>"><?= htmlspecialchars($file); ?></p>
                                                <a href="<?= $file_path; ?>" class="btn btn-sm btn-primary mt-2 px-3 w-100" download><i class="fa-solid fa-download me-1"></i>Unduh</a>
                                            </div>
                                        <?php elseif($ext === 'pdf'): ?>
                                            <div class="p-3 text-center bg-light border rounded">
                                                <i class="fa-solid fa-file-pdf fs-1 text-danger mb-2"></i>
                                                <p class="mb-1 text-truncate small fw-semibold" title="<?= htmlspecialchars($file); ?>"><?= htmlspecialchars($file); ?></p>
                                                <div class="d-flex gap-1 mt-2">
                                                    <a href="<?= $file_path; ?>" target="_blank" class="btn btn-sm btn-outline-danger w-100">Buka</a>
                                                    <a href="<?= $file_path; ?>" class="btn btn-sm btn-danger w-100" download>Unduh</a>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="p-3 text-center bg-light border rounded">
                                                <i class="fa-solid fa-file-invoice fs-1 text-secondary mb-2"></i>
                                                <p class="mb-1 text-truncate small fw-semibold" title="<?= htmlspecialchars($file); ?>"><?= htmlspecialchars($file); ?></p>
                                                <a href="<?= $file_path; ?>" class="btn btn-sm btn-success mt-2 px-3 w-100" download><i class="fa-solid fa-download me-1"></i>Unduh</a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                            <?php 
                                } 
                            } else { 
                                echo "<span class='text-muted small italic'>Tidak ada berkas lampiran</span>";
                            } 
                            ?>
                        </div>
                    </td>
                </tr>
            </table>
            
            <div class="mt-4 pt-3 border-top d-flex gap-2">
                <a href="index.php" class="btn btn-secondary px-4 py-2 fw-semibold" style="border-radius: 10px;">
                    <i class="fa-solid fa-chevron-left me-1"></i> Kembali
                </a>
                <a href="edit.php?id=<?= $d['id']; ?>" class="btn btn-warning px-4 py-2 fw-semibold text-dark" style="border-radius: 10px;">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit Data
                </a>
                <a href="cetak_tug5.php?id=<?= $d['id']; ?>" target="_blank" class="btn btn-primary px-4 py-2 fw-semibold" style="border-radius: 10px;">
                    <i class="fa-solid fa-print me-1"></i> Cetak Form TUG 5
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="previewImageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark border-0">
        <div class="modal-body p-0 text-center rounded">
            <img src="" id=\"modalLargeImg\" class="img-fluid rounded" style="max-height: 80vh;">
        </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openPreviewImg(src) {
        document.getElementById('modalLargeImg').src = src;
        var viewModal = new bootstrap.Modal(document.getElementById('previewImageModal'));
        viewModal.show();
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