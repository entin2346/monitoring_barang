<?php
session_start();
include "../config/koneksi.php";

// Proses Import
if(isset($_POST['import'])){
    if($_FILES['file']['error'] == 0){
        $file = $_FILES['file']['tmp_name'];
        $handle = fopen($file, "r");

        // Lewati 2 baris awal header CSV
        fgetcsv($handle, 10000, ",");
        fgetcsv($handle, 10000, ",");

        while(($data = fgetcsv($handle, 10000, ",")) !== FALSE){
            if(count(array_filter($data)) == 0) continue;
            if(trim($data[3] ?? '') == '') continue;

            $no_urut            = mysqli_real_escape_string($conn, trim($data[0] ?? ''));
            $jenis_berita_acara = mysqli_real_escape_string($conn, trim($data[1] ?? ''));
            
            // Konversi Tanggal
            $tanggal_csv = trim($data[2] ?? '');
            $tanggal = NULL;
            if(!empty($tanggal_csv)){
                $date = DateTime::createFromFormat('d-M-y', $tanggal_csv);
                $tanggal = ($date) ? $date->format('Y-m-d') : NULL;
            }

            $nama_barang            = mysqli_real_escape_string($conn, trim($data[3] ?? ''));
            $merk_jenis             = mysqli_real_escape_string($conn, trim($data[4] ?? ''));
            $jenis_barang           = mysqli_real_escape_string($conn, trim($data[5] ?? ''));
            $sumber_barang          = mysqli_real_escape_string($conn, trim($data[6] ?? ''));
            $satuan                 = mysqli_real_escape_string($conn, trim($data[7] ?? ''));
            $jumlah                 = (int)($data[8] ?? 0);
            $tujuan                 = mysqli_real_escape_string($conn, trim($data[9] ?? ''));
            $kondisi_material       = mysqli_real_escape_string($conn, trim($data[10] ?? ''));
            $no_seri                = mysqli_real_escape_string($conn, trim($data[11] ?? ''));
            $asal_barang_vendor     = mysqli_real_escape_string($conn, trim($data[12] ?? ''));
            $berita_acara           = mysqli_real_escape_string($conn, trim($data[13] ?? ''));
            $dokumentasi_ba_kembali = mysqli_real_escape_string($conn, trim($data[14] ?? ''));
            $keterangan             = mysqli_real_escape_string($conn, trim($data[15] ?? ''));
            $keterangan_tambahan    = mysqli_real_escape_string($conn, trim($data[16] ?? ''));
            $tug5                   = mysqli_real_escape_string($conn, trim($data[17] ?? ''));

            $sql = "INSERT INTO database_ba VALUES (NULL, '$no_urut', '$jenis_berita_acara', ".($tanggal ? "'$tanggal'" : "NULL").", '$nama_barang', '$merk_jenis', '$jenis_barang', '$sumber_barang', '$satuan', '$jumlah', '$tujuan', '$kondisi_material', '$no_seri', '$asal_barang_vendor', '$berita_acara', '$dokumentasi_ba_kembali', '$keterangan', '$keterangan_tambahan', '$tug5')";
            mysqli_query($conn, $sql);
        }
        fclose($handle);
        echo "<script>alert('Import Database BA Berhasil'); window.location='import.php';</script>";
    } else {
        echo "<script>alert('Pilih file CSV terlebih dahulu!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Database BA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background:#f4f6f9; }
        .import-card { max-width:800px; margin:auto; border:none; border-radius:20px; overflow:hidden; }
        .header-import { background:linear-gradient(135deg,#0d6efd,#0a58ca); color:white; padding:30px; text-align:center; }
        .upload-box { border:2px dashed #0d6efd; border-radius:15px; padding:40px; text-align:center; background:#f8f9fa; }
        .info-box { background:#fff3cd; border-left:5px solid #ffc107; padding:15px; border-radius:10px; }
        .btn-import { padding:12px 30px; font-weight:bold; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="card shadow-lg import-card">
        <div class="header-import">
            <h2><i class="fa-solid fa-file-csv"></i> Import Database BA</h2>
            <p class="mb-0">Monitoring Distribusi Alat & Material UPT Makassar</p>
        </div>

        <div class="card-body p-4">
            <?php
            $total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM database_ba"));
            ?>
            <div class="row mb-4">
                <div class="col-md-6"><div class="card bg-success text-white text-center p-3"><h6>Total Data BA Saat Ini</h6><h2><?= number_format($total['total']); ?></h2></div></div>
                <div class="col-md-6"><div class="card bg-primary text-white text-center p-3"><h6>Status Database</h6><h2>AKTIF</h2></div></div>
            </div>

            <div class="info-box mb-4">
                <b>Petunjuk Import :</b>
                <ul class="mb-0 mt-2">
                    <li>Format file harus CSV</li>
                    <li>Gunakan data dari sheet Database BA</li>
                    <li>Pastikan kolom tidak berubah</li>
                    <li>Tanggal otomatis dikonversi ke format MySQL</li>
                </ul>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div class="upload-box mb-4">
                    <i class="fa-solid fa-cloud-arrow-up fa-4x text-primary mb-3"></i>
                    <h5>Pilih File CSV</h5>
                    <input type="file" name="file" class="form-control" accept=".csv" required>
                </div>
                <div class="text-center">
                    <button type="submit" name="import" class="btn btn-success btn-import"><i class="fa-solid fa-upload"></i> Import Database BA</button>
                    <a href="../dashboard/index.php" class="btn btn-secondary btn-import"><i class="fa-solid fa-house"></i> Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>