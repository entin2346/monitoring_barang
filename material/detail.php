<?php

session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// PERBAIKAN: Menggunakan COALESCE bersama NULLIF untuk mengatasi string kosong ('')
$query = mysqli_query($conn, "
    SELECT mg.*, 
           COALESCE(NULLIF(mg.sumber_barang, ''), ba.sumber_barang) AS sumber_barang,
           COALESCE(NULLIF(mg.keterangan, ''), ba.keterangan) AS keterangan
    FROM material_gudang mg
    LEFT JOIN database_ba ba ON mg.nama_material = ba.nama_barang
    WHERE mg.id='$id'
");
$data = mysqli_fetch_assoc($query);

if(!$data){
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Material - I-CALM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
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

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary); }

        body { background: var(--bg-body); color: var(--text-main); font-family: 'Plus Jakarta Sans', sans-serif; overflow-x: hidden; }
        
        /* SIDEBAR PREMIUM SESUAI INDEX */
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

        /* Main Content Layout */
        .content { margin-left: 260px; padding: 40px; }
        .glass-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 32px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .info-item { background: #f8fafc; border: 1px solid var(--border-color); border-radius: 12px; padding: 18px; height: 100%; }
        .info-label { font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; margin-bottom: 5px; }
        .info-value { font-size: 1rem; font-weight: 700; color: var(--text-main); }
        
        /* Photo & File Grid */
        .photo-thumb-box { width: 100px; height: 100px; border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #ffffff; transition: 0.2s; position: relative; padding: 4px; }
        .photo-thumb-box:hover { border-color: var(--primary); transform: scale(1.03); }
        .img-material-premium { width: 100%; height: 100%; object-fit: cover; border-radius: 6px; }
        
        /* Document Icons Style */
        .doc-icon-detail { font-size: 1.8rem; margin-bottom: 2px; }
        .doc-name-detail { font-size: 0.6rem; text-align: center; color: var(--text-main); max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding: 0 2px; font-weight: 600; }
        
        /* Badges */
        .badge-stock { background: rgba(2, 132, 199, 0.08); color: var(--primary); padding: 4px 10px; border-radius: 6px; font-size: 0.85rem; font-weight: 700; }
        .badge-status-baik { background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem; font-weight: 700; }
        .badge-status-other { background: rgba(245, 158, 11, 0.1); color: #d97706; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem; font-weight: 700; }

        .badge-kat { display: inline-block; padding: 5px 12px; font-size: 0.75rem; font-weight: 700; border-radius: 6px; text-transform: uppercase; }
        .kat-stock { background: #e0f2fe; color: #0369a1; }
        .kat-nonstock { background: #fee2e2; color: #b91c1c; }
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
        <a href="../material/index.php" class="active-menu">Material Gudang</a>
        <a href="../ba/index.php">Database BA</a>
    </div>

    <button class="dropdown-btn">
        <span class="menu-content-wrapper">
            <i class="fa-solid fa-tags menu-icon"></i>
            <span>Kategori</span>
        </span>
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
        <a href="../import/form_stok.php">Import Stok</a>
        <a href="../import/form_non_stok.php">Import Non Stok</a>
        <a href="../import/form_non_po.php">Import Non PO</a>
        <a href="../import/form_ex_bongkaran.php">Import Ex Bongkaran</a>
        <a href="../import/form_pre_memory.php">Import Pre Memory</a>
        <a href="../import/form_peminjaman.php">Import Peminjaman</a>
        <a href="../import/form_pemakaian.php">Import Pemakaian</a>
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Detail Material</h3>
        <a href="index.php" class="btn btn-primary"><i class="fa-solid fa-arrow-left me-2"></i>Kembali</a>
    </div>

    <div class="glass-card">
        <h4 class="mb-4 text-primary"><?= htmlspecialchars($data['nama_material']); ?></h4>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="info-item">
                           <div class="info-label">Kategori Kelompok</div>
                            <div class="info-value">
                                <?php
                                if ((int)$data['id'] <= 63) {
                                    echo '<span class="badge-kat kat-stock">Stok</span>';
                                } else {
                                    echo '<span class="badge-kat kat-nonstock">Non Stok</span>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">Volume / Jumlah Stok</div>
                            <div class="info-value"><span class="badge-stock"><?= number_format($data['jumlah']); ?></span></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">Satuan Ukur</div>
                            <div class="info-value"><?= htmlspecialchars($data['satuan'] ?: '-'); ?></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">Penomoran Rak</div>
                            <div class="info-value"><?= htmlspecialchars($data['no_rak'] ?: '-'); ?></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">Kondisi Fisik</div>
                            <div class="info-value">
                                <?php
                                if(strtoupper($data['kondisi'] ?? '') == 'BAIK'){
                                    echo "<span class='badge-status-baik'><i class='fa-solid fa-circle-check me-1 small'></i> BAIK</span>";
                                } else {
                                    echo "<span class='badge-status-other'><i class='fa-solid fa-triangle-exclamation me-1 small'></i> ".htmlspecialchars(strtoupper($data['kondisi'] ?: 'TIDAK DIKETAHUI'))."</span>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">Sumber Barang / Asal Material</div>
                            <div class="info-value"><?= htmlspecialchars($data['sumber_barang'] ?: '-'); ?></div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-item">
                            <div class="info-label">Titik Lokasi Penyimpanan</div>
                            <div class="info-value"><?= htmlspecialchars($data['lokasi_penyimpanan'] ?: '-'); ?></div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-item">
                            <div class="info-label">Catatan Tambahan / Keterangan</div>
                            <div class="info-value" style="font-weight: 400; font-size: 0.95rem; line-height: 1.6;">
                                <?= nl2br(htmlspecialchars($data['keterangan'] ?: 'Tidak ada deskripsi tambahan untuk material ini.')); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="info-item">
                    <div class="info-label mb-3">Lampiran Visual / File Berkas</div>
                    <div class="d-flex gap-2 flex-wrap">
                        <?php 
                        if(!empty($data['foto_barang'])){
                            $array_foto = explode(',', $data['foto_barang']);
                            $has_photo = false;
                            foreach($array_foto as $item_foto){
                                $item_foto = trim($item_foto);
                                if(!empty($item_foto) && file_exists("upload/".$item_foto)){
                                    $has_photo = true;
                                    $ext = strtolower(pathinfo($item_foto, PATHINFO_EXTENSION));
                                    $img_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                                    
                                    if(in_array($ext, $img_exts)){
                                        echo '<div class="photo-thumb-box" onclick="openPreviewFile(\'upload/'.$item_foto.'\', true)">';
                                        echo '<img src="upload/'.$item_foto.'" class="img-material-premium" alt="Foto Material">';
                                        echo '</div>';
                                    } else {
                                        $icon = 'fa-file-lines text-secondary';
                                        if($ext == 'pdf') $icon = 'fa-file-pdf text-danger';
                                        elseif(in_array($ext, ['xls', 'xlsx', 'csv'])) $icon = 'fa-file-excel text-success';
                                        elseif(in_array($ext, ['doc', 'docx'])) $icon = 'fa-file-word text-primary';
                                        elseif(in_array($ext, ['zip', 'rar'])) $icon = 'fa-file-zipper text-warning';
                                        
                                        echo '<div class="photo-thumb-box" onclick="openPreviewFile(\'upload/'.$item_foto.'\', false)" title="Buka berkas '.htmlspecialchars($item_foto).'">';
                                        echo '<i class="fa-solid '.$icon.' doc-icon-detail"></i>';
                                        echo '<span class="doc-name-detail">'.htmlspecialchars($item_foto).'</span>';
                                        echo '</div>';
                                    }
                                }
                            }
                            if(!$has_photo){
                                echo '<span class="text-muted small">Dokumentasi Berkas Kosong</span>';
                            }
                        } else { ?>
                            <span class="text-muted small">Dokumentasi Berkas Kosong</span>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 pt-3 d-flex gap-2 border-top" style="border-color: var(--border-color) !important;">
            <a href="edit.php?id=<?= $data['id']; ?>" class="btn btn-warning text-white"><i class="fa-solid fa-pen me-2"></i>Edit</a>
            <a href="kartu_gantung.php?id=<?= $data['id']; ?>" target="_blank" class="btn btn-success"><i class="fa-solid fa-print me-2"></i>Cetak</a>
        </div>
    </div>
</div>

<div class="modal fade" id="previewImageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-transparent border-0 text-end">
        <button type="button" class="btn-close btn-close-white ms-auto mb-2" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-body p-0 text-center">
            <img src="" id="modalLargeImg" style="max-width: 100%; max-height: 82vh; object-fit: contain; border-radius: 14px; box-shadow: 0 20px 50px rgba(0,0,0,0.4);">
        </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function openPreviewFile(src, isImage) {
        if(isImage) {
            document.getElementById('modalLargeImg').src = src;
            var viewModal = new bootstrap.Modal(document.getElementById('previewImageModal'));
            viewModal.show();
        } else {
            window.open(src, '_blank');
        }
    }

    /* JavaScript Toggle Dropdown Sidebar */
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