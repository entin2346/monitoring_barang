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

$id = $_GET['id'];

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
</head>
<body class="bg-light">

<div class="container mt-4 mb-5">

    <div class="alert alert-info shadow-sm">
        <h3 class="mb-1">📄 Detail Berita Acara</h3>
        <small>I-CALM (Integrated Control and Logistics Monitoring)</small>
    </div>

    <table class="table table-bordered bg-white shadow-sm">
        <tr>
            <th width="250">Jenis Berita Acara</th>
            <td><?= htmlspecialchars($d['jenis_berita_acara']); ?></td>
        </tr>
        <tr>
            <th>Tanggal</th>
            <td><?= (!empty($d['tanggal']) && $d['tanggal'] != '0000-00-00') ? date('d-m-Y', strtotime($d['tanggal'])) : '-'; ?></td>
        </tr>
        <tr>
            <th>Nama Barang</th>
            <td><?= htmlspecialchars($d['nama_barang']); ?></td>
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
            <td><?= htmlspecialchars($d['satuan']); ?></td>
        </tr>
        <tr>
            <th>Jumlah</th>
            <td><?= number_format($d['jumlah']); ?></td>
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
                    echo "<span class='badge bg-success'>BAIK</span>";
                }elseif(strtoupper($d['kondisi_material']) == 'RUSAK'){
                    echo "<span class='badge bg-danger'>RUSAK</span>";
                }else{
                    echo "<span class='badge bg-warning text-dark'>".$d['kondisi_material']."</span>";
                }
                ?>
            </td>
        </tr>
        <tr>
            <th>No Seri</th>
            <td><?= htmlspecialchars($d['no_seri']); ?></td>
        </tr>
        <tr>
            <th>Vendor</th>
            <td><?= htmlspecialchars($d['asal_barang_vendor']); ?></td>
        </tr>

        <?php if(isset($d['kategori_material'])){ ?>
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
                    <a href="../uploads/<?= urlencode($d['dokumentasi_ba_kembali']); ?>" target="_blank" class="btn btn-info btn-sm">
                        📷 Lihat Dokumentasi
                    </a>
                    <a href="../uploads/<?= urlencode($d['dokumentasi_ba_kembali']); ?>" download class="btn btn-success btn-sm">
                        ⬇ Download
                    </a>
                <?php } else { ?>
                    <span class="text-danger">Belum ada dokumentasi</span>
                <?php } ?>
            </td>
        </tr>

        <tr>
            <th>File Lampiran</th>
            <td>
                <b>Nama File:</b> <?= htmlspecialchars($d['file_ba'] ?? 'NULL'); ?>
                <br><br>
                <?php if(!empty($d['file_ba'])){ ?>
                    <a href="../uploads/<?= urlencode($d['file_ba']); ?>" target="_blank" class="btn btn-primary btn-sm">
                        📄 Lihat File
                    </a>
                    <a href="../uploads/<?= urlencode($d['file_ba']); ?>" download class="btn btn-success btn-sm">
                        ⬇ Download
                    </a>
                <?php } else { ?>
                    <span class="text-danger">Belum ada file lampiran</span>
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
            <td><?= htmlspecialchars($d['tug5']); ?></td>
        </tr>
    </table>

    <div class="mt-3">
        <a href="index.php" class="btn btn-secondary">Kembali</a>

        <a href="edit.php?id=<?= $d['id']; ?>" class="btn btn-warning">
            Edit
        </a>

        <a href="kartu_gantung.php?id=<?= $d['id']; ?>"
           target="_blank"
           class="btn btn-success">
            📄 Cetak Kartu Gantung
        </a>
    </div>

</div>

</body>
</html>