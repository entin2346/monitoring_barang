<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../../login/index.php");
    exit;
}
include "../../config/koneksi.php";

$id = $_GET['id'] ?? '';
$fetch_query = mysqli_query($conn, "SELECT * FROM material_gudang WHERE id = '$id' AND jenis_kategori = 'ex_bongkaran'");
$d = mysqli_fetch_assoc($fetch_query);

if (!$d) {
    die("Data tidak ditemukan.");
}

if (isset($_POST['update'])) {
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

    $update_query = "UPDATE material_gudang SET 
                        jenis_ba = '$jenis_ba', tanggal = '$tanggal', nama_material = '$nama_material', 
                        merk_jenis = '$merk_jenis', jenis_barang = '$jenis_barang', sumber_barang = '$sumber_barang', 
                        satuan = '$satuan', jumlah = '$jumlah', tujuan = '$tujuan', kondisi = '$kondisi', 
                        vendor = '$vendor', berita_acara = '$berita_acara' 
                    WHERE id = '$id' AND jenis_kategori = 'ex_bongkaran'";

    if (mysqli_query($conn, $update_query)) {
        header("Location: ex_bongkaran.php");
        exit;
    } else {
        $error = "Gagal memperbarui data: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Data Ex Bongkaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f4f7fc; font-family: 'Plus Jakarta Sans', sans-serif; padding: 40px 0; }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card p-4">
                <h4 class="fw-bold text-warning mb-4">Edit Data Material Ex Bongkaran</h4>
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?= $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis BA</label>
                            <input type="text" name="jenis_ba" class="form-control" value="<?= htmlspecialchars($d['jenis_ba'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($d['tanggal'] ?? ''); ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nama Barang / Material</label>
                            <input type="text" name="nama_material" class="form-control" value="<?= htmlspecialchars($d['nama_material'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Merk / Jenis</label>
                            <input type="text" name="merk_jenis" class="form-control" value="<?= htmlspecialchars($d['merk_jenis'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Barang</label>
                            <input type="text" name="jenis_barang" class="form-control" value="<?= htmlspecialchars($d['jenis_barang'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Sumber Barang</label>
                            <input type="text" name="sumber_barang" class="form-control" value="<?= htmlspecialchars($d['sumber_barang'] ?? ''); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Satuan</label>
                            <input type="text" name="satuan" class="form-control" value="<?= htmlspecialchars($d['satuan'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Jumlah</label>
                            <input type="number" name="jumlah" class="form-control" value="<?= htmlspecialchars($d['jumlah'] ?? 0); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tujuan</label>
                            <input type="text" name="tujuan" class="form-control" value="<?= htmlspecialchars($d['tujuan'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kondisi</label>
                            <input type="text" name="kondisi" class="form-control" value="<?= htmlspecialchars($d['kondisi'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vendor</label>
                            <input type="text" name="vendor" class="form-control" value="<?= htmlspecialchars($d['vendor'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Berita Acara</label>
                            <input type="text" name="berita_acara" class="form-control" value="<?= htmlspecialchars($d['berita_acara'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" name="update" class="btn btn-warning text-white px-4 fw-bold">Perbarui</button>
                        <a href="ex_bongkaran.php" class="btn btn-secondary px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>