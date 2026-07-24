<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}
include "../../config/koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = mysqli_query($conn, "SELECT * FROM ex_bongkaran WHERE id = $id");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='ex_bongkaran.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Detail Ex Bongkaran</title>
    
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
            --bg-input-like: #f8fafc;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: var(--bg-body); color: var(--text-main); min-height: 100vh; }

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

        /* CONTENT & CONTAINER STYLE */
        .content { margin-left: 260px; position: relative; width: calc(100% - 260px); }
        .navbar-custom { background: #ffffff; padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999; }
        .main-body-wrapper { padding: 40px; }
        
        .glass-detail-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 40px; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04); }
        
        /* FORM SIMULATION FIELDS STYLE (LIKE IMAGE 2) */
        .detail-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; margin-bottom: 6px; }
        .detail-value { background-color: var(--bg-input-like); border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px; font-size: 0.9rem; color: var(--text-main); min-height: 48px; display: flex; align-items: center; word-break: break-word; }
        .detail-value.textarea-like { align-items: flex-start; display: block; min-height: 70px; }

        .btn-back-custom { border-radius: 8px; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 10px 24px; font-size: 0.9rem; font-weight: 600; color: #475569; text-decoration: none; transition: all 0.2s; display: inline-flex; align-items: center; }
        .btn-back-custom:hover { background: #e2e8f0; color: #1e293b; }

        .img-preview { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #cbd5e1; margin-right: 8px; margin-bottom: 8px; transition: transform 0.2s; }
        .img-preview:hover { transform: scale(1.05); }

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
        <a href="/monitoring_barang/kategori/ex_bongkaran/ex_bongkaran.php" class="active-menu">Ex Bongkaran</a>
        <a href="/monitoring_barang/kategori/pre_memory/pre_memory.php">Pre Memory</a>
        <a href="/monitoring_barang/kategori/peminjaman/peminjaman.php">Peminjaman</a>
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
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Kategori: Ex Bongkaran / Informasi Detail</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="glass-detail-card">
            <h4 class="fw-bold mb-4" style="color: #0f172a; letter-spacing: -0.02em;"><i class="fa-solid fa-circle-info text-primary me-2"></i>Lembar Detail Item Ex Bongkaran</h4>
            
            <div class="container-fluid px-0">
                <!-- Baris 1 -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="detail-label">Unit</div>
                        <div class="detail-value"><?= htmlspecialchars($data['unit'] ?? '-'); ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-label">MTU</div>
                        <div class="detail-value"><?= htmlspecialchars($data['mtu'] ?? '-'); ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-label">Tegangan</div>
                        <div class="detail-value"><?= htmlspecialchars($data['tegangan'] ?? '-'); ?></div>
                    </div>
                </div>

                <!-- Baris 2 -->
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <div class="detail-label">Nama Barang / Material</div>
                        <div class="detail-value fw-bold text-primary"><?= htmlspecialchars($data['nama_material'] ?? '-'); ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-label">Jumlah / Satuan</div>
                        <div class="detail-value">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 me-2" style="font-size: 0.85rem;">
                                <?= number_format((float)($data['jumlah']??0)); ?>
                            </span>
                            <?= htmlspecialchars($data['satuan'] ?? ''); ?>
                        </div>
                    </div>
                </div>

                <!-- Baris 3 -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="detail-label">Merk / Tipe</div>
                        <div class="detail-value"><?= htmlspecialchars($data['merk_tipe'] ?? '-'); ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-label">No Seri</div>
                        <div class="detail-value"><?= htmlspecialchars($data['no_seri'] ?? '-'); ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-label">Gardu Induk</div>
                        <div class="detail-value"><?= htmlspecialchars($data['gardu_induk'] ?? '-'); ?></div>
                    </div>
                </div>

                <!-- Baris 4 -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="detail-label">Lokasi Asal Eks Bongkaran</div>
                        <div class="detail-value"><?= htmlspecialchars($data['lokasi_asal_eks_bongkaran'] ?? '-'); ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-label">No Kontrak Penggantian</div>
                        <div class="detail-value"><?= htmlspecialchars($data['no_kontrak_penggantian'] ?? '-'); ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-label">Judul Kontrak Penggantian</div>
                        <div class="detail-value"><?= htmlspecialchars($data['judul_kontrak_penggantian'] ?? '-'); ?></div>
                    </div>
                </div>

                <!-- Baris 5 -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="detail-label">Nilai Buku (Rp)</div>
                        <div class="detail-value">Rp <?= number_format((float)($data['nilai_buku']??0)); ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-label">Berat (Kg)</div>
                        <div class="detail-value"><?= htmlspecialchars($data['berat'] ?? '-'); ?> Kg</div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-label">Lokasi Penempatan Material</div>
                        <div class="detail-value"><?= htmlspecialchars($data['lokasi_penyimpanan'] ?? '-'); ?></div>
                    </div>
                </div>

                <!-- Baris 6 -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="detail-label">Kondisi</div>
                        <div class="detail-value"><?= htmlspecialchars($data['kondisi'] ?? '-'); ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-label">Status</div>
                        <div class="detail-value"><?= htmlspecialchars($data['status'] ?? '-'); ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-label">Keterangan Waktu Pembongkaran</div>
                        <div class="detail-value"><?= htmlspecialchars($data['ket_waktu_pembongkaran'] ?? '-'); ?></div>
                    </div>
                </div>

                <!-- Baris 7 Textarea-like Fields -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="detail-label">Justifikasi Kondisi</div>
                        <div class="detail-value textarea-like"><?= nl2br(htmlspecialchars($data['justifikasi_kondisi'] ?? '-')); ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Keterangan Ex Bongkaran (Penggantian/Uprating)</div>
                        <div class="detail-value textarea-like"><?= nl2br(htmlspecialchars($data['keterangan_ex_bongkaran'] ?? '-')); ?></div>
                    </div>
                </div>

                <!-- Baris 8 -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="detail-label">Kelengkapan Aksesoris</div>
                        <div class="detail-value textarea-like"><?= nl2br(htmlspecialchars($data['kelengkapan_aksesoris'] ?? '-')); ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Keterangan Kelengkapan Aksesoris</div>
                        <div class="detail-value textarea-like"><?= nl2br(htmlspecialchars($data['ket_kelengkapan_aksesoris'] ?? '-')); ?></div>
                    </div>
                </div>

                <!-- Baris 9 -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="detail-label">No AT</div>
                        <div class="detail-value"><?= htmlspecialchars($data['no_at'] ?? '-'); ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-label">Nilai Perolehan (Rp)</div>
                        <div class="detail-value">Rp <?= number_format((float)($data['nilai_perolehan']??0)); ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-label">Techidentno</div>
                        <div class="detail-value"><?= htmlspecialchars($data['techidentno'] ?? '-'); ?></div>
                    </div>
                </div>

                <!-- Baris 10 -->
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <div class="detail-label">UPT</div>
                        <div class="detail-value"><?= htmlspecialchars($data['upt'] ?? '-'); ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="detail-label">Tahun Pembuatan</div>
                        <div class="detail-value"><?= htmlspecialchars($data['tahun_pembuatan'] ?? '-'); ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="detail-label">Umur Operasi</div>
                        <div class="detail-value"><?= htmlspecialchars($data['umur_operasi'] ?? '-'); ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="detail-label">Umur Simpan</div>
                        <div class="detail-value"><?= htmlspecialchars($data['umur_simpan'] ?? '-'); ?></div>
                    </div>
                </div>

                <!-- Baris 11 -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="detail-label">Funloct</div>
                        <div class="detail-value"><?= htmlspecialchars($data['funloct'] ?? '-'); ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-label">Katalog Mara</div>
                        <div class="detail-value"><?= htmlspecialchars($data['katalog_mara'] ?? '-'); ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-label">No Aset</div>
                        <div class="detail-value"><?= htmlspecialchars($data['no_aset'] ?? '-'); ?></div>
                    </div>
                </div>

                <!-- Berita Acara (Dokumen Pendukung) -->
                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <div class="detail-label">Tautan Dokumen Berita Acara</div>
                        <div class="detail-value textarea-like">
                            <div class="d-flex flex-wrap gap-3">
                                <?php if(!empty($data['link_ba_pemindahan'])): ?><a href="<?= htmlspecialchars($data['link_ba_pemindahan']); ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-file-pdf me-1"></i>BA Pemindahan</a><?php endif; ?>
                                <?php if(!empty($data['link_ba_pemanfaatan'])): ?><a href="<?= htmlspecialchars($data['link_ba_pemanfaatan']); ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-file-pdf me-1"></i>BA Pemanfaatan</a><?php endif; ?>
                                <?php if(!empty($data['link_hasil_uji'])): ?><a href="<?= htmlspecialchars($data['link_hasil_uji']); ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-vial me-1"></i>Hasil Uji</a><?php endif; ?>
                                <?php if(!empty($data['link_ba_penggantian'])): ?><a href="<?= htmlspecialchars($data['link_ba_penggantian']); ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-file-pdf me-1"></i>BA Penggantian</a><?php endif; ?>
                                <?php if(empty($data['link_ba_pemindahan']) && empty($data['link_ba_pemanfaatan']) && empty($data['link_hasil_uji']) && empty($data['link_ba_penggantian'])): ?>
                                    <span class="text-muted" style="font-size: 0.85rem;"><i class="fa-solid fa-link-slash me-1"></i>Belum ada file atau dokumen tautan pendukung yang diunggah.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Baris Galeri File Foto Upload Multi-Image -->
                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <div class="detail-label">Berkas / Foto Terlampir Saat Ini</div>
                        <div class="detail-value textarea-like">
                            <div class="row">
                                <!-- Bagian Nameplate -->
                                <div class="col-md-6 border-end">
                                    <span class="d-block fw-semibold mb-2 text-secondary" style="font-size: 0.8rem;">FOTO NAMEPLATE:</span>
                                    <?php 
                                    $np_images = json_decode($data['foto_nameplate'] ?? '[]', true);
                                    if (!empty($np_images) && is_array($np_images)): 
                                        foreach ($np_images as $img):
                                            $file_path = "upload/" . $img;
                                            if (file_exists($file_path)):
                                    ?>
                                                <a href="<?= $file_path; ?>" target="_blank">
                                                    <img src="<?= $file_path; ?>" class="img-preview" alt="Nameplate">
                                                </a>
                                    <?php 
                                            endif;
                                        endforeach;
                                    else:
                                        echo '<span class="text-muted" style="font-size:0.85rem;">Tidak ada foto nameplate</span>';
                                    endif; 
                                    ?>
                                </div>
                                <!-- Bagian Material -->
                                <div class="col-md-6 p-md-3 pt-3">
                                    <span class="d-block fw-semibold mb-2 text-secondary" style="font-size: 0.8rem;">FOTO MATERIAL:</span>
                                    <?php 
                                    $mat_images = json_decode($data['foto_material'] ?? '[]', true);
                                    if (!empty($mat_images) && is_array($mat_images)): 
                                        foreach ($mat_images as $img):
                                            $file_path = "upload/" . $img;
                                            if (file_exists($file_path)):
                                    ?>
                                                <a href="<?= $file_path; ?>" target="_blank">
                                                    <img src="<?= $file_path; ?>" class="img-preview" alt="Material">
                                                </a>
                                    <?php 
                                            endif;
                                        endforeach;
                                    else:
                                        echo '<span class="text-muted" style="font-size:0.85rem;">Tidak ada foto material</span>';
                                    endif; 
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Keterangan Bebas -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="detail-label">Keterangan</div>
                        <div class="detail-value textarea-like"><?= nl2br(htmlspecialchars($data['keterangan'] ?? '-')); ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Keterangan Tambahan</div>
                        <div class="detail-value textarea-like"><?= nl2br(htmlspecialchars($data['keterangan_tambahan'] ?? '-')); ?></div>
                    </div>
                </div>

                <!-- Informasi Update -->
                <div class="mb-4 text-end text-muted" style="font-size: 0.8rem;">
                    <i class="fa-regular fa-clock me-1"></i>Terakhir Diperbarui: <?= $data['tanggal_update_terakhir'] ?? '-'; ?>
                </div>

            </div>

            <div class="pt-2">
                <a href="ex_bongkaran.php" class="btn-back-custom"><i class="fa-solid fa-arrow-left me-2"></i>Kembali ke List</a>
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