<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

// Sanitasi parameter ID
if(!isset($_GET['id'])){
    echo "ID tidak ditemukan";
    exit;
}
$id = mysqli_real_escape_string($conn, $_GET['id']);

// Ambil data lama untuk ditampilkan di form
$data = mysqli_query($conn, "SELECT * FROM database_ba WHERE id='$id'");
$d = mysqli_fetch_assoc($data);

if(!$d){
    echo "Data tidak ditemukan";
    exit;
}

if(isset($_POST['update'])){

    $jenis_berita_acara = mysqli_real_escape_string($conn, $_POST['jenis_berita_acara']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $merk_jenis = mysqli_real_escape_string($conn, $_POST['merk_jenis']);
    $jenis_barang = mysqli_real_escape_string($conn, $_POST['jenis_barang']);
    $sumber_barang = mysqli_real_escape_string($conn, $_POST['sumber_barang']);
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);
    $jumlah = (int)$_POST['jumlah'];
    $no_seri = mysqli_real_escape_string($conn, $_POST['no_seri']);
    $asal_barang_vendor = mysqli_real_escape_string($conn, $_POST['asal_barang_vendor']);
    $kategori_material = mysqli_real_escape_string($conn, $_POST['kategori_material']);
    $tujuan = mysqli_real_escape_string($conn, $_POST['tujuan']);
    $kondisi_material = mysqli_real_escape_string($conn, $_POST['kondisi_material']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

    // Proses Upload File
    $file_sql = "";
    if(isset($_FILES['file_ba']) && $_FILES['file_ba']['error'] == 0){
        $folder = "../uploads/";
        if(!is_dir($folder)){
            mkdir($folder, 0777, true);
        }

        $nama_file = time() . "_" . $_FILES['file_ba']['name'];

        if(move_uploaded_file($_FILES['file_ba']['tmp_name'], $folder . $nama_file)){
            $file_sql = ", file_ba='$nama_file'";
        } else {
            die("Gagal upload file. Pastikan folder ../uploads/ memiliki izin tulis (write permission).");
        }
    }

    $sql = "
    UPDATE database_ba SET
        jenis_berita_acara='$jenis_berita_acara',
        tanggal='$tanggal',
        nama_barang='$nama_barang',
        merk_jenis='$merk_jenis',
        jenis_barang='$jenis_barang',
        sumber_barang='$sumber_barang',
        satuan='$satuan',
        jumlah='$jumlah',
        no_seri='$no_seri',
        asal_barang_vendor='$asal_barang_vendor',
        kategori_material='$kategori_material',
        tujuan='$tujuan',
        kondisi_material='$kondisi_material',
        keterangan='$keterangan'
        $file_sql
    WHERE id='$id'
    ";

    if(mysqli_query($conn, $sql)){
        echo "<script>
            alert('Data berhasil diupdate');
            window.location='detail.php?id=$id';
        </script>";
    } else {
        die("Error Query Database: " . mysqli_error($conn));
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit BA | I-CALM</title>
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
            max-width: 850px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* HEADER BOX */
        .cyber-header-card {
            background: rgba(15, 19, 42, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        /* FORM PANEL CONTAINER */
        .cyber-form-card {
            background: #0f132a !important;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        /* INPUT CONTROLS RE-STYLING */
        .cyber-form-card label {
            color: var(--text-sub);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            margin-bottom: 8px;
            display: inline-block;
        }

        .cyber-input {
            background-color: #090d16 !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #ffffff !important;
            border-radius: 8px;
            padding: 11px 14px;
            font-size: 0.95rem;
            transition: all 0.2s ease-in-out;
        }

        .cyber-input:focus {
            border-color: var(--primary-glow) !important;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.25) !important;
            background-color: #0d1321 !important;
        }

        .cyber-input::placeholder {
            color: #475569;
        }

        /* SELECT OVERRIDE ARROW OPTIONS */
        select.cyber-input option {
            background-color: #0f132a;
            color: #fff;
        }

        /* ACTIONS BUTTONS */
        .btn-cyber-save {
            background: #fbbf24;
            color: #1e293b;
            font-weight: 700;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-cyber-save:hover {
            background: #d97706;
            color: #1e293b;
            box-shadow: 0 0 15px rgba(251, 191, 36, 0.35);
        }

        .btn-cyber-cancel {
            background: #475569;
            color: #fff;
            font-weight: 700;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-cyber-cancel:hover {
            background: #334155;
            color: #fff;
        }
    </style>
</head>
<body>

<div class="cyber-container">

    <div class="cyber-header-card d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h3 class="fw-bold text-white m-0"><i class="fa-solid fa-user-pen text-warning me-2"></i>Edit Berita Acara</h3>
            <p class="text-muted m-0 small mt-1">I-CALM | Integrated Control and Logistics Monitoring</p>
        </div>
        <div>
            <a href="index.php" class="btn btn-outline-light btn-sm fw-bold px-3">← Batal & Keluar</a>
        </div>
    </div>

    <div class="cyber-form-card mb-5">
        <form method="POST" enctype="multipart/form-data">
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label>Jenis Berita Acara</label>
                    <input type="text" name="jenis_berita_acara" class="form-control cyber-input" value="<?= htmlspecialchars($d['jenis_berita_acara']); ?>" required>
                </div>

                <div class="col-md-6 mb-4">
                    <label>Tanggal Dokumen</label>
                    <input type="date" name="tanggal" class="form-control cyber-input" value="<?= $d['tanggal']; ?>" required>
                </div>
            </div>

            <div class="mb-4">
                <label>Nama Barang / Material</label>
                <input type="text" name="nama_barang" class="form-control cyber-input" value="<?= htmlspecialchars($d['nama_barang']); ?>" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label>Merk / Jenis</label>
                    <input type="text" name="merk_jenis" class="form-control cyber-input" value="<?= htmlspecialchars($d['merk_jenis']); ?>">
                </div>

                <div class="col-md-6 mb-4">
                    <label>Jenis Barang</label>
                    <input type="text" name="jenis_barang" class="form-control cyber-input" value="<?= htmlspecialchars($d['jenis_barang']); ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label>Sumber Barang</label>
                    <input type="text" name="sumber_barang" class="form-control cyber-input" value="<?= htmlspecialchars($d['sumber_barang']); ?>">
                </div>

                <div class="col-md-3 mb-4">
                    <label>Satuan</label>
                    <input type="text" name="satuan" class="form-control cyber-input" value="<?= htmlspecialchars($d['satuan']); ?>">
                </div>

                <div class="col-md-3 mb-4">
                    <label>Jumlah Volume</label>
                    <input type="number" name="jumlah" class="form-control cyber-input" value="<?= $d['jumlah']; ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label>Nomor Seri Komponen</label>
                    <input type="text" name="no_seri" class="form-control cyber-input" style="font-family: monospace;" value="<?= htmlspecialchars($d['no_seri']); ?>">
                </div>

                <div class="col-md-6 mb-4">
                    <label>Pemasok / Asal Material (Vendor)</label>
                    <input type="text" name="asal_barang_vendor" class="form-control cyber-input" value="<?= htmlspecialchars($d['asal_barang_vendor']); ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label>Kategori Material</label>
                    <select name="kategori_material" class="form-select cyber-input">
                        <option value="Material Gardu" <?= ($d['kategori_material']=='Material Gardu')?'selected':''; ?>>Material Gardu</option>
                        <option value="Material Proteksi" <?= ($d['kategori_material']=='Material Proteksi')?'selected':''; ?>>Material Proteksi</option>
                        <option value="Material Kabel" <?= ($d['kategori_material']=='Material Kabel')?'selected':''; ?>>Material Kabel</option>
                        <option value="Material Trafo" <?= ($d['kategori_material']=='Material Trafo')?'selected':''; ?>>Material Trafo</option>
                        <option value="Alat Kerja" <?= ($d['kategori_material']=='Alat Kerja')?'selected':''; ?>>Alat Kerja</option>
                        <option value="Alat Uji" <?= ($d['kategori_material']=='Alat Uji')?'selected':''; ?>>Alat Uji</option>
                        <option value="Lainnya" <?= ($d['kategori_material']=='Lainnya')?'selected':''; ?>>Lainnya</option>
                    </select>
                </div>

                <div class="col-md-6 mb-4">
                    <label>Kondisi Fisik Material</label>
                    <select name="kondisi_material" class="form-select cyber-input">
                        <option value="BAIK" <?= ($d['kondisi_material']=='BAIK')?'selected':''; ?>>BAIK</option>
                        <option value="RUSAK" <?= ($d['kondisi_material']=='RUSAK')?'selected':''; ?>>RUSAK</option>
                        <option value="PERBAIKAN" <?= ($d['kondisi_material']=='PERBAIKAN')?'selected':''; ?>>PERBAIKAN</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label>Lokasi / Unit Tujuan</label>
                <input type="text" name="tujuan" class="form-control cyber-input" value="<?= htmlspecialchars($d['tujuan']); ?>">
            </div>

            <div class="mb-4">
                <label>Keterangan Deskriptif</label>
                <textarea name="keterangan" class="form-control cyber-input" rows="4"><?= htmlspecialchars($d['keterangan']); ?></textarea>
            </div>

            <div class="mb-5">
                <label>Dokumen / File Lampiran BA</label>
                <?php if(!empty($d['file_ba'])){ ?>
                    <div class="mb-3 d-flex align-items-center gap-2 bg-dark p-2 rounded border border-secondary" style="max-width: max-content;">
                        <span class="small text-sub text-truncate px-2"><i class="fa-solid fa-file-pdf me-2"></i><?= htmlspecialchars($d['file_ba']); ?></span>
                        <a href="../uploads/<?= $d['file_ba']; ?>" target="_blank" class="btn btn-info btn-sm fw-bold text-dark px-2 py-1" style="font-size: 11px;">📄 Lihat</a>
                    </div>
                <?php } ?>
                <input type="file" name="file_ba" class="form-control cyber-input" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
                <small class="text-muted d-block mt-1">Format: PDF/Gambar/Office. Kosongkan apabila file lampiran tidak ingin diubah.</small>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="update" class="btn btn-cyber-save">
                    <i class="fa-solid fa-cloud-arrow-up me-2"></i>Simpan Perubahan
                </button>
                <a href="index.php" class="btn btn-cyber-cancel">
                    <i class="fa-solid fa-xmark me-2"></i>Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>