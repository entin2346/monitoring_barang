<?php
session_start();

// Cek apakah user sudah login
if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

// Pastikan ID ada di URL
if(!isset($_GET['id'])){
    echo "ID tidak ditemukan";
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Mengambil data dari database
$data = mysqli_query($conn,"SELECT * FROM database_ba WHERE id='$id'");
$d = mysqli_fetch_assoc($data);

if(!$d){
    echo "Data tidak ditemukan";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail BA | I-CALM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-deep: #060814;
            --bg-dark: #090d16;
            --bg-surface: #0f132a;
            --bg-card: #151b3d;
            --primary-glow: #38bdf8;
            --secondary-glow: #818cf8;
            --emerald-glow: #34d399;
            --rose-glow: #f43f5e;
            --text-main: #f8fafc;
            --text-sub: #94a3b8;
            --border-glass: rgba(255, 255, 255, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body { 
            background: radial-gradient(circle at top right, #111638 0%, var(--bg-deep) 60%) !important;
            color: var(--text-main);
            min-height: 100vh;
        }

        .cyber-container {
            max-width: 950px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* HEADER GLASS CARD */
        .cyber-header-card {
            background: rgba(15, 19, 42, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        /* DETAIL TABLE WRAPPER */
        .cyber-table-wrapper {
            background: #0f132a !important; 
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            margin-bottom: 25px;
        }

        .table-cyber-detail {
            width: 100%;
            margin-bottom: 0;
            border-collapse: collapse;
        }

        .table-cyber-detail tr {
            border-bottom: 1px solid rgba(255,255,255,0.06) !important;
        }

        .table-cyber-detail tr:last-child {
            border-bottom: none !important;
        }

        .table-cyber-detail th {
            background-color: #111635 !important;
            color: #94a3b8 !important;
            font-weight: 700;
            padding: 16px 20px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: 280px;
            vertical-align: middle;
            border-right: 1px solid rgba(255,255,255,0.06);
        }

        .table-cyber-detail td {
            padding: 16px 20px;
            font-size: 14px;
            vertical-align: middle;
            color: #e2e8f0 !important;
            background: transparent !important;
        }

        /* BADGES */
        .badge-kategori-masuk { background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.4); padding: 5px 10px; border-radius: 6px; font-weight: 700; }
        .badge-kategori-keluar { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); padding: 5px 10px; border-radius: 6px; font-weight: 700; }
        
        .badge-kondisi-baik { background: #15803d; color: #fff; padding: 4px 10px; border-radius: 6px; font-weight: 700; }
        .badge-kondisi-rusak { background: #b91c1c; color: #fff; padding: 4px 10px; border-radius: 6px; font-weight: 700; }

        /* BUTTONS STYLING */
        .btn-cyber-back { background: #475569; color: #fff; font-weight: 700; border: none; padding: 10px 20px; border-radius: 8px; transition: all 0.2s; }
        .btn-cyber-back:hover { background: #334155; color: #fff; }

        .btn-cyber-edit { background: #fbbf24; color: #1e293b; font-weight: 700; border: none; padding: 10px 20px; border-radius: 8px; transition: all 0.2s; }
        .btn-cyber-edit:hover { background: #d97706; color: #1e293b; }

        .btn-cyber-print { background: #10b981; color: #fff; font-weight: 700; border: none; padding: 10px 20px; border-radius: 8px; transition: all 0.2s; }
        .btn-cyber-print:hover { background: #059669; color: #fff; }
    </style>
</head>
<body>

<div class="cyber-container">

    <div class="cyber-header-card d-flex justify-content-between align-items-center flex-wrap g-3">
        <div>
            <h3 class="fw-bold text-white m-0"><i class="fa-solid fa-file-invoice text-info me-2"></i>Detail Berita Acara</h3>
            <p class="text-muted m-0 small mt-1">I-CALM | Integrated Control and Logistics Monitoring</p>
        </div>
        <div>
            <a href="index.php" class="btn btn-outline-light btn-sm fw-bold px-3">← Kembali ke Database</a>
        </div>
    </div>

    <div class="cyber-table-wrapper">
        <table class="table table-cyber-detail">
            <tr>
                <th>Jenis Berita Acara</th>
                <td>
                    <?php
                    $kategori = strtoupper($d['jenis_berita_acara']);
                    if(strpos($kategori,'MASUK') !== false){ echo "<span class='badge-kategori-masuk'>MASUK</span>"; }
                    elseif(strpos($kategori,'KELUAR') !== false || strpos($kategori,'TERPAKAI') !== false){ echo "<span class='badge-kategori-keluar'>KELUAR</span>"; }
                    else{ echo "<span class='badge bg-secondary text-uppercase'>".$kategori."</span>"; }
                    ?>
                </td>
            </tr>
            <tr>
                <th>Tanggal</th>
                <td style="color: #94a3b8 !important;"><i class="fa-regular fa-calendar me-2"></i><?= (!empty($d['tanggal']) && $d['tanggal'] != '0000-00-00') ? date('d-m-Y', strtotime($d['tanggal'])) : '-'; ?></td>
            </tr>
            <tr>
                <th>Nama Barang / Material</th>
                <td class="fw-bold text-info" style="font-size: 1.1rem;"><?= htmlspecialchars($d['nama_barang']); ?></td>
            </tr>
            <tr>
                <th>Merk / Jenis</th>
                <td><?= htmlspecialchars($d['merk_jenis']); ?></td>
            </tr>
            <tr>
                <th>Jenis Barang</th>
                <td><?= htmlspecialchars($d['jenis_barang']); ?></td>
            </tr>
            <tr>
                <th>Sumber Barang</th>
                <td><?= htmlspecialchars($d['sumber_barang']); ?></td>
            </tr>
            <tr>
                <th>Satuan</th>
                <td><span class="badge bg-dark border border-secondary text-white px-3 py-2"><?= htmlspecialchars($d['satuan']); ?></span></td>
            </tr>
            <tr>
                <th>Jumlah</th>
                <td class="fw-bold fs-5 text-white"><?= number_format($d['jumlah']); ?></td>
            </tr>
            <tr>
                <th>Tujuan</th>
                <td><?= htmlspecialchars($d['tujuan']); ?></td>
            </tr>
            <tr>
                <th>Kondisi Material</th>
                <td>
                    <?php
                    if(strtoupper($d['kondisi_material']) == 'BAIK'){
                        echo "<span class='badge-kondisi-baik'><i class='fa-solid fa-circle-check me-1'></i>BAIK</span>";
                    }elseif(strtoupper($d['kondisi_material']) == 'RUSAK'){
                        echo "<span class='badge-kondisi-rusak'><i class='fa-solid fa-circle-xmark me-1'></i>RUSAK</span>";
                    }else{
                        echo "<span class='badge bg-warning text-dark'>".$d['kondisi_material']."</span>";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>No Seri</th>
                <td style="color: #34d399 !important; font-family: monospace; font-weight: 600;"><?= htmlspecialchars($d['no_seri']); ?></td>
            </tr>
            <tr>
                <th>Vendor / Pemasok</th>
                <td><?= htmlspecialchars($d['asal_barang_vendor']); ?></td>
            </tr>

            <?php if(!empty($d['kategori_material'])){ ?>
            <tr>
                <th>Kategori Material</th>
                <td><?= htmlspecialchars($d['kategori_material']); ?></td>
            </tr>
            <?php } ?>

            <tr>
                <th>Berita Acara</th>
                <td><?= htmlspecialchars($d['berita_acara']); ?></td>
            </tr>

            <tr>
                <th>Dokumentasi BA Kembali</th>
                <td>
                    <?php if(!empty($d['dokumentasi_ba_kembali'])){ ?>
                        <div class="d-inline-flex gap-2">
                            <a href="../uploads/<?= urlencode($d['dokumentasi_ba_kembali']); ?>" target="_blank" class="btn btn-info btn-sm fw-bold text-dark">
                                <i class="fa-solid fa-image me-1"></i> Lihat Dokumentasi
                            </a>
                            <a href="../uploads/<?= urlencode($d['dokumentasi_ba_kembali']); ?>" download class="btn btn-success btn-sm fw-bold">
                                <i class="fa-solid fa-download me-1"></i> Download
                            </a>
                        </div>
                    <?php } else { ?>
                        <span class="text-danger small fw-bold"><i class="fa-solid fa-circle-exclamation me-1"></i> Belum ada dokumentasi</span>
                    <?php } ?>
                </td>
            </tr>

            <tr>
                <th>File Lampiran BA</th>
                <td>
                    <?php if(!empty($d['file_ba'])){ ?>
                        <div class="mb-2 small text-muted">
                            <i class="fa-solid fa-paperclip me-1"></i> <b>Nama File:</b> <?= htmlspecialchars($d['file_ba']); ?>
                        </div>
                        <div class="d-inline-flex gap-2">
                            <a href="../uploads/<?= urlencode($d['file_ba']); ?>" target="_blank" class="btn btn-primary btn-sm fw-bold">
                                <i class="fa-solid fa-file-lines me-1"></i> Lihat File
                            </a>
                            <a href="../uploads/<?= urlencode($d['file_ba']); ?>" download class="btn btn-success btn-sm fw-bold">
                                <i class="fa-solid fa-download me-1"></i> Download
                            </a>
                        </div>
                    <?php } else { ?>
                        <span class="text-danger small fw-bold"><i class="fa-solid fa-circle-exclamation me-1"></i> Belum ada file lampiran</span>
                    <?php } ?>
                </td>
            </tr>

            <tr>
                <th>Keterangan</th>
                <td><?= htmlspecialchars($d['keterangan']); ?></td>
            </tr>
            <tr>
                <th>Keterangan Tambahan</th>
                <td><?= htmlspecialchars($d['keterangan_tambahan']); ?></td>
            </tr>
            <tr>
                <th>TUG 5</th>
                <td style="font-weight: 600; color: #fbbf24 !important;"><?= htmlspecialchars($d['tug5']); ?></td>
            </tr>
        </table>
    </div>

    <div class="d-flex gap-2 justify-content-start align-items-center flex-wrap pb-5">
        <a href="index.php" class="btn btn-cyber-back">
            <i class="fa-solid fa-arrow-left me-2"></i>Kembali
        </a>

        <a href="edit.php?id=<?= $d['id']; ?>" class="btn btn-cyber-edit">
            <i class="fa-solid fa-pen-to-square me-2"></i>Edit Data
        </a>

        <a href="kartu_gantung.php?id=<?= $d['id']; ?>" target="_blank" class="btn btn-cyber-print">
            <i class="fa-solid fa-print me-2"></i>Cetak Kartu Gantung
        </a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>