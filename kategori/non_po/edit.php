<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../../login/index.php");
    exit;
}
// PERBAIKAN PATH: Menggunakan ../../ agar keluar dari 2 sub-folder (non_po dan kategori)
include "../../config/koneksi.php";

$id = $_GET['id'] ?? '';
$get_data = mysqli_query($conn, "SELECT * FROM material_gudang WHERE id = '$id'");
$d = mysqli_fetch_assoc($get_data);

if(!$d) {
    echo "<script>alert('Data tidak valid!'); window.location.href='non_po.php';</script>";
    exit;
}

if(isset($_POST['update'])){
    $jenis_ba = mysqli_real_escape_string($conn, $_POST['jenis_ba']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $nama_material = mysqli_real_escape_string($conn, $_POST['nama_material']);
    $merk_jenis = mysqli_real_escape_string($conn, $_POST['merk_jenis']);
    $jenis_barang = mysqli_real_escape_string($conn, $_POST['jenis_barang']);
    $sumber_barang = mysqli_real_escape_string($conn, $_POST['sumber_barang']);
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);
    $jumlah = (int)$_POST['jumlah'];
    $tujuan = mysqli_real_escape_string($conn, $_POST['tujuan']);
    $kondisi = mysqli_real_escape_string($conn, $_POST['kondisi']);
    $vendor = mysqli_real_escape_string($conn, $_POST['vendor']);
    $berita_acara = mysqli_real_escape_string($conn, $_POST['berita_acara']);

    $query = "UPDATE material_gudang SET 
                jenis_ba='$jenis_ba', tanggal='$tanggal', nama_material='$nama_material', 
                merk_jenis='$merk_jenis', jenis_barang='$jenis_barang', sumber_barang='$sumber_barang', 
                satuan='$satuan', jumlah='$jumlah', tujuan='$tujuan', kondisi='$kondisi', 
                vendor='$vendor', berita_acara='$berita_acara' 
              WHERE id='$id'";

    if(mysqli_query($conn, $query)){
        echo "<script>alert('Data berhasil diperbarui!'); window.location.href='non_po.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data: ".mysqli_error($conn)."');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>I-CALM | Edit Non PO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background: #f4f7fc; font-family: 'Plus Jakarta Sans', sans-serif; padding: 40px 0; }
        .card-custom { background: #ffffff; border: 1px solid rgba(148, 163, 184, 0.12); border-radius: 16px; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom p-5 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold m-0 text-warning">Edit Material Non PO</h4>
                    <a href="non_po.php" class="btn btn-outline-secondary btn-sm fw-bold">Batal</a>
                </div>
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label fw-bold">Jenis BA</label><input type="text" name="jenis_ba" class="form-control" value="<?= htmlspecialchars($d['jenis_ba'] ?? ''); ?>" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Tanggal</label><input type="date" name="tanggal" class="form-control" value="<?= $d['tanggal'] ?? ''; ?>" required></div>
                        <div class="col-md-12"><label class="form-label fw-bold">Nama Barang</label><input type="text" name="nama_material" class="form-control" value="<?= htmlspecialchars($d['nama_material'] ?? ''); ?>" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Merk / Jenis</label><input type="text" name="merk_jenis" class="form-control" value="<?= htmlspecialchars($d['merk_jenis'] ?? ''); ?>"></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Jenis Barang</label><input type="text" name="jenis_barang" class="form-control" value="<?= htmlspecialchars($d['jenis_barang'] ?? ''); ?>"></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Sumber Barang</label><input type="text" name="sumber_barang" class="form-control" value="<?= htmlspecialchars($d['sumber_barang'] ?? ''); ?>"></div>
                        <div class="col-md-3"><label class="form-label fw-bold">Satuan</label><input type="text" name="satuan" class="form-control" value="<?= htmlspecialchars($d['satuan'] ?? ''); ?>" required></div>
                        <div class="col-md-3"><label class="form-label fw-bold">Jumlah</label><input type="number" name="jumlah" class="form-control" value="<?= $d['jumlah'] ?? 0; ?>" min="0" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Tujuan</label><input type="text" name="tujuan" class="form-control" value="<?= htmlspecialchars($d['tujuan'] ?? ''); ?>"></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Kondisi</label><input type="text" name="kondisi" class="form-control" value="<?= htmlspecialchars($d['kondisi'] ?? ''); ?>"></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Vendor</label><input type="text" name="vendor" class="form-control" value="<?= htmlspecialchars($d['vendor'] ?? ''); ?>"></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Berita Acara</label><input type="text" name="berita_acara" class="form-control" value="<?= htmlspecialchars($d['berita_acara'] ?? ''); ?>"></div>
                    </div>
                    <button type="submit" name="update" class="btn btn-warning w-100 mt-4 fw-bold text-white py-2" style="border-radius: 10px;">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>