<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

$id = $_GET['id'];

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
    <title>Edit BA | I-CALM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4 mb-5">
    <div class="alert alert-warning shadow-sm mb-4">
        <h3 class="mb-1">✏️ Edit Data Berita Acara</h3>
        <small>I-CALM (Integrated Control and Logistics Monitoring)</small>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label>Jenis Berita Acara</label>
                    <input type="text" name="jenis_berita_acara" class="form-control" value="<?= htmlspecialchars($d['jenis_berita_acara']); ?>">
                </div>

                <div class="mb-3">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= $d['tanggal']; ?>">
                </div>

                <div class="mb-3">
                    <label>Nama Barang</label>
                    <input type="text" name="nama_barang" class="form-control" value="<?= htmlspecialchars($d['nama_barang']); ?>">
                </div>

                <div class="mb-3">
                    <label>Merk / Jenis</label>
                    <input type="text" name="merk_jenis" class="form-control" value="<?= htmlspecialchars($d['merk_jenis']); ?>">
                </div>

                <div class="mb-3">
                    <label>Jenis Barang</label>
                    <input type="text" name="jenis_barang" class="form-control" value="<?= htmlspecialchars($d['jenis_barang']); ?>">
                </div>

                <div class="mb-3">
                    <label>Sumber Barang</label>
                    <input type="text" name="sumber_barang" class="form-control" value="<?= htmlspecialchars($d['sumber_barang']); ?>">
                </div>

                <div class="mb-3">
                    <label>Satuan</label>
                    <input type="text" name="satuan" class="form-control" value="<?= htmlspecialchars($d['satuan']); ?>">
                </div>

                <div class="mb-3">
                    <label>Jumlah</label>
                    <input type="number" name="jumlah" class="form-control" value="<?= $d['jumlah']; ?>">
                </div>

                <div class="mb-3">
                    <label>Nomor Seri</label>
                    <input type="text" name="no_seri" class="form-control" value="<?= htmlspecialchars($d['no_seri']); ?>">
                </div>

                <div class="mb-3">
                    <label>Pemasok / Asal Material</label>
                    <input type="text" name="asal_barang_vendor" class="form-control" value="<?= htmlspecialchars($d['asal_barang_vendor']); ?>">
                </div>

                <div class="mb-3">
                    <label>Kategori Material</label>
                    <select name="kategori_material" class="form-control">
                        <option value="Material Gardu" <?= ($d['kategori_material']=='Material Gardu')?'selected':''; ?>>Material Gardu</option>
                        <option value="Material Proteksi" <?= ($d['kategori_material']=='Material Proteksi')?'selected':''; ?>>Material Proteksi</option>
                        <option value="Material Kabel" <?= ($d['kategori_material']=='Material Kabel')?'selected':''; ?>>Material Kabel</option>
                        <option value="Material Trafo" <?= ($d['kategori_material']=='Material Trafo')?'selected':''; ?>>Material Trafo</option>
                        <option value="Alat Kerja" <?= ($d['kategori_material']=='Alat Kerja')?'selected':''; ?>>Alat Kerja</option>
                        <option value="Alat Uji" <?= ($d['kategori_material']=='Alat Uji')?'selected':''; ?>>Alat Uji</option>
                        <option value="Lainnya" <?= ($d['kategori_material']=='Lainnya')?'selected':''; ?>>Lainnya</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Tujuan</label>
                    <input type="text" name="tujuan" class="form-control" value="<?= htmlspecialchars($d['tujuan']); ?>">
                </div>

                <div class="mb-3">
                    <label>Kondisi Material</label>
                    <select name="kondisi_material" class="form-control">
                        <option value="BAIK" <?= ($d['kondisi_material']=='BAIK')?'selected':''; ?>>BAIK</option>
                        <option value="RUSAK" <?= ($d['kondisi_material']=='RUSAK')?'selected':''; ?>>RUSAK</option>
                        <option value="PERBAIKAN" <?= ($d['kondisi_material']=='PERBAIKAN')?'selected':''; ?>>PERBAIKAN</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="4"><?= htmlspecialchars($d['keterangan']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label>Dokumen / File BA</label>
                    <?php if(!empty($d['file_ba'])){ ?>
                        <div class="mb-2">
                            <a href="../uploads/<?= $d['file_ba']; ?>" target="_blank" class="btn btn-info btn-sm">📄 Lihat File Saat Ini</a>
                        </div>
                    <?php } ?>
                    <input type="file" name="file_ba" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
                    <small class="text-muted">Kosongkan jika tidak ingin mengganti file.</small>
                </div>

                <button type="submit" name="update" class="btn btn-warning">💾 Update Data</button>
                <a href="index.php" class="btn btn-secondary">↩ Kembali</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>